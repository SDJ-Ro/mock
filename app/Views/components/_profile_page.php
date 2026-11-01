<?php
// My Profile (every role): the hero, the sections of cards, the account section with Change Password, and the
// Save / Cancel row while editing; "Changes saved." is the page frame's sliding banner. Its look: profile-page.css
// (+ profile-hero.css, info-card.css); profile-page.js
// switches editing on / off and opens the password form. Both forms are sent to ProfileController.
// The boxes sit inside the cards, so they join the Save form with form="j-profile-form".
// From ProfileController::show(): $profile (hero, sections — the last one has 'password' for Change Password, canEdit), $editing,
// $error (a refused save, inside the page it belongs to), $passwordError. From the frame: $currentRole, $csrfField.
$profileUrl = "/{$currentRole}/profile";
?>
<div class="c-profile-page" id="j-profile-page" data-editing="<?= $editing ? 'true' : 'false' ?>">
  <?php if ($error !== null): ?>
    <p class="c-banner c-banner--error" role="alert"><?= e($error) ?></p>
  <?php endif; ?>

  <?php
  $hero        = $profile['hero'];
  $heroEditing = $profile['canEdit'] ? $editing : null;
  require __DIR__ . '/_profile_hero.php';
  ?>

  <?php foreach ($profile['sections'] as $section): ?>
    <section class="c-card c-profile-section">
      <div class="c-profile-section__header">
        <div class="c-profile-section__title-row">
          <span class="c-profile-section__icon"><?= icon($section['icon'], 15) ?></span>
          <h2 class="c-panel__title"><?= e($section['title']) ?></h2>
        </div>
        <?php if ($section['password']): ?>
          <button type="button" class="c-btn c-btn--outline" id="j-profile-pwd-toggle"><?= $passwordError !== null ? 'Cancel' : 'Change Password' ?></button>
        <?php endif; ?>
      </div>

      <div class="c-info-card-grid<?= $section['columns'] === 4 ? ' c-info-card-grid--four' : '' ?><?= $section['tint'] !== null ? " c-profile-tinted c-profile-tinted--{$section['tint']}" : '' ?>">
        <?php foreach ($section['cards'] as $card): ?>
          <?php
          $infoCardForm    = 'j-profile-form';
          $infoCardVariant = $section['tint'] !== null ? 'on-tint' : '';
          require __DIR__ . '/_info_card.php';
          ?>
        <?php endforeach; ?>
      </div>

      <?php if ($section['password']): ?>
        <form class="c-profile-pwd" id="j-profile-pwd" method="post" action="<?= e("{$profileUrl}/password") ?>"<?= $passwordError === null ? ' hidden' : '' ?>>
          <?= $csrfField ?>
          <?php if ($passwordError !== null): ?>
            <p class="c-banner c-banner--error" role="alert"><?= e($passwordError) ?></p>
          <?php endif; ?>
          <div class="c-info-card-grid">
            <?php foreach (['current_password' => 'Current Password', 'password' => 'New Password', 'password_confirm' => 'Confirm New Password'] as $name => $label): ?>
              <div class="c-profile-pwd__field">
                <label class="c-label" for="j-<?= e($name) ?>"><?= e($label) ?></label>
                <?php
                $passwordName         = $name;
                $passwordId           = "j-{$name}";
                $passwordClass        = 'c-field__input c-field__input--small c-profile-pwd__input';
                $passwordPlaceholder  = '••••••••';
                $passwordAutocomplete = $name === 'current_password' ? 'current-password' : 'new-password';
                require __DIR__ . '/_password_field.php';
                ?>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="c-profile-pwd__actions">
            <button type="submit" class="c-btn c-btn--solid">Update Password</button>
          </div>
        </form>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <?php if ($profile['canEdit']): ?>
    <form class="c-profile-actions" id="j-profile-form" method="post" action="<?= e("{$profileUrl}/save") ?>" data-warn-unsaved>
      <?= $csrfField ?>
      <button type="reset" class="c-btn c-btn--ghost">Cancel</button>
      <button type="submit" class="c-btn c-btn--solid"><?= icon('check', 14) ?> Save Changes</button>
    </form>
  <?php endif; ?>
</div>
