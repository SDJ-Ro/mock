<?php
// The fields for a programme's teacher in charge or coach. Used by the Edit Programme pop-up (both people) and the
// Edit Teacher in Charge / Edit Coach pop-ups (_staff_pop_up.php).
//   tic    one pick from the teachers (their email, phone and subjects come from People, not typed here)
//   coach  an outside person, not an account: name, specialty, email, phone, in two pairs
// Looks: form-field.css. Set before requiring (cleared at the end):
//   $staffFields = ['who' => 'tic' | 'coach', 'programme' => the programme (Extracurriculars::page()), 'id' => a prefix for the dropdown's id]
// From the page: $teachers (TeacherChoices::options()).
$staffProgramme = $staffFields['programme'];
?>
<?php if ($staffFields['who'] === 'tic'): ?>
  <div class="c-field">
    <span class="c-label c-field__label">Faculty Name</span>
    <?php
    $dropdownId    = "{$staffFields['id']}-tic";
    $dropdownLabel = words('{Teacher} in charge');
    $name          = 'teacher';
    $options       = $teachers;
    $variant       = 'teacher';
    $selectedValue = (string) ($staffProgramme['ticId'] ?? '');
    require __DIR__ . '/_dropdown.php';
    ?>
  </div>
<?php else: ?>
  <?php $staffCoach = $staffProgramme['coach']; ?>
  <div class="c-field-pair">
    <label class="c-field">
      <span class="c-label c-field__label">Name</span>
      <input type="text" class="c-field__input" name="coach_name" value="<?= e($staffCoach['name']) ?>" placeholder="e.g. Coach Dinesh" maxlength="150">
    </label>
    <label class="c-field">
      <span class="c-label c-field__label">Specialty</span>
      <input type="text" class="c-field__input" name="coach_specialty" value="<?= e($staffCoach['specialty']) ?>" maxlength="150">
    </label>
  </div>
  <div class="c-field-pair">
    <label class="c-field">
      <span class="c-label c-field__label">Email</span>
      <input type="email" class="c-field__input" name="coach_email" value="<?= e($staffCoach['email']) ?>" maxlength="191">
    </label>
    <label class="c-field">
      <span class="c-label c-field__label">Phone</span>
      <input type="tel" class="c-field__input" name="coach_phone" value="<?= e($staffCoach['phone']) ?>" maxlength="30">
    </label>
  </div>
<?php endif; ?>
<?php unset($staffFields, $staffProgramme, $staffCoach); ?>
