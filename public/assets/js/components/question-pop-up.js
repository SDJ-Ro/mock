/**
 * "Are you sure?" pop-up (components/_question_pop_up.php), for every page.
 * Any script asks a question like this:
 *   window.askQuestion({
 *     title: "Delete 'Science Fair'?", text: 'This cannot be undone.', button: 'Delete Event',
 *     buttonIcon: 'logOut' (optional, a small icon before the button's words), cancel: 'Cancel' (optional),
 *     icon: 'trash', tone: 'danger', wide: true (optional: two equal-width buttons, the log-out look),
 *     onConfirm: () => { …do it… }
 *   });
 * tone: 'danger' (maroon), 'warning' (terracotta), 'success' (moss). Cancel, ×, Esc and a click outside just close it.
 * Two optional extras (every delete on the site can use them):
 *   blocked: 'reason' → the reason in a red box, and the main button can't be pressed ("6-A still has 4 students")
 *   extra: a <template> id → its content goes inside (e.g. the grade / class choices); if it has checkboxes,
 *          the main button works only once one is ticked; a `required` box (e.g. a reason) must be filled first
 * A form can ask first without any script of its own: give it data-question-title, data-question-text and
 * data-question-button (optional: data-question-icon, data-question-tone, data-question-blocked, data-question-extra).
 * Sending it opens the question; "yes" sends it. The extra's fields are sent with that form (form="its id").
 * Opening / closing: pop-up.js.
 */
(function ()
{
  'use strict';

  let confirmAction = null;   // what the main button does for the question that is open now

  // The main button works unless the question is blocked, its extra has checkboxes and none is ticked, or a
  // required box in it is still empty
  function updateConfirm()
  {
    const boxes = [...document.querySelectorAll('#j-question-extra input[type="checkbox"]')];
    const empty = [...document.querySelectorAll('#j-question-extra [required]')].some(field => field.value.trim() === '');
    const blocked = !document.getElementById('j-question-blocked').hidden;

    document.getElementById('j-question-confirm').disabled = blocked || empty || (boxes.length > 0 && !boxes.some(box => box.checked));
  }

  function askQuestion({ title, text, button, buttonIcon = '', cancel = 'Cancel', icon = 'trash', tone = 'danger', wide = false, blocked = '', extra = '', onConfirm })
  {
    const popUp = document.getElementById('j-question');
    const confirmIcon = document.getElementById('j-question-button-icon');
    const blockedBox = document.getElementById('j-question-blocked');
    const template = extra ? document.getElementById(extra) : null;

    document.getElementById('j-question-title').textContent = title;
    document.getElementById('j-question-text').textContent = text;
    document.getElementById('j-question-extra').replaceChildren(...(template ? [template.content.cloneNode(true)] : []));
    blockedBox.querySelector('span').textContent = blocked;
    blockedBox.hidden = blocked === '';
    updateConfirm();
    document.getElementById('j-question-button-text').textContent = button;
    document.getElementById('j-question-cancel').textContent = cancel;
    document.getElementById('j-question-icon').setAttribute('href', `#icon-${icon}`);
    confirmIcon.querySelector('use').setAttribute('href', `#icon-${buttonIcon}`);
    confirmIcon.toggleAttribute('hidden', buttonIcon === '');   // an <svg> has no .hidden property, so set the attribute

    popUp.classList.remove('c-question--danger', 'c-question--warning', 'c-question--success');
    popUp.classList.add(`c-question--${tone}`);
    popUp.classList.toggle('c-question--wide', wide);

    confirmAction = onConfirm;
    window.openPopUp(popUp);
  }

  // A form with data-question-title asks before it is sent (e.g. a notice's delete form)
  document.addEventListener('submit', (event) =>
  {
    const form = event.target;

    if (!form.dataset.questionTitle || form.dataset.questionAsked === 'yes') return;

    event.preventDefault();
    askQuestion({
      title: form.dataset.questionTitle,
      text: form.dataset.questionText,
      button: form.dataset.questionButton,
      icon: form.dataset.questionIcon,     // not set → the default (trash, danger)
      tone: form.dataset.questionTone,
      blocked: form.dataset.questionBlocked,
      extra: form.dataset.questionExtra,
      // Send it again, marked as answered, so it doesn't ask twice. requestSubmit() fires 'submit' at once (an
      // example-data form shows its message then: feedback-banner.js), so the mark is removed right after
      onConfirm: () =>
      {
        form.dataset.questionAsked = 'yes';
        form.requestSubmit();
        delete form.dataset.questionAsked;
      }
    });

    // The extra's own fields (e.g. a reason) are sent with this form. getAttribute: a form's .id is its field named "id" if it has one
    document.querySelectorAll('#j-question-extra [name]:not([form])').forEach(field => field.setAttribute('form', form.getAttribute('id')));
  });

  document.addEventListener('DOMContentLoaded', function ()
  {
    const confirmButton = document.getElementById('j-question-confirm');
    if (!confirmButton) return;

    document.getElementById('j-question-extra').addEventListener('change', updateConfirm);
    document.getElementById('j-question-extra').addEventListener('input', updateConfirm);

    confirmButton.addEventListener('click', () =>
    {
      window.closePopUp('j-question');
      if (confirmAction) confirmAction();
    });
  });

  window.askQuestion = askQuestion;
})();
