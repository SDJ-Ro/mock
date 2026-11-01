<?php
// The end of every signed-in page (pairs with layouts/page_top.php): the "Are you sure?" pop-up, the sliding banner
// (with what the last save or refusal left: Controller::page() $flashNotice / $flashError), then the scripts.
// Every page gets school-dates.js (first: the others use it), pop-up.js (open / close pop-ups),
// question-pop-up.js, feedback-banner.js (the sliding banner), unsaved-changes.js (nothing typed is lost by accident),
// send-once.js (a form is sent once), session-timeout.js (a warning before the idle sign-out) and sidebar.js;
// $scripts adds the page's own (component JS files, without ".js").
?>
  </main>
</div>

<?php require dirname(__DIR__) . '/components/_question_pop_up.php'; ?>
<?php
$feedbackMessage = $flashError ?? $flashNotice;
$feedbackType    = $flashError !== null ? 'error' : 'success';
require dirname(__DIR__) . '/components/_feedback_banner.php';
?>

<?php foreach (['school-dates', 'pop-up', 'question-pop-up', 'feedback-banner', 'unsaved-changes', 'send-once', 'session-timeout', 'sidebar', ...$scripts] as $script): ?>
  <script src="<?= e(asset("js/components/{$script}.js")) ?>"></script>
<?php endforeach; ?>
</body>
</html>
