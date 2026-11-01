<?php
// The "Add Grade" pop-up, in the shared frame (_pop_up.php); "+ Add Grade" opens it.
// A grade is only its number (its name comes from word('grade')), so it is picked from the numbers not used yet,
// never typed. Saving: AcademicController::addGrade() → back to the page at the new grade.
// Set before requiring:
//   $freeGradeNumbers  the grade numbers this school can still add ([] → a sentence instead of the picker)
// From the page: $pageUrl, $csrfField.
ob_start();
?>
<form class="c-pop-up__form" method="post" action="<?= e("{$pageUrl}/grades/add") ?>">
  <?= $csrfField ?>
  <div class="c-pop-up__body">
    <?php if ($freeGradeNumbers === []): ?>
      <p class="c-banner c-banner--info"><?= icon('info', 16) ?><span><?= e('Every ' . strtolower(word('grade')) . ' number is already in use.') ?></span></p>
    <?php else: ?>
      <div class="c-field">
        <span class="c-label"><?= e(word('grade')) ?></span>
        <?php
        $dropdownId    = 'j-add-grade-number';
        $dropdownLabel = word('grade');
        $options       = array_map(fn (int $number): array => ['value' => (string) $number, 'label' => grade_name($number)], $freeGradeNumbers);
        $selectedValue = (string) $freeGradeNumbers[0];
        $name          = 'number';
        require __DIR__ . '/_dropdown.php';
        ?>
        <span class="c-field__hint"><?= e(words('It starts with one {class}, section ') . config('white_label.academic.first_section') . words('; add more {classes} on its card.')) ?></span>
      </div>
    <?php endif; ?>
  </div>
  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--outline j-pop-up-close">Cancel</button>
    <?php if ($freeGradeNumbers !== []): ?>
      <button type="submit" class="c-btn c-btn--solid"><?= icon('plus', 14) ?><?= e('Add ' . word('grade')) ?></button>
    <?php endif; ?>
  </div>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-add-grade';
$popUpIcon        = 'graduationCap';
$popUpEyebrow     = 'Academic Structure';
$popUpTitle       = 'Add ' . word('grade');
$popUpDescription = words('The new {grade} shows up in reports, schedules and the {student} directory.');
require __DIR__ . '/_pop_up.php';
unset($freeGradeNumbers);
