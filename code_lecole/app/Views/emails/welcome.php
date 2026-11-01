<?php
// Welcome email: the school office added account(s) (PeopleAddController::welcome()). ONE email per addition:
// a teacher / staff member gets theirs; a parent gets one for a new child (and for their own new account in the same
// email). For each account a box with everything the sign-up page asks except the password — the full name exactly
// as registered and the sign-in ID (AuthController::handleSignup() checks both) — and its own button.
// Receives: $name (who reads it), $intro (the sentence under the greeting), $accounts [['title', 'fullName',
// 'identifier', 'url'], …]
?>
<p style="margin: 0 0 16px;">Hi<?= $name !== '' ? ' <strong>' . e($name) . '</strong>' : '' ?>,</p>

<p style="margin: 0 0 20px; color: rgba(15,65,74,0.85);"><?= e($intro) ?></p>

<?php foreach ($accounts as $account): ?>
    <div class="code-box" style="text-align: left;">
        <span class="code-label"><?= e($account['title']) ?> — enter these on the sign-up page</span>
        <p style="margin: 10px 0 4px;">Full Name: <strong><?= e($account['fullName']) ?></strong></p>
        <p style="margin: 0 0 12px;">Sign-in ID: <strong><?= e($account['identifier']) ?></strong></p>
        <a class="btn-primary" style="margin: 0;" href="<?= e($account['url']) ?>">Set Up This Account</a>
    </div>
<?php endforeach; ?>

<p style="margin: 0;">Type each name exactly as it is written above. Then choose a password; we will email a 6-digit code to confirm it is you.</p>
