<?php
// A programme's "Achievements & Gallery": one card per award, newest first — its photo (or the programme's cover),
// title, year, level · team or individual — each a link to its own page (?award=…, _award_page.php).
// Who may edit gets "+ Add Achievement" (?award=new). Looks: programme-panel.css, award-card.css, empty-box.css.
// From the controller: $programme (awards: Extracurriculars::awardCard()), $can, $awardUrl (an award's address without its id).
?>
<section class="c-card c-programme-panel">
  <header class="c-programme-panel__head">
    <h2 class="c-panel__title c-programme-panel__title"><span class="c-programme-panel__icon c-programme-panel__icon--trophy"><?= icon('trophy', 20) ?></span>Achievements &amp; Gallery</h2>
    <?php if ($can['edit']): ?>
      <a class="c-btn c-btn--sky c-btn--small" href="<?= e("{$awardUrl}new") ?>"><?= icon('plus', 14) ?>Add Achievement</a>
    <?php endif; ?>
  </header>

  <?php if ($programme['awards'] === []): ?>
    <div class="c-empty-box c-empty-box--tinted">
      <p class="c-empty-box__text">No achievements recorded yet.<?= $can['edit'] ? ' Click “+ Add Achievement” above to add one.' : '' ?></p>
    </div>
  <?php else: ?>
    <div class="c-award-grid">
      <?php foreach ($programme['awards'] as $awardCard): ?>
        <a class="c-award-card" href="<?= e($awardUrl . $awardCard['id']) ?>">
          <span class="c-award-card__media"><img class="c-award-card__image" src="<?= e($awardCard['image']) ?>" alt="" loading="lazy"></span>
          <span class="c-award-card__body">
            <span class="c-award-card__row">
              <span class="c-award-card__title"><?= e($awardCard['title']) ?></span>
              <span class="c-tag c-tag--grey"><?= e($awardCard['year']) ?></span>
            </span>
            <span class="c-label c-award-card__tags">
              <span class="c-award-card__level"><?= e($awardCard['level']) ?></span>
              <span aria-hidden="true">•</span>
              <span><?= e($awardCard['kind']) ?></span>
            </span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php unset($awardCard); ?>
