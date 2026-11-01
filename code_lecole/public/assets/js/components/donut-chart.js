/**
 * Donut chart (components/_donut_chart.php)
 * Hovering a slice, or its name in the legend, shows "FOOTBALL · 150 students" (the tooltip box: chart.js).
 */
(function ()
{
  'use strict';

  document.addEventListener('DOMContentLoaded', function ()
  {
    document.querySelectorAll('.j-donut').forEach(card =>
    {
      const slices = card.querySelectorAll('.j-donut-slice');

      // A legend name points at its slice (data-slice = its number), so the data is written once, on the slice.
      // The tooltip is filled once when the mouse comes in (like the bar chart), then only follows the mouse.
      card.querySelectorAll('.j-donut-slice, .j-donut-legend-item').forEach(el =>
      {
        const slice = el.dataset.slice ? slices[el.dataset.slice] : el;

        el.addEventListener('mouseenter', (e) =>
        {
          const value = document.createElement('div');
          const number = document.createElement('strong');

          value.className = 'c-chart-tooltip__value';
          number.textContent = slice.dataset.value;
          value.append(number, ` ${card.dataset.unit}`);
          window.chartTooltip.show(e, window.chartTooltip.text('c-label', slice.dataset.name), value);
        });
        el.addEventListener('mousemove', window.chartTooltip.move);
        el.addEventListener('mouseleave', window.chartTooltip.hide);
      });
    });
  });
})();
