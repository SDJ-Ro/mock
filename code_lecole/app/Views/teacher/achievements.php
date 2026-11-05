<?php
// Teacher achievements: their students as cards (search + Class / Extracurricular filter); View opens one
// student's timeline (?student=…); Record opens the Record pop-up (their class) or the programme's "Add achievement"
// form (a club / sport they run). A student's missing-record request: the red ring, View Issue → the Review pop-up.
// Data: AchievementController::teacher().
$tabTitle = 'Achievements';
$styles   = ['page-header', 'panel', 'search-field', 'dropdown', 'empty-box', 'tag', 'student-card', 'achievements-timeline', 'form-field', 'datepicker', 'record-achievement'];
$scripts  = ['dropdown', 'datepicker', 'achievements-timeline', 'student-cards', 'record-achievement', 'file-box'];
$grids    = ['student-cards'];

require dirname(__DIR__) . '/layouts/page_top.php';
?>

<?php if ($student === null): ?>
  <?php
  $title    = 'Achievements';
  $subtitle = words('Track {student} and {institution} honours, awards, and recognitions.');
  require dirname(__DIR__) . '/components/_page_header.php';
  ?>

  <section class="l-student-cards-bar" aria-label="Filter <?= e(strtolower(plural('student'))) ?>">
    <label class="c-search c-search--rounded">
      <?= icon('search', 16) ?>
      <input type="search" class="c-search__input" id="j-student-search" placeholder="Search <?= e(strtolower(word('student'))) ?> name…" autocomplete="off" aria-label="Search <?= e(strtolower(plural('student'))) ?>">
    </label>
    <?php
    $dropdownId    = 'j-student-from';
    $options       = [['value' => 'all', 'label' => 'All Categories'], word('class'), 'Extracurricular'];   // the cards' 'from'
    $selectedValue = 'all';
    $prefix        = 'Filter';
    $dropdownLabel = 'Filter achievements category';
    require dirname(__DIR__) . '/components/_dropdown.php';
    ?>
  </section>

  <div class="l-student-cards-grid" id="j-student-cards">
    <?php foreach ($students as $studentCard): ?>
      <?php
      $studentCardVariant = 'grid';
      require dirname(__DIR__) . '/components/_student_card.php';
      ?>
    <?php endforeach; ?>
  </div>

  <div class="c-empty-box c-empty-box--titled" id="j-student-cards-empty" hidden>
    <?= icon('search', 40) ?>
    <p class="c-empty-box__title">No <?= e(strtolower(plural('student'))) ?> found</p>
    <p class="c-empty-box__text">Try adjusting your search query or selecting a different category filter.</p>
  </div>
<?php else: ?>
  <p class="l-student-cards-back">
    <a class="c-btn c-btn--link" href="/teacher/achievements"><?= icon('chevronLeft', 18) ?><span>Back</span></a>
  </p>

  <?php
  ob_start();
  $timelineVariant = 'plain';
  require dirname(__DIR__) . '/components/_achievements_timeline.php';
  $studentCardContent = ob_get_clean();
  $studentCard        = $student;
  $studentCardVariant = 'banner';
  require dirname(__DIR__) . '/components/_student_card.php';

  if ($issue !== null)
  {
      $recordVariant = 'review';
      $recordStudent = $student;
      $recordIssue   = $issue;
      require dirname(__DIR__) . '/components/_record_achievement_pop_up.php';
  }
  ?>
<?php endif; ?>

<?php
$recordVariant = 'record';
$recordStudent = null;
$recordIssue   = null;
require dirname(__DIR__) . '/components/_record_achievement_pop_up.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
