/**
 * Digital Record Book (components/_digital_record_book.php). PHP draws every school year × term; picking a Grade (one
 * book per year) or Term shows that one's marks and feedback, renames the labels ("Grade 7 (Repeat)", "Term 2") and
 * points Export PDF at it. A year without the chosen term (this year so far) shows its first term instead.
 * Then the book sends 'record-book:change' (detail: year, term, and the shown marks' data-total, data-out-of,
 * data-average, data-placement) so the cards next to it can follow.
 * For other scripts: showRecord(book, year) → show that year, same term (the trend chart's cards use it).
 * A book added after the page loaded starts when its container is sent with 'content:added' (People's pop-up).
 */
(function ()
{
  'use strict';

  function showRecord(book, year, term = book.dataset.term)
  {
    const all = [...book.querySelectorAll('.j-record-marks')];
    const marks = all.find((element) => element.dataset.year === String(year) && element.dataset.term === String(term))
      || all.find((element) => element.dataset.year === String(year));
    const shown = (element) => element.dataset.year === marks.dataset.year && element.dataset.term === marks.dataset.term;
    const exportButton = book.querySelector('.j-export-pdf');

    year = marks.dataset.year;
    term = marks.dataset.term;
    book.dataset.year = year;
    book.dataset.term = term;
    book.querySelectorAll('.j-record-marks, .j-record-note').forEach((element) => element.toggleAttribute('hidden', !shown(element)));
    book.querySelectorAll('.j-record-grade').forEach((label) => label.replaceChildren(marks.dataset.label));
    book.querySelectorAll('.j-record-term').forEach((label) => label.replaceChildren(`${book.dataset.termWord} ${term}`));
    window.setDropdownValue(`${book.id}-year`, year);
    window.setDropdownValue(`${book.id}-term`, term);

    if (exportButton)   // (People's pop-up has none)
    {
      exportButton.dataset.url = exportButton.dataset.url.replace(/\?.*$/, `?year=${year}&term=${term}`);
    }

    book.dispatchEvent(new CustomEvent('record-book:change', { detail: { ...marks.dataset, year, term }, bubbles: true }));
  }

  // Every book inside root: the page on load, or content added later (People's pop-up sends 'content:added')
  function start(root)
  {
    root.querySelectorAll('.j-record-book').forEach((book) =>
    {
      book.addEventListener('dropdown:change', (event) =>
      {
        const isYear = event.detail.id === `${book.id}-year`;

        showRecord(book, isYear ? event.detail.value : book.dataset.year, isYear ? book.dataset.term : event.detail.value);
      });
    });
  }

  start(document);
  document.addEventListener('content:added', (event) => start(event.detail));

  window.showRecord = showRecord;
})();
