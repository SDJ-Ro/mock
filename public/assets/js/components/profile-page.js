/**
 * My Profile (components/_profile_page.php). The server draws the page, checks the forms and saves them; this file only:
 *   - Edit Profile: shows a box in each card the person may change (data-editing="true", info-card.css) and the
 *     Save / Cancel row; pressed again, or Cancel, puts the saved values back (the form's reset) and stops editing
 *   - Change Password: opens / closes the password form
 *   - a photo chosen on the hero's change-photo button (shown while editing) is sent straight away
 */
(function ()
{
  'use strict';

  const page       = document.getElementById('j-profile-page');
  const editButton = document.getElementById('j-profile-edit-btn');
  const form       = document.getElementById('j-profile-form');
  const pwdButton  = document.getElementById('j-profile-pwd-toggle');
  const pwdForm    = document.getElementById('j-profile-pwd');

  if (!page) return;

  const setEditing = (on) =>
  {
    page.dataset.editing = String(on);
    editButton.setAttribute('aria-pressed', String(on));
    editButton.querySelector('span').textContent = on ? 'Cancel' : 'Edit Profile';
  };

  editButton?.addEventListener('click', () =>
  {
    if (page.dataset.editing === 'true')
    {
      form.reset();   // its 'reset' event (below) stops editing
    }
    else
    {
      setEditing(true);
    }
  });

  form?.addEventListener('reset', () => setEditing(false));

  pwdButton?.addEventListener('click', () =>
  {
    pwdForm.hidden = !pwdForm.hidden;
    pwdButton.textContent = pwdForm.hidden ? 'Change Password' : 'Cancel';
  });

  // A photo chosen on the hero's change-photo button is sent at once (the server checks it and reloads the page)
  page.querySelector('.j-profile-photo')?.addEventListener('change', (event) =>
  {
    if (event.target.files.length > 0) event.target.form.submit();
  });
})();
