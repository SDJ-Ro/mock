/**
 * Feedback card (components/_feedback_card.php): clicking a card (or Enter / Space on it) opens the same card in full
 * in the detail pop-up — a copy of it with c-feedback-card--full (feedback.css shows the whole text and the
 * "Official Feedback Record" footer). The filters are a normal form (the server picks the cards); New Feedback is a
 * pop-up of its own (data-pop-up-open) whose form only pretends to save (data-mock-message, feedback-banner.js).
 */
(function ()
{
  'use strict';

  function openInFull(card)
  {
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
