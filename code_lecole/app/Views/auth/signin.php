<!DOCTYPE html>
<html lang="<?= e(country('language')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($portal['label']) ?> Sign In — <?= e(school('name')) ?></title>
    <link rel="icon" href="<?= e(school('logo')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/pop-up.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/button.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/form-field.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/banner.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/password-field.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/label.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/auth.css')) ?>">
    <script src="<?= e(asset('js/components/pop-up.js')) ?>" defer></script>
    <script src="<?= e(asset('js/components/password-field.js')) ?>" defer></script>
    <script src="<?= e(asset('js/components/send-once.js')) ?>" defer></script>
    <script src="<?= e(asset('js/auth.js')) ?>" defer></script>
</head>
<body>

<?php require dirname(__DIR__) . '/components/icons.php'; ?>

<a class="c-btn c-btn--back c-btn--dark auth-back-btn" href="/auth"><?= icon('arrowLeft') ?> Who's signing in</a>

<div class="auth-shell">
    <aside class="auth-visual">
        <img class="is-active" src="<?= e($portal['image']) ?>" alt="<?= e($portal['alt']) ?>">
        <div class="auth-visual-scrim"></div>
        <div class="auth-visual-inner">
            <span class="auth-visual-badge"><?= icon($portal['icon'], 24) ?></span>
            <p class="c-label auth-visual-eyebrow"><?= e($portal['eyebrow']) ?></p>
            <h2 class="auth-visual-headline"><?= e($portal['headline']) ?></h2>
            <p class="auth-visual-copyright">© <?= date('Y') ?> <?= e(school('name')) ?>. All rights reserved.</p>
        </div>
    </aside>

    <main class="auth-form-section">
        <div class="auth-form-col">
            <p class="c-label auth-eyebrow"><?= e($portal['eyebrow']) ?></p>
            <h1 class="auth-title"><?= e($portal['label']) ?> Sign In</h1>
            <p class="auth-desc"><?= e($portal['desc']) ?></p>

            <form class="auth-form" method="post" action="/auth/<?= e($role) ?>">
                <?= $csrfField ?>

                <?php if ($notice): ?>
                    <div class="auth-notice">
                        <span class="auth-notice-icon"><?= icon('info') ?></span>
                        <p><?= e($notice) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <p class="c-banner c-banner--error" role="alert">
                        <?= icon('alertCircle') ?>
                        <span>
                            <?= e($error) ?>
                            <?php if ($errorLink): ?>
                                <a href="<?= e($errorLink['href']) ?>"><?= e($errorLink['label']) ?> →</a>
                            <?php endif; ?>
                        </span>
                    </p>
                <?php endif; ?>

                <label class="c-field">
                    <span class="c-label"><?= e($portal['field']) ?></span>
                    <input class="c-field__input" type="text" name="identifier" value="<?= e($identifier) ?>"
                        placeholder="<?= e($portal['placeholder']) ?>" required autocomplete="username">
                </label>

                <div class="c-field">
                    <div class="auth-label-row">
                        <span class="c-label">Password</span>
                        <button type="button" class="auth-link auth-link--button auth-link--orange" data-pop-up-open="j-forgot">Forgot Password?</button>
                    </div>
                    <?php
                    $passwordName         = 'password';
                    $passwordClass        = 'c-field__input';
                    $passwordPlaceholder  = 'Enter your password';
                    $passwordAutocomplete = 'current-password';
                    require dirname(__DIR__) . '/components/_password_field.php';
                    ?>
                </div>

                <div class="auth-form-footer">
                    <?php if ($portal['can_signup']): ?>
                        <a class="auth-link" href="/auth/<?= e($role) ?>/signup">Need access? Sign up</a>
                    <?php else: ?>
                        <span class="auth-audited"><?= icon('shield', 14) ?> Access is audited</span>
                    <?php endif; ?>

                    <button class="c-btn c-btn--accent" type="submit">Sign In <?= icon('arrowRight') ?></button>
                </div>
            </form>

            <?php if (feature('demo_quickfill')): ?>
                <button class="auth-demo" type="button" data-identifier="<?= e($portal['demo']) ?>" data-password="Password@123">
                    <?= icon('sparkles', 14) ?> Fill In the Demo Account
                </button>
            <?php endif; ?>

            <?php if ($portal['footnote']): ?>
                <p class="auth-footnote"><?= e($portal['footnote']) ?></p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require __DIR__ . '/forgot_modal.php'; ?>

</body>
</html>
