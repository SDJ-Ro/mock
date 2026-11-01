<?php
// A programme's own "Notice Board": its notices as the shared notice cards
// (_notice_card.php), pinned first, display only (no pin / edit / delete). They are example notices for now.
// Looks: programme-panel.css, notice-card.css, empty-box.css; layout: notice-grid.css.
// From the controller: $programme (name, noticeCards). From the page: $currentRole, $csrfField (the card reads them).
?>
<section class="c-card c-programme-panel">
  <header class="c-programme-panel__head">
    <h2 class="c-panel__title c-programme-panel__title"><span class="c-programme-panel__icon c-programme-panel__icon--boxed"><?= icon('bell', 20) ?></span>Notice Board</h2>
  </header>

  <?php if ($programme['noticeCards'] === []): ?>
    <div class="c-empty-box">
      <p class="c-empty-box__text"><?= e("No recent notices posted for {$programme['name']}.") ?></p>
    </div>
  <?php else: ?>
    <div class="l-notice-grid">
      <?php foreach ($programme['noticeCards'] as $notice): ?>
        <?php
        $showActions = false;
        require __DIR__ . '/_notice_card.php';
        ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
