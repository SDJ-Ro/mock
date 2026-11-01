<?php
// One student, for a teacher: their photo, name and ID, with buttons.
// Its look: student-card.css. Used by teacher/achievements ('grid', 'banner') and teacher/term_marks ('marks').
// Set first:
//   $studentCard        ['name', 'code', 'photo' (or ''), 'tone' (slate · steel · taupe · sand · seafoam · sage);
//                        'grid' also reads 'from' ('Class' | 'Extracurricular') and 'issue' (a reported missing record)]
//   $studentCardVariant 'grid'   a coloured card in the grid: View (opens their timeline) + Record
//                       'marks'  the same card with one "Edit Marks" button, which opens $studentCardTarget
//                                (the id of their marks panel, term-marks-panel.js)
//                       'banner' the top of their timeline page: FINALISED, View Issue (if any) + Record Achievement;
//                                $studentCardContent (HTML built with ob_start(), e.g. the timeline) is drawn under
//                                it, in the same white card
// Record opens the Record pop-up with this student (record-achievement.js reads the data-* below); the search box
// (student-cards.js) reads data-name, data-code and data-from.
$card      = $studentCard;
$cardIssue = $studentCardVariant === 'grid' && $card['issue'];   // not "$issue": the page has its own (the Review Issue pop-up)
$initials  = initials($card['name']);
?>
<?php if ($studentCardVariant !== 'banner'): ?>
  <article class="c-student-card c-student-card--<?= e($card['tone']) ?><?= $cardIssue ? ' c-student-card--issue' : '' ?> j-student-card"
           data-name="<?= e($card['name']) ?>" data-code="<?= e($card['code']) ?>" data-from="<?= e($card['from'] ?? '') ?>" data-initials="<?= e($initials) ?>">
    <?php if ($card['photo'] !== ''): ?>
      <img class="c-avatar c-avatar--large c-student-card__avatar" src="<?= e($card['photo']) ?>" alt="<?= e($card['name']) ?>">
    <?php else: ?>
      <span class="c-avatar c-avatar--large c-student-card__avatar c-student-card__avatar--initials" aria-hidden="true"><?= e($initials) ?></span>
    <?php endif; ?>

    <h2 class="c-student-card__name"><?= e($card['name']) ?></h2>
    <p class="c-student-card__code"><?= e($card['code']) ?></p>

    <div class="c-student-card__actions">
      <?php if ($studentCardVariant === 'marks'): ?>
        <button type="button" class="c-student-card__button c-student-card__button--view c-student-card__button--edit j-marks-open"
                data-panel="<?= e($studentCardTarget) ?>" aria-controls="<?= e($studentCardTarget) ?>" aria-expanded="false">
          <?= icon('edit', 14) ?><span>Edit Marks</span>
        </button>
      <?php else: ?>
        <a class="c-student-card__button c-student-card__button--view" href="/teacher/achievements?student=<?= e(rawurlencode($card['code'])) ?>">
          <?= icon('eye', 14) ?><span>View</span>
        </a>
        <button type="button" class="c-student-card__button c-student-card__button--record j-record-open">
          <?= icon('edit', 14) ?><span>Record</span>
        </button>
      <?php endif; ?>
    </div>
  </article>
<?php else: ?>
  <article class="c-student-card c-student-card--banner j-student-card"
           data-name="<?= e($card['name']) ?>" data-code="<?= e($card['code']) ?>" data-from="<?= e($card['from']) ?>" data-initials="<?= e($initials) ?>">
    <header class="c-student-card__banner">
      <div class="c-student-card__who">
        <?php if ($card['photo'] !== ''): ?>
          <img class="c-avatar c-avatar--medium c-student-card__avatar" src="<?= e($card['photo']) ?>" alt="">
        <?php else: ?>
          <span class="c-avatar c-avatar--medium c-student-card__avatar c-student-card__avatar--initials" aria-hidden="true"><?= e($initials) ?></span>
        <?php endif; ?>
        <div>
          <h2 class="c-student-card__name">
            <?= e($card['name']) ?>
            <span class="c-tag c-tag--seaglass">Finalised</span>
          </h2>
          <p class="c-student-card__code"><?= e($card['code']) ?></p>
        </div>
      </div>

      <div class="c-student-card__actions">
        <?php if ($card['issue']): ?>
          <button type="button" class="c-student-card__button c-student-card__button--issue" data-pop-up-open="j-review-issue">
            <?= icon('alertTriangle', 14) ?><span>View Issue</span>
          </button>
        <?php endif; ?>
        <button type="button" class="c-student-card__button c-student-card__button--record j-record-open">
          <?= icon('edit', 14) ?><span>Record Achievement</span>
        </button>
      </div>
    </header>

    <div class="c-student-card__body">
      <?= $studentCardContent ?>
    </div>
  </article>
<?php endif; ?>
<?php unset($card, $cardIssue, $initials, $studentCard, $studentCardVariant, $studentCardContent, $studentCardTarget); ?>
