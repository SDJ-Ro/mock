<?php
// "Add Team" (an empty one) or "Edit {team}" (one pop-up per team, drawn by PHP), in the shared frame
// (_pop_up.php): name, age group (only when the programme uses them), cover photo, and the roster — one row per
// member (a member picked from the list, a position, ×); "+ Add Member" adds a row (repeat-list.js). Sent to
// ExtracurricularController::saveTeam(). An edited team can also be deleted (asks first: deleteTeam()).
// Looks: team-pop-up.css (+ form-field.css, image-picker.css).
// Set before requiring: $team (Extracurriculars::team(), or null = a new one). From the panel: $programme, $csrfField,
// $currentRole.
$teamId   = $team === null ? 'j-team-new' : "j-team-{$team['id']}";
$teamRows = "{$teamId}-roster";
$teamName = $team['name'] ?? 'Team';
ob_start();
?>
<form class="c-pop-up__form" method="post" action="<?= e("/{$currentRole}/extracurricular/team") ?>" enctype="multipart/form-data">
  <?= $csrfField ?>
  <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
  <input type="hidden" name="team" value="<?= e($team['id'] ?? '') ?>">
  <div class="c-pop-up__body">
    <div class="c-field-pair">
      <div class="c-team-pop-up__column">
        <label class="c-field">
          <span class="c-label c-field__label">Team Name <span class="c-field__required c-field__required--terracotta">*</span></span>
          <input type="text" class="c-field__input" name="name" value="<?= e($team['name'] ?? '') ?>" placeholder="e.g. Senior Team" maxlength="100" required>
        </label>
        <?php if ($programme['ageGroups'] !== []): ?>
          <div class="c-field">
            <span class="c-label c-field__label">Age Group <span class="c-field__required c-field__required--terracotta">*</span></span>
            <?php
            $dropdownId    = "{$teamId}-age";
            $dropdownLabel = 'Age group';
            $name          = 'age_group';
            $options       = $programme['ageGroups'];
            $selectedValue = in_array($team['ageGroup'] ?? '', $options, true) ? $team['ageGroup'] : null;
            $placeholder   = 'Select age group';
            $required      = 'Please choose the team\'s age group.';
            require __DIR__ . '/_dropdown.php';
            ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="c-field">
        <span class="c-label c-field__label">Panel Cover</span>
        <?php
        $imagePicker = ['variant' => 'box', 'name' => 'cover', 'image' => $team['cover'] ?? '', 'label' => 'Team cover photo'];
        require __DIR__ . '/_image_picker.php';
        ?>
      </div>
    </div>

    <section class="c-field-section">
      <div class="c-team-pop-up__roster-head">
        <div>
          <h3 class="c-field-section__title">Roster</h3>
          <p class="c-field-section__hint"><?= e($programme['ageGroups'] !== [] ? 'Add, update, or remove members of this age group.' : 'Add, update, or remove members.') ?></p>
        </div>
        <button type="button" class="c-btn c-btn--ghost c-btn--small" data-repeat-add="<?= e($teamRows) ?>"><?= icon('plus', 13) ?>Add Member</button>
      </div>
      <div class="c-team-pop-up__rows" id="<?= e($teamRows) ?>">
        <?php foreach ($team['roster'] ?? [] as $rowIndex => $member): ?>
          <?php
          $rosterRowId = "{$teamRows}-{$rowIndex}";
          require __DIR__ . '/_roster_row.php';
          ?>
        <?php endforeach; ?>
      </div>
      <p class="c-team-pop-up__empty">No members yet. This team can be saved empty.</p>
      <template id="<?= e("{$teamRows}-row") ?>">
        <?php
        $member      = null;
        $rosterRowId = "{$teamRows}-new";
        require __DIR__ . '/_roster_row.php';
        ?>
      </template>
    </section>
  </div>

  <div class="c-pop-up__footer">
    <?php if ($team !== null): ?>
      <button type="submit" class="c-btn c-btn--danger c-btn--small c-team-pop-up__delete" form="<?= e("{$teamId}-delete") ?>"><?= icon('trash', 14) ?>Delete Team</button>
    <?php endif; ?>
    <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--solid"><?= $team === null ? 'Create Team' : 'Save Changes' ?></button>
  </div>
</form>
<?php if ($team !== null): ?>
  <form id="<?= e("{$teamId}-delete") ?>" method="post" action="<?= e("/{$currentRole}/extracurricular/team-delete") ?>"
        data-question-title="<?= e("Delete \"{$teamName}\"?") ?>" data-question-button="Delete Team"
        data-question-text="Its members go back to the unassigned list; their places stay in the history. This can't be undone.">
    <?= $csrfField ?>
    <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
    <input type="hidden" name="team" value="<?= e($team['id']) ?>">
  </form>
<?php endif; ?>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = $teamId;
$popUpSize        = 'wide';
$popUpIcon        = 'users';
$popUpEyebrow     = 'Team & Roster';
$popUpTitle       = $team === null ? 'Add Team' : "Edit {$teamName}";
$popUpDescription = $team === null ? 'Create a new team panel and assign initial members.' : 'Update this team, its roster, and cover image.';
require __DIR__ . '/_pop_up.php';
unset($teamId, $teamRows, $teamName, $member, $rowIndex, $rosterRowId);
