<?php
// "Forgot Password?" on the sign-in page, in the shared pop-up frame (_pop_up.php) with its own look ('forgot' in auth.css).
// Step 1 asks for the ID / email and sends a 6-digit code; step 2 (?reset=1, $isResetStep from AuthController::signin)
// takes the code + a new password. On step 2 it is already open when the page loads, with any error ($resetError) inside.
ob_start();
?>
<div class="fp-body">
  <?php if ($resetError): ?>
    <p class="c-banner c-banner--error fp-alert" role="alert">
      <?= icon('alertCircle') ?>
      <span><?= e($resetError) ?></span>
    </p>
  <?php endif; ?>

  <?php if (!$isResetStep): ?>
    <form class="auth-form" method="post" action="/auth/<?= e($role) ?>/forgot">
      <?= $csrfField ?>

      <label class="c-field">
        <span class="c-label"><?= e($portal['field']) ?></span>
        <input class="c-field__input" type="text" name="identifier" value="<?= e($identifier) ?>"
               placeholder="<?= e($portal['placeholder']) ?>" required autocomplete="username">
      </label>

      <div class="fp-actions">
        <button type="button" class="auth-link auth-link--button j-pop-up-close">Cancel</button>
        <button class="c-btn c-btn--accent" type="submit">Send 6-Digit Code <?= icon('arrowRight') ?></button>
      </div>
    </form>
  <?php else: ?>
    <form class="auth-form" method="post" action="/auth/<?= e($role) ?>/reset">
      <?= $csrfField ?>

      <label class="c-field">
        <span class="c-label">6-Digit Verification Code</span>
        <input class="c-field__input c-field__input--code" type="text" name="code" placeholder="• • • • • •" maxlength="6"
               inputmode="numeric" pattern="[0-9]*" required autofocus autocomplete="one-time-code">
      </label>

      <label class="c-field">
        <span class="c-label">New Password</span>
        <?php
        $passwordName         = 'password';
        $passwordClass        = 'c-field__input';
        $passwordPlaceholder  = 'Choose a password (min 8 characters)';
        $passwordAutocomplete = 'new-password';
        require dirname(__DIR__) . '/components/_password_field.php';
        ?>
      </label>

      <div class="fp-actions fp-actions--split">
        <a class="auth-link" href="/auth/<?= e($role) ?>">← Start over</a>
        <button class="c-btn c-btn--accent" type="submit">Update Password <?= icon('arrowRight') ?></button>
      </div>
    </form>
  <?php endif; ?>
</div>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-forgot';
$popUpIcon        = 'keyRound';
$popUpTitle       = 'Reset Your Password';
$popUpDescription = $isResetStep ? 'Enter the 6-digit code from your email and a new password.'
                                 : "Enter your account ID or email and we'll send a 6-digit code.";
$popUpVariant     = 'forgot';
$popUpOpenOnLoad  = $isResetStep;
require dirname(__DIR__) . '/components/_pop_up.php';
?>
