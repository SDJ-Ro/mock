<?php
// The part both Inquiries & Complaints pages share: the filter bar (search + category + status; a GET form,
// the server picks the cards), the inquiry cards (_complaint_card.php) and the "nothing found" box.
// Layout: css/layout/filter-bar-grid.css, complaints-grid.css; looks: complaints.css, search-field.css, dropdown.css,
// empty-box.css.
// From ComplaintController: $filters (q, category, status), $categories, $statuses (config/complaints.php), $complaints.
// From the page: $currentPath, $searchText (the search box's hint), $canResolve (management: true).
?>
<form class="l-filter-bar l-filter-bar--page" method="get" action="<?= e($currentPath) ?>" role="search" data-submit-on-change>
  <label class="c-search l-filter-bar__search">
    <?= icon('search', 16) ?>
    <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e($searchText) ?>" aria-label="Search inquiries">
  </label>

  <div class="l-filter-bar__selects">
    <?php
    $dropdownId    = 'j-complaints-category';
    $dropdownLabel = 'Filter by category';
    $name          = 'category';
    $options       = array_merge([['value' => '', 'label' => 'All Categories']], array_keys($categories));
    $selectedValue = $filters['category'];
    require __DIR__ . '/_dropdown.php';
    ?>

    <?php
    $dropdownId    = 'j-complaints-status';
    $dropdownLabel = 'Filter by status';
    $name          = 'status';
    $options       = [['value' => '', 'label' => 'All Statuses']];
    foreach ($statuses as $statusKey => [$statusName])
    {
        $options[] = ['value' => $statusKey, 'label' => $statusName];
    }
    $selectedValue = $filters['status'];
    require __DIR__ . '/_dropdown.php';
    ?>
  </div>
</form>

<?php if ($complaints === []): ?>
  <div class="c-empty-box c-empty-box--titled">
    <?= icon('complaints', 40) ?>
    <p class="c-empty-box__title">No inquiries match your filter</p>
    <p class="c-empty-box__text">Try adjusting your search terms or filter selections.</p>
  </div>
<?php else: ?>
  <div class="l-complaints-list">
    <?php foreach ($complaints as $complaintCard): ?>
      <?php require __DIR__ . '/_complaint_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php unset($filters, $categories, $statuses, $complaints, $searchText, $canResolve, $statusKey, $statusName); ?>
