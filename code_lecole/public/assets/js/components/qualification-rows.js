/**
 * A teacher's qualification rows (components/_qualification_rows.php), on Add Teacher and in the profile pop-up.
 *   + Add Qualification → a copy of the first row, emptied, at the end
 *   Remove              → that row goes (the last one left is only emptied, so there is always a row to type in)
 * Click listeners on the page, so rows that arrive later (inside the pop-up) work too.
 */
(function ()
{
  'use strict';

  document.addEventListener('click', (event) =>
  {
    const add = event.target.closest('.j-qualification-add');
    const remove = event.target.closest('.j-qualification-remove');

    if (add)
    {
      const list = add.closest('.j-qualifications').querySelector('.j-qualification-list');
      const row = list.querySelector('.j-qualification-row').cloneNode(true);

      row.querySelectorAll('input').forEach((input) => { input.value = ''; });
      list.append(row);
      row.querySelector('input').focus();
    }

    if (remove)
    {
      const row = remove.closest('.j-qualification-row');

      if (row.parentElement.children.length > 1)
      {
        row.remove();
      }
      else
      {
        row.querySelectorAll('input').forEach((input) => { input.value = ''; });
      }
    }
  });
})();
