<?php
// Choosing a registered parent (the "Use Existing Parent (Sibling)" search): the list starts with the first 20
// parents; typing narrows it (the server searches: PeopleAddController::parents()); pick one. The chosen one shows
// alone with Change Parent.
// The form sends its id in a hidden field. Used by Add Student and the profile pop-up's Edit Profile (_field_box.php).
// Script: parent-picker.js · look: parent-picker.css (+ search-field.css, avatar, tag).
// Set first (cleared at the end):
//   $parentPicker  ['name' (the hidden field), 'row' (the chosen one: PeopleModel::parentRow(), or null),
//                   'url' (the search's address)]
$ppChosen = $parentPicker['row'];
?>
<div class="c-parent-picker j-parent-picker" data-url="<?= e($parentPicker['url']) ?>">
  <input type="hidden" class="j-parent-id" name="<?= e($parentPicker['name']) ?>" value="<?= e($ppChosen['id'] ?? '') ?>">

  <div class="c-parent-picker__chosen j-parent-chosen"<?= $ppChosen === null ? ' hidden' : '' ?>>
    <?php
    if ($ppChosen !== null)
    {
        $parentRow = $ppChosen;
        require __DIR__ . '/_parent_row.php';
    }
    ?>
    <button type="button" class="c-btn c-btn--outline c-btn--small j-parent-change">Change <?= e(word('parent')) ?></button>
  </div>

  <div class="c-parent-picker__search j-parent-search"<?= $ppChosen !== null ? ' hidden' : '' ?>>
    <span class="c-label"><?= e(words('Search Registered {Parents}')) ?></span>
    <label class="c-search c-search--small">
      <?= icon('search', 14) ?>
      <input type="search" class="c-search__input j-parent-query" placeholder="<?= e(words('Name, code, email, phone or a {student}’s name…')) ?>"
             autocomplete="off" aria-label="<?= e(words('Search registered {parents}')) ?>">
    </label>
    <p class="c-field__hint j-parent-count">Searching…</p>
    <div class="c-parent-picker__results j-parent-results" aria-live="polite"></div>
  </div>
</div>
<?php unset($parentPicker, $ppChosen); ?>
