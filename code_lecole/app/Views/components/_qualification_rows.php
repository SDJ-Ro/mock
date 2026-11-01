<?php
// A teacher's qualifications as rows of three boxes (qualification, institution, year) with Remove, then
// "+ Add Qualification". The form sends three lists (qualification_degree[] …), one row each; empty rows are
// left out (App\Services\PersonFields::qualifications()). qualification-rows.js adds a row (a copy of the first, emptied)
// and removes one. Used by Add Teacher and the profile pop-up's Edit Profile (_field_box.php). Look: qualification-rows.css.
// Set first (cleared at the end):
//   $qualificationRows   [['degree', 'institution', 'year_obtained'], …] (one empty row when there are none)
//   $qualificationSmall  true = the 34px boxes inside a card (the pop-up)
$qrRows  = $qualificationRows !== [] ? $qualificationRows : [['degree' => '', 'institution' => '', 'year_obtained' => '']];
$qrClass = 'c-field__input' . ($qualificationSmall ? ' c-field__input--small c-info-card__input' : '');
?>
<div class="c-qualifications j-qualifications">
  <?php // Says "the rows were in this form": without it, rows left out (not sent) would look like "all removed" ?>
  <input type="hidden" name="qualifications_sent" value="1">
  <div class="c-qualifications__rows j-qualification-list">
    <?php foreach ($qrRows as $qrRow): ?>
      <div class="c-qualifications__row j-qualification-row">
        <input class="<?= e($qrClass) ?>" type="text" name="qualification_degree[]" value="<?= e($qrRow['degree']) ?>" placeholder="e.g. B.Sc. in Physics" aria-label="Qualification">
        <input class="<?= e($qrClass) ?>" type="text" name="qualification_institution[]" value="<?= e($qrRow['institution'] ?? '') ?>" placeholder="Institution" aria-label="Institution">
        <input class="<?= e($qrClass) ?>" type="text" name="qualification_year[]" value="<?= e($qrRow['year_obtained'] ?? '') ?>" placeholder="Year" inputmode="numeric" maxlength="4" aria-label="Year">
        <button type="button" class="c-btn c-btn--maroon c-btn--small j-qualification-remove">Remove</button>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="c-btn c-btn--sunshine c-btn--small j-qualification-add"><?= icon('plus', 14) ?>Add Qualification</button>
</div>
<?php unset($qualificationRows, $qualificationSmall, $qrRows, $qrClass, $qrRow); ?>
