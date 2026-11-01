<?php
// The reason box the "Are you sure?" pop-up shows when something is turned down: a <template>
// the question pop-up copies in (data-question-extra="{id}" on the form; question-pop-up.js links the box to that form).
// Used by the notice board (Reject Notice), Parent → Approvals (Decline) and Extracurricular (Reject a programme / a join request).
// Set before requiring (cleared at the end):
//   $reasonBox = ['id' => template id, 'label' => 'Reason', 'placeholder' => '…', 'required' => true | false]
?>
<template id="<?= e($reasonBox['id']) ?>">
  <label class="c-field c-question__field">
    <span class="c-label c-field__label"><?= e($reasonBox['label']) ?></span>
    <textarea class="c-field__input c-field__input--area" name="note" rows="3" maxlength="255"<?= $reasonBox['required'] ? ' required' : '' ?>
              placeholder="<?= e($reasonBox['placeholder']) ?>"></textarea>
  </label>
</template>
<?php unset($reasonBox); ?>
