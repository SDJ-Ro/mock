<?php
// One detail in a small white box: its icon + label and its value. A detail the person may change also has its
// box(es) (_field_box.php, 'card' look), shown instead of the value while a surrounding element has
// data-editing="true" (info-card.css).
// Used by My Profile (_profile_page.php), People (a class's details) and People's profile pop-up (_person_profile.php).
// Set first:
//   $card            ['label', 'icon' (or null: no icon), 'value', 'edit', 'raw', 'wide' (a full row)], and to edit:
//                      'edit'    the field its box saves ('first_name,last_name' = one box each), or null
//                      'raw'     the box's value (a list for several boxes)
//                      'box'     its kind (default 'text'; see _field_box.php), 'options' for a dropdown
//                      'editOnly' true = the card shows only while editing (People: the parent search, qualification rows)
//   $infoCardForm    the id of the form a text box is sent with (a date / choice box must sit inside its form)
//   $infoCardVariant '' · 'on-tint' (inside a tinted section: see-through white) · 'compact' (People: cream, small value)
//                    · 'rounded' (People's pop-up)
$infoCardClasses = 'c-info-card'
    . ($card['wide'] ? ' c-info-card--wide' : '')
    . ($card['edit'] !== null ? ' c-info-card--editable' : '')
    . (!empty($card['editOnly']) ? ' c-info-card--edit-only' : '')
    . ($infoCardVariant !== '' ? " c-info-card--{$infoCardVariant}" : '');
?>
<div class="<?= e($infoCardClasses) ?>">
  <p class="c-label c-info-card__label"><?= $card['icon'] !== null ? icon($card['icon'], 14) : '' ?><?= e($card['label']) ?></p>
  <p class="c-info-card__value"><?= e($card['value']) ?></p>
  <?php if ($card['edit'] !== null): ?>
    <div class="c-info-card__box">
      <?php
      $infoCardNames = explode(',', $card['edit']);

      foreach ($infoCardNames as $infoCardIndex => $infoCardField)
      {
          $fieldBox        = [
              'name'    => $infoCardField,
              'box'     => $card['box'] ?? 'text',
              'value'   => count($infoCardNames) > 1 ? $card['raw'][$infoCardIndex] : $card['raw'],
              'label'   => count($infoCardNames) > 1 ? ucfirst(str_replace('_', ' ', $infoCardField)) : $card['label'],
              'options' => $card['options'] ?? [],
          ];
          $fieldBoxVariant = 'card';
          $fieldBoxForm    = $infoCardForm;
          require __DIR__ . '/_field_box.php';
      }
      ?>
    </div>
  <?php endif; ?>
</div>
<?php unset($infoCardClasses, $infoCardNames, $infoCardIndex, $infoCardField, $fieldBoxVariant, $fieldBoxForm); ?>
