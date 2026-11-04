<?php
// The parent's "New Inquiry or Complaint" pop-up, in the shared frame (_pop_up.php); the page header's
// "+ New Inquiry" opens it. The selected child is displayed here; the controller reads its ID from the session.
// Set before requiring: $children (selected child's name), $categories and $csrfField.
ob_start();
?>
<form class="c-pop-up__form" method="post" action="/parent/complaints/send">
  <?= $csrfField ?>
  <div class="c-pop-up__body">
    <label class="c-field">
      <span class="c-label c-field__label">Subject</span>
      <input type="text" class="c-field__input" name="subject" maxlength="<?= e(config('complaints.limits.subject')) ?>" required placeholder="e.g. Clarification on Chemistry Revision Syllabus">
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
        $name          = '';
        $options       = $children;
        $selectedValue = $children[0];
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Message</span>
      <textarea class="c-field__input c-field__input--area" name="message" rows="5" maxlength="<?= e(config('complaints.limits.body')) ?>" required placeholder="Describe your inquiry or concern in detail…"></textarea>
    </label>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--outline j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--solid" <?= $children[0] === '' ? 'disabled' : '' ?>><?= icon('send', 16) ?><span>Submit Inquiry</span></button>
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
