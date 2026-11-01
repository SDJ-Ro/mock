<?php
// One event on the Audit Log: who (initials, their ID, their role) on the left; what (the action's
// short name), the details and when on the right. Coloured by the person's role (config/audit.php 'tones'): the left
// edge, the initials and the ID's tint. Its look: audit.css (+ tag.css, avatar.css, label.css).
// Set before requiring:
//   $log          one AuditLog::recent() row: identifier, action, details, created_at, role (null = no account), full_name
//   $actionNames  code → short name (AuditController)
$logRole  = $log['role'] ?? 'system';
$logActor = $log['identifier'] ?: 'System / Automated';
$logTime  = strtotime($log['created_at']);
?>
<article class="c-card c-log-card c-log-card--<?= e(config("audit.tones.{$logRole}")) ?>">
  <div class="c-log-card__actor">
    <span class="c-avatar c-log-card__avatar">
      <?= e($log['full_name'] !== null ? initials($log['full_name']) : strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $logActor), 0, 2))) ?>
    </span>
    <div class="c-log-card__who">
      <span class="c-log-card__id" title="<?= e($log['full_name'] ?? '') ?>"><?= e($logActor) ?></span>
      <span class="c-label c-log-card__role"><?= e($logRole === 'system' ? 'System' : word($logRole)) ?> account</span>
    </div>
  </div>

  <div class="c-log-card__body">
    <span class="c-tag c-tag--round c-tag--sand c-log-card__action"><?= e($actionNames[$log['action']]) ?></span>
    <p class="c-log-card__details"><?= e($log['details']) ?></p>
    <div class="c-log-card__time">
      <p class="c-log-card__date"><?= e(date(country('date_format'), $logTime)) ?></p>
      <p class="c-log-card__clock"><?= e(date('H:i:s', $logTime)) ?></p>
    </div>
  </div>
</article>
<?php unset($log, $logRole, $logActor, $logTime); ?>
