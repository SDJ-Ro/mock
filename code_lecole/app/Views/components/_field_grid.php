<?php
// The Add page's boxes in two columns (one on phones): each one its label (+ the role-coloured * when needed) over
// its box (_field_box.php, 'form' look). Wide ones take the whole row. Used by _add_person_page.php (each section,
// and the new parent's part). Its look: add-person.css (the grid) + form-field.css (labels, boxes).
// Set first (cleared at the end):
//   $fieldGrid      the boxes (PeopleAddController::boxes()): ['name', 'box', 'label', 'value', 'options', 'wide',
//                   'required', 'minAge', 'maxAge']
//   $fieldGridTone  the role colour: sky · sunshine · terracotta · maroon
?>
<div class="c-add-person__grid">
  <?php foreach ($fieldGrid as $fgBox): ?>
    <div class="c-field<?= $fgBox['wide'] ? ' c-add-person__wide' : '' ?>">
      <span class="c-label c-field__label"><?= e($fgBox['label']) ?><?php if ($fgBox['required']): ?> <span class="c-field__required c-field__required--<?= e($fieldGridTone) ?>">*</span><?php endif; ?></span>
      <?php
      $fieldBox        = $fgBox;
      $fieldBoxVariant = 'form';
      $fieldBoxTone    = $fieldGridTone;
      $fieldBoxForm    = '';
      require __DIR__ . '/_field_box.php';
      ?>
    </div>
  <?php endforeach; ?>
</div>
<?php unset($fieldGrid, $fieldGridTone, $fgBox, $fieldBoxVariant, $fieldBoxTone, $fieldBoxForm); ?>
