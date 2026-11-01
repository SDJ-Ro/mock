<?php
// What happened, oldest first, as dots on a line: the page's "Certificate Activity" and
// each request's own timeline in the Student Requests pop-up (--small). Its look: certificates.css.
// Set before requiring:
//   $activityItems    [[when 'YYYY-MM-DD HH:MM', what, who], …]
//   $activitySmall    optional, true = the small dots and words of a request card
?>
<ol class="c-activity<?= !empty($activitySmall) ? ' c-activity--small' : '' ?>">
  <?php foreach ($activityItems as [$activityWhen, $activityWhat, $activityWho]): ?>
    <li class="c-activity__item">
      <p class="c-activity__what"><?= e($activityWhat) ?></p>
      <p class="c-activity__when"><?= e(date(country('date_format') . ', g:i A', strtotime($activityWhen)) . " · {$activityWho}") ?></p>
    </li>
  <?php endforeach; ?>
</ol>
<?php unset($activityItems, $activitySmall, $activityWhen, $activityWhat, $activityWho); ?>
