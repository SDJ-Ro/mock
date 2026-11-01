<?php
// One programme on the Extracurricular list: the photo with its pill (Sport / Club,
// Enrolled, Pending Approval, Archived), category and name; the teacher in charge (+ "Change" for the office:
// _pick_menu.php); the coach's phone, or why it's hidden; and what this person can do:
//   a programme not approved yet (--waiting): the admin's Reject (asks why) / Accept, everyone else a greyed "Pending Approval"
//   an archived one (greyed, the admin's Archived tab): Restore
//   a student: View Details (enrolled), Requested (asked already), Interest (ask to join), or why they can't
//              (a greyed button with a lock: "Full (Under 17)", "Girls only", "Academic Watch"… — JoinRules)
//   staff: View Details
// The small line under the teacher in charge: the coach's phone or why it's hidden, a person's no ("Not accepted: …"),
// or "Academic Watch" on a membership while the student is on watch (that card greyed).
// Its look: extracurricular-card.css. Set before requiring: $programmeCard (Extracurriculars::cards()).
// From the list: $can (manage, approve, join), $listPath + $listKeep, $teachers. From the page: $csrfField, $currentRole.
$cardState   = $programmeCard['state'];
$cardType    = config("extracurricular.types.{$programmeCard['type']}");
$cardPill    = match ($cardState)
{
    'waiting'  => ['Pending Approval', 'waiting'],
    'archived' => ['Archived', 'waiting'],
    'enrolled' => ['Enrolled', 'enrolled'],
    default    => [$cardType['pill'], $programmeCard['type']],
};
$cardContact = match (true)   // [icon, words, quiet look?]
{
    $programmeCard['watched']                                  => ['lock', 'Academic Watch: no new clubs or sports for now', true],
    $programmeCard['answer'] !== null                          => ['close', $programmeCard['answer'], true],
    $cardState === 'waiting'                                   => ['lock', 'Contact hidden until program approval', true],
    $programmeCard['whyNot'] !== null || ($can['join'] && $cardState !== 'enrolled') => ['lock', 'Coach contact hidden until enrollment', true],
    default                                                    => ['phone', $programmeCard['phone'] !== '' ? $programmeCard['phone'] : 'No coach contact yet', false],
};
$cardAction  = "/{$currentRole}/extracurricular";
$cardForm    = "j-programme-reject-{$programmeCard['id']}";
?>
<article class="c-programme-card<?= in_array($cardState, ['waiting', 'archived'], true) || $programmeCard['watched'] ? ' c-programme-card--waiting' : '' ?>">
  <div class="c-programme-card__top">
    <img class="c-programme-card__photo" src="<?= e($programmeCard['image']) ?>" alt="" loading="lazy">
    <span class="c-tag c-tag--round c-programme-card__pill c-programme-card__pill--<?= e($cardPill[1]) ?>">
      <?php if ($cardState === 'enrolled'): ?><?= icon('check', 10) ?><?php endif; ?><?= e($cardPill[0]) ?>
    </span>
    <div class="c-programme-card__titles">
      <p class="c-label c-programme-card__category"><?= e($programmeCard['category']) ?></p>
      <h2 class="c-programme-card__name"><?= e($programmeCard['name']) ?></h2>
    </div>
  </div>

  <div class="c-programme-card__bottom">
    <div class="c-programme-card__tic">
      <?php if ($programmeCard['tic']['photo'] !== ''): ?>
        <img class="c-programme-card__tic-photo" src="<?= e($programmeCard['tic']['photo']) ?>" alt="">
      <?php else: ?>
        <span class="c-avatar c-programme-card__tic-photo" aria-hidden="true"><?= $programmeCard['tic']['assigned'] ? e(initials($programmeCard['tic']['name'])) : icon('user', 16) ?></span>
      <?php endif; ?>
      <div class="c-programme-card__tic-text">
        <p class="c-label c-programme-card__tic-label"><?= e(words('{Teacher} in Charge')) ?></p>
        <p class="c-programme-card__tic-name"><?= e($programmeCard['tic']['name']) ?></p>
      </div>
      <?php if ($can['manage'] && $cardState !== 'archived'): ?>
        <?php
        $pickMenu = ['variant' => 'change', 'label' => 'Change', 'title' => words('Change {Teacher} in Charge'), 'options' => $teachers,
                     'chosen' => (string) ($programmeCard['ticId'] ?? ''), 'action' => "{$cardAction}/save", 'field' => 'teacher',
                     'hidden' => ['id' => $programmeCard['id'], 'part' => 'teacher', 'back' => 'list']];
        require __DIR__ . '/_pick_menu.php';
        ?>
      <?php endif; ?>
    </div>

    <p class="c-programme-card__contact<?= $cardContact[2] ? ' c-programme-card__contact--locked' : '' ?>">
      <?= icon($cardContact[0], $cardContact[2] ? 12 : 13) ?><span><?= e($cardContact[1]) ?></span>
    </p>

    <div class="c-programme-card__actions">
      <?php if ($cardState === 'waiting' && $can['approve']): ?>
        <form id="<?= e($cardForm) ?>" method="post" action="<?= e("{$cardAction}/review") ?>"
              data-question-title="Reject Programme?" data-question-button="Reject Programme" data-question-icon="alertCircle"
              data-question-extra="j-reason" data-question-text="<?= e("\"{$programmeCard['name']}\" is removed, and your reason is kept in the audit log.") ?>">
          <?= $csrfField ?>
          <input type="hidden" name="id" value="<?= e($programmeCard['id']) ?>">
          <input type="hidden" name="decision" value="reject">
          <button type="submit" class="c-btn c-btn--decision c-btn--maroon"><?= icon('close', 13) ?>Reject</button>
        </form>
        <form method="post" action="<?= e("{$cardAction}/review") ?>">
          <?= $csrfField ?>
          <input type="hidden" name="id" value="<?= e($programmeCard['id']) ?>">
          <input type="hidden" name="decision" value="approve">
          <button type="submit" class="c-btn c-btn--decision c-btn--solid"><?= icon('check', 13) ?>Accept</button>
        </form>
      <?php elseif ($cardState === 'archived' && $can['approve']): ?>
        <form method="post" action="<?= e("{$cardAction}/archive") ?>">
          <?= $csrfField ?>
          <input type="hidden" name="id" value="<?= e($programmeCard['id']) ?>">
          <input type="hidden" name="restore" value="1">
          <button type="submit" class="c-btn c-btn--decision c-btn--solid"><?= icon('check', 13) ?>Restore</button>
        </form>
      <?php elseif ($cardState === 'waiting'): ?>
        <span class="c-programme-card__button c-programme-card__button--done"><?= icon('clock', 13) ?>Pending Approval</span>
      <?php elseif ($cardState === 'requested'): ?>
        <span class="c-programme-card__button c-programme-card__button--done"><?= icon('check', 13) ?>Requested</span>
      <?php elseif ($cardState === 'open' && $programmeCard['whyNot'] !== null): ?>
        <span class="c-programme-card__button c-programme-card__button--done"><?= icon('lock', 13) ?><?= e($programmeCard['whyNot']) ?></span>
      <?php elseif ($cardState === 'open' && $can['join']): ?>
        <form method="post" action="<?= e("{$cardAction}/interest") ?>">
          <?= $csrfField ?>
          <input type="hidden" name="id" value="<?= e($programmeCard['id']) ?>">
          <button type="submit" class="c-programme-card__button c-programme-card__button--interest"><?= icon('heart', 13) ?>Interest</button>
        </form>
      <?php else: ?>
        <a class="c-programme-card__button c-programme-card__button--<?= e($programmeCard['type']) ?>" href="<?= e(url_with($listPath, $listKeep + ['id' => $programmeCard['id']])) ?>">View Details →</a>
      <?php endif; ?>
    </div>
  </div>
</article>
<?php unset($programmeCard, $cardState, $cardType, $cardPill, $cardContact, $cardAction, $cardForm); ?>
