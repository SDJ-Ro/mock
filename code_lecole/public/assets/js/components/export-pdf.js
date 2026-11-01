/**
 * Export PDF button (components/_export_pdf_button.php). A click loads the button's printable page (data-url, drawn
 * by the server) into an out-of-sight frame and opens the browser's print box for it, where the person can print it
 * or "Save as PDF". The page itself is never left. Only a printable page (body.c-print) is ever printed.
 */
(function ()
{
  'use strict';

  document.addEventListener('click', (event) =>
  {
    const button = event.target.closest('.j-export-pdf');

    if (!button || button.getAttribute('aria-busy') === 'true') return;

    const text  = button.querySelector('.j-export-pdf-text');
    const label = text.textContent;
    let frame   = document.getElementById('j-export-frame');

    if (!frame)
    {
      frame = document.createElement('iframe');
      frame.id = 'j-export-frame';
      frame.className = 'c-export-frame';
      frame.title = 'Printable page';
      document.body.appendChild(frame);
    }

    button.setAttribute('aria-busy', 'true');
    text.textContent = 'Preparing PDF…';

    // Signed out meanwhile → the frame got the sign-in page instead: reload, so the person sees it (never print it)
    frame.onload = () =>
    {
      button.removeAttribute('aria-busy');
      text.textContent = label;

      if (!frame.contentDocument.querySelector('.c-print'))
      {
        window.location.reload();
        return;
      }

      frame.contentWindow.print();
    };

    frame.src = button.dataset.url;
  });
})();
