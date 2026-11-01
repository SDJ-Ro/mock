<?php
// A password box with an eye button inside its right end: clicking the eye shows / hides what was typed
// (password-field.css, password-field.js). Used by sign-in, sign-up, forgot password and My Profile.
// The box keeps its page's own look ($passwordClass); this piece only adds the eye. Set first:
//   $passwordName         the form field's name          $passwordClass        the box's own class(es)
//   $passwordPlaceholder  the grey hint                   $passwordAutocomplete 'current-password' / 'new-password'
//   $passwordId           (optional) id, for a <label for="…">
?>
<span class="c-password-field">
  <input class="<?= e($passwordClass) ?>" type="password" name="<?= e($passwordName) ?>"<?php if (isset($passwordId)): ?> id="<?= e($passwordId) ?>"<?php endif; ?>
         placeholder="<?= e($passwordPlaceholder) ?>" required autocomplete="<?= e($passwordAutocomplete) ?>">
  <button class="c-password-field__eye" type="button" aria-label="Show password"><?= icon('eye') ?></button>
</span>
<?php unset($passwordName, $passwordClass, $passwordPlaceholder, $passwordAutocomplete, $passwordId); ?>
