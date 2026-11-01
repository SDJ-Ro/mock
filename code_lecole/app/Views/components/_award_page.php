<?php
// One achievement of a programme on its own page (drawn by the server): Back, the chips (place, level, year), title, date · venue, the summary,
// "Event information", the participants, the gallery, and (who may) the Danger Zone.
// $editing (Edit Achievement, or Add Achievement: award=new) turns the same page into a form sent to
// ExtracurricularController::saveAward(): the words become boxes, a cover picker appears, participants (members picked
// from a list, + other names typed in) and photos can be added and removed (repeat-list.js, image-picker.js). While
// editing, leaving the page asks first (data-warn-unsaved, unsaved-changes.js). Its look: award-page.css.
// From the controller: $award (Extracurriculars::award()), $editing, $can, $programme, $programmeUrl, $awardUrl.
// From the page: $csrfField, $currentRole.
$awardNew    = $award['id'] === 'new';
$awardDate   = $award['date'] !== '' ? date(country('date_format'), strtotime($award['date'])) : '';
$awardOthers = array_values(array_filter(explode(', ', $award['others'])));
$awardFacts  = [   // [field (Extracurriculars::award()), label, the form's name] — level, age group and who it represents are lists
    [['tournament', 'Tournament / event', 'tournament'], ['date', 'Date', ''], ['venue', 'Venue', ''], ['level', 'Scope / level', 'level'],
     ['ageGroup', 'Age Group', 'age_group'], ['representing', 'Representing (Team / Individual)', 'representing']],
    [['organisedBy', 'Organised by', 'organiser'], ['place', 'Position / result', 'place'], ['medal', 'Award / medal', 'medal']],
];
$awardPick   = [
    'level'        => country('achievement_levels'),
    'ageGroup'     => $programme['ageGroups'],   // [] = the programme doesn't use age groups: no age group box
    'representing' => array_values(array_unique([...array_column($programme['teams'], 'name'), ...($award['representing'] !== '' ? [$award['representing']] : []), 'Individual'])),
];
if ($programme['ageGroups'] === [] && $award['ageGroup'] === '')
{
    $awardFacts[0] = array_values(array_filter($awardFacts[0], fn (array $fact): bool => $fact[0] !== 'ageGroup'));
}
?>
<div class="c-award-bar">
  <a class="c-btn c-btn--back" href="<?= e($programmeUrl) ?>"><?= icon('arrowLeft', 16) ?><span>Back</span></a>
  <?php if ($editing): ?>
    <div class="c-award-bar__actions">
      <a class="c-btn c-btn--ghost" href="<?= e($awardNew ? $programmeUrl : $awardUrl . $award['id']) ?>">Cancel</a>
      <button type="submit" class="c-btn c-btn--solid" form="j-award-form"><?= icon('check', 14) ?><?= $awardNew ? 'Save Achievement' : 'Save Changes' ?></button>
    </div>
  <?php elseif ($can['edit']): ?>
    <a class="c-btn c-btn--sky c-btn--small" href="<?= e("{$awardUrl}{$award['id']}&edit=1") ?>"><?= icon('edit', 14) ?>Edit Achievement</a>
  <?php endif; ?>
</div>

<?php if ($editing): ?>
<form class="c-card c-award" id="j-award-form" method="post" action="<?= e("/{$currentRole}/extracurricular/award") ?>" enctype="multipart/form-data" data-warn-unsaved>
  <?= $csrfField ?>
  <input type="hidden" name="id" value="<?= e($programme['id']) ?>">
  <input type="hidden" name="award" value="<?= e($awardNew ? '' : $award['id']) ?>">
<?php else: ?>
<article class="c-card c-award">
<?php endif; ?>
  <header class="c-award__hero">
    <?php if (!$awardNew): ?>
      <div class="c-award__chips">
        <?php if ($award['place'] !== ''): ?>
          <span class="c-tag c-tag--round c-tag--sunshine"><?= e($award['place']) ?></span>
        <?php endif; ?>
        <span class="c-tag c-tag--round c-tag--sky"><?= e($award['level']) ?></span>
        <span class="c-tag c-tag--round c-award__year"><?= e($award['year']) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($editing): ?>
      <input type="text" class="c-field__input c-award__title-input" name="title" value="<?= e($award['title']) ?>" placeholder="Achievement title" aria-label="Achievement title" maxlength="150" required>
      <div class="c-award__meta">
        <?= icon('calendar', 16) ?><input type="date" class="c-field__input c-field__input--small c-award__meta-box" name="date" value="<?= e($award['date']) ?>" max="<?= e(date('Y-m-d')) ?>" aria-label="Date" required>
        <?= icon('mapPin', 16) ?><input type="text" class="c-field__input c-field__input--small c-award__meta-box" name="venue" value="<?= e($award['venue']) ?>" placeholder="Venue" aria-label="Venue" maxlength="150">
      </div>
    <?php else: ?>
      <h1 class="c-award__title"><?= e($award['title']) ?></h1>
      <p class="c-award__meta"><?= icon('calendar', 16) ?><span><?= e($awardDate) ?></span><span aria-hidden="true">·</span><?= icon('mapPin', 16) ?><span><?= e($award['venue'] !== '' ? $award['venue'] : '—') ?></span></p>
    <?php endif; ?>
  </header>

  <div class="c-award__body">
    <?php if ($editing): ?>
      <section>
        <h2 class="c-award__heading"><?= icon('imagePlus', 18) ?>Cover photo</h2>
        <?php
        $imagePicker = ['variant' => 'row', 'name' => 'cover', 'image' => $award['image'], 'label' => 'Choose Cover Photo'];
        require __DIR__ . '/_image_picker.php';
        ?>
      </section>
      <label class="c-field">
        <span class="c-label c-field__label">Achievement Summary / Bio</span>
        <textarea class="c-field__input c-field__input--area" name="summary" rows="3" maxlength="600" placeholder="What happened, and why it matters…"><?= e($award['summary']) ?></textarea>
      </label>
    <?php elseif ($award['summary'] !== ''): ?>
      <p class="c-award__summary"><?= e($award['summary']) ?></p>
    <?php endif; ?>

    <section>
      <h2 class="c-award__heading"><?= icon('trophy', 18) ?>Event information</h2>
      <div class="c-award__facts">
        <?php foreach ($awardFacts as $factColumn): ?>
          <ul class="c-award__fact-list">
            <?php foreach ($factColumn as [$factField, $factLabel, $factName]): ?>
              <li class="c-award__fact">
                <div class="c-award__fact-text">
                  <p class="c-label"><?= e($factLabel) ?></p>
                  <?php if (!$editing): ?>
                    <?php $factValue = $factField === 'date' ? $awardDate : $award[$factField]; ?>
                    <p class="c-award__fact-value"><?= e($factValue !== '' ? $factValue : '—') ?></p>
                  <?php elseif (isset($awardPick[$factField])): ?>
                    <?php
                    $dropdownId    = "j-award-{$factField}";
                    $dropdownLabel = $factLabel;
                    $name          = $factName;
                    $options       = $factField === 'ageGroup' ? [['value' => '', 'label' => 'None'], ...$awardPick[$factField]] : $awardPick[$factField];
                    $selectedValue = $award[$factField];
                    $placeholder   = 'Select…';
                    $required      = $factField === 'ageGroup' ? '' : "Please choose the {$factLabel}.";
                    require __DIR__ . '/_dropdown.php';
                    ?>
                  <?php elseif ($factName !== ''): ?>
                    <input type="text" class="c-field__input c-field__input--small" name="<?= e($factName) ?>" value="<?= e($award[$factField]) ?>" aria-label="<?= e($factLabel) ?>" maxlength="150">
                  <?php else: ?>
                    <p class="c-award__fact-value"><?= e(($factField === 'date' ? $awardDate : $award[$factField]) ?: 'Set above') ?></p>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="c-award__people">
      <div class="c-award__people-head">
        <h2 class="c-award__heading c-award__heading--flat"><?= icon('users', 18) ?>Participants</h2>
        <span class="c-chip c-chip--round c-award__count"><?= e(count($award['participants']) + count($awardOthers)) ?></span>
      </div>
      <?php if ($editing): ?>
        <div class="c-award__people-add">
          <?php
          $dropdownId    = 'j-award-person';
          $dropdownLabel = 'Choose a member';
          $options       = array_map(fn (array $member): array => ['value' => $member['id'], 'label' => $member['name']], $programme['members']);
          $selectedValue = null;
          $placeholder   = 'Choose a member, then Add';
          require __DIR__ . '/_dropdown.php';
          ?>
          <button type="button" class="c-btn c-btn--sky c-btn--small" data-repeat-add="j-award-people" data-repeat-from="j-award-person">Add</button>
        </div>
      <?php endif; ?>
      <ul class="c-award__people-list" id="j-award-people">
        <?php foreach ($award['participants'] as $person): ?>
          <li class="c-award__person" data-repeat-row>
            <span><?= e($person['name']) ?></span>
            <?php if ($editing): ?>
              <input type="hidden" name="participant[]" value="<?= e($person['id']) ?>" data-repeat-value>
              <button type="button" class="c-award__remove" data-repeat-remove aria-label="<?= e("Remove {$person['name']}") ?>"><?= icon('close', 14) ?></button>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        <?php if (!$editing): ?>
          <?php foreach ($awardOthers as $person): ?>
            <li class="c-award__person"><span><?= e($person) ?></span></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
      <?php if ($editing): ?>
        <template id="j-award-people-row">
          <li class="c-award__person" data-repeat-row>
            <span data-repeat-text></span>
            <input type="hidden" name="participant[]" data-repeat-value>
            <button type="button" class="c-award__remove" data-repeat-remove aria-label="Remove participant"><?= icon('close', 14) ?></button>
          </li>
        </template>
        <label class="c-field c-award__others">
          <span class="c-label c-field__label">Other Names (not in the portal)</span>
          <input type="text" class="c-field__input c-field__input--small" name="others" value="<?= e($award['others']) ?>" maxlength="255"
                 placeholder="e.g. Kavindu Perera, Sanjula Silva">
          <span class="c-field__hint">Shown here only. Members chosen above also see it on their own timeline.</span>
        </label>
      <?php endif; ?>
    </section>

    <?php if ($editing || $award['gallery'] !== []): ?>
      <section>
        <h2 class="c-award__heading"><?= icon('imagePlus', 18) ?>Event gallery</h2>
        <div class="c-award__gallery" id="j-award-gallery">
          <?php foreach ($award['gallery'] as $photo): ?>
            <div class="c-award__photo" data-repeat-row>
              <img src="<?= e($photo['url']) ?>" alt="" loading="lazy">
              <?php if ($editing): ?>
                <input type="hidden" name="keep_photo[]" value="<?= e($photo['id']) ?>">
                <button type="button" class="c-award__photo-remove" data-repeat-remove aria-label="Remove photo"><?= icon('close', 14) ?></button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <?php if ($editing): ?>
            <label class="c-award__photo-add">
              <?= icon('imagePlus', 22) ?><span class="c-label">Add Photos</span>
              <input type="file" class="c-field__hidden-input" name="gallery[]" accept="image/png,image/jpeg,image/webp" multiple data-gallery-add="j-award-gallery">
            </label>
          <?php endif; ?>
        </div>
        <?php if ($editing): ?>
          <p class="c-field__hint"><?= e('At most ' . config('extracurricular.gallery_photos') . ' photos, ' . round(config('extracurricular.cover_kb') / 1024) . ' MB each (JPG, PNG or WebP).') ?></p>
          <template id="j-award-gallery-photo">
            <div class="c-award__photo" data-gallery-photo>
              <img src="" alt="">
              <button type="button" class="c-award__photo-remove" data-gallery-remove aria-label="Remove photo"><?= icon('close', 14) ?></button>
            </div>
          </template>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php // Only while viewing: its form can't sit inside the edit form ?>
    <?php if ($can['deleteAward'] && !$editing): ?>
      <?php
      $dangerZone = ['text' => "Permanently delete this achievement card from the records of {$programme['name']}, and from its participants' timelines. This can't be undone.",
                     'button' => 'Delete Achievement', 'question' => "Delete \"{$award['title']}\"?",
                     'action' => "/{$currentRole}/extracurricular/award-delete", 'hidden' => ['id' => $programme['id'], 'award' => $award['id']]];
      require __DIR__ . '/_danger_zone.php';
      ?>
    <?php endif; ?>
  </div>
<?php if ($editing): ?>
</form>
<?php else: ?>
</article>
<?php endif; ?>
<?php unset($awardNew, $awardDate, $awardOthers, $awardFacts, $awardPick, $factColumn, $factField, $factLabel, $factName, $factValue, $person, $photo); ?>
