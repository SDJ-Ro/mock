<?php
// Student achievements: 4 number cards and the full timeline. Data: AchievementController::student()
// (example achievements until the achievements backend is built).
$tabTitle = 'Achievements';
$title    = 'Achievements';
$subtitle = 'A complete record of academic, sporting, and extracurricular honours earned during your time at ' . school('name') . '.';
$styles   = ['page-header', 'panel', 'metric-card', 'tag', 'achievements-timeline'];
$scripts  = ['achievements-timeline'];
$grids    = ['metric-cards'];

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_page_header.php';
?>

<div class="l-metric-cards-grid l-metric-cards-grid--four l-metric-cards-grid--tight">
  <?php foreach ($metrics as $card): ?>
    <?php require dirname(__DIR__) . '/components/_metric_card.php'; ?>
  <?php endforeach; ?>
</div>

<?php
$timelineVariant = 'panel';
require dirname(__DIR__) . '/components/_achievements_timeline.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
