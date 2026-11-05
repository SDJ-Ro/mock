<?php
// The student's Character Certificate: Download / Print (the PDF) and Request Character Certificate in the
// header; the A4 certificate and the "Something missing?" card → the real B-3 missing-record pop-up.
// Data: CertificateController::student() → $certificate, $classTeacher, $rejectedRequests.
$tabTitle = 'Character Certificate';
$styles   = ['page-header', 'panel', 'tag', 'dropdown', 'form-field', 'certificate-sheet', 'certificates'];
$scripts  = ['dropdown', 'certificate-sheet', 'file-box'];
$grids    = ['certificates'];

require dirname(__DIR__) . '/layouts/page_top.php';

$title    = 'Character Certificate';
$subtitle = words('Review and verify {student} achievements and conduct records.');
ob_start();
?>
<div class="c-certificates__header-buttons">
  <?php if ($certificate['status'] === 'issued'): ?>
    <a class="c-btn c-btn--outline c-btn--compact" href="/student/certificates/pdf" target="_blank"><?= icon('printer', 16) ?>Download / Print</a>
  <?php else: ?>
    <a class="c-btn c-btn--outline c-btn--compact" aria-disabled="true" tabindex="-1"><?= icon('printer', 16) ?>Download / Print</a>
  <?php endif; ?>
</div>
<?php
$headerContent = ob_get_clean();
require dirname(__DIR__) . '/components/_page_header.php';
?>
<div class="l-certificate-student">
  <div class="l-certificate-student__main">
    <?php
    $sheet    = $certificate;
    $sheetFor = 'screen';
    require dirname(__DIR__) . '/components/_certificate_sheet.php';
    ?>
    <?php if ($certificate['status'] !== 'issued'): ?>
      <p class="c-card c-certificates__note">
        <?= icon('info', 20) ?>
        <span>Final certificate will be available after management finalises it. This document is a <strong>Preview Draft</strong> based on current records.</span>
      </p>
    <?php endif; ?>
  </div>

  <aside class="c-card c-certificates__missing">
    <h2 class="c-panel__title">Something missing?</h2>
    <p class="c-certificates__missing-text"><?= e(words("If an achievement or position isn't appearing, please contact your {Class} {Teacher}.")) ?></p>
    <?php if ($certificate['status'] !== 'issued'): ?>
      <button type="button" class="c-btn c-btn--solid c-btn--large c-certificates__missing-button" data-pop-up-open="j-missing-record">Submit Missing Record Request</button>
    <?php endif; ?>
    <?php foreach ($rejectedRequests as $rejected): ?>
      <p class="c-banner c-banner--error"><?= e($rejected['title'] . ': ' . $rejected['reason']) ?></p>
    <?php endforeach; ?>
  </aside>
</div>
<?php
if ($certificate['status'] !== 'issued')
{
    require dirname(__DIR__) . '/components/_missing_record_pop_up.php';
}
unset($certificate);
require dirname(__DIR__) . '/layouts/page_bottom.php';
