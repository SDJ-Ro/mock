<?php
// The Extracurricular list for the student, management and admin pages (and the Child profile's
// Sports & Clubs tab; the teacher's when they run several): the search (a GET form) and the All / Sports / Clubs tabs
// (+ the admin's Archived; links: the server picks the cards), then the cards — after the office's "Create New
// Extracurricular" card, which opens _programme_pop_up.php ('create').
// Layout: layout/filter-bar-grid.css, extracurricular-grid.css; looks: search-field.css, tabs.css, extracurricular-card.css.
// From ExtracurricularController::listPage(): $can (manage, approve, join), $filters (q, type), $programmes, $emptyText,
// $teachers, $listPath + $listKeep (the list's address and what its links keep: the Child profile's tab).
?>
<?php // On its own page the bar is the first thing under the page header (--page); in the Child profile's tab it isn't ?>
<div class="l-filter-bar<?= $listKeep === [] ? ' l-filter-bar--page' : '' ?> l-filter-bar--close">
  <form class="l-filter-bar__search" method="get" action="<?= e($listPath) ?>" role="search">
    <label class="c-search c-search--rounded">
      <?= icon('search', 16) ?>
      <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="Search activities (e.g. Cricket, Debating, Football…)" aria-label="Search activities">
    </label>
    <?php foreach (array_filter($listKeep + ['type' => $filters['type']]) as $keepName => $keepValue): ?>
      <input type="hidden" name="<?= e($keepName) ?>" value="<?= e($keepValue) ?>">
    <?php endforeach; ?>
  </form>

  <div class="l-filter-bar__selects">
    <nav class="c-tabs" aria-label="Activity type">
      <?php foreach (['' => 'All'] + array_map(fn (array $type): string => $type['tab'], config('extracurricular.types')) + ($can['approve'] ? ['archived' => 'Archived'] : []) as $typeKey => $typeTab): ?>
        <a class="c-tabs__tab" href="<?= e(url_with($listPath, $listKeep + ['q' => $filters['q'], 'type' => $typeKey])) ?>" aria-selected="<?= $filters['type'] === $typeKey ? 'true' : 'false' ?>"><?= e($typeTab) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</div>

<div class="l-programme-grid">
  <?php if ($can['manage'] && $filters['type'] !== 'archived'): ?>
    <button type="button" class="c-programme-create" data-pop-up-open="j-programme-create">
      <span class="c-programme-create__icon" aria-hidden="true"><?= icon('plus', 22) ?></span>
      <span class="c-programme-create__title">Create New Extracurricular</span>
      <span class="c-programme-create__text">Set up a new sport, society, club, or arts program.</span>
    </button>
  <?php endif; ?>

  <?php foreach ($programmes as $programmeCard): ?>
    <?php require __DIR__ . '/_extracurricular_card.php'; ?>
  <?php endforeach; ?>
</div>

<?php if ($programmes === []): ?>
  <div class="c-empty-box">
    <p class="c-empty-box__text"><?= e($emptyText) ?></p>
  </div>
<?php endif; ?>

<?php if ($can['manage']): ?>
  <?php
  $programmePopUp = 'create';
  require __DIR__ . '/_programme_pop_up.php';
  ?>
<?php endif; ?>

<?php if ($can['approve']): ?>
  <?php
  // A waiting card's Reject asks for the reason (the question pop-up)
  $reasonBox = ['id' => 'j-reason', 'label' => 'Rejection Feedback', 'placeholder' => 'Explain why this was rejected…', 'required' => true];
  require __DIR__ . '/_reason_template.php';
  ?>
<?php endif; ?>
<?php unset($keepName, $keepValue, $typeKey, $typeTab); ?>
