<?php
// One grade: its name, students and classes, "+ Add Class" and "Delete", then its class rows.
// Delete asks first in the shared question pop-up (data-question-…): "All of Grade 6" or some of its classes — a class
// with students can't be ticked (its pill says why), and if nothing can be deleted the question says so (blocked).
// Its look: grade-card.css. Set before requiring:
//   $grade           ['id', 'number', 'students', 'classes' (rows for _grade_class_row), 'stage' (or null)]
//   $teacherOptions  the subject pickers' options     $subjectTeachers  [class id => [subject id => teacher id]]
// From the page: $pageUrl, $csrfField.
$cardName    = grade_name($grade['number']);
$cardClasses = count($grade['classes']);
$cardFormId  = "j-delete-grade-{$grade['id']}";
$cardEmpty   = array_filter($grade['classes'], fn (array $class): bool => $class['students'] === 0);
$cardBlocked = $grade['students'] > 0 && $cardEmpty === []
    ? "Every " . strtolower(word('class')) . " in {$cardName} has " . strtolower(plural('student')) . '. Move them first.'
    : '';
$studentWord = fn (int $count): string => $count . ' ' . strtolower($count === 1 ? word('student') : plural('student'));
?>
<article class="c-card c-grade-card" id="grade-<?= e($grade['id']) ?>">
  <div class="c-grade-card__head">
    <div>
      <h3 class="c-grade-card__name"><?= e($cardName) ?></h3>
      <p class="c-grade-card__meta">
        <?= icon('usersRound', 14) ?>
        <?= e($studentWord($grade['students']) . ' · ' . $cardClasses . ' ' . strtolower($cardClasses === 1 ? word('class') : plural('class'))) ?>
      </p>
    </div>

    <div class="c-grade-card__actions">
      <button type="button" class="c-btn c-btn--sky c-btn--small j-class-add"
              data-class="<?= e(json_encode(['id' => '', 'grade_id' => $grade['id'], 'prefix' => class_prefix($grade['number']), 'section' => '', 'capacity' => '', 'teacher' => ''])) ?>">
        <?= icon('plus', 13) ?><?= e('Add ' . word('class')) ?>
      </button>

      <form method="post" action="<?= e("{$pageUrl}/grades/delete") ?>" id="<?= e($cardFormId) ?>"
            data-question-title="<?= e("Delete {$cardName}") ?>"
            data-question-text="Choose the whole <?= e(strtolower(word('grade'))) ?>, or the <?= e(strtolower(plural('class'))) ?> you want to remove."
            data-question-button="Delete Selected" data-question-extra="<?= e("{$cardFormId}-choices") ?>"
            data-question-blocked="<?= e($cardBlocked) ?>">
        <?= $csrfField ?>
        <input type="hidden" name="grade_id" value="<?= e($grade['id']) ?>">
        <button type="submit" class="c-btn c-btn--danger c-btn--small"><?= icon('trash', 13) ?>Delete</button>
      </form>
    </div>
  </div>

  <div class="c-grade-card__classes">
    <?php foreach ($grade['classes'] as $classIndex => $class): ?>
      <?php
      $assigned = $subjectTeachers[$class['id']] ?? [];
      require __DIR__ . '/_grade_class_row.php';
      ?>
    <?php endforeach; ?>
  </div>

  <?php // The delete question's choices (question-pop-up.js copies them in); the boxes belong to the form above ?>
  <template id="<?= e("{$cardFormId}-choices") ?>">
    <div class="c-question__choices">
      <span class="c-label"><?= e(word('grade')) ?></span>
      <div class="c-question__pills">
        <label class="c-question__pill"<?= $grade['students'] > 0 ? ' title="' . e($studentWord($grade['students'])) . '"' : '' ?>>
          <input type="checkbox" name="whole" value="1" form="<?= e($cardFormId) ?>" <?= $grade['students'] > 0 ? 'disabled' : '' ?>>
          <?= icon('trash', 13) ?><?= e("All of {$cardName}") ?>
        </label>
      </div>
    </div>
    <div class="c-question__choices">
      <span class="c-label"><?= e(plural('class') . " ({$cardClasses})") ?></span>
      <div class="c-question__pills">
        <?php foreach ($grade['classes'] as $class): ?>
          <label class="c-question__pill"<?= $class['students'] > 0 ? ' title="' . e($studentWord($class['students'])) . '"' : '' ?>>
            <input type="checkbox" name="classes[]" value="<?= e($class['id']) ?>" form="<?= e($cardFormId) ?>" <?= $class['students'] > 0 ? 'disabled' : '' ?>>
            <?= e(word('class') . " {$class['name']}") ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </template>
</article>
<?php unset($cardName, $cardClasses, $cardFormId, $cardEmpty, $cardBlocked, $studentWord, $class, $classIndex, $assigned); ?>
