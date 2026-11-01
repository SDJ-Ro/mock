<?php
// A person's information: titled sections of small cards (_info_card.php), 2 or 4 across.
// In People's pop-up (inside its Edit Profile form: the cards' boxes join $infoCardForm) and on the parent's Child
// profile (view only). Looks: person-profile.css, info-card.css.
// Set before requiring: $sections (PeopleController::profileData()), $infoCardForm (the form's id, or null).
?>
<?php foreach ($sections as $section): ?>
  <section class="c-person__section">
    <h3 class="c-person__section-title"><?= icon($section['icon'], 16) ?><?= e($section['title']) ?></h3>
    <div class="c-info-card-grid c-info-card-grid--<?= $section['columns'] === 4 ? 'four' : 'two' ?>">
      <?php
      $infoCardVariant = 'rounded';
      foreach ($section['cards'] as $card)
      {
          require __DIR__ . '/_info_card.php';
      }
      ?>
    </div>
  </section>
<?php endforeach; ?>
<?php unset($section, $card, $infoCardVariant); ?>
