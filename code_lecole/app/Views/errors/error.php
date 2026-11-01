<?php
// The page for "not found" (404, from core/Router.php) and "something broke" (500, from core/bootstrap.php).
// Set before requiring: $code, $heading, $message.
?>
<!DOCTYPE html>
<html lang="<?= e(country('language')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($code) ?> — <?= e($heading) ?> · <?= e(school('name')) ?></title>
    <link rel="icon" href="<?= e(school('logo')) ?>">
</head>
<body>
    <main>
        <h1><?= e($code) ?></h1>
        <p><?= e($message) ?></p>
        <a href="/">Back to home</a>
    </main>
</body>
</html>
