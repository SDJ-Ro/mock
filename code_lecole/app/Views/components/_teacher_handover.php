<?php
// The teacher handover, inside People's handover pop-up: switching off a teacher who
// still has duties. Laid out like every other pop-up (Add Event, Add Grade): the teacher, the warning, then one field
// per duty (their class, each subject, each club / sport) — its label + a tag, and the teacher dropdown
// ("Assignment pending" leaves it for the Academic page). Drawn by PeopleController::handoverForm() and sent as JSON;
// people-directory.js opens it. A normal form: PeopleController::handover() passes the duties on, switches them off
// and reloads the page. Its look: pop-up.css (body, footer), banner.css, form-field.css, tag.css, dropdown.css;
// teacher-handover.css only the teacher's line and a label with its tag.
//   $person    the teacher (PeopleModel::find())      $action  where the form goes      $csrfField  the form's token
//   $rows      [['field', 'title', 'tag', 'tone', 'options'], …] (PeopleController::handoverRows())
?>
<form class="c-pop-up__form" method="post" action="<?= e($action) ?>">
  <?= $csrfField ?>
  <input type="hidden" name="id" value="<?= e($person['id']) ?>">

  <div class="c-pop-up__body">
    <div class="c-handover__who">
      <span class="c-avatar c-avatar--medium c-avatar--sunshine" aria-hidden="true"><?= e(initials($person['full_name'])) ?></span>
      <div>
        <p class="c-handover__name"><?= e($person['full_name']) ?></p>
        <p class="c-handover__meta"><?= e(implode(' · ', array_filter([$person['subjects'], $person['code']]))) ?></p>
      </div>
    </div>

    <p class="c-banner c-banner--warning">
      <?= icon('alertTriangle', 18) ?>
      <span><?= e(words('This {teacher} still has duties. Pass each one to a colleague, or leave it as "Assignment pending" to fill in later on the Academic page.')) ?></span>
    </p>

    <?php foreach ($rows as $row): ?>
      <div class="c-field">
        <span class="c-handover__label">
          <span class="c-label"><?= e($row['title']) ?></span>
          <span class="c-tag c-tag--<?= e($row['tone']) ?>"><?= e($row['tag']) ?></span>
        </span>
        <?php
        $dropdownId    = "j-handover-{$row['field']}";
        $dropdownLabel = $row['title'] . ' ' . $row['tag'];
        $options       = $row['options'];
        $selectedValue = '';
        $name          = $row['field'];
        require __DIR__ . '/_dropdown.php';
        ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="c-pop-up__footer">
    <button type="button" class="c-btn c-btn--ghost j-pop-up-close">Cancel</button>
    <button type="submit" class="c-btn c-btn--outline" name="clear" value="1">Clear Roles &amp; Switch Off</button>
    <button type="submit" class="c-btn c-btn--solid"><?= icon('userCheck', 14) ?>Reassign &amp; Switch Off</button>
  </div>
</form>
<?php unset($row); ?>
