/**
 * Achievements timeline (components/_achievements_timeline.php): a filter button shows only its group's items.
 * The CSS hides a year left empty and the line under the last item shown; this file only marks the chosen button,
 * hides the other items and shows "No achievements found…" when nothing is left.
 * One click listener on the page, so a timeline that arrives later (People's profile pop-up) works too.
 */
(function ()
{
  'use strict';

  document.addEventListener('click', (event) =>
  {
    const button = event.target.closest('.j-timeline-filter');

    if (!button) return;

    const timeline = button.closest('.j-timeline');
    const group = button.dataset.group;   // 'all' or a group ('sports'…)
    let shown = 0;

    timeline.querySelectorAll('.j-timeline-filter').forEach((filter) => filter.setAttribute('aria-pressed', String(filter === button)));

    timeline.querySelectorAll('.c-timeline__item').forEach((item) =>
    {
      item.hidden = group !== 'all' && item.dataset.group !== group;
      shown += item.hidden ? 0 : 1;
    });

    timeline.querySelector('.j-timeline-empty').hidden = shown > 0;
  });
})();
