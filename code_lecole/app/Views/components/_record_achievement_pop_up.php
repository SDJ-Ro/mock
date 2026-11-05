<?php
// The Record Achievement pop-up, on the shared pop-up frame (_pop_up.php). Sending it: AchievementController (B-3).
// Its look: record-achievement.css + form-field.css (the sand fields), dropdown, datepicker. Set first:
//   $recordVariant 'record' → id j-record-achievement: empty; record-achievement.js puts in the student whose Record
//                             was clicked; it posts to /teacher/achievements/record (proof optional: the file box)
//                  'review' → id j-review-issue: a student's missing-record request ($recordIssue,
//                             AchievementController::issue()), filled in from it, with the student's evidence instead
//                             of the upload box (View File opens it in a new tab); Approve & Record posts to
//                             /teacher/achievements/approve, Reject opens the reason pop-up (j-reject-request)
//   $recordStudent the student (review), or null (record)
//   $recordIssue   the request (review), or null
$isReview  = $recordVariant === 'review';
$popId     = $isReview ? 'j-review-issue' : 'j-record-achievement';
$student   = $recordStudent;
$issue     = $recordIssue;
$initials  = $student === null ? '' : initials($student['name']);
$typeNames = array_column(config('achievements.groups'), 'type');   // Academic, Sports, Clubs & Societies, Leadership

ob_start();
?>
<form class="c-pop-up__form c-record-form c-fields--sand j-record-form" method="post" enctype="multipart/form-data"
      action="<?= e($isReview ? '/teacher/achievements/approve' : '/teacher/achievements/record') ?>">
  <?= $csrfField ?>
  <?php if ($isReview): ?>
    <input type="hidden" name="request" value="<?= e($issue['id']) ?>">
  <?php else: ?>
    <input type="hidden" name="student" class="j-record-student" value="">
  <?php endif; ?>
  <div class="c-pop-up__body">
    <?php if ($isReview): ?>
      <div class="c-banner c-banner--error c-record-form__issue" role="note">
        <?= icon('alertTriangle', 18) ?>
        <p><strong><?= e(words('Reported Issue from {Student}')) ?></strong><?= e($issue['message']) ?></p>
      </div>
    <?php endif; ?>

    <?php // The student (record-achievement.js fills these in for the Record pop-up) ?>
    <div class="c-record-form__student">
      <span class="c-avatar j-record-initials"><?= e($initials) ?></span>
      <div class="c-record-form__who">
        <p class="c-record-form__name j-record-name"><?= e($student['name'] ?? '') ?></p>
        <p class="c-record-form__code">Index: <span class="j-record-code"><?= e($student['code'] ?? '') ?></span></p>
      </div>
      <span class="c-tag c-tag--oat j-record-from"><?= e($student['from'] ?? '') ?></span>
    </div>

    <div class="c-field">
      <span class="c-label c-field__label">Achievement Type <span class="c-field__required">*</span></span>
      <?php
      $dropdownId    = "{$popId}-type";
      $options       = $typeNames;
      $selectedValue = null;
      $placeholder   = 'Select an achievement type';
      $prefix        = 'Type';
      $dropdownLabel = 'Achievement type';
      $name          = 'type';
      $required      = 'Please select an achievement type.';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Achievement Title <span class="c-field__required">*</span></span>
      <input class="c-field__input" type="text" name="title" maxlength="150" value="<?= e($issue['title'] ?? '') ?>" placeholder="e.g. Debating Society – Senior Member" required>
    </label>

    <div class="c-field-pair">
      <div class="c-field">
        <span class="c-label c-field__label">Level <span class="c-field__required">*</span></span>
        <?php
        $dropdownId    = "{$popId}-level";
        $options       = country('achievement_levels');
        $selectedValue = null;
        $placeholder   = 'Select a level';
        $dropdownLabel = 'Level';
        $name          = 'level';
        $required      = 'Please select a level.';
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
      <label class="c-field">
        <span class="c-label c-field__label">Result</span>
        <input class="c-field__input" type="text" name="result" maxlength="100" placeholder="e.g. First Place, Runners Up">
      </label>
    </div>

    <div class="c-field-pair">
      <div class="c-field">
        <span class="c-label c-field__label">Date It Occurred <span class="c-field__required">*</span></span>
        <?php
        $datepickerId  = "{$popId}-date";
        $name          = 'date';
        $selectedValue = date('Y-m-d');
        $placeholder   = 'Select date';
        $tone          = 'sand';
        $maxDate       = date('Y-m-d');   // not in the future
        require __DIR__ . '/_datepicker.php';
        ?>
      </div>
      <label class="c-field">
        <span class="c-label c-field__label">Verified / Issued By</span>
        <input class="c-field__input" type="text" name="issuer" maxlength="150" placeholder="e.g. Mr. Silva, Club Patron">
      </label>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Description <span class="c-field__required">*</span></span>
      <textarea class="c-field__input c-field__input--area" name="description" rows="3" maxlength="600" required
                placeholder="Explain the achievement details, accomplishments, or missing verification…"><?= e($issue['description'] ?? '') ?></textarea>
    </label>

    <?php if ($isReview): ?>
      <div class="c-field">
        <span class="c-label c-field__label"><?= e(words("{Student}'s Submitted Evidence")) ?></span>
        <div class="c-record-form__evidence">
          <span class="c-record-form__file-icon"><?= icon('fileText', 20) ?></span>
          <div class="c-record-form__file">
            <p class="c-record-form__file-name"><?= e($issue['file']) ?></p>
            <p class="c-record-form__file-meta"><?= e($issue['file_meta']) ?></p>
          </div>
          <a class="c-btn c-btn--solid c-btn--pill c-record-form__view-file" href="/teacher/achievements/file?request=<?= e($issue['id']) ?>"
             target="_blank" rel="noopener"><?= icon('eye', 14) ?><span>View File</span></a>
        </div>
      </div>
    <?php else: ?>
      <div class="c-field">
        <span class="c-label c-field__label">Supporting Proof</span>
        <?php
        // Optional: a teacher may record without a file
        $fileBoxName     = 'proof';
        $fileBoxRequired = false;
        $fileBoxTypes    = config('achievements.proof_types');
        $fileBoxKb       = config('achievements.proof_kb');
        require __DIR__ . '/_file_box.php';
        ?>
      </div>
    <?php endif; ?>
  </div>

  <footer class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
    <?php if ($isReview): ?>
      <button type="button" class="c-btn c-btn--outline" data-pop-up-open="j-reject-request">Reject</button>
    <?php endif; ?>
    <button type="submit" class="c-btn c-btn--sunshine"><?= e($isReview ? 'Approve & Record' : 'Record Achievement') ?></button>
  </footer>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = $popId;
$popUpIcon        = $isReview ? 'alertTriangle' : 'award';
$popUpEyebrow     = $isReview ? 'Issue Report' : 'Achievement Record';
$popUpTitle       = $isReview ? "Review Issue — {$student['name']}" : 'Record Achievement';
$popUpDescription = $isReview ? words("Review the {student}'s reported missing record and evidence.") : words('Add a new verified {student} honour, award, or recognition.');
$popUpVariant     = $isReview ? 'review-issue' : 'record-achievement';
require __DIR__ . '/_pop_up.php';
?>

<?php if ($isReview): ?>
  <?php
  // Reject: the reason goes back with the request (opened from the Review pop-up's Reject button)
  ob_start();
  ?>
  <form class="c-pop-up__form c-fields--sand" method="post" action="/teacher/achievements/reject">
    <?= $csrfField ?>
    <input type="hidden" name="request" value="<?= e($issue['id']) ?>">
    <div class="c-pop-up__body">
      <label class="c-field">
        <span class="c-label c-field__label">Reason <span class="c-field__required">*</span></span>
        <textarea class="c-field__input c-field__input--area" name="reason" rows="3" maxlength="300" required
                  placeholder="<?= e(words('Tell the {student} why this record is not added…')) ?>"></textarea>
      </label>
    </div>
    <footer class="c-pop-up__footer">
      <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
      <button type="submit" class="c-btn c-btn--maroon">Reject Request</button>
    </footer>
  </form>
  <?php
  $popUpContent     = ob_get_clean();
  $popUpId          = 'j-reject-request';
  $popUpIcon        = 'alertTriangle';
  $popUpEyebrow     = 'Issue Report';
  $popUpTitle       = "Reject Request — {$student['name']}";
  $popUpDescription = 'The request is closed as rejected; your reason is kept with it.';
  require __DIR__ . '/_pop_up.php';
  ?>
<?php endif; ?>
<?php unset($isReview, $popId, $student, $issue, $initials, $typeNames, $recordVariant, $recordStudent, $recordIssue); ?>
