<?php
// The Year-End page (admin and management, the same page for both; opened from the Academic page's header):
//   Step 1 the mapping — every class of this year with its active students: a class that moves up picks next year's
//          class (a GET form: "Preview" only shows what would happen); the final grade leaves; an empty class stays
//   Step 2 the preview (after "Preview") — who goes where, who leaves, the teachers carried over, every warning
//          (terracotta) and problem (red). Without problems: "Run Year-End", which asks first (data-question-…,
//          question-pop-up.js) and sends the same choices to …/run.
// Its look: year-end.css (+ panel.css, banner.css, tag.css, dropdown.css); the rows: layout/year-end-grid.css.
// Data: YearEndController::show() → $pageUrl, $year, $rows, $plan (null until "Preview"). From the frame: $csrfField.
$title    = 'Year-End';
$subtitle = words('Move every {student} up one {grade}: ') . "{$year} → " . ($year + 1) . '.';
require __DIR__ . '/_page_header.php';
?>

<div class="l-year-end-grid">
  <section class="c-panel c-year-end" aria-labelledby="j-year-end-mapping">
    <h2 class="c-panel__title" id="j-year-end-mapping"><?= e(words("1 · Next Year's {Classes}")) ?></h2>
    <p class="c-banner c-banner--info"><?= icon('info', 16) ?>
      <span><?= e(words('Choose where each {class} goes next year. The last {grade} leaves the {institution}. Nothing is saved until you run it.')) ?></span>
    </p>

    <form method="get" action="<?= e($pageUrl) ?>" class="c-year-end__form">
      <input type="hidden" name="preview" value="1">
      <ul class="c-year-end__rows">
        <?php foreach ($rows as $row): ?>
          <li class="c-year-end__row">
            <span class="c-year-end__class"><?= e($row['name']) ?></span>
            <span class="c-tag c-tag--soft"><?= e($row['students'] . ' ' . strtolower($row['students'] === 1 ? word('student') : plural('student'))) ?></span>

            <?php if ($row['students'] === 0): ?>
              <span class="c-year-end__note">Nobody to move</span>
            <?php elseif ($row['leaves']): ?>
              <span class="c-tag c-tag--sand c-year-end__target"><?= e(words('Leaves the {institution}')) ?></span>
            <?php else: ?>
              <?= icon('arrowRight', 16, 'c-year-end__arrow') ?>
              <div class="c-year-end__target">
                <?php
                $dropdownId    = "j-year-end-to-{$row['id']}";
                $dropdownLabel = words("Next year's {class} for ") . $row['name'];
                $name          = "to_{$row['id']}";
                $options       = $row['options'];
                $selectedValue = $row['chosen'];
                $placeholder   = words('Choose a {class}');
                $required      = words("Please choose next year's {class}.");
                require __DIR__ . '/_dropdown.php';
                ?>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="c-year-end__actions">
        <button type="submit" class="c-btn c-btn--outline">Preview</button>
      </div>
    </form>
  </section>

  <?php if ($plan !== null): ?>
    <section class="c-panel c-year-end" aria-labelledby="j-year-end-preview">
      <h2 class="c-panel__title" id="j-year-end-preview">2 · Preview</h2>

      <?php foreach ($plan['errors'] as $problem): ?>
        <p class="c-banner c-banner--error"><?= icon('alertCircle', 16) ?><span><?= e($problem) ?></span></p>
      <?php endforeach; ?>
      <?php foreach ($plan['warnings'] as $warning): ?>
        <p class="c-banner c-banner--warning"><?= icon('alertTriangle', 16) ?><span><?= e($warning) ?></span></p>
      <?php endforeach; ?>

      <ul class="c-year-end__rows">
        <?php foreach ($plan['lines'] as $line): ?>
          <li class="c-year-end__row"><?= e($line) ?></li>
        <?php endforeach; ?>
      </ul>

      <p class="c-year-end__note">
        <?= e('Carried over to ' . ($year + 1) . " unchanged: {$plan['carried']['class']} " . words('{class} {teachers}') . ", {$plan['carried']['subject']} "
            . words('{subject} {teachers}') . ", {$plan['carried']['in_charge']} " . strtolower(plural('teacher')) . ' in charge (change them later on the usual pages).') ?>
      </p>

      <?php if ($plan['errors'] === []): ?>
        <form method="post" action="<?= e("{$pageUrl}/run") ?>" class="c-year-end__actions"
              data-question-title="<?= e("Run year-end for {$year}?") ?>"
              data-question-text="<?= e(($year + 1) . words(' starts: every {student} above moves up, and the last {grade} leaves. This can\'t be undone.')) ?>"
              data-question-button="Run Year-End">
          <?= $csrfField ?>
          <input type="hidden" name="year" value="<?= e($year) ?>">
          <?php foreach ($rows as $row): ?>
            <?php if ($row['students'] > 0 && !$row['leaves']): ?>
              <input type="hidden" name="<?= e("to_{$row['id']}") ?>" value="<?= e($row['chosen']) ?>">
            <?php endif; ?>
          <?php endforeach; ?>
          <button type="submit" class="c-btn c-btn--maroon">Run Year-End</button>
        </form>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</div>
<?php unset($rows, $row, $plan, $problem, $warning, $line); ?>
