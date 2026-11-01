/**
 * The People directory (components/_people_directory.php). PHP draws the rows and does the filtering, so this file only:
 *   1. a new grade forgets the chosen class (the filter form is then sent by dropdown.js: data-submit-on-change)
 *   2. colours a row's Account Access pill by what it now says (Active sky, Deactivated terracotta), once the
 *      server has answered (background-save.js saves it and puts it back if refused)
 *   3. a teacher who still has duties can't be switched off: the answer brings the handover's address, and the
 *      handover pop-up opens (people-profile.js fills it)
 */
(function ()
{
  'use strict';

  document.addEventListener('dropdown:change', (e) =>
  {
    const form = e.target.closest('.j-people-filters');
    if (!form) return;

    if (e.detail.id === 'j-people-grade' && form.elements.class) form.elements.class.value = '';
  });

  document.addEventListener('background:answer', (e) =>
  {
    const status = e.target.querySelector('.c-dropdown--status');
    if (!status) return;

    const on = window.getDropdownValue(status.id) === 'on';
    status.classList.toggle('c-dropdown--active', on);
    status.classList.toggle('c-dropdown--deactivated', !on);

    if (e.detail.handover) window.showInPopUp(e.detail.handover, 'j-teacher-handover');
  });
})();
