/**
 * Academic section (components/_academic_section.php): the cards next to the record book.
 *  - The record book changed ('record-book:change') → the total marks card shows that year + term, and the trend
 *    shows that year's chart, marked as the current one.
 *  - ‹ › and the dots show another year's chart (only the trend; the record book stays).
 *  - A chart's click opens its year in the record book (showRecord(), record-book.js).
 *  - A bar shows its tooltip above the mouse.
 */
(function ()
{
  'use strict';

  // Every academic section inside root (the page, on load)
  const start = (root) => root.querySelectorAll('.j-academic-side').forEach((side) =>
  {
    const book    = document.getElementById(side.dataset.book);
    const cards   = [...side.querySelectorAll('.j-trend-card')];
    const tooltip = side.querySelector('.j-trend-tooltip');
    const setText = (selector, text) => side.querySelector(selector).replaceChildren(text);

    // Show one year's chart; the ‹ › stop at the ends and the dots follow
    function showChart(year)
    {
      const index = cards.findIndex((card) => card.dataset.year === String(year));

      cards.forEach((card, i) => card.toggleAttribute('hidden', i !== index));
      side.querySelectorAll('.j-trend-dot').forEach((dot) => dot.setAttribute('aria-pressed', String(dot.dataset.year === String(year))));
      side.querySelector('.j-trend-prev').disabled = index === 0;
      side.querySelector('.j-trend-next').disabled = index === cards.length - 1;
    }

    const shownIndex = () => cards.findIndex((card) => !card.hidden);

    book.addEventListener('record-book:change', (event) =>
    {
      const record = event.detail;

      setText('.j-total-term', `${book.dataset.termWord} ${record.term}`);
      setText('.j-total-scored', record.total);
      setText('.j-total-out-of', record.outOf);
      setText('.j-total-average', record.average);
      setText('.j-total-placement', record.placement);
      cards.forEach((card) => card.classList.toggle('c-trend__card--current', card.dataset.year === String(record.year)));
      showChart(record.year);
    });

    side.querySelector('.j-trend-prev').addEventListener('click', () => showChart(cards[shownIndex() - 1].dataset.year));
    side.querySelector('.j-trend-next').addEventListener('click', () => showChart(cards[shownIndex() + 1].dataset.year));
    side.querySelectorAll('.j-trend-dot').forEach((dot) => dot.addEventListener('click', () => showChart(dot.dataset.year)));
    cards.forEach((card) => card.addEventListener('click', () => window.showRecord(book, card.dataset.year)));

    side.querySelectorAll('.j-trend-bar').forEach((bar) =>
    {
      bar.addEventListener('mouseenter', () =>
      {
        tooltip.querySelector('.j-trend-tooltip-label').replaceChildren(bar.dataset.label);
        tooltip.querySelector('.j-trend-tooltip-mine').replaceChildren(bar.dataset.mine);
        tooltip.querySelector('.j-trend-tooltip-grade').replaceChildren(bar.dataset.average);
        tooltip.hidden = false;
      });
      bar.addEventListener('mousemove', (event) =>
      {
        tooltip.style.left = `${event.clientX}px`;
        tooltip.style.top = `${event.clientY - 14}px`;
      });
      bar.addEventListener('mouseleave', () => tooltip.toggleAttribute('hidden', true));
    });
  });

  start(document);
})();
