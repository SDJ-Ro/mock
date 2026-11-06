/**
 * Feedback card (components/_feedback_card.php): clicking a card (or Enter / Space on it) opens the same card in full
 * in the detail pop-up — a copy of it with c-feedback-card--full (feedback.css shows the whole text and the
 * "Official Feedback Record" footer). A card its reader hasn't opened yet holds a hidden form: the first open sends
 * it in the background (FeedbackController::read() marks it read) and removes it, so it is sent once (if that send
 * fails, nothing is shown: the card still comes with the form next time, and is sent again then).
 * The filters are a normal form (the server picks the cards); New Feedback is a pop-up of its own (data-pop-up-open).
 */
(function ()
{
  'use strict';

  function openInFull(card)
  {
    const read = card.querySelector('.j-feedback-read');

    if (read)
    {
      fetch(read.action, { method: 'POST', body: new FormData(read), headers: { Accept: 'application/json' } })
        .catch(() => {});
      read.remove();
    }

    const copy = card.cloneNode(true);

    copy.classList.add('c-feedback-card--full');
    copy.classList.remove('j-feedback-card');
    copy.removeAttribute('tabindex');
    copy.removeAttribute('role');
    copy.removeAttribute('aria-label');
    copy.querySelector('h2').id = 'j-feedback-detail-title';   // the pop-up's name for screen readers (_pop_up.php)
    document.getElementById('j-feedback-detail-card').replaceChildren(copy);
    window.openPopUp('j-feedback-detail');
  }

  document.addEventListener('click', (event) =>
  {
    const card = event.target.closest('.j-feedback-card');
    if (card) openInFull(card);
  });

  document.addEventListener('keydown', (event) =>
  {
    const card = event.target.closest('.j-feedback-card');
    if (!card || (event.key !== 'Enter' && event.key !== ' ')) return;

    event.preventDefault();
    openInFull(card);
  });
})();
