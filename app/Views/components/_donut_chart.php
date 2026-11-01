<?php
// A donut chart: a ring of coloured slices, the total in the middle, the names underneath.
// Hovering a slice or a name shows "FOOTBALL · 150 students" (donut-chart.js). Needs $donutConfig from the controller:
//   'title'  → the card's title
//   'unit'   → the word after the number in the tooltip: 'students', 'members', 'players'
//   'slices' → [['name' => 'Football', 'value' => 150, 'color' => 'skyblue'], …]   (colour names: chart.css;
//              none → an empty ring and "No members yet.")
//
// How a slice is drawn: every slice is the same circle (radius 60, a 20px-wide outline), but its outline is
// a dash — only as long as its share of the ring, starting where the slice before it ended.
$slices = $donutConfig['slices'];
$total  = array_sum(array_column($slices, 'value'));
$ring   = 2 * M_PI * 60;                  // the length of the whole ring
$gap    = count($slices) > 1 ? 5 : 0;     // the white gap between two slices (about 5°)
$start  = 0;                              // where the next slice starts along the ring
?>
<section class="c-panel c-donut-card j-donut" data-unit="<?= e($donutConfig['unit']) ?>">
  <h2 class="c-panel__title"><?= e($donutConfig['title']) ?></h2>

  <div class="c-donut-card__plot">
    <svg class="c-donut-card__svg" viewBox="0 0 200 200">
      <g transform="rotate(-90 100 100)"><!-- the first slice starts at 12 o'clock -->
        <?php foreach ($slices as $slice): ?>
          <?php $length = $total > 0 ? $slice['value'] / $total * $ring : 0; ?>
          <circle class="c-donut-card__slice c-series--<?= e($slice['color']) ?> j-donut-slice" cx="100" cy="100" r="60"
                  stroke-dasharray="<?= e(max($length - $gap, 0)) ?> <?= e($ring) ?>" stroke-dashoffset="<?= e(-($start + $gap / 2)) ?>"
                  data-name="<?= e($slice['name']) ?>" data-value="<?= e(number_format($slice['value'])) ?>" />
          <?php $start += $length; ?>
        <?php endforeach; ?>
      </g>
    </svg>
    <div class="c-donut-card__center" aria-hidden="true">
      <span class="c-donut-card__center-value"><?= e(number_format($total)) ?></span>
      <span class="c-label c-donut-card__center-label">Total</span>
    </div>
  </div>

  <div class="c-donut-card__legend">
    <?php if ($slices === []): ?>
      <span class="c-donut-card__legend-item">No members yet.</span>
    <?php endif; ?>
    <?php foreach ($slices as $index => $slice): ?>
      <span class="c-donut-card__legend-item j-donut-legend-item" data-slice="<?= e($index) ?>">
        <span class="c-donut-card__legend-dot c-series--<?= e($slice['color']) ?>"></span>
        <?= e($slice['name']) ?>
      </span>
    <?php endforeach; ?>
  </div>
</section>
