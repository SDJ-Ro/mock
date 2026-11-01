/**
 * Pop-up (components/_pop_up.php), for every pop-up on every page.
 * Each pop-up is the browser's own <dialog>, so the browser already does the hard parts: the dark backdrop,
 * staying on top of everything (a pop-up opened from a pop-up sits above it), Esc to close, keeping Tab inside,
 * and giving focus back to the button that opened it. This file only adds:
 *   openPopUp(id) / closePopUp(id)             → for other scripts
 *   <button data-pop-up-open="j-notices-pop-up"> → opens that pop-up, no script needed
 *   .j-pop-up-close (×, Cancel)                  → closes the pop-up it is in
 *   a click on the dark backdrop                → closes it
 *   <dialog data-open-on-load>                  → open as the page loads (e.g. forgot password after a wrong code)
 * Before ×, Cancel or the backdrop close a pop-up it sends 'pop-up:close' (cancelable): unsaved-changes.js stops it
 * when something typed would be lost, and asks first (Esc: the browser's own 'cancel' event, the same there).
 */
(function ()
{
  'use strict';

  const byIdOrElement = (target) => typeof target === 'string' ? document.getElementById(target) : target;

  function openPopUp(target)
  {
    const popUp = byIdOrElement(target);
    if (popUp && !popUp.open) popUp.showModal();
  }

  function closePopUp(target)
  {
    byIdOrElement(target)?.close();
  }

  // The person asked to close it (×, Cancel, the backdrop): close unless another script says wait
  function askToClose(popUp)
  {
    if (popUp.dispatchEvent(new CustomEvent('pop-up:close', { cancelable: true, bubbles: true })))
    {
      popUp.close();
    }
  }

  document.addEventListener('click', function (e)
  {
    const opener = e.target.closest('[data-pop-up-open]');
    const closer = e.target.closest('.j-pop-up-close');

    if (opener)
    {
      openPopUp(opener.dataset.popUpOpen);
    }
    else if (closer)
    {
      askToClose(closer.closest('dialog'));
    }
    else if (e.target instanceof HTMLDialogElement && isOutside(e.target, e))
    {
      askToClose(e.target);
    }
  });

  // A click on the backdrop reaches the <dialog> itself, but so does one on the box's own padding — so check where it was
  function isOutside(popUp, click)
  {
    const box = popUp.getBoundingClientRect();
    return click.clientX < box.left || click.clientX > box.right || click.clientY < box.top || click.clientY > box.bottom;
  }

  document.addEventListener('DOMContentLoaded', function ()
  {
    document.querySelectorAll('dialog[data-open-on-load]').forEach(popUp => popUp.showModal());
  });

  window.openPopUp = openPopUp;
  window.closePopUp = closePopUp;
})();
