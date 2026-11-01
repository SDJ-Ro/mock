/**
 * Saving without reloading the page, for every page: a change that shows nothing new (the screen already shows the
 * choice — e.g. picking a subject's teacher). Any <form data-background> is sent in the background instead of
 * reloading; the server answers JSON { ok, message } (refuse() already does for a refusal) and the feedback banner
 * says it. Refused → the form's dropdowns go back to what was last saved. Either way the form then sends a
 * 'background:answer' event (detail = the answer), for a page that shows more than the dropdown.
 * A dropdown inside such a form sends it as soon as something is picked. Needs components/_feedback_banner.php.
 */
(function ()
{
  'use strict';

  const dropdownsOf = (form) => [...form.querySelectorAll('.c-dropdown')];
  const saved = new WeakMap();   // form → its dropdowns' values the last time the server said yes

  function remember(form)
  {
    saved.set(form, dropdownsOf(form).map(dropdown => window.getDropdownValue(dropdown.id)));
  }

  function goBack(form)
  {
    const values = saved.get(form) || [];
    dropdownsOf(form).forEach((dropdown, i) => window.setDropdownValue(dropdown.id, values[i] ?? ''));
  }

  document.addEventListener('submit', (event) =>
  {
    const form = event.target;

    if (!form.matches('form[data-background]')) return;

    event.preventDefault();
    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } })
      .then(res => res.json())
      .then(answer =>
      {
        window.showFeedbackBanner(answer.message, answer.ok ? 'success' : 'error');
        answer.ok ? remember(form) : goBack(form);
        form.dispatchEvent(new CustomEvent('background:answer', { bubbles: true, detail: answer }));   // a page may react (People recolours)
      })
      .catch(() => window.location.reload());   // not JSON (e.g. signed out) → the reload shows the sign-in page
  });

  // Picking an option in a background form sends it
  document.addEventListener('dropdown:change', (event) =>
  {
    const form = event.target.closest('form[data-background]');
    if (form) form.requestSubmit();
  });

  document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('form[data-background]').forEach(remember));
})();
