<?php
// The message that slides in at the top of the page: "it worked" (green, leaves after a few
// seconds) or "it didn't" after a one-click action (red, stays until closed). A script shows one with
// showFeedbackBanner('…', 'success' | 'error' | 'info') (feedback-banner.js). PHP draws the empty stack and one banner
// as a <template>; the script copies it and fills in the words. Its look: feedback-banner.css.
// Every signed-in page has it: layouts/page_bottom.php requires it once, with what the last save or refusal left.
// Optional: $feedbackMessage — a message PHP already knows (after a save); the script shows it on load.
//           $feedbackType    — 'success' (default) or 'error' (the action was refused)
?>
<div class="c-feedback-banners" id="j-feedback-banners" aria-live="polite"<?php if (isset($feedbackMessage)): ?> data-message="<?= e($feedbackMessage) ?>" data-type="<?= e($feedbackType ?? 'success') ?>"<?php endif; ?>></div>
<template id="j-feedback-banner">
  <div class="c-feedback-banner" role="alert">
    <span class="c-feedback-banner__icon"><?= icon('check', 18) ?></span>
    <p class="c-feedback-banner__text"></p>
    <button type="button" class="c-btn c-btn--close j-feedback-close" aria-label="Dismiss notification"><?= icon('close', 16) ?></button>
  </div>
</template>
<?php unset($feedbackMessage, $feedbackType); ?>
