<?php
// The "Post New Notice" / "Edit Notice" form, shown by the notice board page instead of the board
// (?post or ?edit=5). It is a normal form: the server fills it in, checks it, saves it and goes back to the board
// (or shows this form again with the reason and what was typed). Built from shared pieces: the page box
// (_form_card.php), the fields (form-field.css, sky look), the banners (banner.css); add-notice.css is only its layout.
// Its script (add-notice.js) only shows the target field that fits the category and the chosen file's name.
// Set before requiring:
//   $noticeForm  (from NoticeController)
//     'id'        null = a new notice, else the notice being edited
//     'values'    title, body, category, audience (a list), target ('all' | 'grade:5' | 'class:9'), extracurricular ('all' | id),
//                 publish_at / expires_at ('YYYY-MM-DD' or ''), pinned (bool), attachment_name (a saved file, or null)
//     'choices'   categories (this role's), audience (['all', 'student', …]), grades / classes / extracurriculars ([id => name]),
//                 schoolWide (may this person post to everyone? teachers: only their own classes and clubs)
//     'banner'    the blue box's sentence (it says when a notice waits for an admin's approval)
//     'canPin'    false when the notice will wait (or waits) for approval: only approved notices can be pinned
//     'error'     why the last try was refused, or null
//     'maxFileMb' the attachment's size limit
// From the page: $currentRole, $csrfField.
$noticeIsNew   = $noticeForm['id'] === null;
$noticeValues  = $noticeForm['values'];
$noticeChoices = $noticeForm['choices'];
$noticeBoard   = "/{$currentRole}/notice";

// "Target Grade / Class Section": everyone, a whole grade or one class (values 'all', 'grade:5', 'class:9')
$noticeTargets = $noticeChoices['schoolWide'] ? [['value' => 'all', 'label' => 'All ' . plural('grade') . ' (' . word('institution') . '-Wide)']] : [];
foreach ($noticeChoices['grades'] as $id => $gradeName)
{
    $noticeTargets[] = ['value' => "grade:{$id}", 'label' => "{$gradeName} (All Sections)"];
}
foreach ($noticeChoices['classes'] as $id => $className)
{
    $noticeTargets[] = ['value' => "class:{$id}", 'label' => word('class') . " {$className}"];
}

// "Target Club / Sport Programme": every club, or one
$noticeClubs = $noticeChoices['schoolWide'] ? [['value' => 'all', 'label' => 'All Extracurriculars (' . word('institution') . '-Wide)']] : [];
foreach ($noticeChoices['extracurriculars'] as $id => $clubName)
{
    $noticeClubs[] = ['value' => (string) $id, 'label' => $clubName];
}

// The form itself (the page box around it — Back, white card, sky header: components/_form_card.php)
ob_start();
?>
  <form class="c-fields--sky" id="j-add-notice" method="post" action="<?= e("{$noticeBoard}/save") ?>" enctype="multipart/form-data" novalidate data-warn-unsaved>
    <?= $csrfField ?>
    <?php if (!$noticeIsNew): ?>
      <input type="hidden" name="id" value="<?= e($noticeForm['id']) ?>">
    <?php endif; ?>

    <div class="c-add-notice__body">
      <div class="c-banner c-banner--info">
        <?= icon('info', 20) ?>
        <span><?= e($noticeForm['banner']) ?></span>
      </div>

      <?php if ($noticeForm['error'] !== null): ?>
        <p class="c-banner c-banner--error" role="alert"><?= e($noticeForm['error']) ?></p>
      <?php endif; ?>

      <div class="c-add-notice__grid">
        <div class="c-field c-add-notice__wide">
          <label class="c-label c-field__label" for="j-add-notice-title">Notice Title <span class="c-field__required">*</span></label>
          <input type="text" class="c-field__input" id="j-add-notice-title" name="title" value="<?= e($noticeValues['title']) ?>"
                 placeholder="<?= e(words('e.g. End of {Term} Examinations Schedule')) ?>" required minlength="3" maxlength="200">
        </div>

        <div class="c-field">
          <span class="c-label c-field__label">Category <span class="c-field__required">*</span></span>
          <?php
          $dropdownId    = 'j-add-notice-category';
          $dropdownLabel = 'Category';
          $options       = $noticeChoices['categories'];
          $selectedValue = $noticeValues['category'];
          $name          = 'category';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <div class="c-field">
          <span class="c-label c-field__label">Target Audience <span class="c-field__required">*</span></span>
          <?php
          $dropdownId    = 'j-add-notice-audience';
          $dropdownLabel = 'Target Audience';
          $placeholder   = 'Select audience…';
          $options       = array_map(fn (string $who): array => ['value' => $who, 'label' => $who === 'all' ? 'All' : plural($who)], $noticeChoices['audience']);
          $selectedValue = $noticeValues['audience'];
          $name          = 'audience[]';
          $isMultiSelect = true;
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <?php // Academic notices: a grade or a class · Extracurricular notices: a club / sport (add-notice.js swaps them) ?>
        <div class="c-field c-add-notice__wide" id="j-add-notice-academic" <?= $noticeValues['category'] === 'Academic' ? '' : 'hidden' ?>>
          <span class="c-label c-field__label">Target <?= e(word('grade')) ?> / <?= e(word('class')) ?> Section</span>
          <?php
          $dropdownId    = 'j-add-notice-target';
          $dropdownLabel = 'Target ' . word('grade') . ' / ' . word('class');
          $placeholder   = 'Select a ' . strtolower(word('grade')) . ' or ' . strtolower(word('class'));
          $options       = $noticeTargets;
          $selectedValue = $noticeValues['target'];
          $name          = 'target';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <div class="c-field c-add-notice__wide" id="j-add-notice-club" <?= $noticeValues['category'] === 'Extracurricular' ? '' : 'hidden' ?>>
          <span class="c-label c-field__label">Target Club / Sport Programme</span>
          <?php
          $dropdownId    = 'j-add-notice-extracurricular';
          $dropdownLabel = 'Target club or sport';
          $placeholder   = 'Select a club or sport';
          $options       = $noticeClubs;
          $selectedValue = $noticeValues['extracurricular'];
          $name          = 'extracurricular';
          require __DIR__ . '/_dropdown.php';
          ?>
        </div>

        <div class="c-field">
          <span class="c-label c-field__label">Publish Date (Optional)</span>
          <?php
          $datepickerId  = 'j-add-notice-publish';
          $name          = 'publish_at';
          $selectedValue = $noticeValues['publish_at'];
          $placeholder   = 'Publish immediately (or select date)';
          $minDate       = date('Y-m-d');
          require __DIR__ . '/_datepicker.php';
          ?>
        </div>

        <div class="c-field">
          <span class="c-label c-field__label">Expiration Date (Optional)</span>
          <?php
          $datepickerId  = 'j-add-notice-expires';
          $name          = 'expires_at';
          $selectedValue = $noticeValues['expires_at'];
          $placeholder   = 'Never expires (or select date)';
          $minDate       = date('Y-m-d');
          require __DIR__ . '/_datepicker.php';
          ?>
        </div>

        <div class="c-field c-add-notice__wide">
          <label class="c-label c-field__label" for="j-add-notice-body">Message Body <span class="c-field__required">*</span></label>
          <textarea class="c-field__input c-field__input--area" id="j-add-notice-body" name="body" rows="6"
                    placeholder="Write the notice here (minimum 10 characters)…" required minlength="10" maxlength="5000"><?= e($noticeValues['body']) ?></textarea>
        </div>

        <div class="c-field c-add-notice__wide">
          <span class="c-label c-field__label">Attachment (Optional)</span>
          <?php // The whole box is the file input's label: clicking it opens the file chooser (no script needed) ?>
          <label class="c-field__file">
            <input type="file" class="c-field__hidden-input j-add-notice-file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <?= icon('paperclip', 24) ?>
            <span class="c-field__file-name" id="j-add-notice-file-name"
                  data-empty="Click to upload a file"><?= e($noticeValues['attachment_name'] ?? 'Click to upload a file') ?></span>
            <span class="c-field__hint">PDF, DOCX, JPG or PNG (max <?= e($noticeForm['maxFileMb']) ?>MB)</span>
          </label>
        </div>

        <div class="c-add-notice__footer">
          <?php if ($noticeForm['canPin']): ?>
            <label class="c-field__choice">
              <input type="checkbox" class="c-field__checkbox" name="pinned" value="1" <?= $noticeValues['pinned'] ? 'checked' : '' ?>>
              Pin the notice
            </label>
          <?php endif; ?>

          <button type="submit" class="c-btn c-btn--solid c-btn--pill c-btn--large">
            <?= icon('send', 16) ?><span><?= $noticeIsNew ? 'Publish Notice' : 'Save Changes' ?></span>
          </button>
        </div>
      </div>
    </div>
  </form>
<?php
$formCardContent  = ob_get_clean();
$formCardBack     = $noticeBoard;
$formCardTone     = 'sky';
$formCardTitle    = $noticeIsNew ? 'Post New Notice' : 'Edit Notice';
$formCardSubtitle = $noticeIsNew
    ? 'Write a notice and publish it to the Notice Board.'
    : 'Change the notice or who it is for.';
require __DIR__ . '/_form_card.php';

unset($noticeForm, $noticeIsNew, $noticeValues, $noticeChoices, $noticeBoard, $noticeTargets, $noticeClubs, $id, $gradeName, $className, $clubName);
?>
