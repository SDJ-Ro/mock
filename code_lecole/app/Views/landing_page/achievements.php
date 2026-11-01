<!DOCTYPE html>
<html lang="<?= e(country('language')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honours Board — <?= e(school('name')) ?></title>
    <link rel="icon" href="<?= e(school('logo')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/button.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/label.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/tag.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/landing.css')) ?>">
    <script src="<?= e(asset('js/reveal.js')) ?>" defer></script>
</head>
<body>

<?php require dirname(__DIR__) . '/components/icons.php'; ?>

<header class="ach-page-header">
    <div class="wrap">
        <a class="c-btn c-btn--back c-btn--glass" href="/"><?= icon('arrowLeft') ?> Back to <?= e(school('name')) ?></a>
        <h1 class="ach-page-title"><?= e(word('student')) ?> achievements across sport, stage, and study.</h1>
    </div>
</header>

<main class="ach-section--page">
    <div class="wrap">
        <div class="ach-list">
            <?php $showDetails = true; ?>
            <?php foreach ($achievements as $i => $item): ?>
                <?php require dirname(__DIR__) . '/components/_achievement_row.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<footer class="l-footer">
    <div class="wrap l-footer-bottom-inner">
        <p>© <?= date('Y') ?> <?= e(school('name')) ?></p>
        <p><?= e(school('motto')) ?></p>
    </div>
</footer>

</body>
</html>
