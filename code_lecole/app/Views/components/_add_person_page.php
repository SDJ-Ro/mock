<?php
// People's Add page: Add Student / Teacher / Staff, or for parents a policy card (a parent
// is added with their child). Built from shared pieces: the page box in the role's colour (_form_card.php), the
// sections' boxes (_field_grid.php → _field_box.php), the parent part (_admission_guardian.php), the qualification
// rows (_qualification_rows.php), banners (banner.css). add-person.css is only its layout and the parent part's look.
// A normal form to PeopleAddController::add(). Set before requiring:
//   $personForm (from PeopleAddController::form())
//     'tab', 'tone', 'title', 'sections' ([['title', 'boxes' | 'guardian' | 'qualifications'], …]),
//     'guardian', 'idNumber', 'mode', 'idType', 'parent', 'searchUrl' (the parent part: _admission_guardian.php),
//     'qualifications' (as typed), 'error' (why the last try was refused, or null), 'action', 'back',
//     'studentUrl' (the parents tab's button: Add Student)
// From the page: $csrfField.
$pfButton = ['student' => words('Enroll {Student}'), 'teacher' => words('Add {Teacher}'), 'management' => 'Add Staff'];

ob_start();
?>
  <form class="c-add-person c-fields--people" method="post" action="<?= e($personForm['action']) ?>" enctype="multipart/form-data" novalidate data-warn-unsaved>
    <?= $csrfField ?>
    <input type="hidden" name="tab" value="<?= e($personForm['tab']) ?>">

    <div class="c-add-person__body">
      <?php if ($personForm['error'] !== null): ?>
        <p class="c-banner c-banner--error" role="alert"><?= e($personForm['error']) ?></p>
      <?php endif; ?>

      <?php if ($personForm['tab'] === 'parent'): ?>
        <div class="c-add-person__policy">
          <span class="c-add-person__policy-icon" aria-hidden="true"><?= icon('users', 24) ?></span>
          <div>
            <h3 class="c-add-person__policy-title"><?= e(words('{Parent} & Guardian Registration')) ?></h3>
            <p class="c-add-person__policy-text"><?= e(words('{Parents} and legal guardians are added together with their child, on the {student} enrollment form. This keeps every family linked and lets brothers and sisters share one {parent} account.')) ?></p>
            <a class="c-btn c-btn--sky" href="<?= e($personForm['studentUrl']) ?>"><?= e(words('Go to {Student} Enrollment')) ?></a>
          </div>
        </div>
      <?php endif; ?>

      <?php foreach ($personForm['sections'] as $pfSection): ?>
        <section class="c-add-person__section">
          <h3 class="c-add-person__title"><?= e($pfSection['title']) ?></h3>
          <?php if (is_array($pfSection['boxes'])): ?>
            <?php
            $fieldGrid     = $pfSection['boxes'];
            $fieldGridTone = $personForm['tone'];
            require __DIR__ . '/_field_grid.php';
            ?>
          <?php elseif ($pfSection['boxes'] === 'qualifications'): ?>
            <?php
            $qualificationRows  = $personForm['qualifications'];
            $qualificationSmall = false;
            require __DIR__ . '/_qualification_rows.php';
            ?>
          <?php else: ?>
            <?php require __DIR__ . '/_admission_guardian.php'; ?>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>

    <?php if ($personForm['tab'] !== 'parent'): ?>
      <footer class="c-add-person__footer">
        <p class="c-add-person__note">Boxes marked <span class="c-field__required c-field__required--<?= e($personForm['tone']) ?>">*</span> are required.</p>
        <a class="c-btn c-btn--ghost" href="<?= e($personForm['back']) ?>">Cancel</a>
        <button type="submit" class="c-btn c-btn--<?= e($personForm['tone']) ?>"><?= icon('userPlus', 16) ?><?= e($pfButton[$personForm['tab']]) ?></button>
      </footer>
    <?php endif; ?>
  </form>
<?php
$formCardContent  = ob_get_clean();
$formCardBack     = $personForm['back'];
$formCardTone     = $personForm['tone'];
$formCardTitle    = $personForm['title'];
$formCardSubtitle = '';
require __DIR__ . '/_form_card.php';

unset($personForm, $pfButton, $pfSection);
?>
