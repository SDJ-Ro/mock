<?php
// The title at the top of a page, the line under it, and an optional "+" button on the right. Its look: page-header.css.
// Each page view requires it where its header belongs (a notice page puts it inside the board view, which hides
// when the post form opens). Set these first (they are cleared at the end):
//   $title         the heading
//   $subtitle      (optional) the line under it
//   $headerButton  (optional) the "+" button on the right: ['label' => 'Post Notice', 'href' => '/admin/notice?post'] goes to
//                  a page; ['label' => …, 'popUp' => 'j-…'] opens that pop-up (pop-up.js, no script of the page's own);
//                  'icon' => 'graduationCap' (optional) instead of the "+" (Academic's Year-End)
//   $headerContent (optional) anything else on the right, as HTML collected with ob_start() (People's role tabs,
//                  the teacher's search)
?>
<header class="c-page-header">
  <div class="c-page-header__meta">
    <h1 class="c-page-header__title"><?= e($title) ?></h1>
    <?php if (!empty($subtitle)): ?>
      <p class="c-page-header__subtitle"><?= e($subtitle) ?></p>
    <?php endif; ?>
  </div>

  <?php if (isset($headerButton['href'])): ?>
    <a class="c-btn c-btn--pill c-btn--sky" href="<?= e($headerButton['href']) ?>">
      <?= icon($headerButton['icon'] ?? 'plus') ?>
      <span><?= e($headerButton['label']) ?></span>
    </a>
  <?php elseif (isset($headerButton)): ?>
    <button type="button" class="c-btn c-btn--pill c-btn--sky" data-pop-up-open="<?= e($headerButton['popUp']) ?>">
      <?= icon('plus') ?>
      <span><?= e($headerButton['label']) ?></span>
    </button>
  <?php endif; ?>

  <?= $headerContent ?? '' ?>
</header>
<?php unset($title, $subtitle, $headerButton, $headerContent); ?>
