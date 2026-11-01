<?php
// A programme's "Teams & Roster" for staff:
//   the age-group filter (a GET form: the server picks the teams and students)
//   the members not in a team yet, each with "Assign" (_pick_menu.php: the teams of their own age group) and × Remove
//   (asks first, no reason; the membership goes to history)
//   the team cards (_team_card.php), each with "Edit" → its pop-up; "+ Add Team" → an empty one (_team_pop_up.php)
//   a member's name opens their club view in the profile pop-up (_person_profile_pop_up.php)
// Who may change teams and members: $can['edit'] (the teacher in charge, management, admin).
// Looks: programme-panel.css, team-card.css, pick-menu.css; layout: extracurricular-grid.css.
// From the controller: $programme (filterGroups, age, teamsShown, unassigned), $can.
// From the page: $currentPath, $csrfField, $currentRole.
$memberUrl = "/{$currentRole}/extracurricular/member?id={$programme['id']}&student=";
?>
<section class="c-card c-programme-panel">
  <header class="c-programme-panel__head">
    <h2 class="c-panel__title c-programme-panel__title"><span class="c-programme-panel__icon c-programme-panel__icon--boxed"><?= icon('users', 20) ?></span>Teams &amp; Roster</h2>
    <?php if ($can['edit']): ?>
      <button type="button" class="c-btn c-btn--sky c-btn--small" data-pop-up-open="j-team-new"><?= icon('plus', 14) ?>Add Team</button>
    <?php endif; ?>
  </header>

  <form class="c-teams__filter" method="get" action="<?= e($currentPath) ?>" data-submit-on-change>
    <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
    <span class="c-teams__filter-title">Filter by Age Group</span>
    <div class="c-teams__filter-choice">
      <?php
      $dropdownId    = 'j-teams-age';
      $dropdownLabel = 'Filter by age group';
      $name          = 'age';
      $options       = [['value' => '', 'label' => 'All'], ...array_map(fn (string $group): array => ['value' => $group, 'label' => $group], $programme['filterGroups'])];
      $selectedValue = $programme['age'];
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
  </form>

  <?php if ($programme['unassigned'] !== []): ?>
    <div class="c-teams__unassigned">
      <span class="c-tag c-tag--terracotta"><?= e('Unassigned ' . plural('student') . ' (' . count($programme['unassigned']) . ')') ?></span>
      <ul class="c-teams__unassigned-list">
        <?php foreach ($programme['unassigned'] as $student): ?>
          <li class="c-unassigned">
            <?php if ($student['photo'] !== ''): ?>
              <img class="c-unassigned__photo" src="<?= e($student['photo']) ?>" alt="">
            <?php else: ?>
              <span class="c-avatar c-unassigned__photo" aria-hidden="true"><?= e(initials($student['name'])) ?></span>
            <?php endif; ?>
            <div class="c-unassigned__who">
              <button type="button" class="c-unassigned__name j-person-view" data-url="<?= e($memberUrl . $student['id']) ?>"><?= e($student['name']) ?></button>
              <p class="c-label c-unassigned__grade"><?= e($student['grade'] !== null ? grade_name((int) $student['grade']) : '—') ?> <span class="c-tag c-tag--round c-tag--sky"><?= e($student['ageGroup']) ?></span></p>
            </div>
            <?php if ($can['edit'] && $student['teams'] !== []): ?>
              <?php
              $pickMenu = ['variant' => 'assign', 'label' => 'Assign', 'title' => "Assign {$student['name']} to a team", 'options' => $student['teams'],
                           'chosen' => '', 'action' => "/{$currentRole}/extracurricular/assign", 'field' => 'team',
                           'hidden' => ['id' => $programme['id'], 'student' => $student['id']]];
              require __DIR__ . '/_pick_menu.php';
              ?>
            <?php endif; ?>
            <?php if ($can['edit']): ?>
              <form method="post" action="<?= e("/{$currentRole}/extracurricular/remove") ?>"
                    data-question-title="<?= e("Remove {$student['name']}?") ?>" data-question-button="Remove" data-question-icon="close"
                    data-question-text="<?= e("{$student['name']} leaves {$programme['name']} today. Their membership stays in the history.") ?>">
                <?= $csrfField ?>
                <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
                <input type="hidden" name="student" value="<?= e($student['id']) ?>">
                <button type="submit" class="c-btn c-btn--close" aria-label="<?= e("Remove {$student['name']}") ?>"><?= icon('close', 14) ?></button>
              </form>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($programme['teamsShown'] === []): ?>
    <div class="c-empty-box">
      <p class="c-empty-box__text">No teams defined yet.<?= $can['edit'] ? ' Click “Add Team” above to create one.' : '' ?></p>
    </div>
  <?php else: ?>
    <div class="l-pair-grid">
      <?php foreach ($programme['teamsShown'] as $team): ?>
        <?php require __DIR__ . '/_team_card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php if ($can['edit']): ?>
  <?php
  $team = null;
  require __DIR__ . '/_team_pop_up.php';
  ?>
  <?php foreach ($programme['teamsShown'] as $team): ?>
    <?php require __DIR__ . '/_team_pop_up.php'; ?>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/_person_profile_pop_up.php'; ?>
<?php unset($memberUrl, $student, $team); ?>
