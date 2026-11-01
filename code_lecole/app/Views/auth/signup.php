<!DOCTYPE html>
<html lang="<?= e(country('language')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($portal['label']) ?> Account Activation — <?= e(school('name')) ?></title>
    <link rel="icon" href="<?= e(school('logo')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/global.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/button.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/form-field.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/banner.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/password-field.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/components/label.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/auth.css')) ?>">
    <script src="<?= e(asset('js/components/password-field.js')) ?>" defer></script>
    <script src="<?= e(asset('js/components/send-once.js')) ?>" defer></script>
    <script src="<?= e(asset('js/auth.js')) ?>" defer></script>
</head>
<body>

<?php require dirname(__DIR__) . '/components/icons.php'; ?>

<a class="c-btn c-btn--back c-btn--dark auth-back-btn" href="/auth/<?= e($role) ?>"><?= icon('arrowLeft') ?> Back to Sign In</a>

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
            <h1 class="auth-title"><?= e($portal['signup_title']) ?></h1>
            <p class="auth-desc"><?= e($portal['signup_desc']) ?></p>

            <?php if (!empty($notice)): ?>
                <div class="auth-notice">
                    <span class="auth-notice-icon"><?= icon('info') ?></span>
                    <p><?= e($notice) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p class="c-banner c-banner--error" role="alert">
                    <?= icon('alertCircle') ?>
                    <span><?= e($error) ?></span>
                </p>
            <?php endif; ?>

            <?php if ($step === 'details'): ?>
                <!-- STEP 1: Enter details and choose password -->
                <form class="auth-form" method="post" action="/auth/<?= e($role) ?>/signup">
                    <?= $csrfField ?>

                    <label class="c-field">
                        <span class="c-label">Full Name <em class="auth-label-note">(as registered with the <?= e(strtolower(word('institution'))) ?>)</em></span>
                        <input class="c-field__input" type="text" name="full_name" value="<?= e($fullName) ?>"
                            placeholder="e.g. Samantha Perera" required autofocus>
                    </label>

                    <label class="c-field">
                        <span class="c-label"><?= e($portal['signup_field']) ?></span>
                        <input class="c-field__input" type="text" name="identifier" value="<?= e($identifier) ?>"
                            placeholder="<?= e($portal['signup_placeholder']) ?>" required autocomplete="username">
                    </label>

                    <label class="c-field">
                        <span class="c-label">Create Password</span>
                        <?php
                        $passwordName         = 'password';
                        $passwordClass        = 'c-field__input';
                        $passwordPlaceholder  = 'Choose a password (min 8 characters)';
                        $passwordAutocomplete = 'new-password';
                        require dirname(__DIR__) . '/components/_password_field.php';
                        ?>
                    </label>

                    <div class="auth-form-footer">
                        <a class="auth-link" href="/auth/<?= e($role) ?>">Already have access? Sign in</a>
                        <button class="c-btn c-btn--accent" type="submit">Set Password &amp; Continue <?= icon('arrowRight') ?></button>
                    </div>
                </form>

            <?php else: ?>
                <!-- STEP 2: Enter 6-digit code sent to email -->
                <form class="auth-form" method="post" action="/auth/<?= e($role) ?>/verify">
                    <?= $csrfField ?>

                    <div class="auth-notice">
                        <span class="auth-notice-icon"><?= icon('mail') ?></span>
                        <p>A 6-digit verification code was sent to <strong><?= e($sentTo) ?></strong>. Enter it below to activate your account.</p>
                    </div>

                    <label class="c-field">
                        <span class="c-label">6-Digit Verification Code</span>
                        <input class="c-field__input c-field__input--code" type="text" name="code"
                            placeholder="• • • • • •" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                            required autofocus autocomplete="one-time-code">
                    </label>

                    <div class="auth-form-footer">
                        <a class="auth-link" href="/auth/<?= e($role) ?>/signup?restart=1">← Change details</a>
                        <button class="c-btn c-btn--accent" type="submit">Verify &amp; Activate <?= icon('arrowRight') ?></button>
                    </div>
                </form>

                <p class="auth-resend">
                    Didn't receive the email?
                    <button type="submit" form="resend-form" class="auth-link auth-link--button auth-link--orange">
                        Resend Code
                    </button>
                </p>

                <form id="resend-form" method="post" action="/auth/<?= e($role) ?>/resend">
                    <?= $csrfField ?>
                </form>
            <?php endif; ?>

            <?php if (!empty($portal['footnote'])): ?>
                <p class="auth-footnote"><?= e($portal['footnote']) ?></p>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
