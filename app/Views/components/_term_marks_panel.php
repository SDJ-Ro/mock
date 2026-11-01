<?php
// One student's marks panel on the teacher's Term Marks page: × at the top, the
// student's banner (their card's colour), a subject filter, one mark box per subject (0–100, or AB = absent), the
// feedback box (the class teacher's comment for the term), Cancel / Submit.
// PHP draws one panel per student, hidden; term-marks-panel.js shows the one whose "Edit Marks" was clicked and saves
// it in the background (TermMarkController::save()). Its look: term-marks-panel.css (the tint follows the card:
// c-marks-panel--{tone}). Set first:
//   $marksStudent  ['id', 'name', 'code', 'class', 'tone', 'photo' (or ''), 'marks' ([subject id => '72' / 'AB']), 'feedback']
//   $marksSubjects [subject id => name], in order   $marksTerm the term   $marksPanelId the panel's id
$student = $marksStudent;
?>
<aside class="c-marks-panel c-marks-panel--<?= e($student['tone']) ?> j-marks-panel" id="<?= e($marksPanelId) ?>"
       aria-label="<?= e("{$student['name']}'s marks") ?>" hidden>
  <div class="c-marks-panel__top">
    <button type="button" class="c-btn c-btn--close j-marks-close" aria-label="Close marks panel"><?= icon('close', 16) ?></button>
  </div>

  <div class="c-marks-panel__student">
    <?php if ($student['photo'] !== ''): ?>
      <img class="c-avatar c-avatar--medium c-marks-panel__avatar" src="<?= e($student['photo']) ?>" alt="">
    <?php else: ?>
      <span class="c-avatar c-avatar--medium c-marks-panel__avatar" aria-hidden="true"><?= e(initials($student['name'])) ?></span>
    <?php endif; ?>
    <div class="c-marks-panel__who">
      <h2 class="c-marks-panel__name"><?= e($student['name']) ?></h2>
      <p class="c-marks-panel__sub">
        <span><?= e($student['code']) ?></span>
        <span class="c-marks-panel__dot" aria-hidden="true">•</span>
        <span><?= e(word('class') . " {$student['class']}") ?></span>
      </p>
    </div>
  </div>

  <form class="c-marks-panel__form j-marks-form" method="post" action="/teacher/marks/save" data-warn-unsaved>
    <?= $csrfField ?>
    <input type="hidden" name="student_id" value="<?= e($student['id']) ?>">
    <input type="hidden" name="term" value="<?= e($marksTerm) ?>">

    <div class="c-marks-panel__filter">
      <span class="c-label"><?= e(words('Filter {Subject}')) ?></span>
      <?php
      $dropdownId    = "{$marksPanelId}-subject";
      $variant       = 'tick';
      $options       = [['value' => 'all', 'label' => 'All'], ...array_values($marksSubjects)];
      $selectedValue = 'all';
      $dropdownLabel = words('Filter {subject}');
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <div class="c-marks-panel__grid">
      <?php foreach ($marksSubjects as $subjectId => $subject): ?>
        <label class="c-marks-panel__mark j-marks-row" data-subject="<?= e($subject) ?>">
          <span class="c-marks-panel__subject"><?= e($subject) ?></span>
          <input type="text" class="c-field__input c-field__input--small c-marks-panel__input" name="mark_<?= e($subjectId) ?>"
                 value="<?= e($student['marks'][$subjectId] ?? '') ?>" placeholder="—" maxlength="3" autocomplete="off"
                 pattern="100|[1-9]?[0-9]|[Aa][Bb]" title="A mark from 0 to 100, or AB (absent)">
        </label>
      <?php endforeach; ?>
    </div>

    <label class="c-marks-panel__feedback">
      <span class="c-label">Feedback About Marks</span>
      <textarea class="c-field__input c-field__input--area c-marks-panel__textarea" name="feedback" rows="3" maxlength="500" placeholder="Add feedback about <?= e(strtolower(word('student'))) ?>'s marks…"><?= e($student['feedback']) ?></textarea>
    </label>

    <div class="c-marks-panel__actions">
      <button type="button" class="c-btn c-btn--outline c-btn--pill j-marks-close">Cancel</button>
      <button type="submit" class="c-btn c-btn--solid c-btn--pill"><?= icon('check', 14) ?><span>Submit</span></button>
    </div>
  </form>
</aside>
<?php unset($student, $subjectId, $subject, $marksStudent, $marksPanelId, $marksTerm); ?>
