<?php
// One student request in the Student Requests pop-up: its ID and state, then
//   a certificate request: purpose, copies, delivery, when · a missing record: title, what is missing, the teacher it
//   went to (+ their answer), the evidence files (eye → the evidence preview pop-up)
// then management's note, its timeline (_activity_list.php) and, while it waits, Approve / Reject. Nothing
// is saved yet: Approve / Reject only say so (data-mock-message, feedback-banner.js). Its look: certificates.css.
// Set before requiring:
//   $requestCard  one request (app/Mock/certificates.php 'requests' or 'records')
//   $requestKind  'certificate' | 'record'
$requestTones = config('certificates.request_tones');
?>
<article class="c-request-card">
  <div class="c-request-card__top">
    <span class="c-request-card__id"><?= e($requestCard['id']) ?></span>
    <span class="c-tag c-tag--round c-tag--<?= e($requestTones[$requestCard['status']]) ?>"><?= e($requestCard['status']) ?></span>
  </div>

  <?php if ($requestKind === 'certificate'): ?>
    <dl class="c-request-card__fields">
      <div><dt>Purpose:</dt><dd><?= e($requestCard['purpose']) ?></dd></div>
      <div><dt>Copies:</dt><dd><?= e($requestCard['copies']) ?></dd></div>
      <div><dt>Delivery:</dt><dd><?= e($requestCard['delivery']) ?></dd></div>
      <div><dt>Submitted:</dt><dd><?= e(date(country('date_format') . ', g:i A', strtotime($requestCard['submitted']))) ?></dd></div>
    </dl>
  <?php else: ?>
    <h4 class="c-request-card__title"><?= e($requestCard['title']) ?></h4>
    <p class="c-request-card__text"><?= e($requestCard['description']) ?></p>
    <div class="c-request-card__box c-request-card__assigned">
      <?= icon('user', 14) ?>
      <span>Assigned to: <strong><?= e($requestCard['teacher']) ?></strong></span>
      <span class="c-tag c-tag--round c-tag--<?= e($requestTones[$requestCard['teacherStatus']]) ?>"><?= e($requestCard['teacherStatus']) ?></span>
    </div>
  <?php endif; ?>

  <?php if ($requestCard['note'] !== ''): ?>
    <div class="c-request-card__box">
      <p class="c-label">Management Note</p>
      <p class="c-request-card__text"><?= e($requestCard['note']) ?></p>
    </div>
  <?php endif; ?>

  <?php if (($requestCard['files'] ?? []) !== []): ?>
    <div class="c-request-card__section">
      <p class="c-label">Attached Evidence</p>
      <?php foreach ($requestCard['files'] as $requestFileNumber => $requestFile): ?>
        <div class="c-request-card__box c-request-card__file">
          <span class="c-request-card__file-icon"><?= icon('fileText', 14) ?></span>
          <span class="c-request-card__file-name"><?= e($requestFile['name']) ?><small><?= e($requestFile['size']) ?></small></span>
          <button type="button" class="c-btn c-btn--icon" data-pop-up-open="<?= e("j-evidence-{$requestCard['id']}-{$requestFileNumber}") ?>"
                  aria-label="<?= e("Preview {$requestFile['name']}") ?>"><?= icon('eye', 14) ?></button>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($requestCard['activity'] !== []): ?>
    <div class="c-request-card__section c-request-card__section--line">
      <p class="c-label">Timeline</p>
      <?php
      $activityItems = $requestCard['activity'];
      $activitySmall = true;
      require __DIR__ . '/_activity_list.php';
      ?>
    </div>
  <?php endif; ?>

  <?php if ($requestCard['status'] === 'Pending'): ?>
    <div class="c-request-card__section c-request-card__section--line c-request-card__actions">
      <form class="c-request-card__action" data-mock-message="Request approved.">
        <button type="submit" class="c-btn c-btn--decision c-btn--solid"><?= icon('check', 14) ?>Approve</button>
      </form>
      <form class="c-request-card__action" data-mock-message="Request rejected.">
        <button type="submit" class="c-btn c-btn--decision c-btn--maroon"><?= icon('xCircle', 14) ?>Reject</button>
      </form>
    </div>
  <?php endif; ?>
</article>
<?php unset($requestCard, $requestKind, $requestTones, $requestFileNumber, $requestFile); ?>
