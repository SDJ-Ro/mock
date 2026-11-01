<?php
// A programme's "Schedule & Events" (the panel + its details sidebar): the shared dark calendar, and for
// staff beside it the teacher in charge, the coach, Details and Created Date (_staff_card.php, each "Edit" opens
// _staff_pop_up.php). The student's page shows the calendar alone, next to the roster.
// Looks: programme-panel.css, staff-card.css, calendar/*.css; layout: extracurricular-grid.css.
// Set before requiring: $scheduleSidebar (true = staff). From the controller: $programme, $can, $calendarConfig, $teachers.
$scheduleCards = $programme['cards'];
?>
<section class="c-card c-programme-panel">
  <header class="c-programme-panel__head">
    <h2 class="c-panel__title c-programme-panel__title"><span class="c-programme-panel__icon"><?= icon('calendar', 20) ?></span>Schedule &amp; Events</h2>
  </header>

  <?php if (!$scheduleSidebar): ?>
    <?php // The dashboards' width; here the calendar keeps its own height and sets the row's (the roster follows) ?>
    <div class="l-calendar-box">
      <?php if ($calendarConfig !== null): ?>
        <?php require __DIR__ . '/calendar/_calendar.php'; ?>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="l-schedule-grid">
      <div class="l-calendar-box">
        <?php if ($calendarConfig !== null): ?>
          <?php require __DIR__ . '/calendar/_calendar.php'; ?>
        <?php endif; ?>
      </div>
      <div class="l-schedule-grid__side">
        <?php foreach (['tic', 'coach'] as $staffWho): ?>
          <?php
          $staffCard = $scheduleCards[$staffWho] + ['edit' => $can['editStaff'] ? "j-staff-{$staffWho}" : null];
          require __DIR__ . '/_staff_card.php';
          ?>
        <?php endforeach; ?>
        <div class="l-schedule-grid__pair">
          <?php
          $staffCard = $scheduleCards['details'] + ['edit' => $can['edit'] ? 'j-staff-details' : null];
          require __DIR__ . '/_staff_card.php';
          $staffCard = $scheduleCards['created'] + ['edit' => null];
          require __DIR__ . '/_staff_card.php';
          ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php if ($scheduleSidebar): ?>
  <?php foreach (array_filter(['tic' => $can['editStaff'], 'coach' => $can['editStaff'], 'details' => $can['edit']]) as $staffPopUp => $staffAllowed): ?>
    <?php require __DIR__ . '/_staff_pop_up.php'; ?>
  <?php endforeach; ?>
<?php endif; ?>
<?php unset($scheduleSidebar, $scheduleCards, $staffWho, $staffAllowed); ?>
