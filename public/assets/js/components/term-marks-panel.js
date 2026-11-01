/**
 * Marks panels (components/_term_marks_panel.php) on the teacher's Term Marks page (#j-term-marks).
 *  - A card's "Edit Marks" shows that student's panel (data-panel), rings the card and splits the page (--open).
 *  - ×, Cancel and Esc close it; what was typed and not submitted goes back (form.reset()).
 *  - The subject filter shows one subject's row (or All).
 *  - Submit saves in the background (TermMarkController::save()). Saved: what was typed becomes what reset() goes
 *    back to, the bar's "x of y complete" follows and the sliding banner says so; refused: the banner says why.
 */
(function ()
{
  'use strict';

  const page = document.getElementById('j-term-marks');

  if (!page) return;

  let open = null;   // the panel shown now

  function close()
  {
    if (!open) return;

    const button = document.querySelector(`.j-marks-open[data-panel="${open.id}"]`);

    open.querySelector('.j-marks-form').reset();
    open.hidden = true;
    button.setAttribute('aria-expanded', 'false');
    button.closest('.j-student-card').classList.remove('c-student-card--editing');
    page.classList.remove('l-term-marks--open');
    open = null;
  }

  function show(button)
  {
    close();

    open = document.getElementById(button.dataset.panel);
    window.setDropdownValue(`${open.id}-subject`, 'all');
    open.querySelectorAll('.j-marks-row').forEach((row) => row.toggleAttribute('hidden', false));
    open.hidden = false;
    button.setAttribute('aria-expanded', 'true');
    button.closest('.j-student-card').classList.add('c-student-card--editing');
    page.classList.add('l-term-marks--open');

    // On a narrow screen the panel is under the cards
    if (window.innerWidth <= 960)
    {
      open.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  page.addEventListener('click', (event) =>
  {
    const opener = event.target.closest('.j-marks-open');

    if (opener)
    {
      show(opener);
    }
    else if (event.target.closest('.j-marks-close'))
    {
      close();
    }
  });

  // Esc closes the panel, but not while a dropdown's menu is open (then Esc only closes the menu). Checked in the
  // capture phase, before dropdown.js closes that menu.
  document.addEventListener('keydown', (event) =>
  {
    if (event.key === 'Escape' && !document.querySelector('.c-dropdown.c-is-open')) close();
  }, true);

  page.addEventListener('dropdown:change', (event) =>
  {
    if (!open || event.detail.id !== `${open.id}-subject`) return;

    open.querySelectorAll('.j-marks-row').forEach((row) => row.toggleAttribute('hidden', event.detail.value !== 'all' && row.dataset.subject !== event.detail.value));
  });

  page.addEventListener('submit', (event) =>
  {
    const form = event.target;

    if (!form.matches('.j-marks-form')) return;   // the filter and the publish button send normal forms

    event.preventDefault();
    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } })
      .then((response) => response.json())
      .then((answer) =>
      {
        window.showFeedbackBanner(answer.message, answer.ok ? 'success' : 'error');
        if (!answer.ok) return;

        for (const field of form.querySelectorAll('input, textarea'))
        {
          field.defaultValue = field.value;
        }

        document.getElementById('j-marks-complete')?.replaceChildren(String(answer.complete));
      })
      .catch(() => window.location.reload());
  });
})();
