<?php
// The pieces calendar.js copies into the calendar's footer for the chosen day (HTML <template>s, so the JS writes no HTML).
// calendar.js picks per DAY: can this person add an event on this day (staff, today or later)?
//   yes → no events: a short "No events" line + "Add Event"      has events: the cards + "Add Event"
//   no  → no events: the full "No events scheduled" box          has events: the cards + "No more events today"
// The boxes fill whatever space is left in the footer (day-card.css), so it is never half empty.
// Every role gets the "no" pieces (staff on past days need them too); only staff get the "yes" pieces.
?>
<template id="tmpl-calendar-card">
  <article class="c-day-event-card">
    <div class="c-day-event-card__header">
      <p class="c-label c-day-event-card__eyebrow j-card-eyebrow"></p>
      <span class="c-chip c-chip--round c-day-event-card__badge j-card-badge" hidden></span>
    </div>
    <h3 class="c-day-event-card__title j-card-title"></h3>
    <p class="c-day-event-card__details j-card-details" hidden></p>
    <div class="c-day-event-card__actions j-card-actions" hidden>
      <button type="button" class="c-day-event-card__action-btn j-card-edit" aria-label="Edit event">
        <?= icon('edit', 13) ?>
      </button>
      <button type="button" class="c-day-event-card__action-btn c-day-event-card__action-btn--delete j-card-delete" aria-label="Delete event">
        <?= icon('trash', 13) ?>
      </button>
    </div>
  </article>
</template>

<template id="tmpl-calendar-empty">
  <div class="c-calendar__empty-box">
    <?= icon('calendar', 22, 'c-calendar__empty-icon') ?>
    <p class="c-calendar__empty-title">No events scheduled</p>
    <p class="c-calendar__empty-subtext"><span class="j-calendar-empty-date"></span> is clear.</p>
  </div>
</template>

<template id="tmpl-calendar-no-more">
  <div class="c-calendar__no-more-events">
    <?= icon('checkCircle2', 16, 'c-calendar__no-more-icon') ?>
    <span>No more events today</span>
  </div>
</template>

<?php if ($canAdd): ?>
  <template id="tmpl-calendar-empty-short">
    <div class="c-calendar__empty-box">
      <p class="c-calendar__empty-box-text">No events scheduled for <span class="j-calendar-empty-date"></span>.</p>
    </div>
  </template>

  <template id="tmpl-calendar-add-btn">
    <button type="button" class="c-calendar__add-event-btn j-open-event-editor-btn" aria-label="Add event">
      <?= icon('plus', 18) ?>
      <span>Add Event</span>
    </button>
  </template>
<?php endif; ?>
