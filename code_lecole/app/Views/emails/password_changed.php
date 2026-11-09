<?php
// "Password changed" email body (AccountEmails::passwordChanged(): sent after My Profile's change and after a reset)
// Receives: $name ('' when it is a parent reading about a child's account), $whose ('your' or "Nethmi Perera's"),
// $when (a date and time), $how ("in My Profile" / "with a reset code"), $support (the school's support address)
?>
<p style="margin: 0 0 16px;">Hi<?= $name !== '' ? ' <strong>' . e($name) . '</strong>' : '' ?>,</p>

<p style="margin: 0 0 20px; color: rgba(15,65,74,0.85);">
    The password of <?= e($whose) ?> <?= e(school('name')) ?> account was changed on <strong><?= e($when) ?></strong>, <?= e($how) ?>.
</p>

<p style="margin: 0 0 20px; color: rgba(15,65,74,0.85);">
    If this was you, there is nothing more to do.
</p>

<p style="font-size: 13px; color: rgba(15,65,74,0.7); margin: 0;">
    If it was not you, reset the password now with “Forgot password” on the sign-in page and contact
    <strong><?= e($support) ?></strong> so we can look into it.
</p>
