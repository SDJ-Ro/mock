<?php
// A form that is a whole page: the Back link, then a white card with a coloured header (title +
// line under it) and the form under it. Its look: form-card.css. A form in a pop-up uses _pop_up.php instead;
// the fields inside either one are the shared ones (form-field.css).
// Set before requiring (cleared at the end):
//   $formCardBack      where Back goes ('/admin/notice')
//   $formCardTone      the header's colour: 'sky' · 'sunshine' · 'terracotta' · 'maroon' (People's Add pages)
//   $formCardTitle     the header's title
//   $formCardSubtitle  the line under it ('' = none: People's Add pages)
//   $formCardContent   the form itself: build it between ob_start() and ob_get_clean() ("catch what is printed")
?>
<div class="c-form-card">
  <a class="c-btn c-btn--back" href="<?= e($formCardBack) ?>"><?= icon('arrowLeft', 16) ?><span>Back</span></a>

  <section class="c-form-card__box">
    <header class="c-form-card__header c-form-card__header--<?= e($formCardTone) ?>">
      <h2 class="c-form-card__title"><?= e($formCardTitle) ?></h2>
      <?php if ($formCardSubtitle !== ''): ?>
        <p class="c-form-card__subtitle"><?= e($formCardSubtitle) ?></p>
      <?php endif; ?>
    </header>

    <?= $formCardContent ?>
  </section>
</div>
<?php unset($formCardBack, $formCardTone, $formCardTitle, $formCardSubtitle, $formCardContent); ?>
