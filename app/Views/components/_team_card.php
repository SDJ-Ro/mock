<?php
// One team: its cover (+ "Edit" → the team's pop-up), name, size, and its members — photo (or initials), name (opens
// their club view), grade, position. Its look: team-card.css.
// Set before requiring: $team (Extracurriculars::team()). From the panel: $can, $memberUrl.
?>
<article class="c-team-card">
  <div class="c-team-card__cover">
    <img class="c-team-card__image" src="<?= e($team['cover']) ?>" alt="" loading="lazy">
    <?php if ($can['edit']): ?>
      <button type="button" class="c-team-card__edit" data-pop-up-open="<?= e("j-team-{$team['id']}") ?>" aria-label="<?= e("Edit {$team['name']}") ?>"><?= icon('edit', 12) ?>Edit</button>
    <?php endif; ?>
  </div>

  <div class="c-team-card__body">
    <div class="c-team-card__top">
      <h3 class="c-team-card__name"><?= e($team['name']) ?></h3>
      <span class="c-tag c-tag--round c-tag--sky"><?= e(count($team['roster']) . (count($team['roster']) === 1 ? ' Member' : ' Members')) ?></span>
    </div>

    <?php if ($team['roster'] === []): ?>
      <p class="c-team-card__none">No members added yet.<?= $can['edit'] ? ' Use Edit to build this roster.' : '' ?></p>
    <?php else: ?>
      <ul class="c-team-card__roster">
        <?php foreach ($team['roster'] as $member): ?>
          <li class="c-team-card__member">
            <?php if ($member['photo'] !== ''): ?>
              <img class="c-team-card__photo" src="<?= e($member['photo']) ?>" alt="">
            <?php else: ?>
              <span class="c-avatar c-team-card__photo" aria-hidden="true"><?= e(initials($member['name'])) ?></span>
            <?php endif; ?>
            <div class="c-team-card__who">
              <button type="button" class="c-team-card__member-name j-person-view" data-url="<?= e($memberUrl . $member['id']) ?>"><?= e($member['name']) ?></button>
              <p class="c-label"><?= e($member['grade'] !== null ? grade_name((int) $member['grade']) : '—') ?></p>
            </div>
            <?php if ($member['position'] !== null): ?>
              <span class="c-chip c-team-card__position"><?= e($member['position']) ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</article>
<?php unset($member); ?>
