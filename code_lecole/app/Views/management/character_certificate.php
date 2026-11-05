<?php
// Management's Character Certificates: the list (search + status tabs + pathway tabs; links, the server
// picks the cards) in two groups of capsule cards, or one certificate (?student=…): Back, Print certificate (the PDF),
// a header with Student Requests / Edit wording / Finalise, the A4 certificate with its pages, its activity.
// The data is real. Finalise issues one graduating student's certificate after confirmation.
// Data: CertificateController::management() → $certificate (one) or $filters, $counts, $groups.
$tabTitle = 'Character Certificates';
$styles   = ['page-header', 'search-field', 'tabs', 'panel', 'tag', 'empty-box', 'certificate-card', 'certificate-sheet', 'certificates', 'evidence-preview'];
$scripts  = ['certificate-sheet'];
$grids    = ['filter-bar', 'certificates'];

require dirname(__DIR__) . '/layouts/page_top.php';

$title    = 'Character Certificates';
$subtitle = words('Review, refine, and issue official character certificates for {students} who are leaving or graduating.');
require dirname(__DIR__) . '/components/_page_header.php';
?>
<?php if ($certificate === null): ?>
  <?php
  // A tab's address: the current filters with one changed ('' = all)
  $certificatesLink = fn (array $change): string => '/management/character-certificate?' . http_build_query(array_filter($change + $filters));
  $statusNames      = config('certificates.statuses');
  ?>
  <div class="l-certificates">
    <div class="l-filter-bar">
      <form class="l-filter-bar__search" method="get" action="/management/character-certificate" role="search">
        <label class="c-search">
          <?= icon('search', 16) ?>
          <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="Search name, ID, or cohort…" aria-label="Search certificates">
        </label>
        <?php foreach (['status', 'pathway'] as $certificatesFilter): ?>
          <?php if ($filters[$certificatesFilter] !== ''): ?>
            <input type="hidden" name="<?= e($certificatesFilter) ?>" value="<?= e($filters[$certificatesFilter]) ?>">
          <?php endif; ?>
        <?php endforeach; ?>
      </form>

      <div class="l-filter-bar__selects">
        <nav class="c-tabs" aria-label="Certificate status">
          <?php foreach (['' => 'All Certificates'] + array_map(fn (array $status): string => $status[0], $statusNames) as $statusKey => $statusName): ?>
            <a class="c-tabs__tab" href="<?= e($certificatesLink(['status' => $statusKey])) ?>" aria-selected="<?= $filters['status'] === $statusKey ? 'true' : 'false' ?>">
              <?= e($statusName) ?> <span class="c-tabs__count"><?= e($counts[$statusKey]) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>
    </div>

    <?php
    $certificateGroups = [
        'pending' => ['alertCircle', 'Certificates needing review', 'Pending Review'],
        'issued'  => ['shieldCheck', 'Verified Certificates', 'Verified'],
    ];
    ?>
    <?php foreach ($certificateGroups as $groupKey => [$groupIcon, $groupTitle, $groupCount]): ?>
      <?php if ($groups[$groupKey] !== []): ?>
        <section class="l-certificates__group">
          <div class="c-certificates__group-head">
            <h2 class="c-certificates__group-title<?= $groupKey === 'pending' ? ' c-certificates__group-title--pending' : '' ?>"><?= icon($groupIcon, 18) ?><?= e($groupTitle) ?></h2>
            <span class="c-chip c-chip--round c-certificates__group-count--<?= e($groupKey) ?>"><?= e(count($groups[$groupKey]) . " {$groupCount}") ?></span>
          </div>
          <div class="l-certificates-grid">
            <?php foreach ($groups[$groupKey] as $certificateCard): ?>
              <?php require dirname(__DIR__) . '/components/_certificate_card.php'; ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($groups['pending'] === [] && $groups['issued'] === []): ?>
      <div class="c-empty-box">
        <p class="c-empty-box__text">No certificates match these filters.</p>
      </div>
    <?php endif; ?>
  </div>
<?php else: ?>
  <?php [$statusName, $statusTone] = config("certificates.statuses.{$certificate['status']}"); ?>
  <div class="l-certificates">
    <div class="l-certificates__toolbar">
      <a class="c-btn c-btn--back" href="/management/character-certificate"><?= icon('chevronLeft', 16) ?><span>Back</span></a>
      <?php if ($certificate['status'] === 'issued'): ?>
        <a class="c-btn c-btn--solid c-btn--compact" href="<?= e('/management/character-certificate/pdf?student=' . $certificate['studentId']) ?>" target="_blank"><?= icon('printer', 14) ?>Print Certificate</a>
      <?php else: ?>
        <a class="c-btn c-btn--solid c-btn--compact" aria-disabled="true" tabindex="-1"><?= icon('printer', 14) ?>Print Certificate</a>
      <?php endif; ?>
    </div>

    <section class="c-certificate-panel">
      <header class="c-certificate-panel__head">
        <div class="c-certificate-panel__who">
          <span class="c-avatar c-avatar--medium"><?= e(initials($certificate['name'])) ?></span>
          <div>
            <h2 class="c-certificate-panel__name"><?= e($certificate['name']) ?> <span class="c-tag c-tag--round c-tag--<?= e($statusTone) ?>"><?= e($statusName) ?></span></h2>
            <p class="c-certificate-panel__line"><?= e("{$certificate['id']} · {$certificate['cohort']}") ?></p>
          </div>
        </div>
        <div class="c-certificate-panel__actions">
          <button type="button" class="c-btn c-btn--sky c-btn--compact c-certificate-panel__requests" data-pop-up-open="j-certificate-requests">
            <?= icon('message', 14) ?><?= e(words('{Student} Requests')) ?>
            <?php if ($certificate['waitingCount'] > 0): ?>
              <span class="c-certificate-panel__count"><?= e($certificate['waitingCount']) ?></span>
            <?php endif; ?>
          </button>
          <?php if ($certificate['status'] === 'pending'): ?>
            <button type="button" class="c-btn c-btn--outline c-btn--compact j-certificate-edit" aria-pressed="false">
              <?= icon('edit', 14) ?><span class="j-certificate-edit-label">Edit Wording</span>
            </button>
            <form method="post" action="/management/character-certificate/finalise" class="j-certificate-action" data-confirm-finalise="1">
              <?= $csrfField ?>
              <input type="hidden" name="student" value="<?= e($certificate['studentId']) ?>">
              <?php foreach (array_keys(config('certificates.sections')) as $formSection): ?>
                <input type="hidden" name="<?= e($formSection) ?>" value="">
              <?php endforeach; ?>
              <button type="submit" class="c-btn c-btn--maroon c-btn--compact"><?= icon('send', 14) ?>Finalise</button>
            </form>
          <?php endif; ?>
        </div>
      </header>

      <div class="c-certificate-panel__paper">
        <?php
        $sheet         = $certificate;
        $sheetFor      = 'screen';
        $sheetEditable = $certificate['status'] === 'pending';
        require dirname(__DIR__) . '/components/_certificate_sheet.php';
        ?>
      </div>
    </section>

    <?php if ($certificate['status'] === 'pending'): ?>
      <form method="post" action="/management/character-certificate/draft" class="j-certificate-action j-certificate-draft" hidden>
        <?= $csrfField ?>
        <input type="hidden" name="student" value="<?= e($certificate['studentId']) ?>">
        <?php foreach (array_keys(config('certificates.sections')) as $formSection): ?>
          <input type="hidden" name="<?= e($formSection) ?>" value="">
        <?php endforeach; ?>
      </form>
    <?php endif; ?>

    <?php if ($certificate['activity'] !== []): ?>
      <section class="c-card c-certificates__activity">
        <h2 class="c-panel__title">Certificate Activity</h2>
        <?php
        $activityItems = $certificate['activity'];
        require dirname(__DIR__) . '/components/_activity_list.php';
        ?>
      </section>
    <?php endif; ?>
  </div>
  <?php require dirname(__DIR__) . '/components/_certificate_requests_pop_up.php'; ?>
<?php endif; ?>
<?php
unset($certificate, $filters, $counts, $groups, $certificatesLink, $statusNames, $certificatesFilter, $statusKey, $statusName, $statusTone,
    $pathwayKey, $pathwayName, $certificateGroups, $groupKey, $groupIcon, $groupTitle, $groupCount);
require dirname(__DIR__) . '/layouts/page_bottom.php';
