/**
 * The teacher's student cards (components/_student_card.php in #j-student-cards): the search box (#j-student-search)
 * shows only the cards whose name or ID matches, in the browser. Used by teacher/achievements and
 * teacher/term_marks. A page may also have:
 *   #j-student-from        the "Class / Extracurricular" filter (a dropdown; the cards' data-from)
 *   #j-student-cards-empty "No students found", shown instead of the grid when nothing matches
 */
(function ()
{
  'use strict';

  const grid   = document.getElementById('j-student-cards');
  const search = document.getElementById('j-student-search');

  if (!grid || !search) return;

  const empty = document.getElementById('j-student-cards-empty');
  let from    = 'all';

  const filter = () =>
  {
    const words = search.value.trim().toLowerCase();
    let shown   = 0;

    grid.querySelectorAll('.j-student-card').forEach((card) =>
    {
      const fits = (from === 'all' || card.dataset.from === from)
        && `${card.dataset.name} ${card.dataset.code}`.toLowerCase().includes(words);

      card.hidden = !fits;
      shown += fits ? 1 : 0;
    });

    if (empty)
    {
      grid.hidden  = shown === 0;
      empty.hidden = shown > 0;
    }
  };

  search.addEventListener('input', filter);

  document.addEventListener('dropdown:change', (event) =>
  {
    if (event.target.id !== 'j-student-from') return;

    from = event.detail.value;
    filter();
  });
})();
