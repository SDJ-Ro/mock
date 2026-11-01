<?php
// The achievements timeline: "Full Timeline", the filter buttons (All + the 4 groups), then the items by year —
// a coloured dot on a line, the title + its group tag, the month, the description.
// achievements-timeline.js filters in the browser (empty years and the last line follow in CSS).
// Its look: achievements-timeline.css (+ tag.css). Used by student/achievements ('panel': its own white panel),
// teacher/achievements and People's profile pop-up ('plain'); later Child profile. Set first:
//   $timeline         [2025 => [['group', 'title', 'month', 'description'], …], …] (App\Services\AchievementTimeline::byYear())
//   $timelineVariant  'panel' | 'plain'
$timelineGroups = config('achievements.groups');
?>
<section class="c-timeline c-timeline--<?= e($timelineVariant) ?><?= $timelineVariant === 'panel' ? ' c-card' : '' ?> j-timeline" aria-labelledby="j-timeline-heading">
  <h2 class="c-panel__title c-timeline__heading" id="j-timeline-heading">Full Timeline</h2>

  <div class="c-timeline__filters" role="group" aria-label="Show">
    <button type="button" class="c-timeline__filter j-timeline-filter" data-group="all" aria-pressed="true">All</button>
    <?php foreach ($timelineGroups as $key => $group): ?>
      <button type="button" class="c-timeline__filter j-timeline-filter" data-group="<?= e($key) ?>" aria-pressed="false"><?= e($group['label']) ?></button>
    <?php endforeach; ?>
  </div>

  <?php foreach ($timeline as $year => $items): ?>
    <div class="c-timeline__year">
      <p class="c-timeline__year-label"><?= e($year) ?></p>
      <?php foreach ($items as $item): ?>
        <div class="c-timeline__item" data-group="<?= e($item['group']) ?>">
          <span class="c-timeline__marker" aria-hidden="true">
            <span class="c-timeline__dot c-timeline__dot--<?= e($item['group']) ?>"></span>
            <span class="c-timeline__line"></span>
          </span>
          <div class="c-timeline__card">
            <div class="c-timeline__header">
              <h3 class="c-timeline__title"><?= e($item['title']) ?></h3>
              <span class="c-tag c-tag--round c-tag--<?= e($timelineGroups[$item['group']]['tone']) ?>"><?= e($timelineGroups[$item['group']]['label']) ?></span>
            </div>
            <p class="c-timeline__date"><?= e($item['month']) ?></p>
            <?php if ($item['description'] !== ''): ?>
              <p class="c-timeline__description"><?= e($item['description']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <p class="c-timeline__empty j-timeline-empty"<?= $timeline === [] ? '' : ' hidden' ?>>No achievements found in this category.</p>
</section>
<?php unset($timelineGroups, $timelineVariant); ?>
