<?php
// One student on management's Character Certificates list (a capsule card): a tall rounded card in one of the
// student card tones, a big initials circle (a red ring + dot while something waits), the name, index no. and class.
// It is a link to that certificate (?student=…). Its look: certificate-card.css.
// Set before requiring: $certificateCard (a prepared certificate: CertificateController::prepare()).
?>
<a class="c-certificate-card c-certificate-card--<?= e($certificateCard['tone']) ?>"
   href="<?= e('/management/character-certificate?student=' . rawurlencode($certificateCard['id'])) ?>"
   aria-label="<?= e("Open {$certificateCard['name']}'s character certificate") ?>">
  <span class="c-certificate-card__photo<?= $certificateCard['hasWaiting'] ? ' c-certificate-card__photo--waiting' : '' ?>">
    <span class="c-certificate-card__initials"><?= e(initials($certificateCard['name'])) ?></span>
  </span>
  <h3 class="c-certificate-card__name"><?= e($certificateCard['name']) ?></h3>
  <p class="c-certificate-card__line"><?= e($certificateCard['id']) ?></p>
  <p class="c-certificate-card__line"><?= e($certificateCard['class']) ?></p>
</a>
<?php unset($certificateCard); ?>
