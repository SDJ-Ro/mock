<?php
// Teacher Term Marks: the students of the class they are class teacher of this year, as cards under a pinned bar
// (search · Term · the term's status · Publish Term); "Edit Marks" opens that student's marks panel beside the cards.
// Data: TermMarkController::index() ($emptyText: nothing to work on — not a class teacher this year, or no term has
// started yet — only that empty box).
$tabTitle = word('term') . ' Marks';
$styles   = ['page-header', 'search-field', 'dropdown', 'form-field', 'tag', 'empty-box', 'student-card', 'term-marks-panel'];
$scripts  = ['dropdown', 'student-cards', 'term-marks-panel'];
$grids    = ['student-cards', 'term-marks'];

require dirname(__DIR__) . '/layouts/page_top.php';
?>

<div class="l-term-marks" id="j-term-marks">
  <?php
  $title    = word('term') . ' Marks';
  $subtitle = 'Manage and record ' . strtolower(word('student')) . ' ' . strtolower(word('term')) . ' marks and performance feedback.';
  require dirname(__DIR__) . '/components/_page_header.php';
  ?>

  <?php if ($emptyText !== null): ?>
    <div class="c-empty-box">
      <p class="c-empty-box__text"><?= e($emptyText) ?></p>
    </div>
  <?php else: ?>
    <section class="l-student-cards-bar l-student-cards-bar--marks" aria-label="Filter <?= e(strtolower(word('term'))) ?> marks">
      <label class="c-search c-search--rounded">
        <?= icon('search', 16) ?>
        <input type="search" class="c-search__input" id="j-student-search" placeholder="Search <?= e(strtolower(word('student'))) ?> name or index…"
               autocomplete="off" aria-label="Search <?= e(strtolower(plural('student'))) ?>">
      </label>

      <div class="l-student-cards-bar__end">
        <form method="get" action="/teacher/marks" data-submit-on-change>
          <?php
          $dropdownId    = 'j-term-filter';
          $options       = array_map(fn (int $number): array => ['value' => (string) $number, 'label' => word('term') . " {$number}"], $terms);
          $selectedValue = (string) $term;
          $dropdownLabel = 'Filter by ' . strtolower(word('term'));
          $name          = 'term';
          require dirname(__DIR__) . '/components/_dropdown.php';
          ?>
        </form>

        <?php if ($published !== null): ?>
          <span class="c-tag c-tag--round c-tag--moss">Published · <?= e($published) ?></span>
        <?php else: ?>
          <span class="c-tag c-tag--round c-tag--sand">Draft · <span id="j-marks-complete"><?= e($complete) ?></span> of <?= e($needed) ?> complete</span>
          <form method="post" action="/teacher/marks/publish"
                data-question-title="<?= e('Publish ' . word('term') . " {$term}?") ?>" data-question-button="Publish" data-question-icon="check" data-question-tone="success"
                data-question-text="<?= e(words("{$class['name']}'s {students} and their {parents} will see these marks. Corrections stay possible and are logged.")) ?>">
            <?= $csrfField ?>
            <input type="hidden" name="term" value="<?= e($term) ?>">
            <button type="submit" class="c-btn c-btn--solid c-btn--pill"><?= icon('check', 14) ?><span>Publish <?= e(word('term')) ?></span></button>
          </form>
        <?php endif; ?>
      </div>
    </section>

    <div class="l-term-marks__workspace">
      <div class="l-term-marks__cards">
        <div class="l-student-cards-grid l-student-cards-grid--marks" id="j-student-cards">
          <?php foreach ($students as $position => $studentCard): ?>
            <?php
            $studentCardVariant = 'marks';
            $studentCardTarget  = "j-marks-{$position}";
            require dirname(__DIR__) . '/components/_student_card.php';
            ?>
          <?php endforeach; ?>
        </div>
        <?php if ($students === []): ?>
          <div class="c-empty-box">
            <p class="c-empty-box__text"><?= e(words("No {students} in {$class['name']} yet.")) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <div class="l-term-marks__side">
        <?php foreach ($students as $position => $marksStudent): ?>
          <?php
          $marksPanelId  = "j-marks-{$position}";
          $marksSubjects = $subjects;
          $marksTerm     = $term;
          require dirname(__DIR__) . '/components/_term_marks_panel.php';
          ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/page_bottom.php'; ?>
