<?php
// One member row in a team's pop-up (_team_pop_up.php): the member (picked from the programme's members — of the
// team's age group, when it has one), their position, and × (repeat-list.js removes it). Also the empty row
// "+ Add Member" copies from the pop-up's <template>. Its look: team-pop-up.css (.c-roster-row).
// Set before requiring: $member (null = an empty row), $rosterRowId. From the pop-up: $team, $programme.
?>
<div class="c-roster-row" data-repeat-row>
  <?php
  $dropdownId    = $rosterRowId;
  $dropdownLabel = word('student');
  $name          = 'member[]';
  $options       = array_map(fn (array $person): array => ['value' => $person['id'], 'label' => $person['name'],
                       'sub' => ($person['grade'] !== null ? grade_name((int) $person['grade']) : '—') . " · {$person['ageGroup']}"],
                   array_values(array_filter($programme['members'], fn (array $person): bool => in_array($team['ageGroup'] ?? '', ['', $person['ageGroup']], true))));
  $selectedValue = $member['id'] ?? null;
  $placeholder   = 'Choose a member';
  require __DIR__ . '/_dropdown.php';
  ?>
  <input type="text" class="c-field__input" name="member_position[]" value="<?= e($member['position'] ?? '') ?>" placeholder="Role / position" maxlength="50" aria-label="Role or position">
  <button type="button" class="c-roster-row__remove" data-repeat-remove aria-label="Remove member"><?= icon('close', 16) ?></button>
</div>
