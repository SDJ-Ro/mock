<?php
// The start of every signed-in page: the <head> with the page's CSS, the icons and the sidebar.
// The page's own view sets these first, then requires this file (and layouts/page_bottom.php at its end);
// the view then places its own page header (components/_page_header.php) where it belongs:
//   $tabTitle (browser tab), $styles (component CSS files, without ".css"),
//   $grids (the page's rows: css/layout/{name}-grid.css — how components line up, e.g. 'chart-calendar')
// From Controller::page(): $currentRole, $currentPath, $userName, $userPhoto, $csrfField, $sidebarCollapsed,
// $sessionTimeout (seconds idle before sign-out: session-timeout.js warns a minute before).
?>
<!doctype html>
<html lang="<?= e(country('language')) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($tabTitle) ?> — <?= e(school('name')) ?></title>
  <link rel="icon" href="<?= e(school('logo')) ?>" />
  <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>" />
  <?php // Every signed-in page: the sidebar (+ the parent's "Viewing" switch: a pick menu), buttons, labels, avatars
        // (initials circles), banners, the pop-up frame, the "Are you sure?" pop-up (log out needs it; its "can't
        // be done" box is a banner) and the sliding banner ("it worked" / "it didn't": page_bottom.php) ?>
  <?php foreach (['sidebar', 'pick-menu', 'button', 'label', 'avatar', 'banner', 'pop-up', 'question-pop-up', 'feedback-banner'] as $style): ?>
    <link rel="stylesheet" href="<?= e(asset("css/components/{$style}.css")) ?>" />
  <?php endforeach; ?>
  <?php foreach ($styles as $style): ?>
    <link rel="stylesheet" href="<?= e(asset("css/components/{$style}.css")) ?>" />
  <?php endforeach; ?>
  <?php foreach ($grids ?? [] as $grid): ?>
    <link rel="stylesheet" href="<?= e(asset("css/layout/{$grid}-grid.css")) ?>" />
  <?php endforeach; ?>
</head>
<body data-session-timeout="<?= e($sessionTimeout) ?>">

<?php // The school's dates for every script (read by school-dates.js): its today and how it writes a date ?>
<script type="application/json" id="j-school-dates"><?= json_encode([
    'today'    => date('Y-m-d'),
    'formats'  => [
        'date' => country('date_format'),
        'day'  => country('day_format'),
        'full' => country('full_date_format'),
        'long' => country('long_date_format'),
    ],
    'months'   => array_map(fn (int $month): string => date('F', mktime(0, 0, 0, $month, 1)), range(1, 12)),
    'weekdays' => array_map(fn (int $day): string => date('l', mktime(0, 0, 0, 1, $day, 2024)), range(1, 7)),   // 1 Jan 2024 = a Monday
], JSON_HEX_TAG) ?></script>

<?php require dirname(__DIR__) . '/components/icons.php'; ?>

<div id="j-app-root" class="c-app-shell">
  <?php require dirname(__DIR__) . '/components/_sidebar.php'; ?>

  <main class="c-main" id="j-main">
