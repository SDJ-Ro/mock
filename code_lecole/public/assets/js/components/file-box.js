/**
 * File box (components/_file_box.php). The browser opens the file chooser (the box is the input's label); this file:
 *   - shows the chosen (or dropped) file's name and size in the pill under the box
 *   - puts a file dropped on the box into its file input
 *   - hides the pill again when the form is emptied (form.reset(): opening the Record pop-up, after sending)
 * It listens once on the whole page, so every file box works without any setup.
 */
(function ()
{
  'use strict';

  // A chosen file: "Certificate.pdf (1.2 MB)" under the box
  document.addEventListener('change', (event) =>
  {
    if (!event.target.matches('.j-file-box-input')) return;

    const file = event.target.files[0];
    const name = event.target.closest('.j-file-box').querySelector('.j-file-box-name');

    name.hidden = !file;
    name.textContent = file ? `${file.name} (${(file.size / 1048576).toFixed(1)} MB)` : '';
  });

  // A file dropped on the box goes into its file input
  document.addEventListener('dragover', (event) =>
  {
    if (event.target.closest('.j-file-box')) event.preventDefault();
  });

  document.addEventListener('drop', (event) =>
  {
    const box = event.target.closest('.j-file-box');

    if (!box) return;

    event.preventDefault();
    const input = box.querySelector('.j-file-box-input');
    input.files = event.dataTransfer.files;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // An emptied form: no file chosen any more, so no pill
  document.addEventListener('reset', (event) =>
  {
    event.target.querySelectorAll('.j-file-box-name').forEach((name) =>
    {
      name.hidden = true;
      name.textContent = '';
    });
  });
})();
