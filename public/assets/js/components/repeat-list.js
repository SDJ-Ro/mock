/**
 * REPEAT LIST: rows a form can add and remove (a team's members, an achievement's participants and photos).
 *   <button data-repeat-add="{list id}"> copies <template id="{list id}-row"> into the list. With
 *   data-repeat-from="{dropdown id}" it adds the dropdown's choice instead (nothing chosen, or already in the list =
 *   nothing added): its name goes into the row's [data-repeat-text], its value into [data-repeat-value].
 *   <button data-repeat-remove> removes its [data-repeat-row].
 */
(function ()
{
  'use strict';

  function addRow(button)
  {
    const list = document.getElementById(button.dataset.repeatAdd);
    const from = button.dataset.repeatFrom ? document.getElementById(button.dataset.repeatFrom) : null;
    const choice = from?.querySelector('.c-dropdown__option[aria-selected="true"]');   // the dropdown's choice (it has no form name)
    const value = choice ? choice.dataset.value : '';

    if (from && (value === '' || [...list.querySelectorAll('[data-repeat-value]')].some((input) => input.value === value)))
    {
      return;
    }

    const row = document.getElementById(`${button.dataset.repeatAdd}-row`).content.firstElementChild.cloneNode(true);

    // Each copy would repeat the template's ids (a dropdown's): an id must be unique, and the copies don't need one
    row.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));

    if (from)
    {
      row.querySelector('[data-repeat-text]').textContent = choice.dataset.label;
      row.querySelector('[data-repeat-value]').value = value;
      list.append(row);
      return;
    }

    list.append(row);
    row.querySelector('input:not([type="hidden"]), .c-dropdown__trigger')?.focus();   // an empty row: ready to fill in
  }

  document.addEventListener('click', (event) =>
  {
    const add    = event.target.closest('[data-repeat-add]');
    const remove = event.target.closest('[data-repeat-remove]');

    if (add)
    {
      addRow(add);
    }

    if (remove)
    {
      remove.closest('[data-repeat-row]').remove();
    }
  });
})();
