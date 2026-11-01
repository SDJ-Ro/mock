<?php
// Add Student's parent part: every student has a parent or guardian. Two choices:
//   "Use Existing Parent (Sibling)" → the parent search (_parent_picker.php)
//   "Create New Guardian"           → their boxes (config/people.php 'guardian', names start with guardian_), with
//                                     one ID box: the country's ID card number or a passport (a choice above it)
// The choices are radios; CSS shows the matching part (add-person.css, :has(… :checked)), so no script is needed.
// The server reads only the chosen part (PeopleAddController::add()). Inside _add_person_page.php's form.
//   $personForm  the page's data: 'guardian' (the boxes), 'mode' ('existing' | 'new'), 'idType' ('national_id' |
//                'passport'), 'idNumber', 'parent' (the chosen parent's row, or null), 'searchUrl', 'tone'
?>
<div class="c-guardian">
  <p class="c-guardian__intro"><?= e(words('Every enrolled {student} has one {parent} or legal guardian. Link a {parent} already registered (for a brother or sister), or add a new one.')) ?></p>

  <div class="c-guardian__modes" role="radiogroup" aria-label="<?= e(words('{Parent} or guardian')) ?>">
    <?php foreach (['existing' => ['userCheck', words('Use Existing {Parent} (Sibling)'), words('Link a {parent} already registered')],
                    'new'      => ['userPlus', 'Create New Guardian', 'A new guardian profile and account']] as $gMode => [$gIcon, $gTitle, $gText]): ?>
      <label class="c-guardian__mode">
        <input class="c-field__hidden-input j-guardian-<?= e($gMode) ?>" type="radio" name="guardian_mode" value="<?= e($gMode) ?>"<?= $personForm['mode'] === $gMode ? ' checked' : '' ?>>
        <span class="c-guardian__mode-icon" aria-hidden="true"><?= icon($gIcon, 16) ?></span>
        <span>
          <span class="c-guardian__mode-title"><?= e($gTitle) ?></span>
          <span class="c-guardian__mode-text"><?= e($gText) ?></span>
        </span>
      </label>
    <?php endforeach; ?>
  </div>

  <div class="c-guardian__existing">
    <?php
    $parentPicker = ['name' => 'parent_id', 'row' => $personForm['parent'], 'url' => $personForm['searchUrl']];
    require __DIR__ . '/_parent_picker.php';
    ?>
  </div>

  <div class="c-guardian__new">
    <div class="c-field">
      <span class="c-label c-field__label">Identification Document <span class="c-field__required c-field__required--<?= e($personForm['tone']) ?>">*</span></span>
      <div class="c-guardian__ids">
        <?php foreach (['national_id' => country('national_id.label'), 'passport' => 'Passport'] as $gId => $gIdLabel): ?>
          <label class="c-guardian__id">
            <input class="c-field__hidden-input" type="radio" name="guardian_id_type" value="<?= e($gId) ?>"<?= $personForm['idType'] === $gId ? ' checked' : '' ?>>
            <?= e($gIdLabel) ?>
          </label>
        <?php endforeach; ?>
      </div>
      <input class="c-field__input" type="text" name="guardian_id_number" value="<?= e($personForm['idNumber']) ?>" maxlength="30"
             aria-label="<?= e(country('national_id.label') . ' or passport number') ?>">
    </div>

    <?php
    $fieldGrid     = $personForm['guardian'];
    $fieldGridTone = $personForm['tone'];
    require __DIR__ . '/_field_grid.php';
    ?>
  </div>
</div>
<?php unset($gMode, $gIcon, $gTitle, $gText, $gId, $gIdLabel); ?>
