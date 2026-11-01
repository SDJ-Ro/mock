<?php
// Admin home page: 5 number cards, bar chart + calendar (with "Add event"), 2 donuts + upcoming events.
// Data: DashboardController::show().
$tabTitle  = 'Admin Dashboard';
$title     = 'Dashboard';
$subtitle  = "Welcome back, {$userName}!";
$styles    = ['page-header', 'metric-card', 'dropdown', 'panel', 'chart', 'bar-chart', 'calendar/calendar', 'calendar/day-card', 'calendar/day-card-popup', 'calendar/event-editor', 'form-field', 'donut-chart', 'upcoming-events', 'tag'];
$scripts   = ['dropdown', 'chart', 'bar-chart', 'calendar/calendar', 'calendar/event-editor', 'donut-chart'];
$grids     = ['metric-cards', 'chart-calendar', 'donuts-events'];
require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_page_header.php';
?>

<?php if ($metrics !== []): ?>
  <div class="l-metric-cards-grid">
    <?php foreach ($metrics as $card): ?>
      <?php require dirname(__DIR__) . '/components/_metric_card.php'; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($chartConfig !== null || $calendarConfig !== null): ?>
  <div class="l-chart-calendar-grid">
    <?php if ($chartConfig !== null): ?>
      <?php require dirname(__DIR__) . '/components/_bar_chart.php'; ?>
    <?php endif; ?>
    <?php if ($calendarConfig !== null): ?>
      <?php require dirname(__DIR__) . '/components/calendar/_calendar.php'; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if ($donuts !== [] || $upcomingEvents !== null): ?>
  <section class="l-donuts-events-grid" aria-label="Events and participation breakdowns">
    <?php foreach ($donuts as $donutConfig): ?>
      <?php require dirname(__DIR__) . '/components/_donut_chart.php'; ?>
    <?php endforeach; ?>
    <?php if ($upcomingEvents !== null): ?>
      <?php require dirname(__DIR__) . '/components/_upcoming_events.php'; ?>
    <?php endif; ?>
  </section>
<?php endif; ?>

<?php require dirname(__DIR__) . '/layouts/page_bottom.php'; ?>
