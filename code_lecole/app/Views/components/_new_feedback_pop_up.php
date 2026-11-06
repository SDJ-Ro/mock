<?php
// The teacher's "New Feedback" pop-up, in the shared frame (_pop_up.php); the page header's "+ New Feedback" opens
// it. Sends POST /teacher/feedback/save (FeedbackController::save(), which checks every value again). The fields:
// form-field.css. Set before requiring:
//   $students   the students of their class: [['value' => id, 'label' => 'Nethmi Perera (STU-…) - Grade 10-A'], …]
//   $subjects   the class's subjects + the general one (names)
//   $audiences  config/feedback.php 'audiences' (key → name)
// From the page: $types (config/feedback.php), $csrfField.
$lengths = config('feedback.lengths');
ob_start();
?>
<form class="c-pop-up__form" method="post" action="/teacher/feedback/save">
  <?= $csrfField ?>
  <div class="c-pop-up__body">
    <div class="c-field">
      <span class="c-label c-field__label"><?= e(words('Select {Student}')) ?> <span class="c-field__required c-field__required--terracotta">*</span></span>
      <?php
      $dropdownId    = 'j-new-feedback-student';
      $dropdownLabel = words('Select {student}');
      $name          = 'student';
      $options       = $students;
      $selectedValue = $students[0]['value'];
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <div class="c-field">
      <span class="c-label c-field__label">Audience <span class="c-field__required c-field__required--terracotta">*</span></span>
      <?php
      $dropdownId    = 'j-new-feedback-audience';
      $dropdownLabel = 'Who reads it';
      $name          = 'audience';
      $options       = array_map(fn (string $key, string $label): array => ['value' => $key, 'label' => $label], array_keys($audiences), $audiences);
      $selectedValue = array_key_first($audiences);
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
        <span class="c-label c-field__label"><?= e(word('subject')) ?> <span class="c-field__required c-field__required--terracotta">*</span></span>
        <?php
        $dropdownId    = 'j-new-feedback-subject';
        $dropdownLabel = word('subject');
        $name          = 'subject';
        $options       = $subjects;
        $selectedValue = $subjects[0];
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Feedback Title / Topic <span class="c-field__required c-field__required--terracotta">*</span></span>
      <input type="text" class="c-field__input" name="title" maxlength="<?= e($lengths['title']) ?>" required placeholder="e.g. Algebra Assessment Performance Review">
    </label>

    <label class="c-field">
      <span class="c-label c-field__label">Feedback Content <span class="c-field__required c-field__required--terracotta">*</span></span>
      <textarea class="c-field__input c-field__input--area c-new-feedback__content" name="content" rows="4" maxlength="<?= e($lengths['message']) ?>" required
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
$popUpDescription = words('Send positive, constructive, or disciplinary feedback to a {student}, their {parents}, or both');
require __DIR__ . '/_pop_up.php';
unset($students, $subjects, $audiences, $lengths);
