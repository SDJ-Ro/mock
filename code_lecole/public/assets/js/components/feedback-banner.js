/**
 * Feedback banner (components/_feedback_banner.php): showFeedbackBanner('Saved.', 'success' | 'error' | 'info')
 * copies the page's banner <template>, fills in the words and the icon, slides it in at the top, and takes it away
 * after a few seconds (not while the mouse is on it; × takes it away at once). An 'error' stays until × — a problem
 * must stay until it has been read.
 * A message PHP printed on the stack (data-message + data-type, after a save) is shown as soon as the page opens.
 * A form on a page that still shows example data (nothing is saved yet) only pretends to save:
 *   <form data-mock-message="Certificate requested."> → sending it closes its pop-up, empties it and shows that message
 */
(function ()
{
  'use strict';

  const ICONS = { success: 'check', error: 'alertCircle', info: 'info' };

  function showFeedbackBanner(message, type = 'success', duration = 3500)
  {
    const banner = document.getElementById('j-feedback-banner').content.firstElementChild.cloneNode(true);
    const kind   = ICONS[type] ? type : 'info';
    let timer    = null;

    const leave = () =>
    {
      clearTimeout(timer);
      banner.classList.replace('c-is-visible', 'c-is-leaving');
      setTimeout(() => banner.remove(), 300);
    };
    const wait = () =>
    {
      if (kind !== 'error')
      {
        timer = setTimeout(leave, duration);
      }
    };

    banner.classList.add(`c-feedback-banner--${kind}`);
    banner.querySelector('use').setAttribute('href', `#icon-${ICONS[kind]}`);   // the first icon is the type's
    banner.querySelector('.c-feedback-banner__text').textContent = message;
    banner.querySelector('.j-feedback-close').addEventListener('click', leave);
    banner.addEventListener('mouseenter', () => clearTimeout(timer));
    banner.addEventListener('mouseleave', wait);

    document.getElementById('j-feedback-banners').append(banner);
    requestAnimationFrame(() => banner.classList.add('c-is-visible'));
    wait();
  }

  window.showFeedbackBanner = showFeedbackBanner;

  // A form that only pretends to save (example-data pages): the browser has already checked its required fields.
  // One that asks first (data-question-title) waits for the answer (question-pop-up.js sends it again, marked).
  // It closes its pop-up, empties itself and shows its message.
  document.addEventListener('submit', (event) =>
  {
    const form = event.target;

    if (!form.dataset.mockMessage || (form.dataset.questionTitle && form.dataset.questionAsked !== 'yes')) return;

    event.preventDefault();
    form.closest('dialog')?.close();
    form.reset();
    showFeedbackBanner(form.dataset.mockMessage);
  });

  const stack = document.getElementById('j-feedback-banners');
  if (stack.dataset.message)
  {
    showFeedbackBanner(stack.dataset.message, stack.dataset.type);
  }
})();
