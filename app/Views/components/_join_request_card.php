<?php
// One student asking to join (the teacher in charge's view): the programme, the student's grade and age group,
// a cream box with their name, when their parent said yes, their index no. and the parent's yes; Reject (asks for a
// reason, which the student and parent see) and Accept (only if their age group has room).
// Its look: join-request-card.css. Set before requiring: $request (Extracurriculars::page() joinRequests).
// From the panel: $programme. From the page: $csrfField, $currentRole.
$requestForm   = "j-join-reject-{$request['id']}";
$requestAction = "/{$currentRole}/extracurricular/request";
?>
<article class="c-join-card">
  <div>
    <span class="c-tag c-tag--round c-tag--sand">Join Request</span>
    <h3 class="c-join-card__title"><?= e($programme['name']) ?></h3>
    <p class="c-join-card__meta"><?= e(($request['grade'] !== null ? grade_name((int) $request['grade']) : '—') . " · Age group : {$request['ageGroup']}") ?></p>
  </div>

  <div class="c-join-card__student">
    <div class="c-join-card__who">
      <p class="c-join-card__name"><?= e($request['name']) ?></p>
      <p class="c-join-card__date">Submitted <?= e(date(country('date_format'), strtotime($request['submitted']))) ?></p>
    </div>
    <div class="c-join-card__index">
      <span class="c-label">Index No.</span>
      <p class="c-join-card__index-number"><?= e($request['indexNo']) ?></p>
    </div>
    <span class="c-tag c-tag--moss"><?= e(words('{Parent}: Approved')) ?></span>
  </div>

  <div class="c-join-card__actions">
    <form id="<?= e($requestForm) ?>" method="post" action="<?= e($requestAction) ?>"
          data-question-title="Reject Join Request?" data-question-button="Send Feedback & Reject" data-question-icon="alertCircle"
          data-question-extra="j-reason" data-question-text="<?= e("You are rejecting \"{$request['name']}\". Your reason is shown to them and their " . strtolower(word('parent')) . '.') ?>">
      <?= $csrfField ?>
      <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
      <input type="hidden" name="student" value="<?= e($request['id']) ?>">
      <input type="hidden" name="decision" value="reject">
      <button type="submit" class="c-btn c-btn--decision c-btn--maroon"><?= icon('close', 13) ?>Reject</button>
    </form>
    <form method="post" action="<?= e($requestAction) ?>">
      <?= $csrfField ?>
      <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
      <input type="hidden" name="student" value="<?= e($request['id']) ?>">
      <input type="hidden" name="decision" value="accept">
      <button type="submit" class="c-btn c-btn--decision c-btn--solid"><?= icon('check', 13) ?>Accept</button>
    </form>
  </div>
</article>
<?php unset($requestForm, $requestAction); ?>
