<?php
// "Unread Notices" card (student + parent dashboards): the first 2 notices, and "View All" opens every one in a pop-up
// (the shared frame _pop_up.php, plain shape — it keeps its own white header). Its look: unread-notices.css.
// Set $notices first: the notices this person hasn't seen yet (NoticeModel::unread(): title, body, …)
$noticeList  = $notices ?? [];
$noticeCount = count($noticeList);
$noticePill  = $noticeCount . ' ' . ($noticeCount === 1 ? 'notice' : 'notices');   // tag.css shows it in capitals
?>
<section class="c-panel c-notices-card">
  <div class="c-panel__head">
    <h2 class="c-panel__title c-notices-card__title">
      <span class="c-notices-card__bell" aria-hidden="true"><?= icon('bell', 18) ?></span>
      <span>Unread Notices</span>
    </h2>
    <div class="c-notices-card__actions">
      <span class="c-tag c-tag--round c-tag--cherry"><?= e($noticePill) ?></span>
      <button type="button" class="c-label c-notices-card__view-all" data-pop-up-open="j-notices-pop-up" aria-label="View all notices">View All</button>
    </div>
  </div>

  <div class="c-notices-card__list">
    <?php foreach (array_slice($noticeList, 0, 2) as $notice): ?>
      <article class="c-notices-card__item">
        <h3 class="c-notices-card__item-title"><?= e($notice['title']) ?></h3>
        <p class="c-notices-card__item-text"><?= e($notice['body']) ?></p>
      </article>
    <?php endforeach; ?>

    <?php if ($noticeCount <= 1): ?>
      <div class="c-notices-card__empty">
        <h3 class="c-notices-card__empty-title">No more notices</h3>
        <p class="c-notices-card__empty-text">You have read every notice.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
// "View All": every unread notice (collected into $popUpContent, then the shared frame prints it inside its box)
ob_start();
?>
    <div class="c-notices-pop-up__header">
      <div class="c-notices-pop-up__title-group">
        <span class="c-notices-card__bell" aria-hidden="true"><?= icon('bell', 20) ?></span>
        <h2 class="c-pop-up__title c-notices-pop-up__title" id="j-notices-pop-up-title">Unread Notices</h2>
        <span class="c-tag c-tag--round c-tag--cherry"><?= e($noticePill) ?></span>
      </div>
      <button type="button" class="c-btn c-btn--close j-pop-up-close" aria-label="Close notices">
        <?= icon('x', 16) ?>
      </button>
    </div>

    <div class="c-notices-pop-up__body">
      <div class="c-notices-card__list">
        <?php foreach ($noticeList as $notice): ?>
          <article class="c-notices-card__item">
            <h3 class="c-notices-card__item-title"><?= e($notice['title']) ?></h3>
            <p class="c-notices-card__item-text"><?= e($notice['body']) ?></p>
          </article>
        <?php endforeach; ?>

        <?php if ($noticeCount === 0): ?>
          <p class="c-notices-pop-up__empty">No unread notices.</p>
        <?php endif; ?>
      </div>
    </div>
<?php
$popUpContent = ob_get_clean();
$popUpShape   = 'plain';
$popUpId      = 'j-notices-pop-up';
$popUpVariant = 'notices';
require __DIR__ . '/_pop_up.php';
unset($noticeList, $noticeCount, $noticePill, $notice);
?>
