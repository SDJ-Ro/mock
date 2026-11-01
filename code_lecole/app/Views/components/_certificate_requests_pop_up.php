<?php
// Management's "Student Requests" pop-up for one certificate: the certificate requests,
// then the missing-record requests, each a _certificate_request_card.php; "No requests submitted." when there are
// none. Each evidence file gets its own preview pop-up (_evidence_preview_pop_up.php). In the shared frame
// (_pop_up.php); "Student Requests" opens it. Its own look (its width): certificates.css.
// Set before requiring: $certificate (prepared).
ob_start();
?>
<div class="c-pop-up__body c-requests">
  <?php foreach (['certificate' => ['Certificate Requests', 'requests'], 'record' => ['Missing Records', 'records']] as $requestsKind => [$requestsHeading, $requestsKey]): ?>
    <?php if ($certificate[$requestsKey] !== []): ?>
      <section class="c-requests__section">
        <h3 class="c-label c-requests__heading"><?= e($requestsHeading) ?></h3>
        <?php foreach ($certificate[$requestsKey] as $requestCard): ?>
          <?php $requestKind = $requestsKind; require __DIR__ . '/_certificate_request_card.php'; ?>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php if ($certificate['requests'] === [] && $certificate['records'] === []): ?>
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

foreach ($certificate['records'] as $requestsRecord)
{
    foreach ($requestsRecord['files'] as $requestsFileNumber => $requestsFile)
    {
        $evidenceId    = "j-evidence-{$requestsRecord['id']}-{$requestsFileNumber}";
        $evidenceTitle = 'Evidence Preview';
        $evidenceFile  = $requestsFile['name'];
        $evidenceLine  = "PDF document · {$requestsFile['size']}";
        require __DIR__ . '/_evidence_preview_pop_up.php';
    }
}
unset($requestsKind, $requestsHeading, $requestsKey, $requestsRecord, $requestsFileNumber, $requestsFile);
