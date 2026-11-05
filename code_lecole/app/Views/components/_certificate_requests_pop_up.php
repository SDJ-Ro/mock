<?php
// Management sees the student's missing-record requests; the class teacher resolves them through B-3.
ob_start();
?>
<div class="c-pop-up__body c-requests">
  <?php if ($certificate['records'] !== []): ?>
    <section class="c-requests__section">
      <h3 class="c-label c-requests__heading">Missing Records</h3>
      <?php foreach ($certificate['records'] as $requestCard): ?>
        <?php require __DIR__ . '/_certificate_request_card.php'; ?>
      <?php endforeach; ?>
    </section>
  <?php else: ?>
    <div class="c-empty-box c-empty-box--titled">
      <?= icon('message', 32) ?>
      <p class="c-empty-box__text">No requests submitted.</p>
    </div>
  <?php endif; ?>
</div>
<?php
$popUpContent = ob_get_clean();
$popUpId      = 'j-certificate-requests';
$popUpTitle   = words('{Student} Requests');
$popUpVariant = 'requests';
require __DIR__ . '/_pop_up.php';
unset($requestCard);
