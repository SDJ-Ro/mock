<?php
// The student's Character Certificate: Download / Print (the PDF) and Request Character Certificate in the
// header; the A4 certificate with its pages and the "Preview Draft" note; the "Something missing?" card → the
// missing-record pop-up. The certificate is example data (the same for every student) and the certificate request
// pop-up saves nothing yet (it only says so); the missing-record request is real (B-3, AchievementController).
// Data: CertificateController::student() → $certificate, $classTeacher.
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
  <a class="c-btn c-btn--outline c-btn--compact" href="/student/certificates/pdf" target="_blank"><?= icon('printer', 16) ?>Download / Print</a>
  <button type="button" class="c-btn c-btn--maroon c-btn--compact" data-pop-up-open="j-certificate-request"><?= icon('fileCheck', 16) ?>Request Character Certificate</button>
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
    <p class="c-card c-certificates__note">
      <?= icon('info', 20) ?>
      <span>Final certificate will be available upon graduation verification. This document is a <strong>Preview Draft</strong> based on current records.</span>
    </p>
  </div>

  <aside class="c-card c-certificates__missing">
    <h2 class="c-panel__title">Something missing?</h2>
    <p class="c-certificates__missing-text"><?= e(words("If an achievement or position isn't appearing, please contact your {Class} {Teacher}.")) ?></p>
    <button type="button" class="c-btn c-btn--solid c-btn--large c-certificates__missing-button" data-pop-up-open="j-missing-record">Submit Missing Record Request</button>
  </aside>
</div>
<?php
require dirname(__DIR__) . '/components/_certificate_request_pop_up.php';
require dirname(__DIR__) . '/components/_missing_record_pop_up.php';
unset($certificate);
require dirname(__DIR__) . '/layouts/page_bottom.php';
