<?php
// The office's "New Extracurricular" pop-up ('create', from the list's Create card) and the "Edit {name}" pop-up
// ('edit', from the hero's Edit program), in the shared frame (_pop_up.php). Both send ExtracurricularController::save().
//   create: name, type, teacher in charge, the two limits, description (management's waits for the admin)
//   edit, management / admin ($can['editAll']): name, type, category, Who Can Join (age groups, open to, lowest {grade},
//         intake, the two limits), cover photo, the teacher in charge and the coach
//   edit, the teacher in charge: the description and cover photo only
// Looks: form-field.css, image-picker.css. Set before requiring (cleared at the end): $programmePopUp ('create' | 'edit');
// for 'edit' $programme and $can, $gradeOptions. From the page: $teachers (TeacherChoices::options()), $csrfField, $currentRole.
$isEdit     = $programmePopUp === 'edit';
$editAll    = !$isEdit || $can['editAll'];
$settings   = $isEdit ? $programme['settings'] : [];
$popUpTypes = array_map(fn (string $key, array $type): array => ['value' => $key, 'label' => $type['name']],
    array_keys(config('extracurricular.types')), config('extracurricular.types'));
ob_start();
?>
<form class="c-pop-up__form" method="post" action="<?= e("/{$currentRole}/extracurricular/save") ?>" enctype="multipart/form-data" data-warn-unsaved>
  <?= $csrfField ?>
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
    <input type="hidden" name="part" value="programme">
  <?php endif; ?>

  <div class="c-pop-up__body">
    <?php if ($editAll): ?>
      <label class="c-field">
        <span class="c-label c-field__label">Programme Name<?php if ($isEdit): ?> <span class="c-field__required c-field__required--terracotta">*</span><?php endif; ?></span>
        <input type="text" class="c-field__input" name="name" value="<?= e($isEdit ? $programme['name'] : '') ?>" placeholder="e.g. Chess Club" maxlength="100" required>
      </label>

      <div class="c-field-pair">
        <div class="c-field">
          <span class="c-label c-field__label"><?= $isEdit ? 'Programme Type' : 'Type' ?></span>
          <?php
          $dropdownId    = "j-programme-{$programmePopUp}-type";
          $dropdownLabel = 'Type';
          $name          = 'type';
          $options       = $popUpTypes;
          $selectedValue = $isEdit ? $programme['type'] : null;
          $placeholder   = 'Select type';
          $required      = 'Please select a type.';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>
        <?php if ($isEdit): ?>
          <div class="c-field">
            <span class="c-label c-field__label">Category</span>
            <?php
            $dropdownId    = 'j-programme-edit-category';
            $dropdownLabel = 'Category';
            $name          = 'category';
            $options       = config('extracurricular.categories');
            $selectedValue = $programme['category'];
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
        <?php else: ?>
          <div class="c-field">
            <span class="c-label c-field__label"><?= e(words('{Teacher} in Charge (TIC)')) ?></span>
            <?php
            $dropdownId    = 'j-programme-create-tic';
            $dropdownLabel = words('{Teacher} in charge');
            $name          = 'teacher';
            $options       = $teachers;
            $variant       = 'teacher';
            $selectedValue = '';
            $placeholder   = words('Assign {Teacher} in Charge');
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($isEdit && $editAll): ?>
      <section class="c-field-section">
        <div>
          <h3 class="c-field-section__title">Who Can Join</h3>
          <p class="c-field-section__hint"><?= e(words('A sport always uses age groups (from the date of birth); a club may not.')) ?></p>
        </div>
        <div class="c-field-pair">
          <div class="c-field">
            <span class="c-label c-field__label">Age Groups</span>
            <?php
            $dropdownId    = 'j-programme-edit-age-groups';
            $dropdownLabel = 'Age groups';
            $name          = 'uses_age_groups';
            $options       = [['value' => '1', 'label' => 'Used'], ['value' => '0', 'label' => 'Not used', 'disabled' => $programme['type'] === 'sport', 'sub' => $programme['type'] === 'sport' ? 'A sport always uses them' : null]];
            $selectedValue = (string) $settings['uses_age_groups'];
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
          <div class="c-field">
            <span class="c-label c-field__label">Open To</span>
            <?php
            $dropdownId    = 'j-programme-edit-open-to';
            $dropdownLabel = 'Open to';
            $name          = 'open_to';
            $options       = [['value' => 'everyone', 'label' => 'Everyone'], ['value' => 'boys', 'label' => 'Boys'], ['value' => 'girls', 'label' => 'Girls']];
            $selectedValue = $settings['open_to'];
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
        </div>
        <div class="c-field-pair">
          <div class="c-field">
            <span class="c-label c-field__label">From <?= e(word('grade')) ?></span>
            <?php
            $dropdownId    = 'j-programme-edit-lowest-grade';
            $dropdownLabel = 'Lowest ' . strtolower(word('grade'));
            $name          = 'lowest_grade';
            $options       = [['value' => '', 'label' => 'Any ' . strtolower(word('grade'))], ...$gradeOptions];
            $selectedValue = (string) ($settings['lowest_grade'] ?? '');
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
          <div class="c-field">
            <span class="c-label c-field__label">Intake</span>
            <?php
            $dropdownId    = 'j-programme-edit-intake';
            $dropdownLabel = 'Intake';
            $name          = 'is_open';
            $options       = [['value' => '1', 'label' => 'Open'], ['value' => '0', 'label' => 'Closed']];
            $selectedValue = (string) $settings['is_open'];
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($editAll): ?>
      <label class="c-field">
        <span class="c-label c-field__label">Limit of <?= e(plural('student')) ?> per Age Group</span>
        <input type="number" class="c-field__input" name="age_group_limit" min="1" max="1000" value="<?= e($settings['age_group_limit'] ?? '') ?>"
               placeholder="<?= e('e.g. ' . rule('age_group_limit')) ?>">
      </label>
    <?php endif; ?>

    <?php if ($isEdit): ?>
      <div class="c-field">
        <span class="c-label c-field__label">Cover Photo</span>
        <?php
        $imagePicker = ['variant' => 'box', 'name' => 'cover', 'image' => $programme['image'], 'label' => 'Cover photo'];
        require __DIR__ . '/_image_picker.php';
        ?>
      </div>

      <?php if ($editAll): ?>
        <?php foreach (['tic' => [words('{Teacher} in Charge (TIC)'), 'Assign or update the faculty coordinator for this programme.'],
                        'coach' => ['Coach / Instructor', 'Maintain the coordinator or coach details displayed on this programme.']] as $who => [$sectionTitle, $sectionHint]): ?>
          <section class="c-field-section">
            <div>
              <h3 class="c-field-section__title"><?= e($sectionTitle) ?></h3>
              <p class="c-field-section__hint"><?= e($sectionHint) ?></p>
            </div>
            <?php
            $staffFields = ['who' => $who, 'programme' => $programme, 'id' => 'j-programme-edit'];
            require __DIR__ . '/_staff_fields.php';
            ?>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

    <label class="c-field">
      <span class="c-label c-field__label">Description</span>
      <textarea class="c-field__input c-field__input--area" name="description" rows="3" maxlength="1000"
                placeholder="Describe the extracurricular activities, goals, and membership expectations…"><?= e($isEdit ? $programme['description'] : '') ?></textarea>
    </label>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--solid"><?= $isEdit ? 'Save Programme' : 'Create Programme' ?></button>
  </div>
</form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = "j-programme-{$programmePopUp}";
$popUpSize        = 'wide';
$popUpIcon        = $isEdit ? 'edit' : 'usersRound';
$popUpEyebrow     = $isEdit ? 'Extracurricular Programme' : 'New Programme';
$popUpTitle       = $isEdit ? "Edit {$programme['name']}" : 'New Extracurricular';
$popUpDescription = $isEdit ? 'Update the programme information, category, and cover image.' : 'Set up a club, society, or sports programme.';
require __DIR__ . '/_pop_up.php';
unset($programmePopUp, $isEdit, $editAll, $settings, $popUpTypes, $who, $sectionTitle, $sectionHint);
