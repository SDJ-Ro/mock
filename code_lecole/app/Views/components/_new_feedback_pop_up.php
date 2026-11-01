<?php
// The teacher's "New Feedback" pop-up, in the shared frame (_pop_up.php); the page
// header's "+ New Feedback" opens it. The choices are example data and sending saves nothing yet: the form
// closes and says so (data-mock-message, feedback-banner.js). The fields: form-field.css.
// Set before requiring: $students, $subjects (app/Mock/feedback.php), $types (config/feedback.php).
ob_start();
?>
<form class="c-pop-up__form" data-mock-message="Feedback sent.">
  <div class="c-pop-up__body">
    <div class="c-field">
      <span class="c-label c-field__label"><?= e(words('Select {Student}')) ?> <span class="c-field__required c-field__required--terracotta">*</span></span>
      <?php
      $dropdownId    = 'j-new-feedback-student';
      $dropdownLabel = words('Select {student}');
      $name          = 'student';
      $options       = $students;
      $selectedValue = $students[0];
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <div class="c-field-pair">
      <div class="c-field">
        <span class="c-label c-field__label">Feedback Category <span class="c-field__required c-field__required--terracotta">*</span></span>
        <?php
        $dropdownId    = 'j-new-feedback-type';
        $dropdownLabel = 'Feedback category';
        $name          = 'type';
        $options       = array_map(fn (string $key, array $type): array => ['value' => $key, 'label' => $type[0]], array_keys($types), $types);
        $selectedValue = array_key_first($types);
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>

      <div class="c-field">
        <span class="c-label c-field__label">Subject <span class="c-field__required c-field__required--terracotta">*</span></span>
        <?php
        $dropdownId    = 'j-new-feedback-subject';
        $dropdownLabel = 'Subject';
        $name          = 'subject';
        $options       = $subjects;
        $selectedValue = $subjects[0];
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Feedback Title / Topic <span class="c-field__required c-field__required--terracotta">*</span></span>
      <input type="text" class="c-field__input" name="title" maxlength="150" required placeholder="e.g. Algebra Assessment Performance Review">
    </label>

    <label class="c-field">
      <span class="c-label c-field__label">Feedback Content <span class="c-field__required c-field__required--terracotta">*</span></span>
      <textarea class="c-field__input c-field__input--area c-new-feedback__content" name="content" rows="4" required
                placeholder="<?= e(words('Write comprehensive feedback note for the {parent} and {student}…')) ?>"></textarea>
    </label>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--outline j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--terracotta c-btn--pill c-btn--large">Submit Feedback</button>
  </div>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-new-feedback';
$popUpSize        = 'wide';
$popUpEyebrow     = 'Feedback Channel';
$popUpTitle       = 'Create New Feedback';
$popUpDescription = words('Send positive, constructive, or disciplinary feedback to {parents}');
require __DIR__ . '/_pop_up.php';
unset($students, $subjects);
