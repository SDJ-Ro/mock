/**
 * People's Add page (components/_add_person_page.php). PHP draws the form and the server checks it; the parent part
 * switches with CSS (add-person.css), so this file only shows the chosen photo's name in its bar
 * ("No file chosen" again when the choice is cancelled).
 */
(function ()
{
  'use strict';

  document.addEventListener('change', (event) =>
  {
    if (!event.target.matches('.j-photo-input')) return;

    const file = event.target.files[0];
    event.target.closest('.c-field__photo').querySelector('.j-photo-name').textContent = file ? file.name : 'No file chosen';
  });
})();
