/**
 * SEND ONCE — a form really being sent turns its buttons off, so a second press (a double click) can't send it twice.
 * Every signed-in page loads it (layouts/page_bottom.php), and the sign-in / sign-up pages.
 * "Really being sent": nothing stopped it (a question, a must-choose dropdown, example data, a background save), and
 * the page is about to change (a form opening a new tab leaves this page as it is).
 */
(function ()
{
  'use strict';

  document.addEventListener('submit', (event) =>
  {
    const form = event.target;

    setTimeout(() =>
    {
      if (event.defaultPrevented || form.getAttribute('target') === '_blank')
      {
        return;
      }

      // getAttribute: a form's .id / .target is its field of that name if it has one
      const formId  = form.getAttribute('id');
      const outside = formId ? [...document.querySelectorAll(`button[type="submit"][form="${formId}"]`)] : [];
      [...form.querySelectorAll('button[type="submit"], input[type="submit"]'), ...outside].forEach((button) => button.disabled = true);
    });
  });
})();
