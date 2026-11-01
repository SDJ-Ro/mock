/**
 * Bar chart (components/_bar_chart.php)
 * PHP draws every bar and works out every position. This file only:
 *   1. shows a tooltip with both values when the mouse is over a group (the tooltip box: chart.js)
 *   2. scrolls a long chart sideways with the mouse wheel
 *   3. moves the bars to another term when the Term picker changes (each bar carries its values in data-values),
 *      or reloads the page when a filter changes (Academic)
 */
(function ()
{
  'use strict';

  /* =========================================================================
     SECTION 1: THE TOOLTIP (the box itself: chart.js)
     ========================================================================= */
  // The group's name, then one line per bar: coloured dot, series name, value
  // (textContent, so names from the database can't add HTML)
  function tooltipLines(group, unit)
  {
    const lines = [...group.querySelectorAll('.j-chart-bar')].map(bar =>
    {
      const line = document.createElement('div');
      const dot = document.createElement('span');
      const text = document.createElement('span');
      const value = document.createElement('strong');

      line.className = 'c-chart-tooltip__row';
      dot.className = `c-chart-tooltip__color-dot c-series--${bar.dataset.color}`;
      text.textContent = `${bar.dataset.series}: `;
      value.textContent = bar.dataset.value + unit;
      text.appendChild(value);
      line.append(dot, text);
      return line;
    });

    return [window.chartTooltip.text('c-chart-tooltip__title', group.dataset.label), ...lines];
  }

  /* =========================================================================
     SECTION 2: ANOTHER TERM (bar height = value × scale, standing on the X-axis)
     ========================================================================= */
  function showTerm(panel, term)
  {
    const scale = Number(panel.dataset.scale);
    const bottom = Number(panel.dataset.bottom);

    panel.querySelectorAll('.j-chart-bar').forEach(bar =>
    {
      const value = JSON.parse(bar.dataset.values)[term] ?? 0;
      const height = Math.max(value * scale, 2);

      bar.dataset.value = value;
      bar.setAttribute('height', height);
      bar.setAttribute('y', bottom - height);
    });
  }

  /* =========================================================================
     SECTION 3: START
     ========================================================================= */
  document.addEventListener('DOMContentLoaded', function ()
  {
    document.querySelectorAll('.j-bar-chart').forEach(panel =>
    {
      const unit = panel.dataset.unit;
      const area = panel.querySelector('.c-bar-chart__area');

      panel.querySelectorAll('.j-chart-group').forEach(group =>
      {
        group.addEventListener('mouseenter', (e) => window.chartTooltip.show(e, ...tooltipLines(group, unit)));
        group.addEventListener('mousemove', window.chartTooltip.move);
        group.addEventListener('mouseleave', window.chartTooltip.hide);
      });

      // Up / down wheel → sideways, but only when the chart is wider than its card
      area.addEventListener('wheel', (e) =>
      {
        if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && area.scrollWidth > area.clientWidth)
        {
          area.scrollLeft += e.deltaY;
          e.preventDefault();
        }
      }, { passive: false });

      // A filter reloads the page with its choice; the Term picker ('scores' charts) moves the bars
      panel.addEventListener('dropdown:change', (e) =>
      {
        const filters = e.target.closest('.j-chart-filters');
        filters ? filters.submit() : showTerm(panel, e.detail.value);
      });
    });
  });
})();
