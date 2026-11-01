/**
 * Record Achievement pop-up (components/_record_achievement_pop_up.php). PHP draws it; this file only:
 *   - Record (on a student card or banner): puts that student in the pop-up, empties the form (today's date) and opens it
 *   - shows the chosen (or dropped) file's name and size in the file box
 * Sending: nothing is saved yet — the form's data-mock-message (feedback-banner.js) closes it and says so.
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
    setDatepickerValue('j-record-achievement-date', schoolToday);
    popUp.querySelector('.j-record-file-name').hidden = true;

    document.getElementById('j-record-achievement-title').textContent = `Record Achievement — ${student.name}`;
    popUp.querySelector('.j-record-initials').textContent = student.initials;
    popUp.querySelector('.j-record-name').textContent = student.name;
    popUp.querySelector('.j-record-code').textContent = student.code;
    popUp.querySelector('.j-record-from').textContent = student.from;

    openPopUp(popUp);
  });

  // A chosen file: "Certificate.pdf (1.2 MB)" under the box
  document.addEventListener('change', (event) =>
  {
    if (!event.target.matches('.j-record-file')) return;

    const file = event.target.files[0];
    const name = event.target.closest('.c-field__file').querySelector('.j-record-file-name');

    name.hidden = !file;
    name.textContent = file ? `${file.name} (${(file.size / 1048576).toFixed(1)} MB)` : '';
  });

  // A file dropped on the box goes into its file input
  document.addEventListener('dragover', (event) =>
  {
    if (event.target.closest('.c-fields--sand .c-field__file')) event.preventDefault();
  });

  document.addEventListener('drop', (event) =>
  {
    const box = event.target.closest('.c-fields--sand .c-field__file');

    if (!box) return;

    event.preventDefault();
    const input = box.querySelector('.j-record-file');
    input.files = event.dataTransfer.files;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
})();
