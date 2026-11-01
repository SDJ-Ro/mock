<?php
// One teacher's workload card: shown beside a teacher picker while the mouse is on that
// teacher (academic.js). PHP draws one per teacher, hidden, so the script only shows it. Its look: teacher-workload.css.
// Later the Extracurricular teacher-in-charge picker shows it too.
// Set before requiring:
//   $teacher   ['id', 'name', 'qualification' (their subjects, or null), 'class_name' (class teacher of, or null)]
//   $workload  ['subjects' => ['Science' => ['9-B', '10-A']], 'extracurriculars' => ['Chess Club']] (either may be missing)
$workloadSubjects = $workload['subjects'] ?? [];
$workloadClubs    = $workload['extracurriculars'] ?? [];
?>
<div class="c-teacher-workload" id="j-workload-<?= e($teacher['id']) ?>" role="tooltip" hidden>
  <div class="c-teacher-workload__head">
    <span class="c-avatar" aria-hidden="true"><?= e(initials($teacher['name'])) ?></span>
    <div class="c-teacher-workload__who">
      <p class="c-teacher-workload__name"><?= e($teacher['name']) ?></p>
      <p class="c-teacher-workload__sub"><?= e($teacher['qualification'] ?: words('{Subjects} not added yet')) ?></p>
    </div>
  </div>

  <div class="c-teacher-workload__group">
    <p class="c-label"><?= icon('graduationCap', 12) ?> <?= e(words('{Class} {Teacher}')) ?></p>
    <?php if ($teacher['class_name'] !== null): ?>
      <span class="c-chip c-teacher-workload__chip--class"><?= e(word('class') . " {$teacher['class_name']}") ?></span>
    <?php else: ?>
      <span class="c-teacher-workload__empty">Not yet</span>
    <?php endif; ?>
  </div>

  <div class="c-teacher-workload__group">
    <p class="c-label"><?= icon('trophy', 12) ?> In Charge Of</p>
    <div class="c-teacher-workload__chips">
      <?php foreach ($workloadClubs as $club): ?>
        <span class="c-chip c-teacher-workload__chip--club"><?= e($club) ?></span>
      <?php endforeach; ?>
      <?php if ($workloadClubs === []): ?>
        <span class="c-teacher-workload__empty">None</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="c-teacher-workload__group">
    <p class="c-label"><?= icon('bookOpen', 12) ?> <?= e(words('{Subjects} Taught')) ?></p>
    <?php foreach ($workloadSubjects as $subject => $classNames): ?>
      <div class="c-teacher-workload__subject">
        <span class="c-teacher-workload__subject-name"><?= e($subject) ?></span>
        <span class="c-teacher-workload__chips">
          <?php foreach ($classNames as $className): ?>
            <span class="c-chip c-teacher-workload__chip"><?= e($className) ?></span>
          <?php endforeach; ?>
        </span>
      </div>
    <?php endforeach; ?>
    <?php if ($workloadSubjects === []): ?>
      <span class="c-teacher-workload__empty"><?= e(words('No {subjects} yet')) ?></span>
    <?php endif; ?>
  </div>
</div>
<?php unset($workloadSubjects, $workloadClubs, $club, $subject, $classNames, $className); ?>
