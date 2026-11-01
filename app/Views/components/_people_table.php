<?php
// The People table: one row per person, its columns by role. Its look: people-table.css.
// Account access is a status dropdown that saves in the background (background-save.js → PeopleController::status;
// people-directory.js recolours it). View opens the profile pop-up (people-profile.js); Edit opens it already editing.
// Set before requiring:
//   $tableRole   'student' | 'teacher' | 'parent' | 'management'
//   $tableView   'office' (admin, management: status + View / Edit) · 'teacher' (their students, view only, parent columns)
//   $rows        PeopleModel::directory() rows   $lists  PeopleModel::rowLists() for them
// From the page: $pageUrl, $csrfField.
$tableTone   = config("people.tones.{$tableRole}");
$tableIsMine = $tableView === 'teacher';
$tableHeads  = match (true)
{
    $tableIsMine                 => [word('student') . ' Name', 'Reg. Number', 'Extra-Curricular Activities', words('{Parent} Email'), words("{Parent}'s Name"), 'Contact Number', 'View'],
    $tableRole === 'student'     => [word('student'), 'Reg. Number', 'Extra-Curricular Activities', words('{Parent} Email'), 'Account Access', 'Actions'],
    $tableRole === 'teacher'     => [word('teacher'), 'ID', words('{Class} {Teacher} Of'), 'Contact Info', 'Account Access', 'Actions'],
    $tableRole === 'parent'      => [word('parent'), 'ID', words('Linked {Students}'), 'Contact Info', 'Account Access', 'Actions'],
    default                      => ['Staff Member', 'ID', 'Contact Info', 'Account Access', 'Actions'],
};
?>
<div class="c-people-table-wrap">
  <table class="c-people-table c-people-table--<?= e($tableTone) ?>">
    <thead>
      <tr>
        <?php foreach ($tableHeads as $head): ?>
          <th scope="col"><?= e($head) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row): ?>
        <?php $rowLists = $lists[$row['id']] ?? []; ?>
        <tr>
          <td>
            <div class="c-people-table__person">
              <?php $tableAvatar = 'c-avatar' . ($tableRole === 'student' ? ' c-avatar--alabaster' : " c-avatar--row c-avatar--{$tableTone}"); ?>
              <?php if ($row['photo_url'] !== null): ?>
                <img class="<?= e($tableAvatar) ?>" src="<?= e($row['photo_url']) ?>" alt="">
              <?php else: ?>
                <span class="<?= e($tableAvatar) ?>" aria-hidden="true"><?= e(initials($row['full_name'])) ?></span>
              <?php endif; ?>
              <div>
                <span class="c-people-table__name"><?= e($row['full_name']) ?></span>
                <?php if ($tableRole === 'parent' && $row['relationship'] !== null): ?>
                  <p class="c-people-table__sub"><?= e($row['relationship']) ?></p>
                <?php elseif ($tableRole === 'management' && $row['job_title'] !== null): ?>
                  <p class="c-people-table__sub"><?= e($row['job_title']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </td>

          <td class="c-people-table__code"><?= e($row['code'] ?? '—') ?></td>

          <?php if ($tableRole === 'student'): ?>
            <td>
              <div class="c-people-table__tags">
                <?php foreach ($rowLists['extracurriculars'] ?? [] as $extracurricular): ?>
                  <span class="c-tag c-tag--soft c-tag--<?= $extracurricular['type'] === 'sport' ? 'terracotta' : 'sky' ?>"><?= e($extracurricular['name']) ?></span>
                <?php endforeach; ?>
                <?php if (empty($rowLists['extracurriculars'])): ?>
                  <span class="c-people-table__none">None</span>
                <?php endif; ?>
              </div>
            </td>
            <td class="c-people-table__contact"><?= $row['parent_email'] !== null ? icon('mail', 14) . e($row['parent_email']) : '—' ?></td>
            <?php if ($tableIsMine): ?>
              <td><?= e($row['parent_name'] ?? '—') ?></td>
              <td class="c-people-table__contact"><?= e($row['parent_phone'] ?? '—') ?></td>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($tableRole === 'teacher'): ?>
            <?php // Only the class they look after; what they teach and the clubs they run are in their profile (owner) ?>
            <td>
              <?php if (isset($rowLists['homeroom'])): ?>
                <span class="c-tag c-tag--soft c-tag--sunshine"><?= e($rowLists['homeroom']) ?></span>
              <?php else: ?>
                <span class="c-people-table__none">None</span>
              <?php endif; ?>
            </td>
          <?php endif; ?>

          <?php if ($tableRole === 'parent'): ?>
            <td>
              <div class="c-people-table__tags">
                <?php foreach ($rowLists['children'] ?? [] as $child): ?>
                  <span class="c-chip c-people-table__child"><?= e($child['name'] . ($child['class_name'] !== null ? " — {$child['class_name']}" : '')) ?></span>
                <?php endforeach; ?>
              </div>
            </td>
          <?php endif; ?>

          <?php if ($tableRole !== 'student'): ?>
            <td class="c-people-table__contact">
              <p><?= icon('mail', 14) ?><?= e($row['identifier']) ?></p>
              <?php if ($row['phone'] !== null): ?>
                <p><?= icon('phone', 14) ?><?= e($row['phone']) ?></p>
              <?php endif; ?>
            </td>
          <?php endif; ?>

          <?php if (!$tableIsMine): ?>
            <td>
              <?php // Switch on / off in the background; a pending account (no password yet) shows "Pending" while on ?>
              <form method="post" action="<?= e("{$pageUrl}/status") ?>" data-background>
                <?= $csrfField ?>
                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                <?php
                $isOn          = $row['activation_status'] !== 'INACTIVE';
                $dropdownId    = "j-status-{$row['id']}";
                $dropdownLabel = "Account access for {$row['full_name']}";
                $options       = [['value' => 'on', 'label' => $row['activation_status'] === 'PENDING' ? 'Pending' : 'Active'], ['value' => 'off', 'label' => 'Deactivated']];
                $selectedValue = $isOn ? 'on' : 'off';
                $name          = 'status';
                $variant       = 'status';
                $tone          = $isOn ? 'active' : 'deactivated';
                require __DIR__ . '/_dropdown.php';
                ?>
              </form>
            </td>
          <?php endif; ?>

          <td>
            <div class="c-people-table__actions">
              <?php // Icons only (owner); the words stay for the mouse (title) and screen readers (aria-label) ?>
              <button type="button" class="c-btn c-btn--icon j-person-view" data-url="<?= e("{$pageUrl}/profile?id={$row['id']}") ?>"
                      title="View" aria-label="<?= e("View {$row['full_name']}") ?>"><?= icon('eye', 16) ?></button>
              <?php if (!$tableIsMine): ?>
                <button type="button" class="c-btn c-btn--icon j-person-view" data-url="<?= e("{$pageUrl}/profile?id={$row['id']}") ?>" data-edit
                        title="Edit" aria-label="<?= e("Edit {$row['full_name']}") ?>"><?= icon('edit', 16) ?></button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php unset($tableTone, $tableIsMine, $tableHeads, $tableAvatar, $head, $row, $rowLists, $extracurricular, $child, $isOn, $tableRole, $tableView); ?>
