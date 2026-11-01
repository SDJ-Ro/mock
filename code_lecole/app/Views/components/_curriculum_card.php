<?php
// A curriculum stage and its inline editor. Its look: curriculum-card.css.
// $curriculumVariant:
//   'card'   one stage: "Grades 6–9", its description, "6 subjects", ✎ and the bin, its subjects as chips.
//            The bin asks first (shared question pop-up); a stage whose subjects have marks can't be deleted (blocked).
//            Set $stage: ['id', 'first_grade', 'last_grade', 'description', 'subjects' => [id => name], 'marks',
//                         'assigned' => [[grade number, subject id], …] (one per subject teacher chosen in a class)]
//   'editor' the page's ONE stage editor, hidden: academic.js puts it in place of the card whose ✎ was pressed (or at the
//            end for "Add Curriculum") and fills it in; after a refused save the page opens it again ($stageForm).
//            Range (from / to grade), description, subjects as chips (type in a chip to rename it, × removes, "+ Add"
//            adds), Cancel / Save. A save that would remove subject teachers asks first (data-shrink-…: one / many,
//            the script puts the number in front of "many").
// From the page: $pageUrl, $csrfField.
?>
<?php if ($curriculumVariant === 'card'): ?>
  <?php
  $stageTitle = grade_range($stage['first_grade'], $stage['last_grade']);
  $stageCount = count($stage['subjects']);
  ?>
  <article class="c-curriculum-card" id="stage-<?= e($stage['id']) ?>">
    <div class="c-curriculum-card__top">
      <div>
        <h3 class="c-curriculum-card__range"><?= e($stageTitle) ?></h3>
        <?php if ($stage['description']): ?>
          <p class="c-curriculum-card__desc"><?= e($stage['description']) ?></p>
        <?php endif; ?>
      </div>

      <div class="c-curriculum-card__actions">
        <span class="c-tag c-tag--round c-curriculum-card__count"><?= e($stageCount . ' ' . ($stageCount === 1 ? word('subject') : plural('subject'))) ?></span>
        <button type="button" class="c-btn c-btn--icon c-curriculum-card__icon j-stage-edit" aria-label="Edit <?= e($stageTitle) ?>"
                data-stage="<?= e(json_encode(['id' => $stage['id'], 'first_grade' => (string) $stage['first_grade'], 'last_grade' => (string) $stage['last_grade'],
                    'description' => $stage['description'] ?? '', 'assigned' => $stage['assigned'],
                    'subjects' => array_map(fn (int $id, string $name): array => ['id' => $id, 'name' => $name], array_keys($stage['subjects']), $stage['subjects'])])) ?>">
          <?= icon('edit', 14) ?>
        </button>
        <form method="post" action="<?= e("{$pageUrl}/stages/delete") ?>"
              data-question-title="<?= e("Delete the {$stageTitle} curriculum?") ?>"
              data-question-text="<?= e($stageTitle . words(' will have no {subjects}, and their {subject} {teachers} will be removed.')) ?>"
              data-question-button="Delete Curriculum"
              data-question-blocked="<?= e($stage['marks'] > 0 ? words('Its {subjects} have ') . $stage['marks'] . " marks, so it can't be deleted." : '') ?>">
          <?= $csrfField ?>
          <input type="hidden" name="id" value="<?= e($stage['id']) ?>">
          <button type="submit" class="c-btn c-btn--icon c-curriculum-card__icon" aria-label="Delete <?= e($stageTitle) ?>"><?= icon('trash', 14) ?></button>
        </form>
      </div>
    </div>

    <div class="c-curriculum-card__subjects">
      <?php foreach (array_values($stage['subjects']) as $index => $subjectName): ?>
        <span class="c-chip c-curriculum-card__subject c-curriculum-card__subject--<?= e($index % 5) ?>"><?= e($subjectName) ?></span>
      <?php endforeach; ?>
    </div>
  </article>
  <?php unset($stageTitle, $stageCount, $index, $subjectName); ?>

<?php else: ?>
  <?php $gradeNumbers = config('white_label.academic.grade_numbers'); ?>
  <form class="c-curriculum-card c-curriculum-card--editor" id="j-stage-editor" method="post" action="<?= e("{$pageUrl}/stages/save") ?>" hidden data-warn-unsaved
        data-shrink-title="Remove <?= e(words('{subject} {teacher}')) ?> choices?"
        data-shrink-one="<?= e(words('1 {subject} {teacher} choice will be removed: a {class} outside the new range, or a {subject} you took out.')) ?>"
        data-shrink-many="<?= e(words(' {subject} {teacher} choices will be removed: {classes} outside the new range, or {subjects} you took out.')) ?>"
        <?php if ($stageForm !== null): ?>data-reopen="<?= e(json_encode($stageForm)) ?>"<?php endif; ?>>
    <?= $csrfField ?>
    <input type="hidden" name="id" value="">

    <div class="c-curriculum-card__field">
      <span class="c-label"><?= e(word('grade')) ?> Range</span>
      <div class="c-curriculum-card__range-row">
        <?php foreach (['first_grade' => 'From', 'last_grade' => 'To'] as $field => $fieldLabel): ?>
          <div class="c-curriculum-card__range-pick">
            <span class="c-curriculum-card__small"><?= e($fieldLabel) ?></span>
            <?php
            $dropdownId    = "j-stage-{$field}";
            $dropdownLabel = "{$fieldLabel} " . strtolower(word('grade'));
            $options       = array_map(fn (int $n): array => ['value' => (string) $n, 'label' => grade_name($n)], range($gradeNumbers['first'], $gradeNumbers['last']));
            $selectedValue = (string) $gradeNumbers['first'];
            $name          = $field;
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
        <?php endforeach; ?>
        <span class="c-chip c-curriculum-card__range-label j-stage-range" data-grade="<?= e(word('grade')) ?>" data-grades="<?= e(plural('grade')) ?>"></span>
      </div>
    </div>

    <label class="c-curriculum-card__field">
      <span class="c-label">Description</span>
      <input class="c-field__input c-field__input--small" name="description" maxlength="255" placeholder="e.g. Exam preparation and specialisation.">
    </label>

    <div class="c-curriculum-card__field">
      <span class="c-label"><?= e(plural('subject')) ?></span>
      <div class="c-curriculum-card__chips j-stage-chips"></div>
      <div class="c-curriculum-card__add">
        <input class="c-field__input c-field__input--small j-stage-new-subject" maxlength="100" placeholder="<?= e(words('Add a {subject}, e.g. Geography')) ?>" aria-label="<?= e(words('New {subject}')) ?>">
        <button type="button" class="c-btn c-btn--small c-curriculum-card__add-btn j-stage-add-subject"><?= icon('plus', 12) ?>Add</button>
      </div>
      <template id="j-stage-chip">
        <span class="c-chip c-curriculum-card__chip">
          <input type="hidden" name="subject_ids[]" value="">
          <input class="c-curriculum-card__chip-name j-stage-chip-name" name="subjects[]" maxlength="100" aria-label="<?= e(words('{Subject} name')) ?>">
          <button type="button" class="c-curriculum-card__chip-remove j-stage-remove-subject" aria-label="<?= e(words('Remove {subject}')) ?>"><?= icon('close', 10) ?></button>
        </span>
      </template>
    </div>

    <div class="c-curriculum-card__footer">
      <button type="button" class="c-btn c-btn--outline c-btn--small j-stage-editor-cancel">Cancel</button>
      <button type="submit" class="c-btn c-btn--solid c-btn--small"><?= icon('check', 14) ?>Save</button>
    </div>
  </form>
  <?php unset($gradeNumbers, $field, $fieldLabel); ?>
<?php endif; ?>
<?php unset($curriculumVariant, $stage); ?>
