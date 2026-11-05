/**
 * Certificate sheet (components/_certificate_sheet.php) on screen.
 * Pages: the text flows by itself into as many A4 pages as it needs (CSS columns: certificate-sheet.css), side by
 * side in a strip; this file only counts them and slides the strip one page at a time. It counts again whenever the
 * text can change size — the window, the font arriving, typing in Edit wording — so pages are added and removed by
 * themselves, and if the page you were on is gone you land on the last one. One page: no Previous / Next row.
 * Edit wording (management): the sections' points become editable in place (Enter = a new point, the browser's own);
 * nothing is saved yet, the words stay until the page is reloaded.
 */
(function ()
{
  'use strict';

  const frame = document.querySelector('.j-certificate-pages');

  if (!frame) return;

  const strip = frame.querySelector('.j-certificate-strip');
  const nav   = frame.querySelector('.j-certificate-nav');
  let page    = 1;
  let pages   = 1;

  // One page's width on the strip: the sheet's text width + the gap between columns (the sheet's two margins)
  function step()
  {
    return strip.clientWidth + parseFloat(getComputedStyle(strip).columnGap);
  }

  function show()
  {
    pages = Math.max(1, Math.round((strip.scrollWidth + parseFloat(getComputedStyle(strip).columnGap)) / step()));
    page  = Math.min(page, pages);

    strip.style.transform = `translateX(${-(page - 1) * step()}px)`;
    frame.querySelectorAll('.j-certificate-page-label').forEach(label => label.textContent = pages > 1 ? `Page ${page} of ${pages}` : '');
    frame.querySelector('.j-certificate-previous').disabled = page === 1;
    frame.querySelector('.j-certificate-next').disabled = page === pages;
    nav.hidden = pages === 1;
  }

  frame.querySelector('.j-certificate-previous').addEventListener('click', () =>
  {
    page--;
    show();
  });

  frame.querySelector('.j-certificate-next').addEventListener('click', () =>
  {
    page++;
    show();
  });

  window.addEventListener('resize', show);
  document.fonts.ready.then(show);
  strip.addEventListener('input', show);
  show();

  // Edit wording ⇄ Done editing (management only)
  const editButton = document.querySelector('.j-certificate-edit');

  function fillWording(form)
  {
    frame.querySelectorAll('.j-certificate-editable').forEach(list =>
    {
      const field = form.elements.namedItem(list.dataset.certificateSection);

      if (field) field.value = list.innerText.trim();
    });
  }

  document.querySelectorAll('.j-certificate-action').forEach(form => form.addEventListener('submit', event =>
  {
    fillWording(form);

    if (form.dataset.confirmFinalise && !window.confirm('If you click OK, this certificate will be available for the student to download and print as a PDF.'))
    {
      event.preventDefault();
    }
  }));

  editButton?.addEventListener('click', () =>
  {
    const editing = editButton.getAttribute('aria-pressed') !== 'true';

    editButton.setAttribute('aria-pressed', String(editing));
    editButton.querySelector('.j-certificate-edit-label').textContent = editing ? 'Done Editing' : 'Edit Wording';
    frame.querySelector('.j-certificate-sheet').classList.toggle('c-certificate--editing', editing);
    frame.querySelectorAll('.j-certificate-editable').forEach(list => list.contentEditable = String(editing));
    show();

    if (!editing)
    {
      document.querySelector('.j-certificate-draft')?.requestSubmit();
    }
  });
})();
