<?php
// Pick one picture from the computer, with a preview (also the "Upload cover photo" boxes).
// image-picker.js shows the chosen file in the preview; like the rest of the example data nothing is uploaded yet.
// Its look: image-picker.css. Set before requiring (cleared at the end):
//   $imagePicker = ['variant' => 'box' (a dashed box you click: Edit Programme, a team's cover)
//                              | 'row' (a preview + "Choose Cover Photo" button + hint: an achievement's cover),
//                   'name' => the file field's name, 'image' => the current picture's address ('' = none),
//                   'label' => what it is for (screen readers / the button)]
$pickerImage = $imagePicker['image'];
?>
<label class="c-image-picker c-image-picker--<?= e($imagePicker['variant']) ?>">
  <span class="c-image-picker__preview">
    <img class="c-image-picker__image" src="<?= e($pickerImage) ?>" alt=""<?= $pickerImage === '' ? ' hidden' : '' ?>>
    <span class="c-image-picker__empty<?= $imagePicker['variant'] === 'box' ? ' c-label' : '' ?>"<?= $pickerImage !== '' ? ' hidden' : '' ?>>
      <?php if ($imagePicker['variant'] === 'box'): ?><?= icon('imagePlus', 22) ?>Upload Cover Photo<?php else: ?>No cover photo selected<?php endif; ?>
    </span>
  </span>
  <?php if ($imagePicker['variant'] === 'row'): ?>
    <span class="c-image-picker__side">
      <span class="c-btn c-btn--ghost c-image-picker__button"><?= icon('imagePlus', 15) ?><?= e($imagePicker['label']) ?></span>
      <span class="c-image-picker__hint">Select an image file to display on the achievement card.</span>
    </span>
  <?php endif; ?>
  <input type="file" class="c-field__hidden-input" name="<?= e($imagePicker['name']) ?>" accept="image/*" aria-label="<?= e($imagePicker['label']) ?>" data-image-picker>
</label>
<?php unset($imagePicker, $pickerImage); ?>
