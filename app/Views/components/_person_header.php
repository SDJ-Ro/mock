<?php
// A person's header: photo or initials, eyebrow, name, their line, ID and status tags,
// in their role's colour. In People's pop-up (with ×) and on the parent's Child profile (a page: no ×).
// Its look: person-profile.css. Set before requiring (cleared at the end):
//   $header          ['tone', 'name', 'photo' (or null: their initials), 'eyebrow', 'sub', 'code', 'status' => [label, tag tone]]
//   $headerOnPage    true on a page (Child profile), false in the pop-up
?>
<header class="c-person__header c-person__header--<?= e($header['tone']) ?><?= $headerOnPage ? ' c-person__header--page' : '' ?>">
  <?php if ($header['photo'] !== null): ?>
    <img class="c-avatar c-avatar--medium" src="<?= e($header['photo']) ?>" alt="">
  <?php else: ?>
    <span class="c-avatar c-avatar--medium c-avatar--<?= e($header['tone']) ?>" aria-hidden="true"><?= e(initials($header['name'])) ?></span>
  <?php endif; ?>
  <div class="c-person__who">
    <p class="c-label c-person__eyebrow"><?= e($header['eyebrow']) ?></p>
    <h2 class="c-pop-up__title" id="j-person-profile-title"><?= e($header['name']) ?></h2>
    <?php if ($header['sub'] !== ''): ?>
      <p class="c-person__sub"><?= e($header['sub']) ?></p>
    <?php endif; ?>
    <div class="c-person__tags">
      <span class="c-tag c-tag--sky"><?= e($header['code']) ?></span>
      <span class="c-tag c-tag--<?= e($header['status'][1]) ?>"><?= e($header['status'][0]) ?></span>
    </div>
  </div>
  <?php if (!$headerOnPage): ?>
    <button type="button" class="c-btn c-btn--close j-pop-up-close" aria-label="Close"><?= icon('close', 16) ?></button>
  <?php endif; ?>
</header>
<?php unset($headerOnPage); ?>
