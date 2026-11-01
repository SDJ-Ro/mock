<?php
// The student's "Team & Roster": their team (several: one after another), or the programme's members while they are in
// no team — each with its name and size, then one row per member — initials, name, grade, position (or "Member") —
// the signed-in student's own row warm with a "You" tag. The list scrolls inside the panel, which is as tall as the
// calendar beside it (extracurricular-grid.css, no script). Members only: someone else sees the empty box.
// Looks: programme-panel.css, squad-roster.css, empty-box.css.
// From the controller: $programme (squad: [['title', 'players'], …] or null, studentId).
?>
<section class="c-card c-programme-panel c-programme-panel--fill">
  <header class="c-programme-panel__head">
    <h2 class="c-panel__title c-programme-panel__title"><span class="c-programme-panel__icon"><?= icon('users', 20) ?></span>Team &amp; Roster</h2>
  </header>

  <?php if ($programme['squad'] === null): ?>
    <div class="c-empty-box">
      <p class="c-empty-box__text">The roster shows once you join.</p>
    </div>
  <?php else: ?>
    <div class="c-programme-panel__fill c-squad__card">
      <?php foreach ($programme['squad'] as $squad): ?>
        <div class="c-squad__head">
          <span class="c-squad__title"><?= e($squad['title']) ?></span>
          <span class="c-chip c-chip--round c-squad__count"><?= e(count($squad['players']) . (count($squad['players']) === 1 ? ' Player' : ' Players')) ?></span>
        </div>
        <ul class="c-squad__list">
          <?php foreach ($squad['players'] as $player): ?>
            <?php $isYou = $player['id'] === $programme['studentId']; ?>
            <li class="c-squad__player<?= $isYou ? ' c-squad__player--you' : '' ?>">
              <span class="c-avatar"><?= e(initials($player['name'])) ?></span>
              <span class="c-squad__who">
                <span class="c-squad__name"><?= e($player['name']) ?><?php if ($isYou): ?> <span class="c-tag c-tag--round c-squad__you">You</span><?php endif; ?></span>
                <span class="c-squad__grade"><?= e($player['grade'] !== null ? grade_name((int) $player['grade']) : '—') ?></span>
              </span>
              <span class="c-label c-squad__role"><?= e($player['role']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php unset($squad, $player, $isYou); ?>
