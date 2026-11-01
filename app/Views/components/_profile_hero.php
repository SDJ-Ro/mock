<?php
// The coloured band at the top of a profile: the photo, the line above the name, the name, the portal line,
// the ID + status badges and, on My Profile, the Edit Profile button. One colour per role (profile-hero.css).
// Used by My Profile (_profile_page.php); later Child profile. Set first:
//   $hero        ['role', 'name', 'eyebrow', 'sub', 'id', 'status', 'photo', 'photoTo' (where a new photo is sent, or
//                null: no change-photo button)]
//   $heroEditing null = no button · false = "Edit Profile" · true = pressed, "Cancel" (profile-page.js switches it)
// While editing, a small round button on the photo picks a new one; choosing it sends its form (profile-page.js).
// From the page: $csrfField.
?>
<div class="c-profile-hero c-profile-hero--<?= e($hero['role']) ?>">
  <div class="c-profile-hero__photo">
    <img class="c-profile-hero__avatar" src="<?= e($hero['photo']) ?>" alt="<?= e($hero['name']) ?>">
    <?php if ($hero['photoTo'] !== null): ?>
      <form class="c-profile-hero__photo-form" method="post" action="<?= e($hero['photoTo']) ?>" enctype="multipart/form-data">
        <?= $csrfField ?>
        <label class="c-profile-hero__photo-button" title="Change photo">
          <input class="c-field__hidden-input j-profile-photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" aria-label="Change photo">
          <?= icon('imagePlus', 15) ?>
        </label>
      </form>
    <?php endif; ?>
  </div>

  <div class="c-profile-hero__body">
    <p class="c-label c-label--light c-profile-hero__eyebrow"><?= e($hero['eyebrow']) ?></p>
    <h2 class="c-profile-hero__name"><?= e($hero['name']) ?></h2>
    <p class="c-profile-hero__sub"><?= e($hero['sub']) ?></p>
    <div class="c-profile-hero__badges">
      <span class="c-tag c-profile-hero__badge"><?= e($hero['id']) ?></span>
      <span class="c-tag c-profile-hero__badge c-profile-hero__badge--status"><?= e($hero['status']) ?></span>
    </div>
  </div>

  <?php if ($heroEditing !== null): ?>
    <button type="button" class="c-btn c-btn--glass c-profile-hero__edit-btn" id="j-profile-edit-btn" aria-pressed="<?= $heroEditing ? 'true' : 'false' ?>">
      <?= icon('edit', 13) ?>
      <span><?= $heroEditing ? 'Cancel' : 'Edit Profile' ?></span>
    </button>
  <?php endif; ?>
</div>
<?php unset($hero, $heroEditing); ?>
