<?php
// The student's "Submit Missing Record Request" pop-up, in the shared frame (_pop_up.php); the "Something
// missing?" card opens it. Sending it: AchievementController::sendRequest() (B-3) — the request and its evidence (a
// file, required: the shared file box, file-box.js) go to the student's class teacher this year.
// The fields: form-field.css; the record types: config/certificates.php; the evidence's types and size:
// config/achievements.php. Set before requiring: $classTeacher (their class teacher's name, or null: none yet).
ob_start();
?>
<form class="c-pop-up__form" method="post" action="/student/achievements/request" enctype="multipart/form-data">
  <?= $csrfField ?>
  <div class="c-pop-up__body">
    <div class="c-field">
      <span class="c-label c-field__label">Record Type <span class="c-field__required c-field__required--terracotta">*</span></span>
      <?php
      $dropdownId    = 'j-missing-record-type';
      $dropdownLabel = 'Record type';
      $name          = 'type';
      $options       = config('certificates.record_types');
      $placeholder   = 'Select a record type…';
      $required      = 'Please select a record type.';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <div class="c-field-pair">
      <label class="c-field">
        <span class="c-label c-field__label"><?= e(words('Academic Year / {Grade}')) ?> <span class="c-field__required c-field__required--terracotta">*</span></span>
        <input type="text" class="c-field__input" name="year" maxlength="40" required placeholder="<?= e(words('e.g. 2025 or {Grade} 12')) ?>">
      </label>

      <label class="c-field">
        <span class="c-label c-field__label">Title / Role <span class="c-field__required c-field__required--terracotta">*</span></span>
        <input type="text" class="c-field__input" name="title" maxlength="150" required placeholder="e.g. Under-17 Captain">
      </label>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Details &amp; Reason <span class="c-field__required c-field__required--terracotta">*</span></span>
      <textarea class="c-field__input c-field__input--area" name="details" rows="3" required
                maxlength="1000" placeholder="Explain what is missing and why it should appear on your character certificate…"></textarea>
    </label>

    <div class="c-field">
      <span class="c-label c-field__label">Evidence <span class="c-field__required c-field__required--terracotta">*</span></span>
      <?php
      // Required: a request is only sent with its evidence
      $fileBoxName     = 'evidence';
      $fileBoxRequired = true;
      $fileBoxTypes    = config('achievements.proof_types');
      $fileBoxKb       = config('achievements.proof_kb');
      require __DIR__ . '/_file_box.php';
      ?>
    </div>

    <p class="c-banner c-banner--info"><?= icon('info', 16) ?>
      <?php if ($classTeacher === null): ?>
        <span><?= e(words('Your {class} has no {class} {teacher} yet. Please ask the office.')) ?></span>
      <?php else: ?>
        <span><?= e(words('This request will be routed to your {Class} {Teacher}') . " ({$classTeacher}) for review.") ?></span>
      <?php endif; ?>
    </p>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--outline j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--maroon">Submit Request</button>
  </div>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-missing-record';
$popUpSize        = 'wide';
$popUpIcon        = 'award';
$popUpEyebrow     = words('{Student} Portal · Record Correction');
$popUpTitle       = 'Submit Missing Record Request';
$popUpDescription = words("Tell us what's missing from your record. We'll automatically route it to the right {teacher}.");
require __DIR__ . '/_pop_up.php';
unset($classTeacher);
