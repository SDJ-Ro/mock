<?php
// A button that opens a short list to pick one from: a programme card's "Change" (the teacher in charge; the list
// covers the card's photo), a student's "Assign" (a team; the list drops under the button) and the parent's
// "Viewing" in the sidebar (which child the portal shows; the list opens inside the sidebar). A <details>: the browser opens and
// closes it, no script. Picking sends the list's form (POST to 'action').
// Its look: pick-menu.css. Set before requiring (cleared at the end):
//   $pickMenu = ['variant' => 'change' | 'assign' | 'child', 'label' => the button's words, 'title' => what it does,
//                'options' => names, or ['value' => …, 'label' => …] rows, 'chosen' => the current value ('' = none),
//                'action' => where the form goes, optional: 'field' => the chosen value's name (default 'choice'),
//                'hidden' => [name => value] sent with it]
// From the page: $csrfField.
$pickField = $pickMenu['field'] ?? 'choice';
?>
<details class="c-pick-menu c-pick-menu--<?= e($pickMenu['variant']) ?>">
  <summary class="c-pick-menu__button" title="<?= e($pickMenu['title']) ?>">
    <?php if ($pickMenu['variant'] === 'change'): ?><?= icon('edit', 11) ?><?php endif; ?>
    <span><?= e($pickMenu['label']) ?></span>
    <?php if ($pickMenu['variant'] !== 'change'): ?><?= icon('chevronDown', 13) ?><?php endif; ?>
  </summary>
  <form class="c-pick-menu__list" method="post" action="<?= e($pickMenu['action']) ?>" aria-label="<?= e($pickMenu['title']) ?>">
    <?= $csrfField ?>
    <?php foreach ($pickMenu['hidden'] ?? [] as $pickHidden => $pickHiddenValue): ?>
      <input type="hidden" name="<?= e($pickHidden) ?>" value="<?= e($pickHiddenValue) ?>">
    <?php endforeach; ?>
    <?php foreach ($pickMenu['options'] as $pickOption): ?>
      <?php [$pickValue, $pickLabel] = is_array($pickOption) ? [(string) $pickOption['value'], $pickOption['label']] : [$pickOption, $pickOption]; ?>
      <button type="submit" class="c-pick-menu__option" name="<?= e($pickField) ?>" value="<?= e($pickValue) ?>" aria-pressed="<?= $pickValue === (string) $pickMenu['chosen'] ? 'true' : 'false' ?>">
        <span><?= e($pickLabel) ?></span>
        <?php if ($pickMenu['variant'] === 'assign'): ?><?= icon('chevronRight', 12) ?><?php endif; ?>
      </button>
    <?php endforeach; ?>
  </form>
</details>
<?php unset($pickMenu, $pickField, $pickHidden, $pickHiddenValue, $pickOption, $pickValue, $pickLabel); ?>
