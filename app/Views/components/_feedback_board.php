<?php
// The part both Feedback pages share: the filter bar (search + type; a GET form, the server picks the cards),
// the cards (_feedback_card.php) in their grid, the "nothing found" box, and the pop-up that shows one card in full
// (feedback-card.js fills it). Layout: css/layout/filter-bar-grid.css, feedback-grid.css; looks: feedback.css,
// search-field.css, dropdown.css, empty-box.css.
// From FeedbackController: $filters (q, type), $types (config/feedback.php), $feedback (the cards that match).
// From the page: $currentPath.
?>
<form class="l-filter-bar l-filter-bar--page" method="get" action="<?= e($currentPath) ?>" role="search" data-submit-on-change>
  <label class="c-search l-filter-bar__search">
    <?= icon('search', 16) ?>
    <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="Search feedback…" aria-label="Search feedback">
  </label>

  <div class="l-filter-bar__selects">
    <?php
    $dropdownId    = 'j-feedback-type';
    $dropdownLabel = 'Filter by feedback type';
    $name          = 'type';
    $options       = [['value' => '', 'label' => 'All Types']];
    foreach ($types as $typeKey => [$typeName])
    {
        $options[] = ['value' => $typeKey, 'label' => $typeName];
    }
    $selectedValue = $filters['type'];
    require __DIR__ . '/_dropdown.php';
    ?>
  </div>
</form>

<?php if ($feedback === []): ?>
  <div class="c-empty-box c-empty-box--titled">
    <?= icon('feedback', 32) ?>
    <p class="c-empty-box__title">No feedback entries found</p>
    <p class="c-empty-box__text">Try adjusting your search query or filter selection.</p>
  </div>
<?php else: ?>
  <div class="l-feedback-grid">
    <?php foreach ($feedback as $feedbackCard): ?>
      <?php require __DIR__ . '/_feedback_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php
// One card in full: the moss Back button above the card, on the dark backdrop (no white box: feedback.css)
ob_start();
?>
<div><button type="button" class="c-btn c-btn--back j-pop-up-close"><?= icon('arrowLeft', 16) ?><span>Back</span></button></div>
<div id="j-feedback-detail-card"></div>
<?php
$popUpContent = ob_get_clean();
$popUpShape   = 'plain';
$popUpId      = 'j-feedback-detail';
$popUpVariant = 'feedback-detail';
require __DIR__ . '/_pop_up.php';

unset($filters, $types, $feedback, $typeKey, $typeName);
?>
