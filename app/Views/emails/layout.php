<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #fdfaf6; margin: 0; padding: 32px 16px; color: #0f414a; }
  .mail-card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid rgba(15,65,74,0.1); overflow: hidden; box-shadow: 0 4px 20px rgba(15,65,74,0.06); }
  .mail-header { background: #0f414a; padding: 28px 36px; color: #ffffff; }
  .mail-brand { font-size: 22px; font-weight: 700; letter-spacing: -0.01em; margin: 0; color: #ffffff; }
  .mail-sub { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: #7fc7cc; margin-top: 5px; margin-bottom: 0; font-weight: 600; }
  .mail-body { padding: 32px 36px; font-size: 15px; line-height: 1.65; color: #233438; }
  .mail-footer { background: #fbf8f3; padding: 22px 36px; font-size: 11.5px; color: rgba(15,65,74,0.65); border-top: 1px solid rgba(15,65,74,0.08); text-align: center; line-height: 1.6; }
  .mail-link { color: #0f414a; font-weight: 600; text-decoration: underline; }

  /* The email's parts */
  .code-box { background: #f4ece1; padding: 20px; border-radius: 12px; text-align: center; margin: 24px 0; border: 1px solid rgba(15,65,74,0.08); }
  .code-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.18em; color: rgba(15,65,74,0.7); display: block; margin-bottom: 6px; }
  .code-value { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 32px; font-weight: 800; letter-spacing: 0.25em; color: #0f414a; }
  .btn-primary { display: inline-block; background: #0f414a; color: #ffffff !important; text-decoration: none; padding: 13px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; margin: 18px 0; text-align: center; }
</style>
</head>
<body>
  <div class="mail-card">
    <div class="mail-header">
      <h1 class="mail-brand"><?= e(school('name')) ?></h1>
      <p class="mail-sub">Official Academic Notification</p>
    </div>
    <div class="mail-body">
      <?= $content ?>
    </div>
    <div class="mail-footer">
      &copy; <?= date('Y') ?> <?= e(school('name')) ?>. All rights reserved.<br>
      Security & Support: <a href="mailto:<?= e(school('support_email')) ?>" class="mail-link"><?= e(school('support_email')) ?></a>
    </div>
  </div>
</body>
</html>
