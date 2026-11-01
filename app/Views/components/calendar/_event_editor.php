<?php
// The "Add Event" / "Edit Event" pop-up, only for roles that may add events.
// It sits in the shared pop-up frame (_pop_up.php). Its files: css + js components/calendar/event-editor (staff dashboards' lists).
// event-editor.js sends this form as it is to CalendarController, so every field's name is what the controller reads.
// The lists come from CalendarModel::choicesFor() — the controller only accepts what was offered here.
$role      = $calendarConfig['role'];
$choices   = $calendarConfig['choices'];
$ownOnly   = !$calendarConfig['anyGroup'];   // only their own classes (a teacher): no whole grades to choose

// [id => name] → the dropdown's [['value' => id, 'label' => name], …]
$asOptions = fn (array $pairs): array => array_map(fn ($id, $name): array => ['value' => (string) $id, 'label' => $name], array_keys($pairs), $pairs);

// The school's words (config/white_label/wording.php): "grades" / "classes" for a school, "years" / "courses" for a university
$gradesWord  = strtolower(plural('grade'));
$classesWord = strtolower(plural('class'));

$audienceOptions = array_map(fn (string $who): array => ['value' => $who, 'label' => $who === 'all' ? 'All' : plural($who)], config("calendar.audience.{$role}"));

// The time dropdowns: hours 00–23 ("All Day" first on the start hour), minutes 00–59
$hours   = array_map(fn (int $h): string => sprintf('%02d', $h), range(0, 23));
$minutes = array_map(fn (int $m): string => sprintf('%02d', $m), range(0, 59));

// The pop-up's own part: the form. It goes into the shared shell (components/_pop_up.php) at the end of this file.
ob_start();
?>
    <form class="c-pop-up__form c-event-form c-fields--calendar" id="j-event-form" action="/<?= e($role) ?>/calendar/save" data-delete-url="/<?= e($role) ?>/calendar/delete" novalidate>
      <?= $csrfField ?>
      <input type="hidden" name="id" id="j-field-id" value="" />
      <input type="hidden" name="event_date" id="j-field-date" value="" />

      <div class="c-pop-up__body c-event-form__body">
        <div class="c-banner c-banner--error" id="j-event-form-error-banner" hidden></div>

        <!-- Category: decides who the event can be for -->
        <div>
          <span class="c-label c-field__label">Category</span>
          <?php
          $dropdownId    = 'j-field-category';
          $dropdownLabel = 'Category';
          $options       = config("calendar.categories.{$role}");
          $selectedValue = $options[0];
          $name          = 'category';
          require dirname(__DIR__) . '/_dropdown.php';
          ?>
        </div>

        <!-- Academic: whole grades or single classes (teachers: only their own classes) -->
        <div id="j-scope-academic-section" class="c-event-form__scope-box" <?= $ownOnly ? '' : 'hidden' ?>>
          <?php if ($ownOnly): ?>
            <input type="hidden" name="academic_level" value="class" />
          <?php else: ?>
            <span class="c-label c-field__label">Academic Scope Level</span>
            <div class="c-event-form__radio-group">
              <label class="c-field__choice">
                <input type="radio" name="academic_level" value="grade" id="j-academic-level-grade" checked class="c-field__radio">
                <span><?= e(words('Entire {grade}(s)')) ?></span>
              </label>
              <label class="c-field__choice">
                <input type="radio" name="academic_level" value="class" id="j-academic-level-class" class="c-field__radio">
                <span><?= e(words('Specific {class}(es)')) ?></span>
              </label>
            </div>

            <div id="j-academic-grades-container">
              <span class="c-label c-field__label"><?= e(words('Select {Grade}(s)')) ?></span>
              <?php
              $dropdownId    = 'j-select-academic-grades';
              $dropdownLabel = "Select {$gradesWord}";
              $placeholder   = "Select {$gradesWord}…";
              $options       = $asOptions($choices['grades']);
              $selectedValue = [];
              $name          = 'grades[]';
              $isMultiSelect = true;
              require dirname(__DIR__) . '/_dropdown.php';
              ?>
            </div>
          <?php endif; ?>

          <div id="j-academic-classes-container" <?= $ownOnly ? '' : 'hidden' ?>>
            <span class="c-label c-field__label"><?= e(words($ownOnly ? 'Select Your {Class}(es)' : 'Select {Class}(es)')) ?></span>
            <?php
            $dropdownId    = 'j-select-academic-classes';
            $dropdownLabel = "Select {$classesWord}";
            $placeholder   = "Select {$classesWord}…";
            $options       = $asOptions($choices['classes']);
            $selectedValue = [];
            $name          = 'classes[]';
            $isMultiSelect = true;
            require dirname(__DIR__) . '/_dropdown.php';
            ?>
          </div>
        </div>

        <!-- Extracurricular: clubs and sports (teachers: the ones they run) -->
        <div id="j-scope-extracurricular-section" class="c-event-form__scope-box" hidden>
          <span class="c-label c-field__label">Select Club(s) or Sport(s)</span>
          <?php
          $dropdownId    = 'j-select-extracurriculars';
          $dropdownLabel = 'Select clubs or sports';
          $placeholder   = 'Select clubs or sports…';
          $options       = $asOptions($choices['extracurriculars']);
          $selectedValue = [];
          $name          = 'extracurriculars[]';
          $isMultiSelect = true;
          require dirname(__DIR__) . '/_dropdown.php';
          ?>
        </div>

        <div>
          <span class="c-label c-field__label">Target Audience</span>
          <?php
          $dropdownId    = 'j-field-audience';
          $dropdownLabel = 'Target audience';
          $placeholder   = 'Select audience…';
          $options       = $audienceOptions;
          $selectedValue = [$audienceOptions[0]['value']];
          $name          = 'audience[]';
          $isMultiSelect = true;
          require dirname(__DIR__) . '/_dropdown.php';
          ?>
        </div>

        <div class="c-time-range-group">
          <span class="c-label c-field__label">Scheduled Time Window</span>
          <div class="c-time-range-row">
            <div class="c-time-range-col">
              <span class="c-label c-time-range-col__label">Start Time</span>
              <div class="c-time-picker">
                <?php
                $dropdownId    = 'j-start-hour';
                $dropdownLabel = 'Start hour';
                $options       = array_merge([['value' => '', 'label' => 'All Day']], $hours);   // '' = all day (the default)
                $selectedValue = '';
                $name          = 'start_hour';
                $variant       = 'time';
                require dirname(__DIR__) . '/_dropdown.php';
                ?>
                <span class="c-time-picker__colon" id="j-start-colon" hidden>:</span>
                <div id="j-start-minute-wrap" hidden>
                  <?php
                  $dropdownId    = 'j-start-minute';
                  $dropdownLabel = 'Start minute';
                  $options       = $minutes;
                  $selectedValue = '00';
                  $name          = 'start_minute';
                  $variant       = 'time';
                  require dirname(__DIR__) . '/_dropdown.php';
                  ?>
                </div>
              </div>
            </div>
            <span class="c-time-separator" id="j-time-separator" hidden>to</span>
            <div class="c-time-range-col" id="j-time-end-col" hidden>
              <span class="c-label c-time-range-col__label">End Time</span>
              <div class="c-time-picker">
                <?php
                $dropdownId    = 'j-end-hour';
                $dropdownLabel = 'End hour';
                $options       = $hours;
                $selectedValue = '10';
                $name          = 'end_hour';
                $variant       = 'time';
                require dirname(__DIR__) . '/_dropdown.php';
                ?>
                <span class="c-time-picker__colon">:</span>
                <?php
                $dropdownId    = 'j-end-minute';
                $dropdownLabel = 'End minute';
                $options       = $minutes;
                $selectedValue = '00';
                $name          = 'end_minute';
                $variant       = 'time';
                require dirname(__DIR__) . '/_dropdown.php';
                ?>
              </div>
            </div>
          </div>
          <p class="c-field__error" id="j-field-time-error" hidden>End time must be later than start time.</p>
        </div>

        <div>
          <label class="c-label c-field__label" for="j-field-title">Event Title</label>
          <input class="c-field__input" id="j-field-title" name="title" placeholder="e.g. Mathematics examination" type="text" maxlength="150" />
          <p class="c-field__error" id="j-field-title-error" hidden>Enter an event title.</p>
        </div>

        <div>
          <label class="c-label c-field__label" for="j-field-details">Details or Location</label>
          <textarea class="c-field__input c-field__input--area" id="j-field-details" name="details" placeholder="e.g. <?= e(plural('grade')) ?> 6–8 · Respective classrooms" maxlength="500"></textarea>
        </div>

        <!-- Shown when the server finds a clash: the warning, and the box that saves it anyway -->
        <div class="c-banner c-banner--warning" id="j-event-form-warning-banner" hidden></div>
        <div class="c-event-form__parallel-box" id="j-parallel-container" hidden>
          <input type="checkbox" id="j-field-allow-parallel" name="allow_parallel" value="1" class="c-field__checkbox" />
          <label for="j-field-allow-parallel" class="c-event-form__parallel-label">Allow parallel schedule for this time slot</label>
        </div>
      </div>

      <footer class="c-pop-up__footer c-event-form__footer">
        <div>
          <button type="button" class="c-btn c-event-editor__delete-btn" id="j-event-editor-delete-btn" hidden>
            <?= icon('trash', 14) ?>
            Delete Event
          </button>
        </div>
        <div class="c-event-form__actions">
          <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
          <button type="submit" class="c-btn c-btn--solid">
            <?= icon('calendarPlus', 15) ?>
            <span id="j-event-form-submit-label">Save Event</span>
          </button>
        </div>
      </footer>
    </form>
<?php
$popUpContent     = ob_get_clean();
$popUpId          = 'j-event-editor';
$popUpIcon        = 'calendarPlus';
$popUpTitle       = 'Add Event';   // event-editor.js: "Edit Event" when editing; the eyebrow = the event's date
$popUpDescription = 'Configure scheduling, scopes, and target audience.';
$popUpSize        = 'wide';
$popUpVariant     = 'event-editor';
require dirname(__DIR__) . '/_pop_up.php';
?>
