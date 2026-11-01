<?php
// The bar chart on the dashboards: two bars per group (a subject, a grade…), drawn as SVG by PHP.
// PHP works out every position; bar-chart.js only shows the tooltip, scrolls with the wheel and swaps terms.
// Needs $chartConfig from the controller:
//   'variant' → 'scores': 0–100 % with a Term picker (students, parents, teachers)
//               'counts': plain numbers on the 'ticks' you give (admin, management)
//   'id', 'title', 'yAxisTitle', 'xAxisTitle'
//   'series'  → the two bars: [['key' => 'mine', 'label' => 'My Score', 'color' => 'maroon'], […]]
//               (color = a colour name that has a .c-series--{name} rule in bar-chart.css)
//   scores: 'terms' => ['Term 1' => rows, …] and 'term' => the term shown first
//   counts: 'data' => rows and 'ticks' => [0, 40, 80, 120, 160]
//   a row:  ['label' => 'Math', 'mine' => 86, 'best' => 94]
//   'filters' (optional, counts) → pickers in the header that reload the page with ?name=value (Academic's Grade /
//               Subject / Term): [['name' => 'grade', 'label' => 'Grade', 'value' => '3', 'options' => [...]], …]
$isScores = $chartConfig['variant'] === 'scores';
$terms    = $isScores ? $chartConfig['terms'] : ['' => $chartConfig['data']];
$shown    = $isScores ? $chartConfig['term'] : '';
$ticks    = $isScores ? [0, 25, 50, 75, 100] : $chartConfig['ticks'];
$unit     = $isScores ? '%' : '';
$series   = $chartConfig['series'];
$highest  = end($ticks);

// Sizes: 380 high, Y-axis 50 wide; each group 126 wide (95 when there are more than 7); bars 24 wide, 6 apart
$height = 380;
$top    = 14;                                // room above the chart for the Y-axis arrow
$bottom = $height - 22;                      // the X-axis line; the group names go below it
$scale  = ($bottom - $top) / $highest;       // pixels for 1 (one mark, one student…)
$groups = $terms[$shown];
$groupW = count($groups) <= 7 ? 126 : 95;
$plotW  = count($groups) * $groupW;

// One group's value for one series in every term: ['Term 1' => 78, 'Term 2' => 81, …]
// No value → 0; never below 0 or above the top of the chart
$valuesOf = function (string $label, string $key) use ($terms, $highest): array
{
    $values = [];

    foreach ($terms as $term => $rows)
    {
        $row = array_values(array_filter($rows, fn (array $r): bool => $r['label'] === $label))[0] ?? [];
        $values[$term] = min(max((float) ($row[$key] ?? 0), 0), $highest);
    }

    return $values;
};
?>

<section class="c-panel j-bar-chart" id="<?= e($chartConfig['id']) ?>"
         data-scale="<?= e($scale) ?>" data-bottom="<?= e($bottom) ?>" data-unit="<?= e($unit) ?>">

  <div class="c-bar-chart-card__head">
    <h2 class="c-panel__title"><?= e($chartConfig['title']) ?></h2>

    <?php if ($isScores): ?>
      <!-- Term picker: the shared dropdown, cream variant -->
      <div class="c-bar-chart-card__extra">
        <?php
        $dropdownId    = $chartConfig['id'] . '-term';
        $variant       = 'cream';
        $options       = array_keys($terms);
        $selectedValue = $shown;
        $dropdownLabel = 'Select academic term';
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    <?php elseif (!empty($chartConfig['filters'])): ?>
      <!-- Filters: picking one reloads the page with the new choice (bar-chart.js) -->
      <form class="c-bar-chart-card__extra j-chart-filters" method="get">
        <?php foreach ($chartConfig['filters'] as $filter): ?>
          <?php
          $dropdownId    = "{$chartConfig['id']}-{$filter['name']}";
          $options       = $filter['options'];
          $selectedValue = $filter['value'];
          $dropdownLabel = $filter['label'];
          $name          = $filter['name'];
          require __DIR__ . '/_dropdown.php';
          ?>
        <?php endforeach; ?>
      </form>
    <?php endif; ?>
  </div>

  <!-- The Y-axis is its own drawing, so it stays put while the bars scroll sideways -->
  <div class="c-bar-chart__wrapper">
    <svg class="c-bar-chart__y-axis" width="50" height="<?= e($height) ?>" aria-hidden="true">
      <line class="c-bar-chart__axis-line" x1="49" y1="<?= e($top) ?>" x2="49" y2="<?= e($bottom) ?>" />
      <polygon class="c-bar-chart__arrow" points="49,<?= e($top - 6) ?> 44.5,<?= e($top + 2) ?> 53.5,<?= e($top + 2) ?>" />
      <text class="c-label c-bar-chart__axis-title" x="14" y="<?= e(($top + $bottom) / 2) ?>" text-anchor="middle"
            transform="rotate(-90 14 <?= e(($top + $bottom) / 2) ?>)"><?= e($chartConfig['yAxisTitle']) ?></text>
      <?php foreach ($ticks as $tick): ?>
        <text class="c-bar-chart__tick-label" x="42" y="<?= e($bottom - $tick * $scale + 4) ?>" text-anchor="end"><?= e($tick) ?></text>
      <?php endforeach; ?>
    </svg>

    <div class="c-bar-chart__area">
      <svg width="<?= e($plotW + 18) ?>" height="<?= e($height) ?>">
        <line class="c-bar-chart__axis-line" x1="0" y1="<?= e($bottom) ?>" x2="<?= e($plotW) ?>" y2="<?= e($bottom) ?>" />
        <polygon class="c-bar-chart__arrow" points="<?= e($plotW + 6) ?>,<?= e($bottom) ?> <?= e($plotW - 2) ?>,<?= e($bottom - 4.5) ?> <?= e($plotW - 2) ?>,<?= e($bottom + 4.5) ?>" />

        <?php foreach ($groups as $index => $row): ?>
          <?php $center = $index * $groupW + $groupW / 2; ?>
          <g class="c-bar-chart__group j-chart-group" data-label="<?= e($row['label']) ?>">
            <?php foreach ($series as $number => $one): ?>
              <?php
              $values = $valuesOf($row['label'], $one['key']);
              $barH   = max($values[$shown] * $scale, 2);   // a 0 still shows as a thin line
              ?>
              <rect class="c-bar-chart__bar c-series--<?= e($one['color']) ?> j-chart-bar" rx="4"
                    x="<?= e($number === 0 ? $center - 27 : $center + 3) ?>" y="<?= e($bottom - $barH) ?>" width="24" height="<?= e($barH) ?>"
                    data-series="<?= e($one['label']) ?>" data-color="<?= e($one['color']) ?>"
                    data-value="<?= e($values[$shown]) ?>" data-values="<?= e(json_encode($values)) ?>" />
            <?php endforeach; ?>
            <text class="c-bar-chart__cat-label" x="<?= e($center) ?>" y="<?= e($bottom + 16) ?>" text-anchor="middle"><?= e($row['label']) ?></text>
          </g>
        <?php endforeach; ?>
      </svg>
    </div>
  </div>

  <?php if ($chartConfig['xAxisTitle'] !== ''): ?>
    <div class="c-label c-bar-chart__axis-x-title"><?= e($chartConfig['xAxisTitle']) ?></div>
  <?php endif; ?>

  <div class="c-bar-chart__legend">
    <?php foreach ($series as $one): ?>
      <span class="c-bar-chart__legend-item">
        <span class="c-bar-chart__legend-dot c-series--<?= e($one['color']) ?>"></span>
        <?= e($one['label']) ?>
      </span>
    <?php endforeach; ?>
  </div>
</section>
