<?php
// The Academic Overview page (admin and management, the same page for both): the class
// section chart + the calendar, then two section boxes — the grades with their classes, and the curriculum stages.
// The page has one class editor and one stage editor (hidden); academic.js moves them to where ✎ or "+ Add" was pressed.
// Data: AcademicController::overview(). From the frame: $csrfField (and the sliding banner: what a save or refusal left).
$title    = 'Academic Overview';
$subtitle = 'Manage curriculum structure, assessment progress, and term schedules.';
require __DIR__ . '/_page_header.php';
?>

<?php if ($canSetGraduationDate): ?>
  <section class="c-card c-person__body" aria-label="Graduation date">
    <h2 class="c-panel__title">Graduation date</h2>
    <form method="post" action="/admin/academic/graduation-date">
      <?= $csrfField ?>
      <label class="c-field">
        <span class="c-label c-field__label"><?= e(grade_name((int) config('white_label.academic.grade_numbers.last'))) ?> graduation date</span>
        <input class="c-field__input" type="date" name="graduation_date" value="<?= e($graduationBatch['graduation_date'] ?? '') ?>" required>
      </label>
      <button type="submit" class="c-btn c-btn--solid c-btn--compact">Save date</button>
    </form>
  </section>
<?php endif; ?>

<div class="l-chart-calendar-grid l-chart-calendar-grid--tight">
  <?php if ($chartConfig !== null): ?>
    <?php require __DIR__ . '/_bar_chart.php'; ?>
  <?php endif; ?>
  <?php if ($calendarConfig !== null): ?>
    <?php require __DIR__ . '/calendar/_calendar.php'; ?>
  <?php endif; ?>
</div>

<div class="l-grade-sections">
  <?php ob_start(); ?>
  <?php if ($grades === []): ?>
    <div class="c-empty-box"><p class="c-empty-box__text"><?= e(words('No {grades} yet.')) ?></p></div>
  <?php else: ?>
    <div class="l-grade-grid">
      <?php foreach ($grades as $grade): ?>
        <?php require __DIR__ . '/_grade_card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php require __DIR__ . '/_grade_class_editor.php'; ?>
  <?php
  $sectionContent = ob_get_clean();
  $sectionId      = 'grades';
  $sectionTone    = 'sky';
  $sectionIcon    = 'graduationCap';
  $sectionTitle   = words('{Grade} & {Class} Structure');
  $sectionButton  = ['label' => 'Add ' . word('grade'), 'data' => ['pop-up-open' => 'j-add-grade']];
  require __DIR__ . '/_section_box.php';
  ?>

  <?php ob_start(); ?>
  <?php if ($stages === []): ?>
    <div class="c-empty-box"><p class="c-empty-box__text">No curriculum stages yet.</p></div>
  <?php endif; ?>
  <div class="l-stage-grid">
    <?php foreach ($stages as $stage): ?>
      <?php
      $curriculumVariant = 'card';
      require __DIR__ . '/_curriculum_card.php';
      ?>
    <?php endforeach; ?>
    <?php
    $curriculumVariant = 'editor';
    require __DIR__ . '/_curriculum_card.php';
    ?>
  </div>
  <?php
  $sectionContent = ob_get_clean();
  $sectionId      = 'curriculum';
  $sectionTone    = 'cherry';
  $sectionIcon    = 'bookOpen';
  $sectionTitle   = words('Curriculum {Subjects}');
  $sectionButton  = ['label' => 'Add Curriculum', 'data' => ['stage-add' => '']];
  require __DIR__ . '/_section_box.php';
  ?>
</div>

<?php // One workload card per teacher, hidden; academic.js shows the one whose name the mouse is on in a teacher picker ?>
<?php foreach ($teachers as $teacher): ?>
  <?php
  $workload = $workloads[$teacher['id']] ?? [];
  require __DIR__ . '/_teacher_workload.php';
  ?>
<?php endforeach; ?>

<?php require __DIR__ . '/_add_grade_pop_up.php'; ?>

<?php
unset($grade, $stage, $teacher, $workload);
