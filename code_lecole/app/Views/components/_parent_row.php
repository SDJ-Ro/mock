<?php
// One registered parent in the parent search: initials, name, code, how they are related,
// email · phone, their children, and "Select Parent". A button: picking it chooses them (parent-picker.js). Drawn for
// the search's answer (PeopleAddController::parents()) and for the chosen parent (_parent_picker.php).
// Its look: parent-picker.css.
//   $parentRow  PeopleModel::searchParents() / parentRow(): ['id', 'identifier', 'code', 'full_name', 'phone', 'relationship', 'children']
?>
<button type="button" class="c-parent-row j-parent-pick" data-id="<?= e($parentRow['id']) ?>">
  <span class="c-avatar c-avatar--row c-avatar--terracotta" aria-hidden="true"><?= e(initials($parentRow['full_name'])) ?></span>
  <span class="c-parent-row__text">
    <span class="c-parent-row__top">
      <span class="c-parent-row__name"><?= e($parentRow['full_name']) ?></span>
      <span class="c-tag c-tag--terracotta"><?= e($parentRow['code']) ?></span>
    </span>
    <span class="c-parent-row__meta"><?= e(implode(' · ', array_filter([$parentRow['relationship'], $parentRow['phone'], $parentRow['identifier']]))) ?></span>
    <?php if ($parentRow['children'] !== null): ?>
      <span class="c-parent-row__children"><?= e('Siblings: ' . $parentRow['children']) ?></span>
    <?php endif; ?>
  </span>
  <span class="c-parent-row__select"><?= e('Select ' . word('parent')) ?></span>
</button>
<?php unset($parentRow); ?>
