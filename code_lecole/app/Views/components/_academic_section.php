<?php
// The academic section: the Digital Record Book on the left; on the right the total marks card (green) and
// the Performance Trend card — one small bar chart per school year (the student's average and the grade's average in
// each term), shown one at a time with ‹ › and dots. PHP draws every chart; academic-section.js only switches them,
// follows the record book ('record-book:change') and shows the bar tooltip. A chart card's click opens that year in the
// book. Its look: academic-section.css; the two columns: layout/academic-grid.css. Used by student/academic and the
// parent's child profile (People's pop-up shows only the record book). Set first: $records, $year, $term, $exportUrl
// (see _digital_record_book.php). No published term yet → only the book's empty box.
if ($records === [])
{
    require __DIR__ . '/_digital_record_book.php';

    return;
}

$shown = $records[$year]['terms'][$term];
$years = array_keys($records);

// One trend chart: 260 × 130, the plot inside the margins; two bars per term, 0–100
$chart = ['width' => 260, 'height' => 130, 'left' => 34, 'right' => 8, 'top' => 10, 'bottom' => 30];
$plotW = $chart['width'] - $chart['left'] - $chart['right'];
$plotH = $chart['height'] - $chart['top'] - $chart['bottom'];
$baseY = $chart['top'] + $plotH;                       // the X-axis line
$termLetter = mb_substr(word('term'), 0, 1);          // "T1", "T2"…
?>
<div class="l-academic-grid">
  <?php require __DIR__ . '/_digital_record_book.php'; ?>

  <div class="l-academic-grid__side j-academic-side" data-book="j-record-book">
    <section class="c-total-marks" aria-label="Total marks">
      <span class="c-total-marks__icon" aria-hidden="true"><?= icon('trendingUp', 22) ?></span>
      <p class="c-label c-total-marks__label">Total Marks (<span class="j-total-term"><?= e(word('term') . " {$term}") ?></span>)</p>
      <p class="c-total-marks__value">
        <span class="j-total-scored"><?= e($shown['total']) ?></span>
        <span class="c-total-marks__out-of">/<span class="j-total-out-of"><?= e($shown['outOf']) ?></span></span>
      </p>
      <p class="c-total-marks__line">Average: <span class="j-total-average"><?= e($shown['average']) ?></span></p>
      <p class="c-total-marks__line"><?= e(word('class')) ?> Position: <strong class="j-total-placement"><?= e($shown['placement']) ?></strong></p>
    </section>

    <section class="c-card c-trend" aria-labelledby="j-trend-title">
      <h2 class="c-panel__title c-trend__title" id="j-trend-title">Performance Trend</h2>
      <p class="c-trend__sub">
        Comparative progress across academic terms, <?= e($records[reset($years)]['label']) ?> – <?= e($records[end($years)]['label']) ?>
      </p>

      <div class="c-trend__carousel">
        <button type="button" class="c-trend__nav j-trend-prev" aria-label="Previous <?= e(strtolower(word('grade'))) ?>"<?= $year === reset($years) ? ' disabled' : '' ?>>
          <?= icon('chevronLeft', 14) ?>
        </button>

        <?php foreach ($records as $chartYear => $chartBook): ?>
          <?php
          $chartTerms = $chartBook['terms'];
          $groupW     = $plotW / count($chartTerms);   // one term's space
          $barW       = $groupW * 0.12;
          $barGap     = $groupW * 0.06;

          // What a screen reader says for this card (the bars' numbers only show on hover)
          $spoken = "Open {$chartBook['label']} in the record book.";

          foreach ($chartTerms as $chartTermNumber => $chartTerm)
          {
              $spoken .= ' ' . word('term') . " {$chartTermNumber}: my average {$chartTerm['mine']}%, " . strtolower(word('grade')) . " average {$chartTerm['gradeAverage']}%.";
          }
          ?>
          <button type="button" class="c-trend__card j-trend-card<?= $chartYear === $year ? ' c-trend__card--current' : '' ?>"
                  data-year="<?= e($chartYear) ?>" aria-label="<?= e($spoken) ?>"
                  <?= $chartYear === $year ? '' : 'hidden' ?>>
            <span class="c-trend__card-head">
              <span class="c-trend__card-title"><?= e($chartBook['label']) ?></span>
              <span class="c-trend__card-average">Avg <?= e(round(array_sum(array_column($chartTerms, 'mine')) / count($chartTerms))) ?>%</span>
            </span>
            <svg class="c-trend__chart" viewBox="0 0 <?= e($chart['width']) ?> <?= e($chart['height']) ?>" preserveAspectRatio="none" aria-hidden="true">
              <?php foreach ([0, 50, 100] as $tick): ?>
                <text class="c-trend__tick" x="<?= e($chart['left'] - 6) ?>" y="<?= e($baseY - $tick / 100 * $plotH + 3) ?>" text-anchor="end"><?= e($tick) ?></text>
              <?php endforeach; ?>
              <line class="c-trend__axis" x1="<?= e($chart['left']) ?>" y1="<?= e($chart['top']) ?>" x2="<?= e($chart['left']) ?>" y2="<?= e($baseY) ?>"/>
              <line class="c-trend__axis" x1="<?= e($chart['left']) ?>" y1="<?= e($baseY) ?>" x2="<?= e($chart['width'] - $chart['right']) ?>" y2="<?= e($baseY) ?>"/>
              <text class="c-trend__axis-title" x="9" y="<?= e($chart['top'] + $plotH / 2) ?>" text-anchor="middle" transform="rotate(-90 9 <?= e($chart['top'] + $plotH / 2) ?>)">Marks</text>
              <text class="c-trend__axis-title" x="<?= e($chart['left'] + $plotW / 2) ?>" y="<?= e($chart['height'] - 4) ?>" text-anchor="middle">Academic <?= e(word('term')) ?></text>

              <?php foreach ($chartTerms as $chartTermNumber => $chartTerm): ?>
                <?php
                $index  = array_search($chartTermNumber, array_keys($chartTerms), true);
                $middle = $chart['left'] + $index * $groupW + $groupW / 2;
                $bars   = ['mine' => $middle - $barGap / 2 - $barW, 'grade' => $middle + $barGap / 2];
                $values = ['mine' => $chartTerm['mine'], 'grade' => $chartTerm['gradeAverage']];
                ?>
                <?php foreach ($bars as $series => $x): ?>
                  <?php $barH = max($values[$series] / 100 * $plotH, 2); ?>
                  <rect class="c-trend__bar c-trend__bar--<?= e($series) ?> j-trend-bar" x="<?= e($x) ?>" y="<?= e($baseY - $barH) ?>"
                        width="<?= e($barW) ?>" height="<?= e($barH) ?>" rx="2"
                        data-label="<?= e("{$chartBook['label']} — " . word('term') . " {$chartTermNumber}") ?>"
                        data-mine="<?= e($values['mine']) ?>" data-average="<?= e($values['grade']) ?>"/>
                <?php endforeach; ?>
                <text class="c-trend__term" x="<?= e($middle) ?>" y="<?= e($baseY + 13) ?>" text-anchor="middle"><?= e($termLetter . $chartTermNumber) ?></text>
              <?php endforeach; ?>
            </svg>
          </button>
        <?php endforeach; ?>

        <button type="button" class="c-trend__nav j-trend-next" aria-label="Next <?= e(strtolower(word('grade'))) ?>"<?= $year === end($years) ? ' disabled' : '' ?>>
          <?= icon('chevronRight', 14) ?>
        </button>
      </div>

      <div class="c-trend__dots">
        <?php foreach ($years as $dotYear): ?>
          <button type="button" class="c-trend__dot j-trend-dot" data-year="<?= e($dotYear) ?>"
                  aria-label="Show <?= e($records[$dotYear]['label']) ?>" aria-pressed="<?= $dotYear === $year ? 'true' : 'false' ?>"></button>
        <?php endforeach; ?>
      </div>

      <p class="c-trend__legend">
        <span class="c-trend__key c-trend__key--mine">My Average</span>
        <span class="c-trend__key c-trend__key--grade"><?= e(word('grade')) ?> Average</span>
      </p>

      <div class="c-trend__tooltip j-trend-tooltip" role="tooltip" hidden>
        <strong class="j-trend-tooltip-label"></strong><br>
        My Average: <span class="c-trend__tooltip-mine"><span class="j-trend-tooltip-mine"></span>%</span><br>
        <?= e(word('grade')) ?> Average: <span class="c-trend__tooltip-grade"><span class="j-trend-tooltip-grade"></span>%</span>
      </div>
    </section>
  </div>
</div>
<?php unset($shown, $years, $chart, $plotW, $plotH, $baseY, $groupW, $barW, $barGap, $termLetter, $chartYear, $chartBook, $chartTerms, $chartTerm, $chartTermNumber,
            $index, $middle, $bars, $values, $series, $x, $barH, $spoken, $tick, $dotYear); ?>
