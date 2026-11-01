<?php
// One class inside a grade card: a <details> row — section badge, class teacher + ✎, students /
// places, "Subject Teachers" — that opens onto the subject list, one teacher picker per subject. A pick saves in the
// background (a <form data-background>, background-save.js); ✎ opens the page's one class editor here (academic.js).
// Its look: grade-class-row.css (+ dropdown.css 'teacher'). Set before requiring:
//   $class           ['id', 'name', 'capacity', 'students', 'class_teacher_id', 'class_teacher']
//   $classIndex      its place in the grade (0 = first): the badge's tint
//   $grade           the grade it is in (['id', 'number', 'stage'])
//   $assigned        who teaches what here: [subject id => teacher id]
//   $teacherOptions  the subject pickers' options (AcademicController's teachers, "Assignment pending" first)
// From the page: $pageUrl, $csrfField.
$rowSubjects = $grade['stage']['subjects'] ?? [];
$rowSection  = class_section($grade['number'], $class['name']);   // "6-A" → "A"
$rowTone     = ['sky', 'sunshine', 'terracotta', 'moss', 'cherry'][$classIndex] ?? 'linen';   // by place, so any section names work
?>
<details class="c-class-row" id="class-<?= e($class['id']) ?>">
  <summary class="c-class-row__summary">
    <span class="c-class-row__badge c-class-row__badge--<?= e($rowTone) ?>"><?= e($class['name']) ?></span>

    <div class="c-class-row__teacher">
      <span class="c-label"><?= e(words('{Class} {Teacher}')) ?></span>
      <span class="c-class-row__teacher-name<?= $class['class_teacher'] === null ? ' c-class-row__teacher-name--pending' : '' ?>"><?= e($class['class_teacher'] ?? 'Assignment pending') ?></span>
    </div>
    <button type="button" class="c-btn c-btn--icon j-class-edit" aria-label="Edit <?= e($class['name']) ?>"
            data-class="<?= e(json_encode(['id' => $class['id'], 'grade_id' => $grade['id'], 'prefix' => class_prefix($grade['number']), 'section' => $rowSection,
                'capacity' => $class['capacity'], 'teacher' => (string) ($class['class_teacher_id'] ?? '')])) ?>">
      <?= icon('edit', 14) ?>
    </button>

    <span class="c-chip c-chip--round c-class-row__places" title="<?= e(plural('student')) ?> / places">
      <?= icon('graduationCap', 12) ?><?= e("{$class['students']} / {$class['capacity']}") ?>
    </span>

    <span class="c-class-row__toggle"><?= e(words('{Subject} {Teachers}')) ?><?= icon('chevronDown', 16, 'c-class-row__chevron') ?></span>
  </summary>

  <div class="c-class-row__subjects">
    <p class="c-label c-class-row__subjects-title"><?= e(words('{Subject} Assignments')) ?></p>
    <?php foreach ($rowSubjects as $subjectId => $subjectName): ?>
      <form class="c-class-row__subject" method="post" action="<?= e("{$pageUrl}/subject-teacher") ?>" data-background>
        <?= $csrfField ?>
        <input type="hidden" name="class_id" value="<?= e($class['id']) ?>">
        <input type="hidden" name="subject_id" value="<?= e($subjectId) ?>">
        <span class="c-class-row__subject-name"><?= e($subjectName) ?></span>
        <?php
        $dropdownId    = "j-subject-teacher-{$class['id']}-{$subjectId}";
        $dropdownLabel = "{$subjectName} " . strtolower(word('teacher'));
        $options       = $teacherOptions;
        $selectedValue = (string) ($assigned[$subjectId] ?? '');
        $name          = 'teacher_id';
        $variant       = 'teacher';
        require __DIR__ . '/_dropdown.php';
        ?>
      </form>
    <?php endforeach; ?>
    <?php if ($rowSubjects === []): ?>
      <p class="c-class-row__note"><?= e(words('No {subjects} yet: add a curriculum stage for ') . grade_name($grade['number']) . words(' to choose its {subject} {teachers}.')) ?></p>
    <?php endif; ?>
  </div>
</details>
<?php unset($rowSubjects, $rowSection, $rowTone, $subjectId, $subjectName); ?>
