<?php
// The student's "Submit Missing Record Request" pop-up, in the shared frame (_pop_up.php); the "Something
// missing?" card opens it. Sending saves nothing yet: the form closes and says so (data-mock-message,
// feedback-banner.js). The fields: form-field.css; the record types: config/certificates.php.
// Set before requiring: $classTeacher (who the request goes to: app/Mock/certificates.php).
ob_start();
?>
<form class="c-pop-up__form" data-mock-message="Missing record request sent.">
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
                placeholder="Explain what is missing and why it should appear on your character certificate…"></textarea>
    </label>

    <p class="c-banner c-banner--info"><?= icon('info', 16) ?>
      <span><?= e(words('This request will be automatically routed to your {Class} {Teacher} ') . "({$classTeacher}) for initial verification.") ?></span>
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
