<?php
// One number card (dashboards, achievements, Parent → Approvals). Its look: metric-card.css; how the cards line up:
// layout/metric-cards-grid.css. Set $card before requiring it:
//   'color'   sand | maroon | sunshine | lightblue | moss
//   'icon'    an icon name from icons.php, e.g. 'graduationCap'
//   'value'   the big number, already formatted ('2,845', '84.6%')
//   'label'   the words under it
//   'valueId' (optional) an id on the number, for a page script that changes it live
//   'href'    (optional) the card is a link that filters the page (Parent → Approvals): white, and
//             filled in its colour only when 'chosen' => true
// The cards fade in one after another (metric-card.css), so no delay is passed.
$cardTag     = isset($card['href']) ? 'a' : 'div';
$cardClasses = 'c-metric-card'
    . (isset($card['href']) ? ' c-metric-card--tab' : '')
    . (!isset($card['href']) || !empty($card['chosen']) ? " c-metric-card--{$card['color']}" : '');
?>
<<?= e($cardTag) ?> class="<?= e($cardClasses) ?>"<?php if (isset($card['href'])): ?> href="<?= e($card['href']) ?>"<?= !empty($card['chosen']) ? ' aria-current="page"' : '' ?><?php endif; ?>>
  <div class="c-metric-card__top">
    <span class="c-metric-card__icon" aria-hidden="true">
      <?= icon($card['icon'], 20) ?>
    </span>
  </div>
  <p class="c-metric-card__value"<?= isset($card['valueId']) ? ' id="' . e($card['valueId']) . '"' : '' ?>><?= e($card['value']) ?></p>
  <p class="c-label c-metric-card__label"><?= e($card['label']) ?></p>
</<?= e($cardTag) ?>>
<?php unset($card, $cardTag, $cardClasses); ?>
