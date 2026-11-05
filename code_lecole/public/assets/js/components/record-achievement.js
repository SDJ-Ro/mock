/**
 * Record Achievement pop-up (components/_record_achievement_pop_up.php). PHP draws it; this file only:
 *   - Record (on a student card or banner): puts that student in the pop-up (their id goes with the form), empties
 *     the form (today's date) and opens it
 * The Supporting Proof box is the shared file box (file-box.js). Sending it: AchievementController::record().
 */
(function ()
{
  'use strict';

  const popUp = document.getElementById('j-record-achievement');

  document.addEventListener('click', (event) =>
  {
    const button = event.target.closest('.j-record-open');

    if (!button) return;

    const student = button.closest('.j-student-card').dataset;

    popUp.querySelector('.j-record-form').reset();
    resetDropdown('j-record-achievement-type');
    resetDropdown('j-record-achievement-level');
    setDatepickerValue('j-record-achievement-date', schoolToday);

    popUp.querySelector('.j-record-student').value = student.id;
    document.getElementById('j-record-achievement-title').textContent = `Record Achievement — ${student.name}`;
    popUp.querySelector('.j-record-initials').textContent = student.initials;
    popUp.querySelector('.j-record-name').textContent = student.name;
    popUp.querySelector('.j-record-code').textContent = student.code;
    popUp.querySelector('.j-record-from').textContent = student.from;

    openPopUp(popUp);
  });
})();
