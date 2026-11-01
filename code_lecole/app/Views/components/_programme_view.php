<?php
// One programme for staff (the teacher, management and admin), in one
// column: the hero (Back to the list except on the teacher's page), Achievements & Gallery, the teacher's Enrollment
// Processing, Schedule & Events (calendar + staff), the club's Notice Board, Teams & Roster, and the admin's
// Danger Zone (Archive). Or one of its achievements (_award_page.php). Layout: extracurricular-grid.css.
// From the controller: $programme, $award, $can, $listUrl (null: no Back) (and what the parts read).
?>
<div class="l-programme">
  <?php if ($award !== null): ?>
    <?php require __DIR__ . '/_award_page.php'; ?>
  <?php else: ?>
    <?php
    $heroBack = $listUrl;   // null on a teacher's page with one programme
    require __DIR__ . '/_extracurricular_hero.php';
    ?>
    <?php if ($programme['archived']): ?>
      <div class="c-banner c-banner--info">
        <?= icon('info', 20) ?>
        <span>This programme is archived: read only. The admin can restore it from the Archived tab.</span>
      </div>
    <?php endif; ?>
    <?php
    if ($programme['awards'] !== null)
    {
        require __DIR__ . '/_extracurricular_awards_panel.php';   // not when the achievements module is off
    }
    if ($can['edit'])
    {
        require __DIR__ . '/_enrolment_panel.php';   // the teacher in charge, or management / admin (a programme may have no teacher yet)
    }
    $scheduleSidebar = true;
    require __DIR__ . '/_extracurricular_schedule_panel.php';
    if ($programme['noticeCards'] !== null)
    {
        require __DIR__ . '/_club_notice_board.php';
    }
    require __DIR__ . '/_teams_panel.php';
    if ($can['archive'] && !$programme['archived'])
    {
        $dangerZone = ['text' => 'Archive this programme: it is hidden from ' . strtolower(plural('student')) . ' and from joining, its members\' '
                           . 'memberships end today, and its history (members, teams, achievements) is kept. The admin can restore it.',
                       'button' => 'Archive Programme', 'question' => "Archive \"{$programme['name']}\"?",
                       'action' => "/{$currentRole}/extracurricular/archive", 'hidden' => ['id' => $programme['id']]];
        require __DIR__ . '/_danger_zone.php';
    }
    ?>
  <?php endif; ?>
</div>
