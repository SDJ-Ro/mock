<?php
// The student's "Request Character Certificate" pop-up, in the shared frame (_pop_up.php); the page header's
// button opens it. Sending saves nothing yet: the form closes and says so (data-mock-message,
// feedback-banner.js). The fields: form-field.css; the choices: config/certificates.php.
ob_start();
?>
<form class="c-pop-up__form" data-mock-message="Certificate requested.">
  <div class="c-pop-up__body">
    <div class="c-field">
      <span class="c-label c-field__label">Purpose <span class="c-field__required c-field__required--terracotta">*</span></span>
      <?php
      $dropdownId    = 'j-certificate-purpose';
      $dropdownLabel = 'Purpose';
      $name          = 'purpose';
      $options       = config('certificates.purposes');
      $placeholder   = 'Select a purpose…';
      $required      = 'Please select a purpose.';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <div class="c-field-pair">
      <label class="c-field">
        <span class="c-label c-field__label">Number of Copies <span class="c-field__required c-field__required--terracotta">*</span></span>
        <input type="number" class="c-field__input" name="copies" value="1" min="1" max="<?= e(config('certificates.max_copies')) ?>" required>
      </label>

      <div class="c-field">
        <span class="c-label c-field__label">Delivery Method <span class="c-field__required c-field__required--terracotta">*</span></span>
        <?php
        $dropdownId    = 'j-certificate-delivery';
        $dropdownLabel = 'Delivery method';
        $name          = 'delivery';
        $options       = config('certificates.deliveries');
        $placeholder   = 'Select delivery method…';
        $required      = 'Please select a delivery method.';
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Additional Notes</span>
      <textarea class="c-field__input c-field__input--area" name="notes" rows="3"
                placeholder="<?= e(words('Anything the ') . word('principal') . "'s Office should know (optional)…") ?>"></textarea>
    </label>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--outline j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--maroon">Submit Request</button>
  </div>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-certificate-request';
$popUpSize        = 'wide';
$popUpIcon        = 'fileCheck';
$popUpEyebrow     = words('{Student} Portal · Official Document');
$popUpTitle       = 'Request Character Certificate';
$popUpDescription = 'Request your official, signed certificate reviewed by the ' . word('principal') . "'s Office.";
require __DIR__ . '/_pop_up.php';
