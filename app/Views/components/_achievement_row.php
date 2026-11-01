<?php
// One achievement on the public pages (landing page highlights, honours board). Its look: landing.css.
// Set before requiring: $item (one row from AchievementModel::honoursBoard()), $i (its position: odd rows put the photo
// on the other side), $showDetails (the honours board also shows the facts list and the participants).
?>
<article class="ach-row <?= $i % 2 === 1 ? 'ach-row--reversed' : '' ?>" data-reveal>
  <div class="ach-row-media">
    <img src="<?= e($item['photo_url']) ?>" alt="<?= e($item['photo_alt'] ?? $item['title']) ?>">
  </div>

  <div>
    <div class="ach-tags">
      <span class="c-tag c-tag--round c-tag--<?= e($item['tone']) ?>"><?= e($item['category']) ?></span>
      <span class="c-tag c-tag--round c-tag--linen"><?= e($item['level']) ?></span>
      <span class="c-tag c-tag--round c-tag--linen"><?= e(date('Y', strtotime($item['achieved_on']))) ?></span>
    </div>

    <h3 class="ach-title"><?= e($item['title']) ?></h3>

    <p class="ach-meta">
      <span><?= icon('calendar', 14) ?> <?= e(date(country('date_format'), strtotime($item['achieved_on']))) ?></span>
      <span><?= icon('mapPin', 14) ?> <?= e($item['venue']) ?></span>
      <span><?= icon('trophy', 14) ?> <?= e($item['medal']) ?></span>
    </p>

    <p class="ach-summary"><?= e($item['summary']) ?></p>

    <?php if ($showDetails): ?>
      <dl class="ach-facts">
        <?php foreach ([
          'Tournament / Event' => $item['event_name'],
          'Organised By'       => $item['organiser'],
          'Venue'              => $item['venue'],
          'Scope / Level'      => $item['level'],
          'Position / Result'  => $item['result'],
          'Award / Medal'      => $item['medal'],
          'Age Group'          => $item['age_group'],
          'Representing'       => $item['team'],
        ] as $label => $value): ?>
          <div><dt class="c-label"><?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
        <?php endforeach; ?>
      </dl>

      <div class="ach-participants">
        <p class="c-label ach-participants-label"><?= icon('usersRound', 14) ?> Participants</p>
        <p class="ach-participants-names"><?= e(implode(' · ', $item['participants'])) ?></p>
        <p class="ach-participants-details"><?= e($item['details']) ?></p>
      </div>
    <?php endif; ?>
  </div>
</article>
