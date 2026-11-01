<?php
// One person's profile, inside People's pop-up: their header, then tabs (information, activities…). Drawn by PeopleController::profile() and sent as JSON; people-profile.js puts it in the
// page's pop-up, switches the tabs and Edit Profile on / off. Its look: person-profile.css (cards: info-card.css; tabs: tabs.css).
//   $id       the person's account id          $saveUrl  where Edit Profile saves, or null (view only: a teacher's view)
//   $header   ['role', 'tone', 'name', 'photo' (or null: their initials), 'eyebrow', 'sub', 'code', 'status' => [label, tag tone]]
//             (drawn by _person_header.php — also the parent's Child profile header)
//   $sections [['title', 'icon', 'columns' (2 or 4), 'cards' (for _info_card, with their edit boxes)], …]
//             (drawn by _person_sections.php — also the Child profile's Information tab)
//   $student  null, or ['tabs' => [key => name], 'book' (RecordBook::shown()), 'memberships', 'timeline'
//             (AchievementTimeline::byYear())] — a student gets up to four tabs: Information · Academics (the record
//             book only) · Extracurriculars · Achievements (the timeline, like the student's own page). A tab whose
//             module is off is left out (its data is null); with only Information left there is no tab bar.
// The information is one form: its boxes show while the pop-up's content has data-editing="true" (people-profile.js).
$membershipStatus = ['member' => 'Active', 'parent' => words('Waiting for the {parent}'), 'teacher' => words('Waiting for the {teacher}'), 'declined' => 'Declined'];
?>
<?php
$headerOnPage = false;
require __DIR__ . '/_person_header.php';
?>

<div class="c-person__body">
  <?php if (($student['watch'] ?? null) !== null): ?>
    <?php $watch = $student['watch']; ?>
    <div class="c-banner c-banner--warning c-person__watch">
      <?= icon('alertCircle', 20) ?>
      <div class="c-person__watch-body">
        <?php if ($watch['locked']): ?>
          <span><strong>Academic Watch</strong> · <?= e("{$watch['low']} " . strtolower(plural('subject')) . ' under ' . rule('pass_mark') . ' in ' . word('term') . " {$watch['term']}, {$watch['year']}") ?> · can't join new clubs or sports.</span>
        <?php else: ?>
          <span><?= e("Lock lifted by {$watch['lifted']['lifted_by_name']} on " . date(country('date_format'), strtotime($watch['lifted']['lifted_at'])) . ": \"{$watch['lifted']['reason']}\", until the next published " . strtolower(word('term')) . '.') ?></span>
        <?php endif; ?>
        <form class="c-person__watch-form" method="post" action="<?= e($watch['url']) ?>">
          <?= $csrfField ?>
          <input type="hidden" name="student" value="<?= e($id) ?>">
          <?php if ($watch['locked']): ?>
            <input type="hidden" name="action" value="lift">
            <input type="text" class="c-field__input c-field__input--small" name="reason" maxlength="255" required placeholder="Why it is lifted…" aria-label="Why the lock is lifted">
            <button type="submit" class="c-btn c-btn--dark c-btn--small">Lift Lock</button>
          <?php else: ?>
            <input type="hidden" name="action" value="lock">
            <button type="submit" class="c-btn c-btn--dark c-btn--small">Lock Again</button>
          <?php endif; ?>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($student !== null && count($student['tabs']) > 1): ?>
    <div class="c-tabs c-tabs--sub" role="tablist" aria-label="Profile">
      <?php foreach ($student['tabs'] as $panel => $panelLabel): ?>
        <button type="button" class="c-tabs__tab j-person-tab" role="tab" data-panel="<?= e($panel) ?>" aria-selected="<?= $panel === 'information' ? 'true' : 'false' ?>"><?= e($panelLabel) ?></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form class="c-person__panel" data-panel="information" id="j-person-form" method="post" action="<?= e($saveUrl ?? '') ?>">
    <?php if ($saveUrl !== null): ?>
      <?= $csrfField ?>
      <input type="hidden" name="id" value="<?= e($id) ?>">
    <?php endif; ?>
    <?php
    $infoCardForm = 'j-person-form';
    require __DIR__ . '/_person_sections.php';
    ?>
  </form>

  <?php if (($student['book'] ?? null) !== null): ?>
    <div class="c-person__panel" data-panel="academics" hidden>
      <?php
      ['records' => $records, 'year' => $year, 'term' => $term, 'exportUrl' => $exportUrl] = $student['book'];   // no Export PDF here
      require __DIR__ . '/_digital_record_book.php';   // only the book (owner): no total card or trend chart here
      ?>
    </div>
  <?php endif; ?>

  <?php if (($student['memberships'] ?? null) !== null): ?>
    <div class="c-person__panel" data-panel="extracurriculars" hidden>
      <h3 class="c-person__section-title"><?= icon('trophy', 16) ?>Extracurricular Involvement</h3>
      <div class="c-person__items">
        <?php foreach ($student['memberships'] as $membership): ?>
          <article class="c-person__item">
            <p class="c-person__item-title"><?= e($membership['name']) ?></p>
            <p class="c-person__item-meta"><?= e(($membership['position'] ?: 'Member') . ' · ' . $membershipStatus[$membership['status']]) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($student['memberships'] === []): ?>
        <p class="c-person__none">No clubs or sports yet.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (($student['timeline'] ?? null) !== null): ?>
    <div class="c-person__panel" data-panel="achievements" hidden>
      <?php
      // The same timeline as the student's own Achievements page (owner), inside the pop-up without its own card
      $timeline        = $student['timeline'];
      $timelineVariant = 'plain';
      require __DIR__ . '/_achievements_timeline.php';
      ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($saveUrl !== null): ?>
  <footer class="c-pop-up__footer c-person__footer">
    <button type="button" class="c-btn c-btn--dark c-person__view-only j-person-edit"><?= icon('edit', 15) ?>Edit Profile</button>
    <button type="button" class="c-btn c-btn--ghost c-person__edit-only j-person-cancel">Cancel</button>
    <button type="submit" class="c-btn c-btn--sky c-person__edit-only" form="j-person-form"><?= icon('check', 15) ?>Save Changes</button>
  </footer>
<?php endif; ?>
<?php unset($membershipStatus, $watch, $panel, $panelLabel, $infoCardForm, $records, $year, $term, $exportUrl, $membership, $timeline); ?>
