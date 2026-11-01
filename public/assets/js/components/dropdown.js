/**
 * Dropdown (components/_dropdown.php), for every dropdown on every page.
 * Opening and closing (the menu shows while the dropdown has .c-is-open), choosing an option, multi-select chips,
 * the keyboard, and keeping the menu inside the window. Every change tells the page with a 'dropdown:change' event
 * (detail: value, label / values); inside a <form data-submit-on-change> a choice also sends that form.
 * In a form: a must-choose dropdown stops the send while empty; emptying the form (reset) shows its first value again.
 *
 * For other scripts:
 *   setDropdownValue(id, value)              → choose an option (a list of values on a multi-select)
 *   getDropdownValue(id)                     → the chosen value (a list on a multi-select)
 *   setDropdownMultiValues(id, values)       → choose these options on a multi-select
 *   setDropdownOptions(id, options, chosen)  → swap the whole list (['A', 'B'] or [{value, label}]), then choose one
 *   resetDropdown(id)                        → back to nothing chosen (its placeholder shows again)
 *   getSafeViewportBounds()                  → the window area a pop-up may use (right of the sidebar)
 */
(function ()
{
  'use strict';

  const byIdOrElement = (target) => typeof target === 'string' ? document.getElementById(target) : target;
  const isMulti = (root) => root.dataset.multi === 'true';
  const optionsOf = (root) => [...root.querySelectorAll('.c-dropdown__option')];
  const labelOf = (option) => option.dataset.label ?? option.textContent.trim();   // without an option's small second line
  const TICK = '<svg class="c-icon c-dropdown__tick" width="14" height="14" aria-hidden="true"><use href="#icon-check"/></svg>';

  /* =========================================================================
     SECTION 1: KEEPING THE MENU INSIDE THE WINDOW
     ========================================================================= */
  // The area a menu may use: right of the sidebar, 12px from every window edge
  function getSafeViewportBounds()
  {
    const sidebar = document.querySelector('.c-sidebar');
    const sidebarRight = sidebar && getComputedStyle(sidebar).display !== 'none' ? sidebar.getBoundingClientRect().right : 0;

    return {
      minLeft: Math.max(sidebarRight + 12, 12),
      maxRight: window.innerWidth - 12,
      minTop: 12,
      maxBottom: window.innerHeight - 12
    };
  }

  function clearMenuPosition(menu)
  {
    menu.style.left = '';
    menu.style.right = '';
    menu.classList.remove('c-dropdown__menu--end', 'c-dropdown__menu--up');
  }

  // Nudge the menu away from the sidebar / right edge, and open it upwards when there is no room below
  function placeMenu(menu, trigger)
  {
    const bounds = getSafeViewportBounds();
    const menuBox = menu.getBoundingClientRect();
    const triggerBox = trigger.getBoundingClientRect();

    if (menuBox.left < bounds.minLeft)
    {
      menu.style.left = `${Math.max(0, bounds.minLeft - triggerBox.left)}px`;   // a worked-out number: no class can hold it
      menu.style.right = 'auto';
    }
    else if (menuBox.right > bounds.maxRight)
    {
      menu.classList.add('c-dropdown__menu--end');
    }

    const menuHeight = menuBox.height || 85;

    if (bounds.maxBottom - triggerBox.bottom < menuHeight && triggerBox.top - bounds.minTop >= menuHeight)
    {
      menu.classList.add('c-dropdown__menu--up');
    }
  }

  /* =========================================================================
     SECTION 2: OPEN AND CLOSE
     ========================================================================= */
  function closeAllDropdowns()
  {
    document.querySelectorAll('.c-dropdown.c-is-open').forEach(root =>
    {
      root.classList.remove('c-is-open');
      root.querySelector('.c-dropdown__trigger').setAttribute('aria-expanded', 'false');
      clearMenuPosition(root.querySelector('.c-dropdown__menu'));
    });
  }

  function openDropdown(root)
  {
    const trigger = root.querySelector('.c-dropdown__trigger');
    const menu = root.querySelector('.c-dropdown__menu');

    closeAllDropdowns();
    root.classList.add('c-is-open');
    trigger.setAttribute('aria-expanded', 'true');

    placeMenu(menu, trigger);
  }

  /* =========================================================================
     SECTION 3: SINGLE SELECT
     ========================================================================= */
  // Marks one option as chosen, shows its text in the trigger, and fills the hidden input the form sends
  function showSingleValue(root, option)
  {
    const value = root.querySelector('.c-dropdown__value');
    const input = root.querySelector('input[type="hidden"]');

    optionsOf(root).forEach(opt =>
    {
      opt.classList.toggle('c-is-selected', opt === option);
      opt.setAttribute('aria-selected', opt === option ? 'true' : 'false');
    });

    value.textContent = labelOf(option);
    value.classList.remove('c-dropdown__placeholder');

    if (input)
    {
      input.value = option.dataset.value;
    }
  }

  /* =========================================================================
     SECTION 4: MULTI SELECT (chips in the trigger, one hidden input per choice)
     ========================================================================= */
  function optionLabel(root, value)
  {
    const option = optionsOf(root).find(opt => opt.dataset.value === value);
    return option ? labelOf(option) : value;
  }

  // One chip: the option's text and a × to remove it (built as elements, so names can't add HTML)
  function chip(root, value)
  {
    const label = optionLabel(root, value);
    const chipEl = document.createElement('span');
    const text = document.createElement('span');
    const remove = document.createElement('span');

    chipEl.className = 'c-chip';
    text.textContent = label;
    remove.className = 'c-chip__remove j-chip-remove';
    remove.setAttribute('role', 'button');
    remove.setAttribute('aria-label', `Remove ${label}`);
    remove.tabIndex = 0;
    remove.dataset.val = value;
    remove.innerHTML = '<svg class="c-icon" width="10" height="10" aria-hidden="true"><use href="#icon-close"/></svg>';
    chipEl.append(text, remove);
    return chipEl;
  }

  // Marks the chosen options, draws their chips, and writes one hidden input per value (name="grades[]" …)
  function showMultiValues(root, values)
  {
    optionsOf(root).forEach(opt =>
    {
      const chosen = values.includes(opt.dataset.value);
      opt.classList.toggle('c-is-selected', chosen);
      opt.setAttribute('aria-selected', chosen ? 'true' : 'false');
    });

    root.querySelector('.j-dropdown-chips').replaceChildren(...values.map(value => chip(root, value)));
    root.querySelector('.j-dropdown-placeholder').hidden = values.length > 0;
    root.querySelector('.j-dropdown-hidden-inputs').replaceChildren(...values.map(value =>
    {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = root.dataset.name;
      input.value = value;
      return input;
    }));

    root.dispatchEvent(new CustomEvent('dropdown:change', { detail: { value: values.join(','), values }, bubbles: true }));
  }

  // The "All" option (if the list has one) stands alone: choosing it clears the others, choosing another clears it,
  // and nothing left means "All" again
  function allValueOf(root)
  {
    return root.querySelector('[data-value="all"], [data-value="All"]')?.dataset.value;
  }

  function toggleMultiValue(root, value, choose)
  {
    const allValue = allValueOf(root);
    let values = optionsOf(root).filter(opt => opt.classList.contains('c-is-selected')).map(opt => opt.dataset.value);
    choose = typeof choose === 'boolean' ? choose : !values.includes(value);

    if (value === allValue)
    {
      values = choose ? [allValue] : values;
    }
    else
    {
      values = values.filter(v => v !== allValue && v !== value);
      if (choose) values.push(value);
      if (allValue && values.length === 0) values = [allValue];
    }

    showMultiValues(root, values);
  }

  /* =========================================================================
     SECTION 5: CLICKS AND KEYS (one listener each for the whole page)
     ========================================================================= */
  document.addEventListener('click', function (e)
  {
    const remove = e.target.closest('.j-chip-remove');
    const trigger = e.target.closest('.c-dropdown__trigger');
    const option = e.target.closest('.c-dropdown__option');

    // A chip's × (inside the trigger, so checked first)
    if (remove)
    {
      e.preventDefault();
      e.stopPropagation();
      toggleMultiValue(remove.closest('.c-dropdown'), remove.dataset.val, false);
      return;
    }

    if (trigger)
    {
      e.preventDefault();
      e.stopPropagation();
      const root = trigger.closest('.c-dropdown');

      if (root.classList.contains('c-is-open'))
      {
        closeAllDropdowns();
      }
      else
      {
        openDropdown(root);
      }
      return;
    }

    if (option)
    {
      e.preventDefault();
      e.stopPropagation();
      const root = option.closest('.c-dropdown');

      if (isMulti(root))
      {
        toggleMultiValue(root, option.dataset.value);   // the menu stays open to pick more
        return;
      }

      showSingleValue(root, option);
      closeAllDropdowns();
      root.querySelector('.c-dropdown__trigger').focus();
      root.dispatchEvent(new CustomEvent('dropdown:change', { detail: { value: option.dataset.value, label: labelOf(option), id: root.id }, bubbles: true }));

      // A filter form that reloads the page on every choice (<form data-submit-on-change>: People, Audit Log)
      root.closest('form[data-submit-on-change]')?.submit();
      return;
    }

    // A click anywhere else closes any open dropdown
    closeAllDropdowns();
  });

  // ↓ / ↑ on the trigger open the menu on the chosen option; inside the menu they move from option to option
  function moveThroughOptions(root, step)
  {
    const options = optionsOf(root);

    if (!root.classList.contains('c-is-open'))
    {
      openDropdown(root);
      (options.find(opt => opt.classList.contains('c-is-selected')) || options[0])?.focus();
      return;
    }

    const current = options.indexOf(document.activeElement);
    const next = current === -1 ? 0 : Math.min(options.length - 1, Math.max(0, current + step));
    options[next]?.focus();
  }

  document.addEventListener('keydown', function (e)
  {
    const root = e.target.closest('.c-dropdown');

    if (e.key === 'Escape')
    {
      closeAllDropdowns();
      root?.querySelector('.c-dropdown__trigger').focus();
      return;
    }

    if (root && (e.key === 'ArrowDown' || e.key === 'ArrowUp'))
    {
      e.preventDefault();
      moveThroughOptions(root, e.key === 'ArrowDown' ? 1 : -1);
      return;
    }

    // Enter / Space on the multi-select's trigger and on a chip's × (they are not real buttons)
    const fakeButton = e.target.closest('.j-chip-remove, div.c-dropdown__trigger');

    if (fakeButton && (e.key === 'Enter' || e.key === ' '))
    {
      e.preventDefault();
      fakeButton.click();
    }
  });

  /* =========================================================================
     SECTION 6: IN A FORM
     ========================================================================= */
  // A must-choose dropdown (_dropdown.php's $required) with nothing chosen stops its form and shows its red line.
  // Caught on window, on the way down, so nothing else (an example-data form's banner) sees the send.
  window.addEventListener('submit', function (e)
  {
    const empty = [...e.target.querySelectorAll('.c-dropdown[data-required]')].filter(root => getDropdownValue(root) === '');

    if (empty.length === 0)
    {
      return;
    }

    e.preventDefault();
    e.stopPropagation();
    empty.forEach(root => root.querySelector('.j-dropdown-error').hidden = false);
    empty[0].querySelector('.c-dropdown__trigger').focus();
  }, true);

  // Choosing hides the red line
  document.addEventListener('dropdown:change', function (e)
  {
    const error = e.target.querySelector(':scope > .j-dropdown-error');

    if (error)
    {
      error.hidden = true;
    }
  });

  // Emptying a form (after sending, or Discard Changes) puts each dropdown back on its first value. The browser can't:
  // a hidden input ignores reset (setting its value changes its first value too), so the first values are kept here.
  const startValues = new Map([...document.querySelectorAll('.c-dropdown:not([data-multi]) > input[type="hidden"]')]
    .map(input => [input, input.value]));

  document.addEventListener('reset', function (e)
  {
    e.target.querySelectorAll('.c-dropdown:not([data-multi])').forEach(root =>
    {
      const input = root.querySelector(':scope > input[type="hidden"]');
      const error = root.querySelector(':scope > .j-dropdown-error');

      if (input && startValues.has(input))   // a dropdown the form doesn't send (no name) keeps its choice
      {
        startValues.get(input) === '' ? resetDropdown(root) : setDropdownValue(root, startValues.get(input));
      }

      if (error)
      {
        error.hidden = true;
      }
    });
  });

  /* =========================================================================
     SECTION 7: FOR OTHER SCRIPTS
     ========================================================================= */
  function setDropdownMultiValues(dropdownId, values)
  {
    const root = byIdOrElement(dropdownId);
    if (!root) return;

    let list = [].concat(values ?? []).map(String);
    if (list.length === 0 && allValueOf(root)) list = [allValueOf(root)];
    showMultiValues(root, list);
  }

  function setDropdownValue(dropdownId, value)
  {
    const root = byIdOrElement(dropdownId);
    if (!root) return;

    if (isMulti(root))
    {
      setDropdownMultiValues(root, value);
      return;
    }

    const option = optionsOf(root).find(opt => opt.dataset.value === String(value ?? ''));
    if (option) showSingleValue(root, option);
  }

  function getDropdownValue(dropdownId)
  {
    const root = byIdOrElement(dropdownId);
    if (!root) return '';

    const chosen = optionsOf(root).filter(opt => opt.classList.contains('c-is-selected')).map(opt => opt.dataset.value);
    return isMulti(root) ? chosen : (chosen[0] ?? '');
  }

  function resetDropdown(dropdownId)
  {
    const root = byIdOrElement(dropdownId);
    if (!root) return;

    if (isMulti(root))
    {
      showMultiValues(root, []);
      return;
    }

    const value = root.querySelector('.c-dropdown__value');
    const input = root.querySelector('input[type="hidden"]');

    optionsOf(root).forEach(opt =>
    {
      opt.classList.remove('c-is-selected');
      opt.setAttribute('aria-selected', 'false');
    });
    value.textContent = root.dataset.placeholder;
    value.classList.add('c-dropdown__placeholder');

    if (input)
    {
      input.value = '';
    }
  }

  // The same option markup as _dropdown.php (text built as text, so names can't add HTML)
  function setDropdownOptions(dropdownId, options, chosen)
  {
    const root = byIdOrElement(dropdownId);
    if (!root) return;

    root.querySelector('.c-dropdown__menu').replaceChildren(...options.map(opt =>
    {
      const value = String(typeof opt === 'object' ? (opt.value ?? opt.label) : opt);
      const option = document.createElement('button');
      const label = document.createElement('span');
      const text = document.createElement('span');

      option.type = 'button';
      option.className = 'c-dropdown__option';
      option.dataset.value = value;
      option.dataset.label = typeof opt === 'object' ? (opt.label ?? value) : value;
      option.setAttribute('role', 'option');
      option.setAttribute('aria-selected', 'false');
      label.className = 'c-dropdown__label';
      text.textContent = option.dataset.label;
      label.append(text);
      option.append(label);
      option.insertAdjacentHTML('beforeend', TICK);
      return option;
    }));

    resetDropdown(root);
    if (chosen !== undefined) setDropdownValue(root, chosen);
  }

  window.setDropdownValue = setDropdownValue;
  window.getDropdownValue = getDropdownValue;
  window.setDropdownMultiValues = setDropdownMultiValues;
  window.setDropdownOptions = setDropdownOptions;
  window.resetDropdown = resetDropdown;
  window.getSafeViewportBounds = getSafeViewportBounds;
})();
