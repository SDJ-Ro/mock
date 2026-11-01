/**
 * Complaint card (components/_complaint_card.php), management's "Mark as Resolved": it shows the card's resolve form
 * (and hides itself), × puts it away again, and after sending the form is put away too. The sending itself only
 * pretends to save for now (the form's data-mock-message, feedback-banner.js). The filters are a normal form.
 */
(function ()
{
  'use strict';

  // Show the form (true) or the button (false) of one card
  function showForm(card, open)
  {
    card.querySelector('.j-resolve-form').hidden = !open;
    card.querySelector('.j-resolve-open').hidden = open;
    if (open) card.querySelector('.j-resolve-form input').focus();
  }

  document.addEventListener('click', (event) =>
  {
    const opener = event.target.closest('.j-resolve-open');
    const cancel = event.target.closest('.j-resolve-cancel');

    if (opener) showForm(opener.closest('.j-complaint-card'), true);
    if (cancel) showForm(cancel.closest('.j-complaint-card'), false);
  });

  document.addEventListener('submit', (event) =>
  {
    if (event.target.matches('.j-resolve-form')) showForm(event.target.closest('.j-complaint-card'), false);
  });
})();
