<?php
// A student's evidence file in a plain pop-up: title + ×, a big file icon, the file's
// name and a line under it, optional details, Close. Opened by a button with data-pop-up-open="{its id}".
// Used by teacher achievements (a reported issue) and management's certificate requests (a missing record).
// Its look: evidence-preview.css. Set before requiring:
//   $evidenceId       the pop-up's id
//   $evidenceTitle    its title
//   $evidenceFile     the file's name
//   $evidenceLine     the line under the name (what it is, or its size)
//   $evidenceDetails  optional [label => value]
ob_start();
?>
<header class="c-evidence__header">
  <h2 class="c-pop-up__title c-evidence__title" id="<?= e($evidenceId) ?>-title"><?= e($evidenceTitle) ?></h2>
  <button type="button" class="c-btn c-btn--close j-pop-up-close" aria-label="Close"><?= icon('close', 16) ?></button>
</header>
<div class="c-evidence__body">
  <span class="c-evidence__icon"><?= icon('fileText', 32) ?></span>
  <div class="c-evidence__file">
    <h3 class="c-evidence__file-name"><?= e($evidenceFile) ?></h3>
    <p class="c-evidence__file-title"><?= e($evidenceLine) ?></p>
  </div>
  <?php if (!empty($evidenceDetails)): ?>
    <div class="c-evidence__details">
      <?php foreach ($evidenceDetails as $evidenceLabel => $evidenceValue): ?>
        <p><strong><?= e($evidenceLabel) ?>:</strong> <?= e($evidenceValue) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<footer class="c-pop-up__footer">
  <button type="button" class="c-btn c-btn--ghost c-evidence__close j-pop-up-close">Close Preview</button>
</footer>
<?php
$popUpContent = ob_get_clean();
$popUpShape   = 'plain';
$popUpId      = $evidenceId;
$popUpSize    = 'wide';
$popUpVariant = 'evidence';
require __DIR__ . '/_pop_up.php';
unset($evidenceId, $evidenceTitle, $evidenceFile, $evidenceLine, $evidenceDetails, $evidenceLabel, $evidenceValue);
