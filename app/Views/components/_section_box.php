<?php
// A big section with a tinted header: an icon, a title and one button on the right; the content sits on cream below.
// Used twice on Academic ("Grade & Class Structure" and "Curriculum Subjects"). Its look: section-box.css.
// Set before requiring:
//   $sectionId      the element id (also where the page comes back to after a save: #…)
//   $sectionTone    'sky' | 'cherry' (the header's tint and its button)
//   $sectionIcon    icon name   $sectionTitle   the title
//   $sectionButton  ['label' => 'Add Grade', 'data' => ['pop-up-open' => 'j-add-grade']] — data-… attributes for its script
//   $sectionContent what goes inside (collect it with ob_start() / ob_get_clean())
?>
<section class="c-card c-section-box c-section-box--<?= e($sectionTone) ?>" id="<?= e($sectionId) ?>">
  <div class="c-section-box__head">
    <div class="c-section-box__heading">
      <span class="c-section-box__icon" aria-hidden="true"><?= icon($sectionIcon, 18) ?></span>
      <h2 class="c-panel__title"><?= e($sectionTitle) ?></h2>
    </div>
    <button type="button" class="c-btn c-btn--small c-section-box__button"
      <?php foreach ($sectionButton['data'] as $key => $value): ?> data-<?= e($key) ?>="<?= e($value) ?>"<?php endforeach; ?>>
      <?= icon('plus', 13) ?><?= e($sectionButton['label']) ?>
    </button>
  </div>
  <div class="c-section-box__body">
    <?= $sectionContent ?>
  </div>
</section>
<?php unset($sectionId, $sectionTone, $sectionIcon, $sectionTitle, $sectionButton, $sectionContent); ?>
