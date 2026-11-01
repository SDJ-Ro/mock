<?php
// A small cream card on a programme's page (the staff list and the details beside the calendar use the same one):
//   a person: the teacher in charge or the coach — photo (or initials), name, their line, email and phone
//   a list:   Details (schedule, location), Status (enrolled since, date), Created Date — icon, label, value
// Who may change it gets an "Edit" that opens its pop-up (_staff_pop_up.php). Its look: staff-card.css.
// Set before requiring (cleared at the end):
//   $staffCard = ['title' => …, 'person' => ['name', 'line', 'photo' ('' = initials; nobody 'assigned' yet = a person icon), 'email', 'phone'] or null,
//                 'items' => [[icon, label, value], …] (when no person), 'edit' => a pop-up id or null]
?>
<article class="c-staff-card">
  <header class="c-staff-card__head">
    <h3 class="c-label c-staff-card__title"><?= e($staffCard['title']) ?></h3>
    <?php if ($staffCard['edit'] !== null): ?>
      <button type="button" class="c-staff-card__edit" data-pop-up-open="<?= e($staffCard['edit']) ?>"><?= icon('edit', 12) ?>Edit</button>
    <?php endif; ?>
  </header>

  <?php if ($staffCard['person'] !== null): ?>
    <div class="c-staff-card__person">
      <?php if ($staffCard['person']['photo'] !== ''): ?>
        <img class="c-staff-card__photo" src="<?= e($staffCard['person']['photo']) ?>" alt="">
      <?php else: ?>
        <span class="c-avatar c-staff-card__photo" aria-hidden="true"><?= ($staffCard['person']['assigned'] ?? true) ? e(initials($staffCard['person']['name'])) : icon('user', 18) ?></span>
      <?php endif; ?>
      <div>
        <p class="c-staff-card__name"><?= e($staffCard['person']['name']) ?></p>
        <p class="c-staff-card__line"><?= e($staffCard['person']['line']) ?></p>
      </div>
    </div>
    <div class="c-staff-card__contact">
      <p class="c-staff-card__contact-row"><?= icon('mail', 14) ?><span><?= e($staffCard['person']['email']) ?></span></p>
      <p class="c-staff-card__contact-row"><?= icon('phone', 14) ?><span><?= e($staffCard['person']['phone']) ?></span></p>
    </div>
  <?php else: ?>
    <?php foreach ($staffCard['items'] as [$itemIcon, $itemLabel, $itemValue]): ?>
      <div class="c-staff-card__item">
        <span class="c-staff-card__item-icon"><?= icon($itemIcon, 15) ?></span>
        <div>
          <p class="c-label c-staff-card__item-label"><?= e($itemLabel) ?></p>
          <p class="c-staff-card__item-value"><?= e($itemValue) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</article>
<?php unset($staffCard, $itemIcon, $itemLabel, $itemValue); ?>
