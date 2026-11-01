/**
 * Date picker (components/_datepicker.php). The browser opens and closes the calendar (a popover); this file fills
 * it in (the month, the 42 days, the year list, the limits), places it under the button and saves the chosen day.
 * It listens once on the whole page, so a date picker added later (e.g. inside a pop-up) works without any setup.
 * Today and the button's text come from school-dates.js (the school's settings, decided by PHP), never from the
 * computer's clock or language, so they always match what PHP writes.
 * Every choice tells the page with a 'datepicker:change' event (detail: value 'YYYY-MM-DD').
 * Needs dropdown.js on the page (getSafeViewportBounds: where a pop-up may sit).
 *
 * For other scripts:
 *   setDatepickerValue(id, iso)  → show this day ('YYYY-MM-DD'), or '' for nothing chosen (the placeholder again)
 *   refreshDatepicker(id)        → read the limits again (People's "unlock age range" sets data-unlocked="true")
 */
(function ()
{
  'use strict';

  const FIRST_YEAR = 1940;   // without limits the year list runs from 1940 to 5 years ahead
  const YEARS_AHEAD = 5;

  // A Date ↔ 'YYYY-MM-DD', in local time (so a day never shifts by the time zone)
  const toIso = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  const fromIso = (iso) => new Date(Number(iso.slice(0, 4)), Number(iso.slice(5, 7)) - 1, Number(iso.slice(8, 10)));


  /* =========================================================================
     LIMITS
     ========================================================================= */
  // The school's today, the first and last day allowed ('' = no limit): data-min-date / data-max-date,
  // or an age range turned into dates (unless the range was unlocked), and the footer's hint
  function limitsOf(root)
  {
    const data = root.dataset;
    const locked = data.unlocked !== 'true';
    const thisYear = Number(window.schoolToday.slice(0, 4));
    const minAge = locked && data.minAge ? Number(data.minAge) : null;
    const maxAge = locked && data.maxAge ? Number(data.maxAge) : null;

    return {
      today: window.schoolToday,
      first: data.minDate || (maxAge !== null ? `${thisYear - maxAge}-01-01` : ''),
      last: data.maxDate || (minAge !== null ? `${thisYear - minAge}-12-31` : ''),
      hint: minAge !== null || maxAge !== null ? `Restricted to ${minAge ?? 0}–${maxAge} yrs` : 'Select a date'
    };
  }

  // The years the year menu offers: [lowest, highest]
  function yearRange(limits)
  {
    return [
      limits.first ? Number(limits.first.slice(0, 4)) : FIRST_YEAR,
      limits.last ? Number(limits.last.slice(0, 4)) : Number(limits.today.slice(0, 4)) + YEARS_AHEAD
    ];
  }

  const isAllowed = (iso, limits) => (!limits.first || iso >= limits.first) && (!limits.last || iso <= limits.last);

  /* =========================================================================
     DRAWING THE CALENDAR
     ========================================================================= */
  // Show this month (kept on the calendar as data-shown = its 1st day), never outside the allowed years
  function showMonth(root, year, month)
  {
    const [lowest, highest] = yearRange(limitsOf(root));

    root.querySelector('.c-datepicker__calendar').dataset.shown = toIso(new Date(Math.min(Math.max(year, lowest), highest), month, 1));
    draw(root);
  }

  function draw(root)
  {
    const shown = fromIso(root.querySelector('.c-datepicker__calendar').dataset.shown);
    const limits = limitsOf(root);
    const chosen = root.querySelector('.j-datepicker-input').value;
    const today = limits.today;

    // Header: the month and year buttons, both menus closed
    root.querySelectorAll('.j-datepicker-month-menu [data-month]').forEach((item) =>
    {
      const isShown = Number(item.dataset.month) === shown.getMonth();

      item.classList.toggle('c-datepicker__menu-item--selected', isShown);
      if (isShown) root.querySelector('.j-datepicker-month').textContent = item.textContent;
    });
    root.querySelector('.j-datepicker-year').textContent = shown.getFullYear();
    fillYears(root, shown.getFullYear(), limits);
    root.querySelectorAll('.c-datepicker__menu').forEach((menu) => menu.hidden = true);

    // The 42 days, starting on the Monday on or before the 1st
    const start = new Date(shown.getFullYear(), shown.getMonth(), 1 - (shown.getDay() + 6) % 7);

    root.querySelectorAll('.j-datepicker-days > button').forEach((button, i) =>
    {
      const day = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
      const iso = toIso(day);

      button.textContent = day.getDate();
      button.dataset.iso = iso;
      button.disabled = !isAllowed(iso, limits);
      button.classList.toggle('c-datepicker__day--outside', day.getMonth() !== shown.getMonth());
      button.classList.toggle('c-datepicker__day--today', iso === today);
      button.classList.toggle('c-datepicker__day--selected', iso === chosen);
    });

    root.querySelector('.j-datepicker-hint').textContent = limits.hint;
    root.querySelector('.j-datepicker-today').disabled = !isAllowed(today, limits);
  }

  // The year menu: the allowed years, newest first (a date of birth is near the top)
  function fillYears(root, shownYear, limits)
  {
    const [lowest, highest] = yearRange(limits);
    const years = Array.from({ length: Math.max(1, highest - lowest + 1) }, (_, i) => highest - i);

    root.querySelector('.j-datepicker-year-menu').replaceChildren(...years.map((year) =>
    {
      const item = document.createElement('button');

      item.type = 'button';
      item.className = 'c-datepicker__menu-item';
      item.classList.toggle('c-datepicker__menu-item--selected', year === shownYear);
      item.dataset.year = year;
      item.textContent = year;
      return item;
    }));
  }

  // Under the button, or above it when there is no room below; never past the window's edges or over the sidebar
  function place(root)
  {
    const calendar = root.querySelector('.c-datepicker__calendar');
    const button = root.querySelector('.c-datepicker__trigger').getBoundingClientRect();
    const bounds = window.getSafeViewportBounds();
    const left = button.left + calendar.offsetWidth > bounds.maxRight ? button.right - calendar.offsetWidth : button.left;
    const below = button.bottom + 6;

    calendar.style.left = `${Math.max(bounds.minLeft, left)}px`;
    calendar.style.top = `${below + calendar.offsetHeight > bounds.maxBottom ? button.top - 6 - calendar.offsetHeight : below}px`;
  }

  /* =========================================================================
     CHOOSING
     ========================================================================= */
  // Put a day into the hidden field and the button ('' = nothing chosen)
  function showValue(root, iso)
  {
    const label = root.querySelector('.j-datepicker-label');

    root.querySelector('.j-datepicker-input').value = iso;
    label.textContent = iso ? window.formatSchoolDate(iso, 'date') : root.querySelector('.c-datepicker__trigger').dataset.placeholder;
    label.classList.toggle('c-datepicker__label--empty', !iso);
  }

  function choose(root, iso)
  {
    showValue(root, iso);
    root.querySelector('.c-datepicker__calendar').hidePopover();   // the browser gives the focus back to the button
    root.dispatchEvent(new CustomEvent('datepicker:change', { bubbles: true, detail: { value: iso } }));
  }

  // The month / year button opens its menu (and closes the other one)
  function toggleMenu(root, menu)
  {
    const wasOpen = !menu.hidden;

    root.querySelectorAll('.c-datepicker__menu').forEach((each) => each.hidden = true);
    menu.hidden = wasOpen;

    // The chosen year in the middle of the year list (scrolling only the list, never the page)
    const selected = menu.querySelector('.c-datepicker__menu-item--selected');

    if (!wasOpen && selected) menu.scrollTop = selected.offsetTop - (menu.clientHeight - selected.offsetHeight) / 2;
  }

  /* =========================================================================
     LISTENING (once, for every date picker on the page)
     ========================================================================= */
  // The calendar has just opened: show the chosen day's month (or this month), then place it.
  // ('toggle' doesn't bubble, so it is caught on its way down: the third argument, true)
  document.addEventListener('toggle', (event) =>
  {
    if (!event.target.matches?.('.c-datepicker__calendar') || event.newState !== 'open') return;

    const root = event.target.closest('.c-datepicker');
    const start = fromIso(root.querySelector('.j-datepicker-input').value || window.schoolToday);

    showMonth(root, start.getFullYear(), start.getMonth());
    place(root);
  }, true);

  // Scrolling the page moves the button away from its open calendar, so the calendar closes (like the dropdown's
  // menus). Scrolling inside a calendar (its year list) doesn't count.
  document.addEventListener('scroll', (event) =>
  {
    if (event.target.closest?.('.c-datepicker__calendar')) return;

    document.querySelectorAll('.c-datepicker__calendar:popover-open').forEach((calendar) => calendar.hidePopover());
  }, true);

  document.addEventListener('click', (event) =>
  {
    const button = event.target.closest('.c-datepicker__calendar button');

    if (!button) return;

    const root = button.closest('.c-datepicker');
    const shown = fromIso(root.querySelector('.c-datepicker__calendar').dataset.shown);

    if (button.dataset.iso) choose(root, button.dataset.iso);
    else if (button.matches('.j-datepicker-today')) choose(root, window.schoolToday);
    else if (button.matches('.j-datepicker-prev')) showMonth(root, shown.getFullYear(), shown.getMonth() - 1);
    else if (button.matches('.j-datepicker-next')) showMonth(root, shown.getFullYear(), shown.getMonth() + 1);
    else if (button.dataset.month !== undefined) showMonth(root, shown.getFullYear(), Number(button.dataset.month));
    else if (button.dataset.year !== undefined) showMonth(root, Number(button.dataset.year), shown.getMonth());
    else if (button.matches('.j-datepicker-month-btn')) toggleMenu(root, root.querySelector('.j-datepicker-month-menu'));
    else if (button.matches('.j-datepicker-year-btn')) toggleMenu(root, root.querySelector('.j-datepicker-year-menu'));
  });

  /* =========================================================================
     FOR OTHER SCRIPTS
     ========================================================================= */
  function setDatepickerValue(id, iso)
  {
    const root = document.getElementById(id);

    showValue(root, iso || '');
    refreshDatepicker(id);
  }

  // Redraw an open calendar (e.g. after its limits changed)
  function refreshDatepicker(id)
  {
    const root = document.getElementById(id);
    const calendar = root.querySelector('.c-datepicker__calendar');

    if (!calendar.matches(':popover-open')) return;

    const shown = fromIso(calendar.dataset.shown);

    showMonth(root, shown.getFullYear(), shown.getMonth());
  }

  window.setDatepickerValue = setDatepickerValue;
  window.refreshDatepicker = refreshDatepicker;
})();
