<?php
// One feedback, on the teacher's Feedback Channel and the parent's / student's Teacher Feedback: the type badge and
// date, the "To" line (teacher's page only: who it went to, read / unread), the title, the teacher and the subject,
// the start of the text and a tinted "Read Full Feedback" footer in the type's colour. Clicking it opens the same card
// in full in a pop-up (feedback-card.js copies it and adds --full: the whole text and an "Official Feedback Record"
// footer, both here). Its look: feedback.css (+ panel.css's c-card, tag.css, label.css).
// Set before requiring:
//   $feedbackCard  one feedback (FeedbackController::card()): id, type, title, subject, teacher, student, date, text,
//                  to (the teacher's line, '' elsewhere), readUrl (not read yet by this reader: where opening it is
//                  sent — the hidden form below, feedback-card.js; null = nothing to send)
// From the page: $types (config/feedback.php), $csrfField.
[$feedbackTypeName, $feedbackIcon] = $types[$feedbackCard['type']];
$feedbackDate = date(country('date_format'), strtotime($feedbackCard['date']));
?>
<article class="c-card c-feedback-card c-feedback-card--<?= e($feedbackCard['type']) ?> j-feedback-card" tabindex="0" role="button"
         aria-label="<?= e("Read the full feedback: {$feedbackCard['title']}") ?>">
  <?php if ($feedbackCard['readUrl'] !== null): ?>
    <form class="j-feedback-read" method="post" action="<?= e($feedbackCard['readUrl']) ?>" hidden>
      <?= $csrfField ?>
      <input type="hidden" name="id" value="<?= e($feedbackCard['id']) ?>">
    </form>
  <?php endif; ?>

  <div class="c-feedback-card__body">
    <div class="c-feedback-card__top">
      <span class="c-tag c-tag--soft c-feedback-card__type"><?= icon($feedbackIcon, 12) ?><?= e($feedbackTypeName) ?></span>
      <span class="c-label c-feedback-card__date"><?= e($feedbackDate) ?></span>
    </div>

    <?php if ($feedbackCard['to'] !== ''): ?>
      <p class="c-label c-feedback-card__to"><?= e($feedbackCard['to']) ?></p>
    <?php endif; ?>

    <div class="c-feedback-card__heading">
      <h2 class="c-feedback-card__title"><?= e($feedbackCard['title']) ?></h2>
      <p class="c-feedback-card__teacher">
        <strong><?= e($feedbackCard['teacher']) ?></strong><span class="c-feedback-card__separator" aria-hidden="true">•</span><?= e($feedbackCard['subject']) ?>
      </p>
    </div>

    <p class="c-feedback-card__text"><?= e($feedbackCard['text']) ?></p>
  </div>

  <footer class="c-feedback-card__footer">
    <span class="c-feedback-card__more">Read Full Feedback <?= icon('chevronRight', 14) ?></span>
    <span class="c-feedback-card__record">Official Feedback Record</span>
    <span class="c-label c-feedback-card__date c-feedback-card__record"><?= e($feedbackDate) ?></span>
  </footer>
</article>
<?php unset($feedbackCard, $feedbackTypeName, $feedbackIcon, $feedbackDate); ?>
