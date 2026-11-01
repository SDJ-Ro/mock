<?php
// The "View All" pop-up: every event of the chosen day, in the shared pop-up frame (components/_pop_up.php).
// calendar.js fills the title, the description and the list (copies of <template id="tmpl-day-schedule-item">).
ob_start();
?>
<div class="c-day-schedule__body">
  <ol class="c-day-schedule__list" id="j-day-schedule-list"></ol>
  <div class="c-day-schedule__empty" id="j-day-schedule-empty" hidden>
    <span class="c-day-schedule__empty-icon" aria-hidden="true">
      <?= icon('calendar', 28) ?>
    </span>
    <h3 class="c-day-schedule__empty-title">No events scheduled</h3>
    <p class="c-day-schedule__empty-text" id="j-day-schedule-empty-text">This day is clear.</p>
  </div>
</div>
<?php
$popUpContent = ob_get_clean();
$popUpId      = 'j-day-schedule';
$popUpIcon    = 'calendar';
$popUpEyebrow = 'Day schedule';
$popUpVariant = 'day-schedule';
require dirname(__DIR__) . '/_pop_up.php';
?>

<!-- One event in the list (calendar.js copies it per event) -->
<template id="tmpl-day-schedule-item">
  <li class="c-day-schedule__item">
    <div class="c-day-schedule__item-top">
      <div class="c-day-schedule__heading-col">
        <p class="c-label c-day-schedule__time">
          <?= icon('clock', 13) ?>
          <span class="j-schedule-item-time"></span>
        </p>
        <h3 class="c-day-schedule__title j-schedule-item-title"></h3>
      </div>
      <div class="c-day-schedule__actions">
        <span class="c-tag c-day-schedule__type j-schedule-item-category" hidden></span>
        <button type="button" class="c-day-schedule__btn c-day-schedule__btn--edit j-schedule-edit" aria-label="Edit event" hidden>
          <?= icon('edit', 14) ?>
        </button>
        <button type="button" class="c-day-schedule__btn c-day-schedule__btn--delete j-schedule-delete" aria-label="Delete event" hidden>
          <?= icon('trash', 14) ?>
        </button>
      </div>
    </div>
    <p class="c-day-schedule__details j-schedule-item-details" hidden>
      <span class="j-schedule-item-details-text"></span>
    </p>
    <div class="c-day-schedule__meta-row j-schedule-item-meta" hidden>
      <span class="c-chip c-chip--round c-day-event-card__badge j-schedule-item-badge" hidden></span>
      <span class="c-day-schedule__meta-author j-schedule-item-author" hidden></span>
    </div>
  </li>
</template>
