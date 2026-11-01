<?php
// One programme as a student sees it, in one column: Back to the list, the hero,
// Achievements & Gallery, its two staff, the squad beside the calendar, the club's notices, Details & Status — or one
// of its achievements (_award_page.php). On the student's Extracurricular page and the parent's Child profile (the
// child's Sports & Clubs tab). Layout: extracurricular-grid.css.
// From the controller (ExtracurricularController::programmePage()): $programme, $award, $can, $listUrl, $calendarConfig…
?>
<div class="l-programme">
  <?php if ($award !== null): ?>
    <?php require __DIR__ . '/_award_page.php'; ?>
  <?php else: ?>
    <?php
    $heroBack = $listUrl;
    require __DIR__ . '/_extracurricular_hero.php';
    if ($programme['awards'] !== null)
    {
        require __DIR__ . '/_extracurricular_awards_panel.php';   // not when the achievements module is off
    }
    ?>

    <section class="c-card c-programme-panel">
      <div class="l-pair-grid">
        <?php foreach (['tic', 'coach'] as $cardKey): ?>
          <?php
          $staffCard = $programme['cards'][$cardKey] + ['edit' => null];
          require __DIR__ . '/_staff_card.php';
          ?>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="l-roster-calendar-grid">
      <?php
      require __DIR__ . '/_squad_roster.php';
      $scheduleSidebar = false;
      require __DIR__ . '/_extracurricular_schedule_panel.php';
      ?>
    </div>

    <?php if ($programme['noticeCards'] !== null): ?>
      <?php require __DIR__ . '/_club_notice_board.php'; ?>
    <?php endif; ?>

    <section class="c-card c-programme-panel">
      <div class="l-pair-grid">
        <?php foreach (['details', 'status'] as $cardKey): ?>
          <?php
          $staffCard = $programme['cards'][$cardKey] + ['edit' => null];
          require __DIR__ . '/_staff_card.php';
          ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php unset($cardKey); ?>
