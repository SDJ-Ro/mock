/**
 * UNSAVED CHANGES — nothing typed is lost by accident (sending only once: send-once.js). Every signed-in page loads it
 * (layouts/page_bottom.php). A form remembers that something in it was typed, chosen, added or removed when it is:
 *   an edit page's form marked data-warn-unsaved (a notice, a person, My Profile, the Academic editors, Term Marks,
 *   an achievement) → leaving the page (any link: Back, Cancel, the sidebar) asks "Leave Without Saving?";
 *   closing the tab or reloading gets the browser's own warning
 *   any form in a pop-up → closing the pop-up (×, Cancel, Esc, the backdrop) asks "Discard Changes?"
 * Sending the form (Save) or emptying it (reset) forgets the changes. A box joined to its form with form="…" counts;
 * a dropdown choice counts when it is sent with the form (it has a name) and the form isn't a filter.
 */
(function ()
{
  'use strict';

  // The form a change belongs to, if it is watched (an edit page's, or one in a pop-up)
  function watchedForm(element)
  {
    const form = element.form ?? element.closest('form');
    return form && (form.matches('[data-warn-unsaved]') || form.closest('dialog')) ? form : null;
  }

  function remember(element)
  {
    const form = watchedForm(element);

    if (form)
    {
      form.dataset.changed = 'yes';
    }
  }

  // An edit page counts while its form has changes and is on screen (an editor closed with Cancel is hidden again)
  const unsaved = () => [...document.querySelectorAll('form[data-warn-unsaved][data-changed]')].some((form) => form.offsetParent !== null);

  document.addEventListener('input', (event) => remember(event.target));
  document.addEventListener('change', (event) => remember(event.target));
  document.addEventListener('dropdown:change', (event) =>
  {
    const field = event.target.querySelector('input[name]:not([name=""])');

    if (field && !field.form?.matches('[data-submit-on-change]'))
    {
      remember(field);
    }
  });
  document.addEventListener('click', (event) =>   // rows added / removed (repeat-list.js) change the form too
  {
    if (event.target.closest('[data-repeat-add], [data-repeat-remove]'))
    {
      remember(event.target);
    }
  }, true);
  document.addEventListener('submit', (event) => delete event.target.dataset.changed, true);
  document.addEventListener('reset', (event) => delete event.target.dataset.changed, true);

  // ---- Leaving the page ----
  document.addEventListener('click', (event) =>
  {
    const link = event.target.closest('a[href]');

    if (!link || link.target === '_blank' || !unsaved())
    {
      return;
    }

    event.preventDefault();
    window.askQuestion({
      title: 'Leave Without Saving?',
      text: "Your changes on this page aren't saved yet. If you leave now, they are lost.",
      button: 'Leave Without Saving',
      cancel: 'Keep Editing',
      icon: 'alertCircle',
      tone: 'warning',
      onConfirm: () =>
      {
        document.querySelectorAll('form[data-changed]').forEach((form) => delete form.dataset.changed);
        location.assign(link.href);
      }
    });
  });

  window.addEventListener('beforeunload', (event) =>
  {
    if (unsaved())
    {
      event.preventDefault();
    }
  });

  // ---- Closing a pop-up (pop-up.js sends 'pop-up:close' for ×, Cancel and the backdrop; Esc is the browser's 'cancel') ----
  function askBeforeClosing(event)
  {
    const popUp = event.target;
    const form  = popUp instanceof HTMLDialogElement ? popUp.querySelector('form[data-changed]') : null;

    if (!form)
    {
      return;
    }

    event.preventDefault();
    window.askQuestion({
      title: 'Discard Changes?',
      text: "What you typed here isn't saved. If you close it now, it is lost.",
      button: 'Discard Changes',
      cancel: 'Keep Editing',
      icon: 'alertCircle',
      tone: 'warning',
      onConfirm: () =>
      {
        form.reset();
        popUp.close();
      }
    });
  }

  document.addEventListener('pop-up:close', askBeforeClosing);
  document.addEventListener('cancel', askBeforeClosing, true);   // 'cancel' doesn't bubble: catch it on the way down
})();
