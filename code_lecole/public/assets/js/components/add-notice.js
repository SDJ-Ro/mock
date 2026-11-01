/**
 * Post / edit notice form (components/_add_notice_page.php). The server fills the form in, checks it and saves it;
 * this file only:
 *   - shows the target field that fits the chosen category (Academic → a grade / class, Extracurricular → a club / sport)
 *   - writes the chosen file's name in the file box
 */
(function ()
{
  'use strict';

  const form = document.getElementById('j-add-notice');

  if (!form) return;

  form.addEventListener('dropdown:change', (event) =>
  {
    if (event.target.id !== 'j-add-notice-category') return;

    document.getElementById('j-add-notice-academic').hidden = event.detail.value !== 'Academic';
    document.getElementById('j-add-notice-club').hidden = event.detail.value !== 'Extracurricular';
  });

  form.querySelector('.j-add-notice-file').addEventListener('change', (event) =>
  {
    const name = document.getElementById('j-add-notice-file-name');

    name.textContent = event.target.files[0]?.name ?? name.dataset.empty;
  });
})();
