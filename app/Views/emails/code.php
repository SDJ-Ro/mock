<?php
// 6-digit code email body (used for account activation and forgot password)
// Receives: $name, $reason, $code, $minutes
?>
<p style="margin: 0 0 16px;">Hi <strong><?= e($name) ?></strong>,</p>

<p style="margin: 0 0 20px; color: rgba(15,65,74,0.85);"><?= e($reason) ?></p>

<div class="code-box">
    <span class="code-label">Your 6-Digit Verification Code</span>
    <span class="code-value"><?= e($code) ?></span>
</div>

<p style="font-size: 13px; color: rgba(15,65,74,0.7); margin: 0;">
    This code is valid for <strong><?= e($minutes) ?> minutes</strong>. If you did not request this code, you can safely ignore this email.
</p>
