<?php
// The People directory page (the users directory and the teacher's Student Details). Its look: people-directory.css;
// the table: _people_table.php. The filters are a normal GET form: picking one reloads the page
// (people-directory.js sends it when a dropdown changes); tabs, class chips and page links carry the other filters.
// Set before requiring:
//   $directoryView 'office' (admin, management: role tabs, a tinted toolbar per role, status) · 'teacher' (their students)
// Data: PeopleController::index() / students(): $rows, $lists, $total, $page, $pages, $filters, $query, $pageUrl;
// office also $tabs, $tab, $grades, $grade, $class, $extracurricularChoices, $subjectChoices. From the frame: $csrfField
// (and the sliding banner: a handover's or an Add's message; status changes and saves report there from the script).
$directoryRole = $directoryView === 'office' ? $tab : 'student';
$directoryTone = config("people.tones.{$directoryRole}");
$directoryLink = fn (array $change): string => $pageUrl . '?' . http_build_query(array_filter($change + $query, fn (mixed $value): bool => $value !== null && $value !== ''));
$directoryWord = $directoryRole === 'management' ? ($total === 1 ? 'staff member' : 'staff members') : strtolower($total === 1 ? word($directoryRole) : plural($directoryRole));

// All / Active / Deactivated: links (they keep the other filters); the students tab shows them in its second row
ob_start();
?>
<nav class="c-tabs c-tabs--grey" aria-label="Account access">
  <?php foreach (['' => 'All', 'on' => 'Active', 'off' => 'Deactivated'] as $statusValue => $statusLabel): ?>
    <a class="c-tabs__tab" href="<?= e($directoryLink(['status' => $statusValue])) ?>" aria-selected="<?= $statusValue === $filters['status'] ? 'true' : 'false' ?>"><?= e($statusLabel) ?></a>
  <?php endforeach; ?>
</nav>
<?php
$statusTabsContent = ob_get_clean();

ob_start();
?>
<?php if ($directoryView === 'office'): ?>
  <nav class="c-tabs c-tabs--roles" aria-label="Directory">
    <?php foreach ($tabs as $tabRole => $tabLabel): ?>
      <a class="c-tabs__tab c-tabs__tab--<?= e(config("people.tones.{$tabRole}")) ?>" href="<?= e("{$pageUrl}?tab={$tabRole}") ?>"
         aria-selected="<?= $tabRole === $tab ? 'true' : 'false' ?>"><?= e($tabLabel) ?></a>
    <?php endforeach; ?>
  </nav>
<?php else: ?>
  <form class="c-people__header-search" method="get" action="<?= e($pageUrl) ?>" role="search">
    <label class="c-search">
      <?= icon('search', 16) ?>
      <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(words('Search {students}…')) ?>" aria-label="<?= e(words('Search {students}')) ?>">
    </label>
  </form>
<?php endif; ?>
<?php
$headerContent = ob_get_clean();
$title         = $directoryView === 'office' ? 'Users Directory' : words('{Student} Details');
$subtitle      = $directoryView === 'office' ? words('Find {students}, staff, and families by the details that matter.') : words('Find {students} and {parents} by the details that matter.');
require __DIR__ . '/_page_header.php';
?>

<?php // (office: a 52px gap under the header, people-grid.css; the teacher's card sits right under it) ?>
<section class="c-card<?= $directoryView === 'office' ? ' l-people-grid' : '' ?>">
  <?php if ($directoryView === 'office'): ?>
    <form class="j-people-filters" method="get" action="<?= e($pageUrl) ?>" data-submit-on-change>
      <input type="hidden" name="tab" value="<?= e($tab) ?>">
      <input type="hidden" name="status" value="<?= e($filters['status']) ?>">

      <div class="c-people__toolbar c-people__toolbar--<?= e($directoryTone) ?>">
        <div class="c-people__controls">
          <?php if ($tab === 'student'): ?>
            <input type="hidden" name="class" value="<?= e($filters['class_id'] ?? '') ?>">
            <?php
            $dropdownId    = 'j-people-grade';
            $dropdownLabel = word('grade');
            $options       = array_map(fn (array $g): array => ['value' => (string) $g['id'], 'label' => grade_name($g['number'])], $grades);
            $selectedValue = (string) ($grade['id'] ?? '');
            $name          = 'grade';
            $prefix        = word('grade');
            $variant       = 'toolbar';
            require __DIR__ . '/_dropdown.php';
            ?>
            <div class="c-people__chips">
              <a class="c-people__chip" href="<?= e($directoryLink(['class' => ''])) ?>" aria-current="<?= $class === null ? 'true' : 'false' ?>">All</a>
              <?php foreach ($grade['classes'] ?? [] as $gradeClass): ?>
                <a class="c-people__chip" href="<?= e($directoryLink(['class' => $gradeClass['id']])) ?>"
                   aria-current="<?= $gradeClass['id'] === ($class['id'] ?? null) ? 'true' : 'false' ?>"><?= e($gradeClass['name']) ?></a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <label class="c-search c-search--small">
              <?= icon('search', 14) ?>
              <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="Search <?= e($tab === 'management' ? 'staff' : strtolower($tabs[$tab])) ?>…" aria-label="Search">
            </label>
            <?php if ($tab === 'teacher' || $tab === 'parent'): ?>
              <?php
              $dropdownId    = 'j-people-' . ($tab === 'teacher' ? 'subject' : 'relationship');
              $dropdownLabel = $tab === 'teacher' ? word('subject') : 'Relationship';
              $options       = [['value' => '', 'label' => 'All'], ...array_map(fn (string $c): array => ['value' => $c, 'label' => $c], $tab === 'teacher' ? $subjectChoices : config('people.relationships'))];
              $selectedValue = $tab === 'teacher' ? $filters['subject'] : $filters['relationship'];
              $name          = $tab === 'teacher' ? 'subject' : 'relationship';
              $prefix        = $dropdownLabel;
              $variant       = 'toolbar';
              require __DIR__ . '/_dropdown.php';
              ?>
            <?php endif; ?>
            <?= $statusTabsContent ?>
          <?php endif; ?>
        </div>
        <a class="c-btn c-btn--<?= e($directoryTone) ?>" href="<?= e("{$pageUrl}/add?tab={$tab}") ?>"><?= icon('userPlus', 16) ?>Add <?= e($tab === 'management' ? 'Staff' : word($tab)) ?></a>
      </div>

      <?php if ($tab === 'student'): ?>
        <div class="c-people__context">
          <div class="c-people__context-row">
            <h2 class="c-people__context-title"><?= e($class !== null ? word('class') . " {$class['name']}" : grade_name($grade['number'] ?? 0)) ?></h2>
            <div class="c-people__controls">
              <label class="c-search c-search--small">
                <?= icon('search', 14) ?>
                <input type="search" class="c-search__input" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(words('Search {students}…')) ?>" aria-label="<?= e(words('Search {students}')) ?>">
              </label>
              <?php
              $dropdownId    = 'j-people-extracurricular';
              $dropdownLabel = 'Activity';
              $options       = [['value' => '', 'label' => 'All Activities'], ...array_map(fn (int $id, string $n): array => ['value' => (string) $id, 'label' => $n], array_keys($extracurricularChoices), $extracurricularChoices)];
              $selectedValue = (string) ($filters['extracurricular_id'] ?? '');
              $name          = 'extracurricular';
              $prefix        = 'Activity';
              $variant       = 'toolbar';
              require __DIR__ . '/_dropdown.php';
              ?>
              <?= $statusTabsContent ?>
            </div>
          </div>

          <div class="c-people__context-cards">
            <?php
            $infoCardForm    = '';
            $infoCardVariant = 'compact';
            $students        = $class['students'] ?? $grade['students'] ?? 0;
            $contextCards    = [
                ['label' => 'Enrollment', 'icon' => null, 'value' => $students . ' ' . strtolower($students === 1 ? word('student') : plural('student')), 'edit' => null, 'raw' => '', 'wide' => false],
                $class !== null
                    ? ['label' => words('{Class} {Teacher}'), 'icon' => null, 'value' => $class['class_teacher'] ?? 'Assignment pending', 'edit' => null, 'raw' => '', 'wide' => false]
                    : ['label' => plural('class'), 'icon' => null, 'value' => (string) count($grade['classes'] ?? []), 'edit' => null, 'raw' => '', 'wide' => false],
            ];
            foreach ($contextCards as $card)
            {
                require __DIR__ . '/_info_card.php';
            }
            ?>
          </div>
        </div>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <?php if ($directoryView === 'office'): // the teacher's page has no count ?>
    <p class="c-people__found"><?= e("{$total} {$directoryWord} found") ?></p>
  <?php endif; ?>

  <?php if ($rows === []): ?>
    <div class="c-empty-box c-people__empty">
      <p class="c-empty-box__text"><?= e($query === ['tab' => $directoryRole] || $query === [] ? "No {$directoryWord} yet." : "No {$directoryWord} match your search or filters.") ?></p>
    </div>
  <?php else: ?>
    <?php
    $tableRole = $directoryRole;
    $tableView = $directoryView;
    require __DIR__ . '/_people_table.php';
    ?>
  <?php endif; ?>

  <?php
  $pagerPage  = $page;
  $pagerPages = $pages;
  $pagerLink  = fn (int $target): string => $directoryLink(['page' => $target]);
  $pagerRuled = true;
  require __DIR__ . '/_pager.php';
  ?>
</section>

<?php
require __DIR__ . '/_person_profile_pop_up.php';

// The teacher handover: empty until switching off a teacher with duties fills it (PeopleController::handoverForm())
if ($directoryView === 'office')
{
    $popUpContent = '<div id="j-teacher-handover-content"></div>';
    $popUpId      = 'j-teacher-handover';
    $popUpIcon    = 'usersRound';
    $popUpEyebrow = 'Handover';
    $popUpTitle   = words('Switch Off {Teacher}');
    $popUpSize    = 'wide';
    require __DIR__ . '/_pop_up.php';
}
?>
<?php unset($directoryRole, $directoryTone, $directoryLink, $directoryWord, $statusTabsContent, $tabRole, $tabLabel, $gradeClass, $statusValue, $statusLabel,
    $students, $contextCards, $card, $infoCardForm, $infoCardVariant, $directoryView); ?>
