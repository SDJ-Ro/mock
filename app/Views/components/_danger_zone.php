<?php
// The red "Danger Zone" box at the bottom of a page (a programme's: Archive; an achievement's: Delete): a title,
// what will happen, and the button — it asks first (the question pop-up). Its look: danger-zone.css.
// Set before requiring (cleared at the end):
//   $dangerZone = ['text' => what happens, 'button' => 'Archive Programme', 'question' => the pop-up's title,
//                  'action' => where the form goes, 'hidden' => [name => value] sent with it]
// From the page: $csrfField.
?>
<section class="c-danger-zone">
  <div>
    <h3 class="c-danger-zone__title"><?= icon('trash', 18) ?>Danger Zone</h3>
    <p class="c-danger-zone__text"><?= e($dangerZone['text']) ?></p>
  </div>
  <form method="post" action="<?= e($dangerZone['action']) ?>"
        data-question-title="<?= e($dangerZone['question']) ?>" data-question-button="<?= e($dangerZone['button']) ?>" data-question-text="<?= e($dangerZone['text']) ?>">
    <?= $csrfField ?>
    <?php foreach ($dangerZone['hidden'] as $dangerName => $dangerValue): ?>
      <input type="hidden" name="<?= e($dangerName) ?>" value="<?= e($dangerValue) ?>">
    <?php endforeach; ?>
    <button type="submit" class="c-btn c-btn--maroon"><?= e($dangerZone['button']) ?></button>
  </form>
</section>
<?php unset($dangerZone, $dangerName, $dangerValue); ?>
