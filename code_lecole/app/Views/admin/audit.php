<?php
// Admin's Audit Log: search + activity / actor filters, then one card per event.
// The filters are a GET form (search: Enter; a dropdown: sends at once — dropdown.js, data-submit-on-change).
// Data: AuditController::index() → $logs (this page's events), $total, $page, $pages, $filters (action, role, q),
// $actionNames (code → short name).
$tabTitle = 'Audit Logs';
$styles   = ['page-header', 'search-field', 'dropdown', 'panel', 'tag', 'empty-box', 'pager', 'audit'];
$scripts  = ['dropdown'];

$actorNames = ['' => 'All actors'];
foreach (array_keys(config('audit.tones')) as $actorRole)
{
    $actorNames[$actorRole] = $actorRole === 'system' ? 'System' : word($actorRole);
}

require dirname(__DIR__) . '/layouts/page_top.php';
?>
<div class="c-audit">
  <?php
  $title    = 'Audit Logs';
  $subtitle = 'System-wide activity tracking and security monitoring.';
  require dirname(__DIR__) . '/components/_page_header.php';
  ?>

  <form class="c-audit__toolbar" method="get" action="<?= e("/{$currentRole}/audit") ?>" role="search" data-submit-on-change>
    <label class="c-search c-audit__search">
      <?= icon('search', 16) ?>
      <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="Search by actor or details…" autocomplete="off" aria-label="Search the audit log">
    </label>

    <div class="c-audit__filters">
      <?php
      $dropdownId    = 'j-audit-action';
      $dropdownLabel = 'Filter by activity';
      $name          = 'action';
      $options       = array_merge([['value' => '', 'label' => 'All activities']],
          array_map(fn (string $code): array => ['value' => $code, 'label' => $actionNames[$code]], array_keys($actionNames)));
      $selectedValue = $filters['action'];
      require dirname(__DIR__) . '/components/_dropdown.php';
      ?>

      <?php
      $dropdownId    = 'j-audit-role';
      $dropdownLabel = 'Filter by actor role';
      $name          = 'role';
      $options       = array_map(fn (string $value): array => ['value' => $value, 'label' => $actorNames[$value]], array_keys($actorNames));
      $selectedValue = $filters['role'];
      require dirname(__DIR__) . '/components/_dropdown.php';
      ?>

      <span class="c-audit__sliders" aria-hidden="true"><?= icon('sliders', 16) ?></span>
    </div>
  </form>

  <div class="c-audit__list" aria-live="polite">
    <?php foreach ($logs as $log): ?>
      <?php require dirname(__DIR__) . '/components/_audit_card.php'; ?>
    <?php endforeach; ?>

    <?php if ($logs === []): ?>
      <div class="c-empty-box">
        <p class="c-empty-box__text">No audit events match those filters.</p>
      </div>
    <?php endif; ?>
  </div>

  <?php
  $pagerPage  = $page;
  $pagerPages = $pages;
  $pagerLink  = fn (int $target): string => url_with("/{$currentRole}/audit", $filters + ['page' => $target > 1 ? $target : '']);
  require dirname(__DIR__) . '/components/_pager.php';
  ?>
</div>
<?php
unset($actorNames, $actorRole, $pagerPage, $pagerPages, $pagerLink);
require dirname(__DIR__) . '/layouts/page_bottom.php';
