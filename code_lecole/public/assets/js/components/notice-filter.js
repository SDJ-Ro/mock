/**
 * Notice board filters (components/_notice_board.php), in the browser (owner, 2026-10-21: keep it so for now,
 * discuss later): the search box, the audience and category dropdowns and, for teachers and management, the
 * "My Notices" / "My Posts" tabs (the approver: "All Notices" / "Awaiting Approval"). Each card says what it is in
 * data-category, data-audience, data-mine, data-for-me and data-review (the server decides them), so this file only compares.
 *   My Notices: notices for my role that someone else wrote · My Posts: the ones I wrote (the audience filter shows here)
 */
(function ()
{
  'use strict';

  const board = document.getElementById('j-notice-board');

  if (!board) return;

  const search = board.querySelector('.j-notice-search');
  const audienceFilter = board.querySelector('.j-notice-audience-filter');
  const filters = { text: '', category: 'all', audience: 'all', tab: board.dataset.tab };   // tab '' = no tabs

  function matches(card)
  {
    const data = card.dataset;
    const inTab = filters.tab === 'my-notices' ? data.forMe === 'true' && data.mine === 'false'
      : filters.tab === 'my-posts' ? data.mine === 'true'
      : filters.tab === 'waiting' ? data.review === 'pending'
      : true;
    const forAudience = filters.audience === 'all' || data.audience === 'all' || data.audience.split(',').includes(filters.audience);

    return inTab && forAudience
      && (filters.category === 'all' || data.category === filters.category)
      && card.textContent.toLowerCase().includes(filters.text);
  }

  // Hide the cards that don't match; nothing left → the "nothing found" box
  function apply()
  {
    let shown = 0;

    board.querySelectorAll('.c-notice-card').forEach((card) =>
    {
      card.hidden = !matches(card);
      shown += card.hidden ? 0 : 1;
    });
    document.getElementById('j-notice-empty').hidden = shown > 0;
  }

  search.addEventListener('input', () =>
  {
    filters.text = search.value.trim().toLowerCase();
    apply();
  });

  board.addEventListener('dropdown:change', (event) =>
  {
    if (event.target.id === 'j-notice-filter-category') filters.category = event.detail.value;
    if (event.target.id === 'j-notice-filter-audience') filters.audience = event.detail.value;
    apply();
  });

  board.querySelectorAll('.j-notice-tab').forEach((tab) => tab.addEventListener('click', () =>
  {
    filters.tab = tab.dataset.tab;
    board.querySelectorAll('.j-notice-tab').forEach((each) => each.setAttribute('aria-selected', String(each === tab)));

    // In "My Notices" everything is for me, so the audience filter only shows in "My Posts"
    audienceFilter.hidden = filters.tab === 'my-notices';
    if (filters.tab === 'my-notices')
    {
      filters.audience = 'all';
      window.setDropdownValue('j-notice-filter-audience', 'all');
    }
    apply();
  }));

  board.querySelector('.j-notice-clear').addEventListener('click', () =>
  {
    search.value = '';
    Object.assign(filters, { text: '', category: 'all', audience: 'all' });
    window.setDropdownValue('j-notice-filter-category', 'all');
    if (audienceFilter) window.setDropdownValue('j-notice-filter-audience', 'all');
    apply();
  });

  apply();
})();
