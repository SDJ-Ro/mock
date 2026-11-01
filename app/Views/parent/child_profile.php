<?php
// The parent's Child profile: the child's header, then four tabs — links, so each tab has its own address and
// only the chosen one is drawn. Each tab is a piece that already exists: People's information sections, the
// student's Academic section, the student's Extracurricular list / programme page, the student's achievements.
// Which child: the sidebar's "Viewing" (the whole parent portal follows it).
// Data: ChildProfileController::show() → $tabs, $tab, $profile (null: no child linked yet), and the chosen tab's own
// data ($records…, $programmes / $programme…, $metrics + $timeline).
$tabTitle = 'Child Profile';
$styles   = ['page-header', 'panel', 'tabs', 'tag', 'empty-box', 'info-card', 'person-profile',
             'dropdown', 'export-pdf-button', 'record-book', 'academic-section',
             'search-field', 'extracurricular-card', 'extracurricular-hero', 'programme-panel', 'award-card', 'award-page', 'staff-card',
             'squad-roster', 'notice-card', 'calendar/calendar', 'calendar/day-card', 'calendar/day-card-popup',
             'metric-card', 'achievements-timeline'];
$scripts  = ['dropdown', 'export-pdf', 'record-book', 'academic-section', 'calendar/calendar', 'achievements-timeline'];
$grids    = ['child-profile', 'academic', 'filter-bar', 'extracurricular', 'notice', 'metric-cards'];
require dirname(__DIR__) . '/layouts/page_top.php';

$title    = 'Child Profile';
$subtitle = "Welcome back, {$userName}.";
require dirname(__DIR__) . '/components/_page_header.php';
?>
<?php if ($profile === null): ?>
  <div class="c-empty-box">
    <p class="c-empty-box__text"><?= e(words('No {student} is linked to your account yet. Ask the {institution} office to link them.')) ?></p>
  </div>
<?php else: ?>
  <div class="l-child-profile__top">
    <?php
    $header       = $profile['header'];
    $headerOnPage = true;
    require dirname(__DIR__) . '/components/_person_header.php';
    ?>

    <nav class="c-tabs c-tabs--wide" aria-label="Child profile">
      <?php foreach ($tabs as $tabKey => $tabName): ?>
        <a class="c-tabs__tab" href="<?= e(url_with('/parent/child-profile', ['tab' => $tabKey])) ?>" aria-selected="<?= $tab === $tabKey ? 'true' : 'false' ?>"><?= e($tabName) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>

  <?php if ($tab === 'information'): ?>
    <div class="c-card c-person__body">
      <?php
      $sections     = $profile['sections'];
      $infoCardForm = null;   // view only: no edit boxes
      require dirname(__DIR__) . '/components/_person_sections.php';
      ?>
    </div>
  <?php elseif ($tab === 'academics'): ?>
    <?php require dirname(__DIR__) . '/components/_academic_section.php'; ?>
  <?php elseif ($tab === 'clubs'): ?>
    <?php require dirname(__DIR__) . '/components/' . ($programme === null ? '_extracurricular_list.php' : '_student_programme_view.php'); ?>
  <?php else: ?>
    <div class="l-metric-cards-grid l-metric-cards-grid--four l-metric-cards-grid--tight">
      <?php foreach ($metrics as $card): ?>
        <?php require dirname(__DIR__) . '/components/_metric_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <?php
    $timelineVariant = 'panel';
    require dirname(__DIR__) . '/components/_achievements_timeline.php';
    ?>
  <?php endif; ?>
<?php endif; ?>
<?php
unset($header, $tabKey, $tabName, $sections, $infoCardForm, $card, $timelineVariant);
require dirname(__DIR__) . '/layouts/page_bottom.php';
