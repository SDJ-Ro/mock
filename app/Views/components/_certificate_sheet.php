<?php
// The A4 character certificate: the school's name and motto, the student's
// particulars, the three sections as points, the issue date and the principal's signature line.
// ONE template for the screen and the PDF (Dompdf reads this same HTML, so certificate-sheet.css uses only what
// Dompdf understands: blocks, tables, positions — no flex / grid, soft text by opacity).
// On screen it sits in a page frame: the text flows into as many A4 pages as it needs (CSS columns) and
// certificate-sheet.js counts them for Previous / Next — pages come and go by themselves when the text changes.
// Set before requiring:
//   $sheet          one prepared certificate (CertificateController::prepare())
//   $sheetFor       'screen' (the frame, waiting requests as "Pending Review") or 'pdf' (just the paper)
//   $sheetEditable  optional, management: the sections can be edited in place (certificate-sheet.js, Edit wording)
$sheetOnScreen = $sheetFor === 'screen';
$sheetIcon     = fn (string $name): string => $sheetOnScreen ? icon($name, 17) : icon_image($name, 17, 'terracotta');
$sheetFacts    = [
    [['Full name', $sheet['name']], ['Name with initials', $sheet['withInitials']]],
    [[words('{Student} index no.'), $sheet['id']], [words('Current {class}'), $sheet['cohort']]],
    [['Period of study', $sheet['period']], null],
];
?>
<?php if ($sheetOnScreen): ?>
  <div class="j-certificate-pages">
    <div class="c-certificate-pages__paper">
      <div class="c-certificate-pages__strip j-certificate-strip">
<?php endif; ?>

<article class="c-certificate<?= !empty($sheetEditable) ? ' j-certificate-sheet' : '' ?>">
  <header class="c-certificate__header">
    <p class="c-certificate__school"><?= e(school('name')) ?></p>
    <p class="c-certificate__motto"><?= e(school('motto')) ?></p>
    <div class="c-certificate__rule"></div>
    <h2 class="c-certificate__title">Character Certificate</h2>
    <p class="c-certificate__awarded"><span class="c-certificate__soft">Awarded to</span> <strong><?= e($sheet['name']) ?></strong></p>
  </header>

  <section class="c-certificate__section">
    <div class="c-certificate__heading">
      <h3 class="c-certificate__heading-title"><?= e(words('{Student} particulars')) ?></h3>
      <span class="c-certificate__note">Verified <?= e(strtolower(word('student'))) ?> record</span>
    </div>
    <table class="c-certificate__facts">
      <?php foreach ($sheetFacts as $sheetRow): ?>
        <tr>
          <?php foreach ($sheetRow as $sheetColumn => $sheetFact): ?>
            <?php if ($sheetColumn === 1): ?><td class="c-certificate__gap"></td><?php endif; ?>
            <?php if ($sheetFact === null): ?>
              <td colspan="2"></td>
            <?php else: ?>
              <td class="c-certificate__fact-label"><?= e($sheetFact[0]) ?></td>
              <td class="c-certificate__fact-value"><?= e($sheetFact[1]) ?></td>
            <?php endif; ?>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </table>
  </section>

  <?php foreach (config('certificates.sections') as $sheetSection => [$sheetHeading, $sheetIconName]): ?>
    <section class="c-certificate__section">
      <div class="c-certificate__heading">
        <h3 class="c-certificate__heading-title"><?= $sheetIcon($sheetIconName) ?> <?= e($sheetHeading) ?></h3>
        <?php if (!empty($sheetEditable)): ?>
          <span class="c-certificate__note c-certificate__edit-note">Editable (Press Enter for a new point)</span>
        <?php endif; ?>
      </div>
      <ul class="c-certificate__points<?= !empty($sheetEditable) ? ' c-certificate__points--editable j-certificate-editable' : '' ?>">
        <?php foreach ($sheet[$sheetSection] as $sheetPoint): ?>
          <li><?= e($sheetPoint) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($sheetOnScreen && $sheet['waitingPoints'][$sheetSection] !== []): ?>
        <ul class="c-certificate__points c-certificate__points--waiting">
          <?php foreach ($sheet['waitingPoints'][$sheetSection] as $sheetPoint): ?>
            <li><?= e($sheetPoint) ?> <span class="c-tag c-tag--round c-tag--flame">Pending Review</span></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>

  <div class="c-certificate__footer">
    <div class="c-certificate__issued">
      <p class="c-certificate__issued-on">Issued on <?= e(date(country('date_format'), strtotime($sheet['requested']))) ?></p>
      <p class="c-certificate__soft"><?= e(words('{Student} pathway: ') . config("certificates.pathways.{$sheet['pathway']}")[1]) ?></p>
    </div>
    <div class="c-certificate__signature"><span><?= e(word('principal')) ?> Signature</span></div>
  </div>
</article>

<?php if ($sheetOnScreen): ?>
      </div>
      <p class="c-certificate-pages__corner j-certificate-page-label" aria-hidden="true"></p>
    </div>
    <div class="c-certificate-pages__nav j-certificate-nav" hidden>
      <button type="button" class="c-btn c-btn--solid c-btn--compact j-certificate-previous"><?= icon('chevronLeft', 14) ?>Previous page</button>
      <span class="c-certificate-pages__label j-certificate-page-label" aria-live="polite"></span>
      <button type="button" class="c-btn c-btn--solid c-btn--compact j-certificate-next">Next page<?= icon('chevronRight', 14) ?></button>
    </div>
  </div>
<?php endif; ?>
<?php unset($sheet, $sheetFor, $sheetEditable, $sheetOnScreen, $sheetIcon, $sheetFacts, $sheetRow, $sheetColumn, $sheetFact, $sheetSection, $sheetHeading, $sheetIconName, $sheetPoint); ?>
