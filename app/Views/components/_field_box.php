<?php
// One field's box, chosen by its kind (config/people.php 'fields'): a text box, several lines, a phone number, the date
// picker, a dropdown, a photo — and two lists the profile pop-up edits: 'parent' (the parent search: _parent_picker.php)
// and 'qualifications' (_qualification_rows.php). The label is the caller's. Used by the info card while editing
// (_info_card.php) and by the Add page (_add_person_page.php), so both draw a field the same way.
// Set first (cleared at the end):
//   $fieldBox        ['name' (the form field), 'box' (its kind), 'value' (as saved; 'parent': ['row', 'url'],
//                     'qualifications': the rows), 'label' (for screen readers), 'options' (a dropdown's), and for a
//                     date of birth 'minAge' / 'maxAge' (optional)]
//   $fieldBoxVariant 'card' (the 34px light-sky box inside an info card) · 'form' (the Add page's boxes, $fieldBoxTone)
//   $fieldBoxTone    the Add page's role colour: sky · sunshine · terracotta · maroon
//   $fieldBoxForm    the form a text box belongs to when it sits outside it ('' = the form around it)
$fbName  = $fieldBox['name'];
$fbBox   = $fieldBox['box'];
$fbCard  = $fieldBoxVariant === 'card';
$fbId    = "j-{$fieldBoxVariant}-" . str_replace('_', '-', $fbName);
$fbClass = 'c-field__input' . ($fbCard ? ' c-field__input--small c-info-card__input' : '');
$fbType  = str_contains($fbName, 'phone') ? 'tel' : (str_contains($fbName, 'email') ? 'email' : 'text');
?>
<?php if ($fbBox === 'date'): ?>
  <?php
  $datepickerId  = $fbId;
  $name          = $fbName;
  $selectedValue = $fieldBox['value'];
  $tone          = $fbCard ? 'sky' : $fieldBoxTone;
  $minAge        = $fieldBox['minAge'] ?? '';
  $maxAge        = $fieldBox['maxAge'] ?? '';
  require __DIR__ . '/_datepicker.php';
  ?>
<?php elseif ($fbBox === 'choice' || $fbBox === 'class'): ?>
  <?php
  $dropdownId    = $fbId;
  $dropdownLabel = $fieldBox['label'];
  $options       = $fieldBox['options'];
  $selectedValue = $fieldBox['value'];
  $placeholder   = 'Select';
  $name          = $fbName;
  $variant       = $fbCard ? 'small' : 'tinted';
  $tone          = $fbCard ? '' : $fieldBoxTone;
  require __DIR__ . '/_dropdown.php';
  ?>
<?php elseif ($fbBox === 'parent'): ?>
  <?php
  $parentPicker = ['name' => $fbName] + $fieldBox['value'];
  require __DIR__ . '/_parent_picker.php';
  ?>
<?php elseif ($fbBox === 'qualifications'): ?>
  <?php
  $qualificationRows  = $fieldBox['value'];
  $qualificationSmall = $fbCard;
  require __DIR__ . '/_qualification_rows.php';
  ?>
<?php elseif ($fbBox === 'photo'): ?>
  <label class="c-field__photo">
    <input class="c-field__hidden-input j-photo-input" type="file" name="<?= e($fbName) ?>" accept="image/png,image/jpeg,image/webp">
    <span class="c-field__photo-button"><?= icon('upload', 14) ?>Choose File</span>
    <span class="c-field__photo-name j-photo-name">No file chosen</span>
  </label>
<?php elseif ($fbBox === 'area' && !$fbCard): ?>
  <textarea class="c-field__input c-field__input--area" id="<?= e($fbId) ?>" name="<?= e($fbName) ?>" rows="3"
            aria-label="<?= e($fieldBox['label']) ?>"><?= e($fieldBox['value']) ?></textarea>
<?php elseif ($fbBox === 'phone' && !$fbCard): ?>
  <span class="c-field__prefixed">
    <span class="c-field__prefix"><?= e(country('phone.code')) ?></span>
    <input class="<?= e($fbClass) ?>" type="tel" id="<?= e($fbId) ?>" name="<?= e($fbName) ?>" value="<?= e($fieldBox['value']) ?>" aria-label="<?= e($fieldBox['label']) ?>">
  </span>
<?php else: ?>
  <input class="<?= e($fbClass) ?>" type="<?= e($fbType) ?>" id="<?= e($fbId) ?>" name="<?= e($fbName) ?>" value="<?= e($fieldBox['value']) ?>"
         aria-label="<?= e($fieldBox['label']) ?>"<?php if ($fieldBoxForm !== ''): ?> form="<?= e($fieldBoxForm) ?>"<?php endif; ?>>
<?php endif; ?>
<?php unset($fieldBox, $fbName, $fbBox, $fbCard, $fbId, $fbClass, $fbType); ?>
