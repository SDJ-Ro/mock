<?php
// One missing-record request shown to management. The class teacher answers it on the B-3 page;
// management sees its status and any rejection reason but does not answer it here.
$requestTones = config('certificates.request_tones');
?>
<article class="c-request-card">
  <div class="c-request-card__top">
    <span class="c-request-card__id"><?= e($requestCard['id']) ?></span>
    <span class="c-tag c-tag--round c-tag--<?= e($requestTones[$requestCard['status']]) ?>"><?= e($requestCard['status']) ?></span>
  </div>
  <h4 class="c-request-card__title"><?= e($requestCard['title']) ?></h4>
  <p class="c-request-card__text"><?= e($requestCard['description']) ?></p>
  <div class="c-request-card__box c-request-card__assigned">
    <?= icon('user', 14) ?>
    <span><?= e(words('Current {Class} {Teacher}')) ?>: <strong><?= e($requestCard['teacher']) ?></strong></span>
  </div>
  <?php if ($requestCard['note'] !== ''): ?>
    <div class="c-request-card__box">
      <p class="c-label">Reason</p>
      <p class="c-request-card__text"><?= e($requestCard['note']) ?></p>
    </div>
  <?php endif; ?>
</article>
<?php unset($requestCard, $requestTones); ?>
