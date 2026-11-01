/**
 * Calendar "Add event" pop-up (components/calendar/_event_editor.php)
 * Only on pages where the user may add events. calendar.js calls window.calendarEditor.open(event or null)
 * and window.calendarEditor.remove(event).
 * The form is sent as it is (its fields are named as CalendarController reads them); every answer brings the fresh
 * events, which go back to calendar.js. The server checks everything again; the checks here only save a round trip.
 */
(function ()
{
  'use strict';

  document.addEventListener('DOMContentLoaded', function ()
  {
    const popUp = document.getElementById('j-event-editor');
    const form = document.getElementById('j-event-form');
    if (!popUp || !form) return;

    const byId = (id) => document.getElementById(id);
    const gradeRadio = byId('j-academic-level-grade');   // missing for teachers (classes only)
    const firstCategory = byId('j-field-category').querySelector('[data-value]').dataset.value;   // config/calendar.php's default
    let editing = null;                                  // the event being edited, null when adding

    /* =========================================================================
       SECTION 1: WHO THE EVENT IS FOR (category → which part of the form shows)
       ========================================================================= */
    function showScopeFor(category)
    {
      byId('j-scope-academic-section').hidden = category !== 'Academic';
      byId('j-scope-extracurricular-section').hidden = category !== 'Extracurricular';
    }

    function showAcademicLevel(level)
    {
      if (!gradeRadio) return;
      gradeRadio.checked = level === 'grade';
      byId('j-academic-level-class').checked = level === 'class';
      byId('j-academic-grades-container').hidden = level !== 'grade';
      byId('j-academic-classes-container').hidden = level !== 'class';
    }

    // A new category also picks its default audience: General → everyone, otherwise students + parents
    byId('j-field-category').addEventListener('dropdown:change', (e) =>
    {
      showScopeFor(e.detail.value);
      window.setDropdownMultiValues('j-field-audience', e.detail.value === 'General' ? ['all'] : ['student', 'parent']);
    });

    form.querySelectorAll('input[name="academic_level"][type="radio"]').forEach(radio =>
    {
      radio.addEventListener('change', () => showAcademicLevel(radio.value));
    });

    /* =========================================================================
       SECTION 2: MESSAGES IN THE FORM
       ========================================================================= */
    function showError(message)
    {
      const banner = byId('j-event-form-error-banner');
      banner.textContent = message;
      banner.hidden = !message;
    }

    function clearMessages()
    {
      showError('');
      byId('j-field-title-error').hidden = true;
      byId('j-field-time-error').hidden = true;
      byId('j-event-form-warning-banner').hidden = true;
    }

    // Only when the pop-up opens: a ticked "Allow parallel schedule" must survive the next Save
    function resetParallel()
    {
      byId('j-parallel-container').hidden = true;
      byId('j-field-allow-parallel').checked = false;
    }

    /* =========================================================================
       SECTION 3: TIMES ("All Day" = no start time, then no end time either)
       ========================================================================= */
    // Each time is two dropdowns: hour (00–23) and minute (00–59) → "09:30". Start hour '' = all day.
    function timeOf(which)
    {
      const hour = window.getDropdownValue(`j-${which}-hour`);
      return hour === '' ? '' : `${hour}:${window.getDropdownValue(`j-${which}-minute`)}`;
    }

    // "14:30" → the two dropdowns ('' → All day / nothing changed)
    function setTime(which, time)
    {
      const [hour, minute] = time ? time.split(':') : ['', '00'];
      window.setDropdownValue(`j-${which}-hour`, hour);
      window.setDropdownValue(`j-${which}-minute`, minute);
    }

    // End must be after start ("09:30" < "10:00" works as text because both are HH:MM). All day is always fine.
    function timesAreValid()
    {
      const start = timeOf('start');
      const ok = start === '' || timeOf('end') > start;
      byId('j-field-time-error').hidden = ok;
      return ok;
    }

    // All day → only the start hour shows. A start hour → its minutes and End Time show too.
    function showTimes()
    {
      const allDay = timeOf('start') === '';
      ['j-start-colon', 'j-start-minute-wrap', 'j-time-separator', 'j-time-end-col'].forEach(id => byId(id).hidden = allDay);
    }

    // End = one hour after the start (23:xx → 23:59)
    function endOneHourLater()
    {
      const start = timeOf('start');
      if (start === '') return;

      const [hour, minute] = start.split(':');
      setTime('end', hour < '23' ? `${String(Number(hour) + 1).padStart(2, '0')}:${minute}` : '23:59');
    }

    // A new start moves End to one hour later (then End can be changed)
    ['j-start-hour', 'j-start-minute'].forEach(id => byId(id).addEventListener('dropdown:change', () =>
    {
      endOneHourLater();
      showTimes();
      timesAreValid();
    }));
    ['j-end-hour', 'j-end-minute'].forEach(id => byId(id).addEventListener('dropdown:change', timesAreValid));

    /* =========================================================================
       SECTION 4: OPEN (empty for a new event, filled for an existing one)
       ========================================================================= */
    function open(ev)
    {
      editing = ev;
      const category = ev ? ev.category : firstCategory;
      const date = ev ? ev.date : window.calendarApi.selectedDate();
      const scopes = ev ? ev.scopes : [];
      const ids = (type) => scopes.filter(s => s.type === type).map(s => String(s.id));

      byId('j-event-editor-title').textContent = ev ? 'Edit Event' : 'Add Event';
      byId('j-event-form-submit-label').textContent = ev ? 'Save Changes' : 'Save Event';
      byId('j-event-editor-eyebrow').textContent = window.calendarApi.formatDate(date, true);
      byId('j-event-editor-delete-btn').hidden = !ev;

      byId('j-field-id').value = ev ? ev.id : '';
      byId('j-field-date').value = window.calendarApi.formatIsoDate(date);
      byId('j-field-title').value = ev ? ev.title : '';
      byId('j-field-details').value = ev ? ev.details : '';

      window.setDropdownValue('j-field-category', category);
      showScopeFor(category);
      showAcademicLevel(ids('class').length > 0 ? 'class' : 'grade');   // a new event starts on "Entire grade(s)"
      window.setDropdownMultiValues('j-select-academic-grades', ids('grade'));
      window.setDropdownMultiValues('j-select-academic-classes', ids('class'));
      window.setDropdownMultiValues('j-select-extracurriculars', ids('extracurricular'));
      window.setDropdownMultiValues('j-field-audience', ev ? ev.audience : (category === 'General' ? ['all'] : ['student', 'parent']));

      setTime('start', ev ? ev.start : '');   // a new event starts as "All Day"
      if (ev && ev.end)
      {
        setTime('end', ev.end);
      }
      else
      {
        endOneHourLater();
      }
      showTimes();

      clearMessages();
      resetParallel();
      window.openPopUp(popUp);
    }

    /* =========================================================================
       SECTION 5: SEND TO THE SERVER
       ========================================================================= */
    // POST a form's values; the answer is always JSON. Not JSON (e.g. signed out) → reload to the sign-in page.
    function send(url, data)
    {
      const only = document.getElementById('j-calendar').dataset.only;   // a programme's page: only its events come back

      if (only) data.append('only', only);

      return fetch(url, { method: 'POST', body: data, headers: { Accept: 'application/json' } })
        .then(res => res.json())
        .catch(() =>
        {
          window.location.reload();
          return null;
        });
    }

    // Success: close, give calendar.js the fresh events (the new / changed event shows in the calendar straight away)
    // and say so with the page's sliding banner ("Event saved." / "Event deleted.")
    function done(answer)
    {
      window.closePopUp(popUp);
      window.calendarApi.setEvents(answer.events, answer.date);
      window.showFeedbackBanner(answer.message);
    }

    form.addEventListener('submit', (e) =>
    {
      e.preventDefault();
      clearMessages();

      const titleMissing = byId('j-field-title').value.trim() === '';
      byId('j-field-title-error').hidden = !titleMissing;
      if (!timesAreValid() || titleMissing) return;

      const submitBtn = form.querySelector('button[type="submit"]');
      submitBtn.disabled = true;

      send(form.action, new FormData(form)).then(answer =>
      {
        submitBtn.disabled = false;
        if (!answer) return;

        if (answer.ok)
        {
          done(answer);
        }
        else if (answer.clash)
        {
          // A clash is only a warning: tick "Allow parallel schedule" and save again
          const warning = byId('j-event-form-warning-banner');
          warning.textContent = answer.message;
          warning.hidden = false;
          byId('j-parallel-container').hidden = false;
          warning.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        else
        {
          showError(answer.message);
          byId('j-field-title-error').hidden = !answer.errors.title;
        }
      });
    });

    /* =========================================================================
       SECTION 6: DELETE (after the shared "Are you sure?" pop-up)
       ========================================================================= */
    function remove(ev)
    {
      window.askQuestion({
        title: `Delete '${ev.title}'?`,
        text: 'This event will be permanently removed from the school calendar schedule.',
        button: 'Delete Event',
        icon: 'trash',
        tone: 'danger',
        onConfirm: () =>
        {
          const data = new FormData();
          data.append('_csrf_token', form.querySelector('[name="_csrf_token"]').value);
          data.append('id', ev.id);

          // (A refused delete changes nothing; the buttons only show for events this user may delete)
          send(form.dataset.deleteUrl, data).then(answer =>
          {
            if (answer?.ok) done(answer);
          });
        }
      });
    }

    byId('j-event-editor-delete-btn').addEventListener('click', () =>
    {
      window.closePopUp(popUp);
      remove(editing);
    });

    window.calendarEditor = { open: open, remove: remove };
  });
})();
