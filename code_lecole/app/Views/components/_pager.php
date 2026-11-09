<?php
// "Previous · Page 2 of 5 · Next" links (shown only when there is more than one page). Its look: pager.css.
// Needs: $pagerPage, $pagerPages, $pagerLink (fn (int $page): string → the address of that page, keeping the filters).
// Optional: $pagerRuled = true → a line above (a pager at the bottom of a table).
$pagerRuled ??= false;
?>
<?php if ($pagerPages > 1): ?>
  <nav class="c-pager<?= $pagerRuled ? ' c-pager--ruled' : '' ?>" aria-label="Pages">
    <?php if ($pagerPage > 1): ?>
      <a class="c-btn c-btn--outline c-btn--small" href="<?= e($pagerLink($pagerPage - 1)) ?>"><?= icon('chevronLeft', 14) ?>Previous</a>
    <?php endif; ?>
    <span class="c-pager__number"><?= e("Page {$pagerPage} of {$pagerPages}") ?></span>
    <?php if ($pagerPage < $pagerPages): ?>
      <a class="c-btn c-btn--outline c-btn--small" href="<?= e($pagerLink($pagerPage + 1)) ?>">Next<?= icon('chevronRight', 14) ?></a>
    <?php endif; ?>
  </nav>
<?php endif; ?>
