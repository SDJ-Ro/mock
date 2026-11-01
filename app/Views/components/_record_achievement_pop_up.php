<?php
// The Record Achievement pop-up, on the shared pop-up frame (_pop_up.php).
// It saves nothing yet: sending it closes it and shows "Achievement recorded." (record-achievement.js).
// Its look: record-achievement.css + form-field.css (the sand fields), dropdown, datepicker. Set first:
//   $recordVariant 'record' → id j-record-achievement: empty; record-achievement.js puts in the student whose Record was clicked
//                  'review' → id j-review-issue: a student's reported issue ($recordIssue, example data: app/Mock/achievements.php),
//                             filled in, with the student's evidence instead of the upload box (+ the evidence preview pop-up)
//   $recordStudent the student (review), or null (record)
//   $recordIssue   the issue (review), or null
$isReview  = $recordVariant === 'review';
$popId     = $isReview ? 'j-review-issue' : 'j-record-achievement';
$student   = $recordStudent;
$issue     = $recordIssue;
$initials  = $student === null ? '' : initials($student['name']);
$typeNames = array_column(config('achievements.groups'), 'type');   // Academic, Sports, Clubs & Societies, Leadership

ob_start();
?>
<form class="c-pop-up__form c-record-form c-fields--sand j-record-form" data-mock-message="Achievement recorded.">
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
      $selectedValue = $isReview ? config("achievements.groups.{$issue['group']}.type") : null;
      $placeholder   = 'Select an achievement type';
      $prefix        = 'Type';
      $dropdownLabel = 'Achievement type';
      $name          = 'type';
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Achievement Title <span class="c-field__required">*</span></span>
      <input class="c-field__input" type="text" name="title" value="<?= e($issue['title'] ?? '') ?>" placeholder="e.g. Debating Society – Senior Member" required>
    </label>

    <div class="c-field-pair">
      <div class="c-field">
        <span class="c-label c-field__label">Date It Occurred <span class="c-field__required">*</span></span>
        <?php
        $datepickerId  = "{$popId}-date";
        $name          = 'date';
        $selectedValue = $issue['date'] ?? date('Y-m-d');
        $placeholder   = 'Select date';
        $tone          = 'sand';
        require __DIR__ . '/_datepicker.php';
        ?>
      </div>
      <label class="c-field">
        <span class="c-label c-field__label">Verified / Issued By</span>
        <input class="c-field__input" type="text" name="issuer" value="<?= e($issue['issuer'] ?? '') ?>" placeholder="e.g. Mr. Silva, Club Patron">
      </label>
    </div>

    <label class="c-field">
      <span class="c-label c-field__label">Description <span class="c-field__required">*</span></span>
      <textarea class="c-field__input c-field__input--area" name="description" rows="3" required
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
          <button type="button" class="c-btn c-btn--solid c-btn--pill c-record-form__view-file" data-pop-up-open="j-evidence-preview"><?= icon('eye', 14) ?><span>View File</span></button>
        </div>
      </div>
    <?php else: ?>
      <div class="c-field">
        <span class="c-label c-field__label">Supporting Proof <span class="c-field__required">*</span></span>
        <?php // The whole box is the file input's label: clicking it opens the file chooser (no script needed) ?>
        <label class="c-field__file">
          <input type="file" class="c-field__hidden-input j-record-file" name="proof" accept="image/*,.pdf">
          <?= icon('upload', 28) ?>
          <span class="c-field__file-title"><strong>Click to upload</strong> or drag and drop</span>
          <span class="c-field__hint">Photos or PDFs, up to 10MB each</span>
          <span class="c-field__file-name j-record-file-name" hidden></span>
        </label>
      </div>
    <?php endif; ?>
  </div>

  <footer class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
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
  // The student's evidence, opened from "View File" (example content)
  $evidenceId      = 'j-evidence-preview';
  $evidenceTitle   = words('{Student} Evidence Preview');
  $evidenceFile    = $issue['file'];
  $evidenceLine    = $issue['file_title'];
  $evidenceDetails = ['Issued by' => $issue['issued_by'], 'Date' => $issue['issued_on'], 'Content' => $issue['content']];
  require __DIR__ . '/_evidence_preview_pop_up.php';
  ?>
<?php endif; ?>
<?php unset($isReview, $popId, $student, $issue, $initials, $typeNames, $recordVariant, $recordStudent, $recordIssue); ?>
