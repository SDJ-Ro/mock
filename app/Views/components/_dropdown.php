<?php
// The dropdown used everywhere (a styled one, not the browser's <select>). dropdown.js opens it, picks options and sends 'dropdown:change'.
// Set these before requiring it (they are cleared at the end, so the next dropdown starts fresh):
//   $dropdownId    (required) the element id, also how scripts reach it
//   $options       ['Academic', 'General'] or [['value' => '5', 'label' => 'Grade 10'], …]; an option may also have
//                  'sub' (a small second line) and 'disabled' => true (shown, can't be chosen; 'sub' says why)
//   $selectedValue the chosen value ('' is a real choice when an option has value ''); a list for a multi-select
//   $placeholder   shown while nothing is chosen (default 'Select option')
//   $dropdownLabel for screen readers
//   $name          the form field it fills (a hidden input); 'grades[]' style for a multi-select
//   $prefix        a grey word before the value, e.g. 'Grade' → "Grade: 10" (filter bars)
//   $variant       its look:
//                    'default'  white pill (forms, profile)  'time'     the same, shorter list (hour / minute)
//                    'cream'    card-header filters         'calendar' the dark calendar's month / year
//                    'tinted'   form fields in the role's colour ($tone)
//                    'status'   Active / Deactivated in a table row ($tone)
//                    'toolbar'  small white filter (people directory)
//                    'tick'     the white pill with smaller options, the chosen one ticked (term marks' subject filter)
//                    'teacher'  the white pill whose options show initials, a name and a 'sub' line (Academic's teacher pickers)
//                    'small'    the light-sky box inside an info card while editing (People's Edit Profile)
//   $tone          'tinted': 'sky' | 'sunshine' | 'terracotta' | 'maroon'   'status': 'active' | 'deactivated'
//   $isMultiSelect true → several choices, shown as chips in the trigger
//   $required      a must-choose dropdown: the red line shown under it when its form is sent with nothing chosen
//                  ('Please select a purpose.'); dropdown.js stops the send
$dId      = $dropdownId;
$dLabel   = $dropdownLabel ?? 'Dropdown';
$dHolder  = $placeholder ?? 'Select option';
$dVal     = $selectedValue ?? null;
$dName    = $name ?? '';
$dPrefix  = $prefix ?? '';
$dVariant = $variant ?? 'default';
$dTone    = $tone ?? '';
$isMulti  = !empty($isMultiSelect);
$dMissing = $required ?? '';

// Every option as ['value' => …, 'label' => …], each value once
$formattedOptions = [];
foreach ($options ?? [] as $opt)
{
    $val = (string) (is_array($opt) ? ($opt['value'] ?? $opt['label']) : $opt);
    $formattedOptions[$val] ??= [
        'value'    => $val,
        'label'    => is_array($opt) ? ($opt['label'] ?? $val) : (string) $opt,
        'sub'      => is_array($opt) ? ($opt['sub'] ?? '') : '',
        'disabled' => is_array($opt) && !empty($opt['disabled']),
    ];
}
$formattedOptions = array_values($formattedOptions);
$labels           = array_column($formattedOptions, 'label', 'value');

$selectedValues = $isMulti ? array_map('strval', (array) ($dVal ?? [])) : [];
$hasSelected    = !$isMulti && $dVal !== null && isset($labels[(string) $dVal]);
$displayLabel   = $hasSelected ? $labels[(string) $dVal] : $dHolder;

$dClasses = 'c-dropdown c-dropdown--' . $dVariant
          . ($dTone !== '' ? ' c-dropdown--' . $dTone : '')
          . ($isMulti ? ' c-dropdown--multi' : '');
?>

<div class="<?= e($dClasses) ?>"
     id="<?= e($dId) ?>"
     aria-label="<?= e($dLabel) ?>"
     data-placeholder="<?= e($dHolder) ?>"
     <?= $isMulti ? 'data-multi="true"' : '' ?>
     <?= $dMissing !== '' ? 'data-required' : '' ?>
     <?= $dName !== '' ? 'data-name="' . e($dName) . '"' : '' ?>>

  <?php if ($isMulti): ?>
    <div class="j-dropdown-hidden-inputs">
      <?php foreach ($selectedValues as $sv): ?>
        <input type="hidden" name="<?= e($dName) ?>" value="<?= e($sv) ?>" />
      <?php endforeach; ?>
    </div>

    <div role="button" tabindex="0" class="c-dropdown__trigger" aria-haspopup="listbox" aria-expanded="false">
      <div class="c-dropdown__chips j-dropdown-chips">
        <?php foreach ($selectedValues as $sv): ?>
          <span class="c-chip">
            <span><?= e($labels[$sv] ?? $sv) ?></span>
            <span role="button" tabindex="0" class="c-chip__remove j-chip-remove" data-val="<?= e($sv) ?>" aria-label="Remove <?= e($labels[$sv] ?? $sv) ?>">
              <?= icon('close', 10) ?>
            </span>
          </span>
        <?php endforeach; ?>
      </div>
      <span class="c-dropdown__placeholder j-dropdown-placeholder" <?= $selectedValues !== [] ? 'hidden' : '' ?>><?= e($dHolder) ?></span>
      <?= icon('chevronDown', 14, 'c-dropdown__chevron') ?>
    </div>
  <?php else: ?>
    <?php if ($dName !== ''): ?>
      <input type="hidden" name="<?= e($dName) ?>" value="<?= e($hasSelected ? $dVal : '') ?>" />
    <?php endif; ?>

    <button type="button" class="c-dropdown__trigger" aria-haspopup="listbox" aria-expanded="false">
      <span class="c-dropdown__text">
        <?php if ($dPrefix !== ''): ?>
          <span class="c-dropdown__prefix"><?= e($dPrefix) ?>:</span>
        <?php endif; ?>
        <span class="c-dropdown__value<?= $hasSelected ? '' : ' c-dropdown__placeholder' ?>"><?= e($displayLabel) ?></span>
      </span>
      <?= icon('chevronDown', 14, 'c-dropdown__chevron') ?>
    </button>
  <?php endif; ?>

  <div class="c-dropdown__menu" role="listbox" aria-label="<?= e($dLabel) ?>">
    <?php foreach ($formattedOptions as $opt): ?>
      <?php $isSelected = $isMulti ? in_array($opt['value'], $selectedValues, true) : ($hasSelected && $opt['value'] === (string) $dVal); ?>
      <button type="button" class="c-dropdown__option<?= $isSelected ? ' c-is-selected' : '' ?>" <?= $opt['disabled'] ? 'disabled' : '' ?>
              data-value="<?= e($opt['value']) ?>" data-label="<?= e($opt['label']) ?>" role="option" aria-selected="<?= $isSelected ? 'true' : 'false' ?>">
        <?php if ($dVariant === 'teacher'): ?>
          <span class="c-avatar" aria-hidden="true"><?= e($opt['value'] === '' ? '–' : initials($opt['label'])) ?></span>
        <?php endif; ?>
        <span class="c-dropdown__label">
          <span><?= e($opt['label']) ?></span>
          <?php if ($opt['sub'] !== ''): ?>
            <span class="c-dropdown__sub"><?= e($opt['sub']) ?></span>
          <?php endif; ?>
        </span>
        <?= icon('check', 14, 'c-dropdown__tick') ?>
      </button>
    <?php endforeach; ?>
  </div>

  <?php if ($dMissing !== ''): ?>
    <p class="c-field__error j-dropdown-error" hidden><?= e($dMissing) ?></p>
  <?php endif; ?>
</div>
<?php unset($dropdownId, $dropdownLabel, $placeholder, $options, $selectedValue, $name, $prefix, $variant, $tone, $isMultiSelect, $required); ?>
