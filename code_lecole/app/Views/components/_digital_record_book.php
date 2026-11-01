<?php
// The Digital Record Book: a white card with the title + Export PDF, the Grade and Term dropdowns, the marks
// table (Subject · Marks · Highest Mark in Class) and the class teacher's feedback for the chosen year + term.
// One book per school year (the Grade dropdown lists them: "Grade 7", "Grade 7 (Repeat)"). PHP draws every year × term
// (each table body and feedback card carries data-year / data-term); record-book.js shows the chosen one and hides the
// rest. Its look: record-book.css. Used by _academic_section.php (student/academic, the parent's child profile) and on
// its own in People's profile pop-up. Set first:
//   $records   [year => ['grade', 'label', 'class', 'terms' => [term => ['marks' => [['subject', 'mark', 'highest']],
//              'total', 'outOf', 'average', 'placement', 'teacher', 'date', 'feedback']]]] (RecordBook::forStudent())
//   $year      the year shown first   $term the term shown first
//   $exportUrl the printable page (the chosen ?year=&term= is added to it), or null: no Export PDF button
// No published term yet → the empty box instead (the page loads empty-box.css).
if ($records === [])
{
    echo '<div class="c-empty-box"><p class="c-empty-box__text">No published marks yet.</p></div>';

    return;
}

$bookId = 'j-record-book';
$terms  = array_unique(array_merge(...array_map(fn (array $book): array => array_keys($book['terms']), array_values($records))));
sort($terms);
?>
<section class="c-card c-record-book j-record-book" id="<?= e($bookId) ?>" aria-labelledby="<?= e($bookId) ?>-title"
         data-year="<?= e($year) ?>" data-term="<?= e($term) ?>" data-term-word="<?= e(word('term')) ?>">
  <header class="c-record-book__header">
    <div class="c-record-book__heading">
      <span class="c-record-book__icon" aria-hidden="true"><?= icon('book', 16) ?></span>
      <h2 class="c-panel__title" id="<?= e($bookId) ?>-title">
        Digital Record Book — <span class="j-record-grade"><?= e($records[$year]['label']) ?></span>
      </h2>
    </div>
    <?php if ($exportUrl !== null): // (null: no printable page here — People's pop-up) ?>
      <?php
      $bookExportUrl = $exportUrl;
      $exportUrl     = "{$bookExportUrl}?year={$year}&term={$term}";
      require __DIR__ . '/_export_pdf_button.php';
      $exportUrl = $bookExportUrl;
      ?>
    <?php endif; ?>
  </header>

  <div class="c-record-book__filters">
    <div class="c-record-book__filter">
      <span class="c-label"><?= e(word('grade')) ?></span>
      <?php
      $dropdownId    = "{$bookId}-year";
      $options       = array_map(fn (int $bookYear): array => ['value' => (string) $bookYear, 'label' => $records[$bookYear]['label']], array_keys($records));
      $selectedValue = (string) $year;
      $dropdownLabel = word('grade');
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
    <div class="c-record-book__filter">
      <span class="c-label"><?= e(word('term')) ?></span>
      <?php
      $dropdownId    = "{$bookId}-term";
      $options       = array_map(fn (int $number): array => ['value' => (string) $number, 'label' => word('term') . " {$number}"], $terms);
      $selectedValue = (string) $term;
      $dropdownLabel = word('term');
      require __DIR__ . '/_dropdown.php';
      ?>
    </div>
  </div>

  <table class="c-record-book__table">
    <thead>
      <tr>
        <th scope="col"><?= e(word('subject')) ?></th>
        <th scope="col">Marks</th>
        <th scope="col">Highest Mark in <?= e(word('class')) ?></th>
      </tr>
    </thead>
    <?php foreach ($records as $recordYear => $book): ?>
      <?php foreach ($book['terms'] as $recordTerm => $record): ?>
        <tbody class="j-record-marks" data-year="<?= e($recordYear) ?>" data-term="<?= e($recordTerm) ?>" data-label="<?= e($book['label']) ?>"
               data-total="<?= e($record['total']) ?>" data-out-of="<?= e($record['outOf']) ?>"
               data-average="<?= e($record['average']) ?>" data-placement="<?= e($record['placement']) ?>"
               <?= $recordYear === $year && $recordTerm === $term ? '' : 'hidden' ?>>
          <?php foreach ($record['marks'] as $row): ?>
            <tr>
              <td><?= e($row['subject']) ?></td>
              <td><?= e($row['mark']) ?></td>
              <td><?= e($row['highest']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </table>

  <div class="c-record-book__feedback">
    <h3 class="c-record-book__feedback-title">
      <span class="c-record-book__feedback-icon" aria-hidden="true"><?= icon('feedback', 14) ?></span>
      <span>
        <?= e(word('class') . ' ' . word('teacher')) ?>'s Feedback — <span class="j-record-term"><?= e(word('term') . " {$term}") ?></span>,
        <span class="j-record-grade"><?= e($records[$year]['label']) ?></span>
      </span>
    </h3>
    <?php foreach ($records as $recordYear => $book): ?>
      <?php foreach ($book['terms'] as $recordTerm => $record): ?>
        <div class="c-record-book__note j-record-note" data-year="<?= e($recordYear) ?>" data-term="<?= e($recordTerm) ?>"
             <?= $recordYear === $year && $recordTerm === $term ? '' : 'hidden' ?>>
          <span class="c-avatar" aria-hidden="true"><?= icon('user', 20) ?></span>
          <div class="c-record-book__note-body">
            <p class="c-record-book__note-meta">
              <span class="c-record-book__teacher"><?= e($record['teacher']) ?></span>
              <span class="c-record-book__date"><?= e($record['date']) ?></span>
            </p>
            <p class="c-record-book__text"><?= e($record['feedback']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
</section>
<?php unset($bookId, $terms, $bookExportUrl, $recordYear, $book, $recordTerm, $record, $row); ?>
