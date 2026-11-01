/**
 * The parent search (components/_parent_picker.php), on Add Student and in the profile pop-up's Edit Profile.
 * The server does the searching and draws the rows (PeopleAddController::parents(): JSON { ok, html, message });
 * this file only:
 *   1. asks for the list when a picker's search shows (the first 20 parents) and again as the office types (after a short pause)
 *   2. picking a row → its id goes in the hidden field, and the row shows alone with Change Parent
 *   3. Change Parent → the search shows again
 * A picker that arrives later (inside the pop-up) starts through 'content:added'.
 */
(function ()
{
  'use strict';

  const timers = new WeakMap();   // picker → its waiting search

  function search(picker)
  {
    const query = picker.querySelector('.j-parent-query').value.trim();

    fetch(`${picker.dataset.url}?q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' } })
      .then((response) => response.json())
      .then((answer) =>
      {
        picker.querySelector('.j-parent-count').textContent = answer.message;
        if (answer.ok) picker.querySelector('.j-parent-results').innerHTML = answer.html;   // the server's own HTML, escaped (e())
      })
      .catch(() => window.location.reload());   // not JSON (e.g. signed out) → the reload shows the sign-in page
  }

  // Only pickers whose search shows (not one that already has its parent: that one searches on Change Parent)
  function start(root)
  {
    root.querySelectorAll('.j-parent-picker').forEach((picker) =>
    {
      if (!picker.querySelector('.j-parent-search').hidden) search(picker);
    });
  }

  document.addEventListener('input', (event) =>
  {
    const picker = event.target.closest('.j-parent-picker');
    if (!picker || !event.target.matches('.j-parent-query')) return;

    clearTimeout(timers.get(picker));
    timers.set(picker, setTimeout(() => search(picker), 250));
  });

  document.addEventListener('click', (event) =>
  {
    const row = event.target.closest('.j-parent-pick');
    const change = event.target.closest('.j-parent-change');
    const picker = event.target.closest('.j-parent-picker');

    if (row && picker && !row.closest('.j-parent-chosen'))
    {
      const chosen = picker.querySelector('.j-parent-chosen');

      chosen.querySelector('.j-parent-pick')?.remove();
      chosen.prepend(row.cloneNode(true));
      picker.querySelector('.j-parent-id').value = row.dataset.id;
      chosen.hidden = false;
      picker.querySelector('.j-parent-search').hidden = true;
    }

    if (change && picker)
    {
      picker.querySelector('.j-parent-search').hidden = false;
      picker.querySelector('.j-parent-query').focus();
      search(picker);
    }
  });

  document.addEventListener('content:added', (event) => start(event.detail));
  document.addEventListener('DOMContentLoaded', () => start(document));
})();
