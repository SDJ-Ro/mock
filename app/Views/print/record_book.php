<?php
// The record book as an official A4 page (drawn by the server, not the browser): the school's letterhead, the student,
// the marks table, the totals, the class teacher's feedback, two signature lines. Its own page, no app shell;
// export-pdf.js loads it out of sight and opens the print box. Its look: css/global.css + css/print.css.
// From AcademicController::recordPrint(): $student ['name', 'code'], $book (one year: 'label', …), $term, $record (one term).
$gradeName   = $book['label'];
$termName    = word('term') . " {$term}";
$teacherName = word('class') . ' ' . word('teacher');
?>
<!doctype html>
<html lang="<?= e(country('language')) ?>">
<head>
  <meta charset="UTF-8" />
  <title><?= e("{$student['name']} — {$gradeName} ({$termName}) Record Book") ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>" />
  <link rel="stylesheet" href="<?= e(asset('css/print.css')) ?>" />
</head>
<body class="c-print">
  <header class="c-print__letterhead">
    <div class="c-print__school">
      <img class="c-print__logo" src="<?= e(school('logo')) ?>" alt="">
      <div>
        <h1 class="c-print__name"><?= e(school('name')) ?></h1>
        <p class="c-print__motto"><?= e(school('motto')) ?></p>
      </div>
    </div>
    <div class="c-print__badge">
      <span class="c-print__pill">Official Transcript</span>
      <p class="c-print__date">Date: <?= e(date(country('date_format'))) ?></p>
    </div>
  </header>

  <dl class="c-print__facts">
    <div><dt><?= e(word('student')) ?> Full Name</dt><dd><?= e($student['name']) ?></dd></div>
    <div><dt>Registration No.</dt><dd><?= e($student['code']) ?></dd></div>
    <div><dt>Academic Level</dt><dd><?= e($gradeName) ?></dd></div>
    <div><dt>Evaluation Period</dt><dd><?= e($termName) ?></dd></div>
  </dl>

  <h2 class="c-print__section-title"><?= e(words('Academic Marks & {Subject} Performance')) ?></h2>
  <table class="c-print__marks">
    <thead>
      <tr>
        <th scope="col"><?= e(word('subject')) ?></th>
        <th scope="col">Marks</th>
        <th scope="col">Highest Mark in <?= e(word('class')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($record['marks'] as $row): ?>
        <tr>
          <td><?= e($row['subject']) ?></td>
          <td><?= e($row['mark']) ?></td>
          <td><?= e($row['highest']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <dl class="c-print__summary">
    <div><dt>Total Marks</dt><dd><?= e("{$record['total']} /{$record['outOf']}") ?></dd></div>
    <div><dt>Average Score</dt><dd><?= e($record['average']) ?></dd></div>
    <div><dt><?= e(word('class')) ?> Position</dt><dd><?= e($record['placement']) ?></dd></div>
  </dl>

  <section class="c-print__feedback">
    <p class="c-print__feedback-head">
      <span class="c-print__feedback-badge"><?= e($teacherName) ?>'s Assessment (<?= e($termName) ?>)</span>
      <span class="c-print__feedback-date"><?= e($record['date']) ?></span>
    </p>
    <p class="c-print__feedback-text">"<?= e($record['feedback']) ?>"</p>
    <p class="c-print__feedback-author"><strong>Evaluated by:</strong> <?= e($record['teacher']) ?></p>
  </section>

  <div class="c-print__signatures">
    <p class="c-print__signature"><?= e($teacherName) ?><span>Academic In-Charge</span></p>
    <p class="c-print__signature">Head of <?= e(word('institution')) ?> / Registrar<span>Official Institutional Seal</span></p>
  </div>

  <footer class="c-print__footer">
    This is an official computer-generated <?= e(strtolower(word('student'))) ?> record book issued by <?= e(school('name')) ?> Administration.
  </footer>
</body>
</html>
