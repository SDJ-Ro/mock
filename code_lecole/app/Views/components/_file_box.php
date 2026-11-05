<?php
// The file box: a dashed box to click (it opens the file chooser) or to drop one file on; the chosen file's name
// and size show in a pill under it (file-box.js). Its look: form-field.css (c-field__file--sand).
// It draws the box only: the caller puts its own label right above it (the label's * follows the caller's form).
// Set before requiring:
//   $fileBoxName      the form field's name ('proof')
//   $fileBoxRequired  true: the form can't be sent without a file
//   $fileBoxTypes     the types allowed: the file's type => the word the hint shows (['application/pdf' => 'PDF', …])
//   $fileBoxKb        the largest size, in KB (5120 → the hint says "up to 5 MB")
$fbWords = array_values($fileBoxTypes);
$fbLast  = array_pop($fbWords);
$fbHint  = ($fbWords === [] ? $fbLast : implode(', ', $fbWords) . " or {$fbLast}") . ', up to ' . round($fileBoxKb / 1024) . ' MB';
?>
<?php // The whole box is the file input's label: clicking it opens the file chooser (no script needed) ?>
<label class="c-field__file c-field__file--sand j-file-box">
  <input type="file" class="c-field__hidden-input j-file-box-input" name="<?= e($fileBoxName) ?>"
         accept="<?= e(implode(',', array_keys($fileBoxTypes))) ?>"<?= $fileBoxRequired ? ' required' : '' ?>>
  <?= icon('upload', 28) ?>
  <span class="c-field__file-title"><strong>Click to upload</strong> or drag and drop</span>
  <span class="c-field__hint"><?= e($fbHint) ?></span>
  <span class="c-field__file-name j-file-box-name" hidden></span>
</label>
<?php unset($fileBoxName, $fileBoxRequired, $fileBoxTypes, $fileBoxKb, $fbWords, $fbLast, $fbHint); ?>
