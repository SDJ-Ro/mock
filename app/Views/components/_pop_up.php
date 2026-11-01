<?php
// The frame every pop-up shares (CSS: pop-up.css, JS: pop-up.js): the browser's own <dialog> — dark backdrop, white box,
// and for the 'panel' shape a header with icon, eyebrow, title, description and ×.
// The pop-up's own part (list, form + buttons…) is passed in as $popUpContent: start with ob_start(), write the HTML,
// then $popUpContent = ob_get_clean(); set the values below and require this file. Open it with openPopUp('{id}')
// or a <button data-pop-up-open="{id}">.
//
//   $popUpShape       'panel' (default): header bar (icon, eyebrow, title, description, ×) + the content
//                     'plain': no header bar — the content brings everything (the "Are you sure?" pop-up, Unread notices)
//   $popUpId          (required) the dialog's id; the title / eyebrow / description get "{id}-title" … so scripts can fill
//                     them (a plain pop-up gives its own heading the id "{id}-title")
//   $popUpTitle       the title (scripts may change it)
//   $popUpIcon        the header icon's name (icons.php); not set = no icon (e.g. New Feedback)
//   $popUpEyebrow     small text above the title (optional)
//   $popUpDescription small text under the title (optional)
//   $popUpSize       'normal' (32rem, default), 'wide' (36rem) or 'large' (56rem: People's profile)
//   $popUpVariant     the pop-up's own name → class c-pop-up--{variant} for its own CSS, e.g. 'event-editor'
//   $popUpOpenOnLoad  true → already open when the page loads (e.g. the form came back with an error)
$pId      = $popUpId;
$pClasses = 'c-pop-up'
          . (($popUpSize ?? 'normal') !== 'normal' ? " c-pop-up--{$popUpSize}" : '')
          . (isset($popUpVariant) ? ' c-pop-up--' . $popUpVariant : '');
?>

<dialog class="<?= e($pClasses) ?>" id="<?= e($pId) ?>" aria-labelledby="<?= e($pId) ?>-title"<?= empty($popUpOpenOnLoad) ? '' : ' data-open-on-load' ?>>
  <?php if (($popUpShape ?? 'panel') === 'panel'): ?>
    <header class="c-pop-up__header">
      <div class="c-pop-up__heading-group">
        <?php if (isset($popUpIcon)): ?>
          <span class="c-pop-up__icon-badge" aria-hidden="true">
            <?= icon($popUpIcon, 20) ?>
          </span>
        <?php endif; ?>
        <div>
          <p class="c-label c-pop-up__eyebrow" id="<?= e($pId) ?>-eyebrow"><?= e($popUpEyebrow ?? '') ?></p>
          <h2 class="c-pop-up__title" id="<?= e($pId) ?>-title"><?= e($popUpTitle ?? '') ?></h2>
          <p class="c-pop-up__description" id="<?= e($pId) ?>-description"><?= e($popUpDescription ?? '') ?></p>
        </div>
      </div>
      <button type="button" class="c-btn c-btn--close j-pop-up-close" aria-label="Close">
        <?= icon('close', 16) ?>
      </button>
    </header>
  <?php endif; ?>

  <?= $popUpContent ?>
</dialog>
<?php unset($popUpShape, $popUpId, $popUpTitle, $popUpIcon, $popUpEyebrow, $popUpDescription, $popUpSize, $popUpVariant, $popUpContent, $popUpOpenOnLoad); ?>
