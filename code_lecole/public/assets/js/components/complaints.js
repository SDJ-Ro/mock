/**
 * Complaint card (components/_complaint_card.php), management's "Mark as Resolved": it shows the card's resolve form
 * (and hides itself), and × puts it away again. The form sends a normal POST request; the filters use GET.
 */
(function ()
{
  'use strict';

  // Show the form (true) or the button (false) of one card
  function showForm(card, open)
  {
    card.querySelector('.j-resolve-form').hidden = !open;
    card.querySelector('.j-resolve-open').hidden = open;
    if (open) card.querySelector('.j-resolve-form input[name="response"]').focus();
  }

  document.addEventListener('click', (event) =>
  {
    const opener = event.target.closest('.j-resolve-open');
    const cancel = event.target.closest('.j-resolve-cancel');

    if (opener) showForm(opener.closest('.j-complaint-card'), true);
    if (cancel) showForm(cancel.closest('.j-complaint-card'), false);
  });
})();
