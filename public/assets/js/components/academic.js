/**
 * The Academic Overview page (components/_academic_overview.php). PHP draws everything; saving is a normal form that
 * reloads the page (AcademicController), so this file only:
 *   1. the class editor: moves the page's one editor under the class whose ✎ was pressed (or to the end of a grade for
 *      "+ Add Class"), fills it in, and lets that class keep its own class teacher in the picker
 *   2. the stage editor: puts the page's one editor in place of the stage whose ✎ was pressed (or at the end for
 *      "+ Add Curriculum"), fills it in, keeps "To" ≥ "From", writes the range ("Grades 6–9"), adds / removes /
 *      renames subjects, and asks first when saving would remove subject teachers
 *   3. after a refused save: opens the editor again where it was, with what was typed (data-reopen)
 *   4. the teacher workload card next to a teacher picker's option while the mouse is on it
 * Subject teachers save in the background (background-save.js); deletes ask first (question-pop-up.js).
 */
(function ()
{
  'use strict';

  /* =========================================================================
     SECTION 1: THE CLASS EDITOR
     ========================================================================= */
  const classEditor = document.getElementById('j-class-editor');
  const teacherPicker = 'j-class-editor-teacher';

  // data: { id, grade_id, prefix ("6-"), section, capacity, teacher } — from the ✎ / "+ Add Class" button, or a refused save
  function openClassEditor(data)
  {
    const row = data.id ? document.getElementById(`class-${data.id}`) : null;

    row ? row.after(classEditor) : document.querySelector(`#grade-${data.grade_id} .c-grade-card__classes`).append(classEditor);

    classEditor.elements.id.value = data.id ?? '';
    classEditor.elements.grade_id.value = data.grade_id;
    classEditor.elements.section.value = data.section ?? '';
    classEditor.elements.capacity.value = data.capacity ?? '';
    classEditor.querySelector('.j-class-editor-prefix').textContent = data.prefix ?? '';

    // A teacher who already looks after a class is greyed out — except this class's own teacher
    document.querySelectorAll(`#${teacherPicker} .c-dropdown__option`).forEach(option =>
    {
      option.disabled = option.dataset.busy === 'true' && option.dataset.value !== String(data.teacher ?? '');
    });
    window.setDropdownValue(teacherPicker, data.teacher ?? '');

    classEditor.hidden = false;
    classEditor.elements.section.focus();
  }

  /* =========================================================================
     SECTION 2: THE STAGE EDITOR
     ========================================================================= */
  const stageEditor = document.getElementById('j-stage-editor');
  const chips = stageEditor.querySelector('.j-stage-chips');
  const newSubject = stageEditor.querySelector('.j-stage-new-subject');
  let hiddenCard = null;   // the stage card the editor stands in for while it is open
  let assigned = [];       // that stage's subject teachers: [[grade number, subject id], …]
  let confirmed = false;   // "yes" was said to losing some of them

  // "Grade 8", or "Grades 6–9" (the words come from the page: data-grade / data-grades)
  function showRange()
  {
    const label = stageEditor.querySelector('.j-stage-range');
    const first = Number(window.getDropdownValue('j-stage-first_grade'));
    let last = Number(window.getDropdownValue('j-stage-last_grade'));

    if (last < first)
    {
      window.setDropdownValue('j-stage-last_grade', first);
      last = first;
    }

    label.textContent = first === last ? `${label.dataset.grade} ${first}` : `${label.dataset.grades} ${first}–${last}`;
  }

  // A chip as wide as its name (size = characters)
  function fitChip(nameBox)
  {
    nameBox.size = Math.max(nameBox.value.length, 1);
  }

  // id = the subject's id ('' for a new one): a renamed subject keeps it, so its teachers and marks stay
  function addChip(name, id = '')
  {
    const taken = [...chips.querySelectorAll('.j-stage-chip-name')].some(box => box.value.toLowerCase() === name.toLowerCase());
    if (name === '' || taken) return;

    const chip = document.getElementById('j-stage-chip').content.firstElementChild.cloneNode(true);
    const nameBox = chip.querySelector('.j-stage-chip-name');

    chip.querySelector('input[type="hidden"]').value = id;
    nameBox.value = name;
    fitChip(nameBox);
    chips.append(chip);
  }

  function closeStageEditor()
  {
    stageEditor.hidden = true;
    if (hiddenCard) hiddenCard.hidden = false;
    hiddenCard = null;
  }

  // data: { id, first_grade, last_grade, description, subjects: [{ id, name }], assigned } — from ✎,
  // "+ Add Curriculum" ({}), or a refused save
  function openStageEditor(data)
  {
    closeStageEditor();
    hiddenCard = data.id ? document.getElementById(`stage-${data.id}`) : null;

    if (hiddenCard)
    {
      hiddenCard.before(stageEditor);
      hiddenCard.hidden = true;
    }
    else
    {
      stageEditor.parentElement.append(stageEditor);
    }

    stageEditor.elements.id.value = data.id ?? '';
    stageEditor.elements.description.value = data.description ?? '';
    // A new stage starts at the first grade number
    const lowest = stageEditor.querySelector('#j-stage-first_grade .c-dropdown__option').dataset.value;
    window.setDropdownValue('j-stage-first_grade', data.first_grade || lowest);
    window.setDropdownValue('j-stage-last_grade', data.last_grade || lowest);
    showRange();

    chips.replaceChildren();
    (data.subjects ?? []).forEach(subject => addChip(subject.name, subject.id ?? ''));
    newSubject.value = '';
    assigned = data.assigned ?? [];
    confirmed = false;

    stageEditor.hidden = false;
    stageEditor.scrollIntoView({ block: 'nearest' });
  }

  // "+ Add" or Enter in the box adds what was typed (Enter must not send the whole form)
  function addTypedSubject()
  {
    addChip(newSubject.value.trim());
    newSubject.value = '';
    newSubject.focus();
  }

  newSubject.addEventListener('keydown', (e) =>
  {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    addTypedSubject();
  });

  stageEditor.addEventListener('dropdown:change', showRange);
  chips.addEventListener('input', (e) => fitChip(e.target));
  chips.addEventListener('keydown', (e) =>
  {
    if (e.key !== 'Enter') return;
    e.preventDefault();   // Enter in a chip finishes the rename, it doesn't save the form
    e.target.blur();
  });

  // Saving would drop subject teachers (a class now outside the range, or a subject taken out) → ask first
  stageEditor.addEventListener('submit', (e) =>
  {
    const first = Number(window.getDropdownValue('j-stage-first_grade'));
    const last = Number(window.getDropdownValue('j-stage-last_grade'));
    const kept = [...chips.querySelectorAll('input[type="hidden"]')].map(input => input.value).filter(Boolean);
    const lost = assigned.filter(([grade, subject]) => grade < first || grade > last || !kept.includes(String(subject))).length;

    if (lost === 0 || confirmed) return;

    e.preventDefault();
    window.askQuestion({
      title: stageEditor.dataset.shrinkTitle,
      text: lost === 1 ? stageEditor.dataset.shrinkOne : lost + stageEditor.dataset.shrinkMany,
      button: 'Save Anyway',
      icon: 'alertTriangle',
      tone: 'warning',
      onConfirm: () =>
      {
        confirmed = true;
        stageEditor.requestSubmit();
      },
    });
  });

  /* =========================================================================
     SECTION 3: THE TEACHER WORKLOAD CARD (beside the open menu, on the side with room)
     ========================================================================= */
  function hideWorkload()
  {
    document.querySelectorAll('.c-teacher-workload').forEach(card => card.hidden = true);
  }

  function showWorkload(option)
  {
    const card = document.getElementById(`j-workload-${option.dataset.value}`);
    hideWorkload();
    if (!card) return;

    const menu = option.closest('.c-dropdown__menu').getBoundingClientRect();
    card.hidden = false;

    const width = card.offsetWidth;
    const left = menu.right + 8 + width <= window.innerWidth ? menu.right + 8 : menu.left - 8 - width;
    const top = Math.min(option.getBoundingClientRect().top, window.innerHeight - card.offsetHeight - 8);

    card.style.left = `${Math.max(left, 8)}px`;
    card.style.top = `${Math.max(top, 8)}px`;
  }

  document.addEventListener('mouseover', (e) =>
  {
    const option = e.target.closest('.c-dropdown--teacher .c-dropdown__option');
    option && option.dataset.value !== '' ? showWorkload(option) : hideWorkload();
  });

  /* =========================================================================
     SECTION 4: CLICKS + START
     ========================================================================= */
  document.addEventListener('click', (e) =>
  {
    const classButton = e.target.closest('.j-class-edit, .j-class-add');
    const stageButton = e.target.closest('.j-stage-edit, [data-stage-add]');

    if (classButton)
    {
      e.preventDefault();   // ✎ sits in the row's <summary>: don't open / close the row too
      openClassEditor(JSON.parse(classButton.dataset.class));
    }
    else if (stageButton)
    {
      openStageEditor(stageButton.dataset.stage ? JSON.parse(stageButton.dataset.stage) : {});
    }
    else if (e.target.closest('.j-class-editor-cancel'))
    {
      classEditor.hidden = true;
    }
    else if (e.target.closest('.j-stage-editor-cancel'))
    {
      closeStageEditor();
    }
    else if (e.target.closest('.j-stage-add-subject'))
    {
      addTypedSubject();
    }
    else if (e.target.closest('.j-stage-remove-subject'))
    {
      e.target.closest('.c-curriculum-card__chip').remove();
    }
  });

  // Remember which class-teacher options PHP greyed out (openClassEditor turns one back on for the class's own teacher)
  document.querySelectorAll(`#${teacherPicker} .c-dropdown__option`).forEach(option =>
  {
    option.dataset.busy = String(option.disabled);
  });

  if (classEditor.dataset.reopen) openClassEditor(JSON.parse(classEditor.dataset.reopen));
  if (stageEditor.dataset.reopen) openStageEditor(JSON.parse(stageEditor.dataset.reopen));
})();
