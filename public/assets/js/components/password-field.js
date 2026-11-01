/**
 * Password field (components/_password_field.php): the eye shows / hides what was typed in its box.
 * One listener for the whole page, so it also works in pop-ups (forgot password).
 */
(function ()
{
  'use strict';

  document.addEventListener('click', (event) =>
  {
    const eye = event.target.closest('.c-password-field__eye');

    if (!eye) return;

    const input     = eye.parentElement.querySelector('input');
    const isShowing = input.type === 'text';

    input.type = isShowing ? 'password' : 'text';
    eye.querySelector('use').setAttribute('href', isShowing ? '#icon-eye' : '#icon-eyeOff');
    eye.setAttribute('aria-label', isShowing ? 'Show password' : 'Hide password');
  });
})();
