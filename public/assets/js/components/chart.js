/**
 * Chart tooltip, shared by the bar chart and the donuts (CSS: chart.css).
 * One tooltip box for the whole page, next to the mouse:
 *   chartTooltip.show(event, …elements)   fill it with these elements and show it
 *   chartTooltip.move(event)              follow the mouse
 *   chartTooltip.hide()
 *   chartTooltip.text('class', 'words')   a small element with this text (textContent, so names can't add HTML)
 */
(function ()
{
  'use strict';

  let box = null;   // made the first time it is needed

  // Above-right of the mouse; flipped when it would leave the window
  function move(e)
  {
    const size = box.getBoundingClientRect();
    let left = e.clientX + 14;
    let top = e.clientY - size.height - 14;

    if (left + size.width > window.innerWidth - 12)
    {
      left = e.clientX - size.width - 14;
    }

    if (top < 12)
    {
      top = e.clientY + 14;
    }

    box.style.left = `${Math.max(12, left)}px`;
    box.style.top = `${Math.max(12, top)}px`;
  }

  function show(e, ...elements)
  {
    if (!box)
    {
      box = document.createElement('div');
      box.className = 'c-chart-tooltip';
      document.body.appendChild(box);
    }

    box.replaceChildren(...elements);
    box.classList.add('c-is-visible');
    move(e);
  }

  function hide()
  {
    box?.classList.remove('c-is-visible');
  }

  function text(className, words)
  {
    const el = document.createElement('div');
    el.className = className;
    el.textContent = words;
    return el;
  }

  window.chartTooltip = { show, move, hide, text };
})();
