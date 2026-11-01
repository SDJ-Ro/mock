<?php
// The parent's "New Inquiry or Complaint" pop-up, in the shared frame (_pop_up.php); the page header's
// "+ New Inquiry" opens it. Sending saves nothing yet: the form closes and says so (data-mock-message,
// feedback-banner.js). The fields: form-field.css; its sky icon badge: complaints.css.
// Set before requiring: $children (app/Mock/complaints.php). From the page: $categories (config/complaints.php).
ob_start();
?>
<form class="c-pop-up__form" data-mock-message="Inquiry submitted.">
  <div class="c-pop-up__body">
    <label class="c-field">
      <span class="c-label c-field__label">Subject</span>
      <input type="text" class="c-field__input" name="subject" maxlength="150" required placeholder="e.g. Clarification on Chemistry Revision Syllabus">
    </label>

    <div class="c-field-pair">
      <div class="c-field">
        <span class="c-label c-field__label">Category</span>
        <?php
        $dropdownId    = 'j-new-inquiry-category';
        $dropdownLabel = 'Category';
        $name          = 'category';
        $options       = array_keys($categories);
        $selectedValue = array_key_first($categories);
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>

      <div class="c-field">
        <span class="c-label c-field__label"><?= e(words('Associated {Student}')) ?></span>
        <?php
        $dropdownId    = 'j-new-inquiry-student';
        $dropdownLabel = words('Associated {student}');
        $name          = 'student';
        $options       = $children;
        $selectedValue = $children[0];
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Message</span>
      <textarea class="c-field__input c-field__input--area" name="message" rows="5" required placeholder="Describe your inquiry or concern in detail…"></textarea>
    </label>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--outline j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--solid"><?= icon('send', 16) ?><span>Submit Inquiry</span></button>
  </div>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-new-inquiry';
$popUpVariant     = 'inquiry';
$popUpIcon        = 'complaints';
$popUpEyebrow     = 'Communication';
$popUpTitle       = 'New Inquiry or Complaint';
$popUpDescription = words('Direct communication with {institution} departments and administration.');
require __DIR__ . '/_pop_up.php';
unset($children);
