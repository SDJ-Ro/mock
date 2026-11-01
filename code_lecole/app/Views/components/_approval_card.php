<?php
// One request on Parent → Approvals: a tag and the status, the club / sport, who runs it and when,
// what it is, who asked and when, the parent's reason if they said no, and Decline / Approve while it still waits.
// Its look: approval-card.css (+ panel.css's c-card, tag.css, banner.css, button.css).
// Decline asks first in the shared question pop-up; its optional reason box is the page's <template id="j-approval-reason">.
// Set before requiring:
//   $request  one ApprovalController::card(): extracurricular_id, student_id, status (pending | approved | declined), title, meta,
//             description, from, date, note (the reason for no, or null) + noteLabel (whose no), student (the child's name)
// From the page: $csrfField.
$requestTones = ['pending' => 'sand', 'approved' => 'moss', 'declined' => 'maroon'];
$requestForm  = "j-approval-decline-{$request['extracurricular_id']}-{$request['student_id']}";
?>
<article class="c-card c-approval-card">
  <div class="c-approval-card__top">
    <div>
      <span class="c-tag c-tag--terracotta">Join Request</span>
      <h2 class="c-approval-card__title"><?= e($request['title']) ?></h2>
      <?php if ($request['meta'] !== ''): ?>
        <p class="c-approval-card__meta"><?= e($request['meta']) ?></p>
      <?php endif; ?>
    </div>
    <span class="c-tag c-tag--<?= e($requestTones[$request['status']]) ?>"><?= e(ucfirst($request['status'])) ?></span>
  </div>

  <p class="c-approval-card__description"><?= e($request['description']) ?></p>

  <div class="c-approval-card__info">
    <p class="c-approval-card__from">Request from: <strong><?= e($request['from']) ?></strong></p>
    <p class="c-approval-card__date"><?= e($request['date']) ?></p>
  </div>

  <?php if ($request['note'] !== null): ?>
    <div class="c-banner c-banner--error">
      <?= icon('message', 16) ?>
      <div>
        <p class="c-label c-approval-card__note-label"><?= e($request['noteLabel']) ?></p>
        <p class="c-approval-card__note"><?= e($request['note']) ?></p>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($request['status'] === 'pending'): ?>
    <div class="c-approval-card__actions">
      <form class="c-approval-card__action" id="<?= e($requestForm) ?>" method="post" action="/parent/approvals/answer"
            data-question-title="Decline Request?" data-question-button="Decline Request" data-question-icon="close"
            data-question-extra="j-approval-reason"
            data-question-text="<?= e("{$request['student']} won't join {$request['title']}. You can say why (optional).") ?>">
        <?= $csrfField ?>
        <input type="hidden" name="extracurricular" value="<?= e($request['extracurricular_id']) ?>">
        <input type="hidden" name="student" value="<?= e($request['student_id']) ?>">
        <input type="hidden" name="answer" value="decline">
        <button type="submit" class="c-btn c-btn--decision c-btn--maroon"><?= icon('close', 14) ?>Decline</button>
      </form>
      <form class="c-approval-card__action" method="post" action="/parent/approvals/answer">
        <?= $csrfField ?>
        <input type="hidden" name="extracurricular" value="<?= e($request['extracurricular_id']) ?>">
        <input type="hidden" name="student" value="<?= e($request['student_id']) ?>">
        <input type="hidden" name="answer" value="approve">
        <button type="submit" class="c-btn c-btn--decision c-btn--solid"><?= icon('check', 14) ?>Approve</button>
      </form>
    </div>
  <?php endif; ?>
</article>
<?php unset($request, $requestTones, $requestForm); ?>
