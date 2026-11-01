<?php
// The class editor: section, places and class teacher, in a row inside a grade card.
// The page has ONE, hidden; academic.js moves it under the class whose ✎ was pressed (or to the end of a grade for
// "Add Class") and fills it in. After a refused save the page opens it again where it was ($classForm).
// Saving is a normal form: AcademicController::saveClass() → back to the grade. Its look: grade-class-editor.css.
// Set before requiring:
//   $classTeacherOptions  the class teacher picker's options (teachers who already have a class are greyed out;
//                         academic.js lets a class keep its own teacher)
//   $classForm            null, or the refused save: ['id', 'grade_id', 'prefix', 'section', 'capacity', 'teacher']
// From the page: $pageUrl, $csrfField.
$classSize = config('white_label.academic.class_size');
?>
<form class="c-class-editor" id="j-class-editor" method="post" action="<?= e("{$pageUrl}/classes/save") ?>" hidden data-warn-unsaved
      <?php if ($classForm !== null): ?>data-reopen="<?= e(json_encode($classForm)) ?>"<?php endif; ?>>
  <?= $csrfField ?>
  <input type="hidden" name="id" value="">
  <input type="hidden" name="grade_id" value="">

  <label class="c-class-editor__field">
    <span class="c-label">Section</span>
    <span class="c-class-editor__section">
      <span class="c-class-editor__prefix j-class-editor-prefix"></span>
      <input class="c-field__input c-field__input--small" name="section" placeholder="e.g. C" maxlength="10" required autocomplete="off">
    </span>
  </label>

  <label class="c-class-editor__field">
    <span class="c-label">Places</span>
    <input class="c-field__input c-field__input--small" name="capacity" type="number" required
           min="<?= e($classSize['min']) ?>" max="<?= e($classSize['max']) ?>" placeholder="e.g. <?= e($classSize['max']) ?>">
  </label>

  <div class="c-class-editor__field">
    <span class="c-class-editor__head">
      <span class="c-label"><?= e(words('{Class} {Teacher}')) ?></span>
      <span class="c-class-editor__hint">Hover a <?= e(strtolower(word('teacher'))) ?> to preview workload</span>
    </span>
    <?php
    $dropdownId    = 'j-class-editor-teacher';
    $dropdownLabel = words('{Class} {teacher}');
    $options       = $classTeacherOptions;
    $selectedValue = '';
    $name          = 'class_teacher_id';
    $variant       = 'teacher';
    require __DIR__ . '/_dropdown.php';
    ?>
  </div>

  <div class="c-class-editor__actions">
    <button type="submit" class="c-btn c-btn--solid c-btn--small"><?= icon('check', 14) ?>Save</button>
    <button type="button" class="c-btn c-btn--outline c-btn--small j-class-editor-cancel"><?= icon('close', 14) ?>Cancel</button>
  </div>
</form>
<?php unset($classSize); ?>
