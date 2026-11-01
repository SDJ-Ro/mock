<!DOCTYPE html>
<html lang="<?= e(country('language')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Who's signing in? — <?= e(school('name')) ?></title>
    <link rel="icon" href="<?= e(school('logo')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/button.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/label.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/auth.css')) ?>">
    <script src="<?= e(asset('js/auth.js')) ?>" defer></script>
</head>
<body>

<?php require dirname(__DIR__) . '/components/icons.php'; ?>

<a class="c-btn c-btn--back c-btn--dark auth-back-btn" href="/"><?= icon('arrowLeft') ?> <?= e(school('name')) ?> Home</a>

<div class="auth-shell">
    <aside class="auth-visual">
        <img class="is-active" data-role-image="" src="/assets/images/collage.jpg" alt="Life at <?= e(school('name')) ?>">
        <?php foreach ($portals as $role => $portal): ?>
            <img data-role-image="<?= e($role) ?>" src="<?= e($portal['image']) ?>" alt="<?= e($portal['alt']) ?>">
        <?php endforeach; ?>

        <div class="auth-visual-scrim"></div>
        <div class="auth-visual-inner">
            <span class="auth-visual-badge"><?= icon('graduationCap', 24) ?></span>
            <p class="c-label auth-visual-eyebrow">One <?= e(strtolower(word('institution'))) ?>, many roles</p>
            <h2 class="auth-visual-headline">Who are you?</h2>
            <p class="auth-visual-copyright">© <?= date('Y') ?> <?= e(school('name')) ?>. All rights reserved.</p>
        </div>
    </aside>

    <main class="auth-form-section">
        <div class="auth-form-col">
            <p class="c-label auth-eyebrow">Access your account</p>
            <h1 class="auth-title">Who's signing in?</h1>
            <p class="auth-desc">Choose your role to continue to the right sign-in form.</p>

            <?php if ($notice): ?>
                <div class="auth-notice auth-notice--top">
                    <span class="auth-notice-icon"><?= icon('info') ?></span>
                    <p><?= e($notice) ?></p>
                </div>
            <?php endif; ?>

            <nav class="auth-role-list" aria-label="Choose your role">
                <?php foreach ($portals as $role => $portal): ?>
                    <a class="auth-role" href="/auth/<?= e($role) ?>" data-role="<?= e($role) ?>">
                        <span class="auth-role-icon tone-<?= e($portal['tone']) ?>"><?= icon($portal['icon'], 20) ?></span>
                        <span class="auth-role-copy">
                            <span class="auth-role-name"><?= e($portal['chooser_name']) ?></span>
                            <span class="auth-role-tagline"><?= e($portal['tagline']) ?></span>
                        </span>
                        <?= icon('chevronRight') ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </main>
</div>

</body>
</html>
