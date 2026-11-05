<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ClassModel;
use App\Models\PeopleModel;
use App\Services\AcademicWatch;
use App\Services\AchievementTimeline;
use App\Services\PersonDetails;
use App\Services\RecordBook;
use App\Services\PersonFields;
use App\Services\TeacherChoices;
use Core\Controller;
use Core\Validator;
use DomainException;

// People (the users directory): admin and management see every student, teacher and parent (the admin also the
// management accounts), filter and search them, switch accounts on / off and edit them; a teacher sees their own
// classes' students. Who may do what: config/permissions.php 'people'. Filters are a normal form: picking one reloads
// the page. View opens a person's profile in a pop-up (its HTML fetched from profile() / studentProfile()); Edit
// Profile saves through update() (the rules: App\Services\PersonFields). Switching off a teacher who still has duties
// opens the handover pop-up instead. Adding people: PeopleAddController (it shares guard() and tabs()).
class PeopleController extends Controller
{
    // A student's tabs in the pop-up: [name, the module it needs (config/white_label/features.php), or null]
    private const PROFILE_TABS = [
        'information'      => ['Information', null],
        'academics'        => ['Academics', 'term_marks'],
        'extracurriculars' => ['Extracurriculars', 'extracurricular'],
        'achievements'     => ['Achievements', 'achievements'],
    ];

    // GET /{role}/people?tab=student&grade=…&class=…&q=…&status=on|off&extracurricular=…&subject=…&relationship=…&page=…
    public function index(string $role): void
    {
        $user   = $this->guard($role);
        $tabs   = $this->tabs();
        $tab    = array_key_exists($this->queryText('tab'), $tabs) ? $this->queryText('tab') : 'student';
        $people = new PeopleModel();

        // The students tab: a grade (by default the first that has students), then all its classes or one of them
        $grades  = array_values(array_filter((new ClassModel())->grades(), fn (array $grade): bool => $grade['classes'] !== []));
        $first   = array_values(array_filter($grades, fn (array $grade): bool => $grade['students'] > 0))[0] ?? $grades[0] ?? null;
        $grade   = array_column($grades, null, 'id')[(int) $this->queryText('grade')] ?? $first;
        $class   = array_column($grade['classes'] ?? [], null, 'id')[(int) $this->queryText('class')] ?? null;

        $filters = [
            'q'                  => $this->queryText('q'),
            'status'             => in_array($this->queryText('status'), ['on', 'off'], true) ? $this->queryText('status') : '',
            'grade_id'           => $tab === 'student' && $class === null ? $grade['id'] ?? null : null,
            'class_id'           => $tab === 'student' ? $class['id'] ?? null : null,
            'extracurricular_id' => array_key_exists((int) $this->queryText('extracurricular'), $people->extracurricularChoices()) ? (int) $this->queryText('extracurricular') : null,
            'subject'            => in_array($this->queryText('subject'), $people->subjectChoices(), true) ? $this->queryText('subject') : '',
            'relationship'       => in_array($this->queryText('relationship'), config('people.relationships'), true) ? $this->queryText('relationship') : '',
        ];

        // The filters in force, as the page's links repeat them (each link changes one)
        $query = array_filter([
            'tab'             => $tab,
            'grade'           => $tab === 'student' ? $grade['id'] ?? null : null,
            'class'           => $filters['class_id'],
            'q'               => $filters['q'],
            'status'          => $filters['status'],
            'extracurricular' => $tab === 'student' ? $filters['extracurricular_id'] : null,
            'subject'         => $tab === 'teacher' ? $filters['subject'] : '',
            'relationship'    => $tab === 'parent' ? $filters['relationship'] : '',
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        $this->page("{$role}/people", $user, $this->listData($tab, $filters) + [
            'pageUrl'                => "/{$role}/people",
            'query'                  => $query,
            'tabs'                   => $tabs,
            'tab'                    => $tab,
            'grades'                 => $grades,
            'grade'                  => $grade,
            'class'                  => $class,
            'extracurricularChoices' => $people->extracurricularChoices(),
            'subjectChoices'         => $people->subjectChoices(),
        ]);
    }

    // GET /teacher/students?q=…&page=… — the students of the classes this teacher teaches, view only
    public function students(string $role): void
    {
        $user = $this->requireRole($role);

        if (!can('people.own_students'))
        {
            $this->refuse('You are not allowed to see this page.', "/{$role}", 403);
        }

        $search = $this->queryText('q');

        $this->page("{$role}/students", $user, $this->listData('student', ['q' => $search, 'teacher_id' => $user['id']]) + [
            'pageUrl' => "/{$role}/students",
            'query'   => array_filter(['q' => $search]),
        ]);
    }

    // POST /{role}/people/status — switch an account on or off; sent in the background, answered in JSON.
    // Refused: a parent while a child is still at the school (every student keeps an active
    // parent), a teacher who still looks after a class, subjects or clubs: the answer then carries 'handover',
    // the address of the handover pop-up (people-directory.js opens it).
    public function status(string $role): never
    {
        $user   = $this->guard($role);
        $back   = "/{$role}/people";
        $people = new PeopleModel();
        $person = $people->find((int) $this->postText('id', 11));
        $on     = $this->postText('status', 3) === 'on';

        // An unknown id and one this role doesn't manage get the same answer
        if ($person === null || !array_key_exists($person['role'], $this->tabs()))
        {
            $this->refuse('That account no longer exists.', $back, 404);
        }

        if (!in_array($this->postText('status', 3), ['on', 'off'], true))
        {
            $this->refuse('Please choose Active or Deactivated.', $back, 422);
        }

        if (!$on && $person['role'] === 'parent' && ($children = $people->activeChildren($person['id'])) > 0)
        {
            $this->refuse("{$person['full_name']} still has {$children} " . strtolower($children === 1 ? word('student') : plural('student'))
                . ' at the ' . strtolower(word('institution')) . '. Every ' . strtolower(word('student')) . ' needs an active ' . strtolower(word('parent')) . '.', $back, 422);
        }

        if (!$on && $person['role'] === 'teacher' && $this->handoverRows($people->duties($person['id']), []) !== [])
        {
            $this->sendJson(['ok' => false, 'message' => "Hand over {$person['full_name']}'s duties first.", 'handover' => "/{$role}/people/handover?id={$person['id']}"], 422);
        }

        $alsoChanged = $people->setActive($person['id'], $on, $user['id']);
        $this->log($user, $on ? 'ACCOUNT_SWITCHED_ON' : 'ACCOUNT_SWITCHED_OFF', $person['full_name'] . ($alsoChanged === [] ? '' : ' + ' . implode(', ', $alsoChanged)));

        $message = $person['full_name'] . ($on ? ' switched on.' : ' switched off.');

        if ($alsoChanged !== [])
        {
            $message .= ' ' . implode(', ', $alsoChanged) . ($on ? ' too.' : ' too (no other ' . strtolower(plural('student')) . ' at the ' . strtolower(word('institution')) . ').');
        }

        $this->sendJson(['ok' => true, 'message' => $message]);
    }

    // GET /{role}/people/profile?id=… — one person's profile for the pop-up (people-profile.js), as JSON { ok, html }:
    // a JSON answer also carries every refusal (signed out, wrong role), so nothing else lands in the pop-up
    public function profile(string $role): never
    {
        $this->guard($role);
        $person = (new PeopleModel())->find((int) $this->queryText('id', 11));

        // An unknown id and one this role doesn't manage get the same answer
        if ($person === null || !array_key_exists($person['role'], $this->tabs()))
        {
            $this->refuse('That account no longer exists.', "/{$role}/people", 404);
        }

        $this->sendProfile($person, $role);
    }

    // GET /teacher/students/profile?id=… — one of the teacher's own students (the classes they teach), same answer.
    // Only their class teacher sees the medical notes.
    public function studentProfile(string $role): never
    {
        $user   = $this->requireRole($role);
        $people = new PeopleModel();
        $person = $people->find((int) $this->queryText('id', 11));

        if (!can('people.own_students') || $person === null || $person['role'] !== 'student' || !$people->teaches($user['id'], $person['id']))
        {
            $this->refuse('That account no longer exists.', "/{$role}/students", 404);
        }

        $this->sendProfile($person, null, '', $people->isClassTeacherOf($user['id'], $person['id']) ? null : 'subject_teacher');
    }

    // POST /{role}/people/update — Edit Profile in the pop-up, sent in the background. The fields on this person's
    // cards (config/people.php 'profile'), each checked by its rules ('fields') and then problems(); a student may also
    // get another parent. Answer: JSON { ok, message, html } (the profile drawn again), or the first problem.
    public function update(string $role): never
    {
        $user   = $this->guard($role);
        $back   = "/{$role}/people";
        $people = new PeopleModel();
        $person = $people->find((int) $this->postText('id', 11));

        // An unknown id and one this role doesn't manage get the same answer
        if ($person === null || !array_key_exists($person['role'], $this->tabs()))
        {
            $this->refuse('That account no longer exists.', $back, 404);
        }

        $labels = $this->editable($person['role']);
        $values = [];

        foreach ($labels as $field => $label)
        {
            $values[$field] = $this->postText($field, 1000);
        }

        $problems = Validator::check($values, PersonFields::rules(array_keys($labels)), $labels)
            ?: PersonFields::problems($person['role'], $person['id'], $values, $people);

        // A student's main parent: one found by the parent search (not switched off); the same one = no change
        $parentId = $person['role'] === 'student' && $this->postText('parent_id', 11) !== '' ? (int) $this->postText('parent_id', 11) : null;

        if ($parentId !== null && $people->parentRow($parentId) === null)
        {
            $problems[] = 'Please choose a ' . strtolower(word('parent')) . ' from the search.';
        }

        // A teacher's qualifications: the rows as they are now (all of them, replacing the old list) — only when the
        // form had the rows (qualifications_sent), so a request without them can't wipe the list
        [$qualifications, $problem] = $person['role'] === 'teacher' && $this->postText('qualifications_sent', 1) === '1'
            ? PersonFields::qualifications($this->postList('qualification_degree'), $this->postList('qualification_institution'), $this->postList('qualification_year'))
            : [null, null];

        if ($problem !== null)
        {
            $problems[] = $problem;
        }

        if ($problems !== [])
        {
            $this->refuse(reset($problems), $back, 422);
        }

        // Empty optional fields are saved as "not given"; profiles get theirs, the role's details table the rest
        $columns = array_map(fn (string $value): ?string => $value === '' ? null : $value, $values);
        $profile = array_intersect_key($columns, array_flip(PeopleModel::PROFILE_COLUMNS));
        $parent  = $parentId === (int) ($person['parents'][0]['id'] ?? 0) ? null : $parentId;

        try
        {
            $people->update($person['id'], $person['role'], $profile, array_diff_key($columns, $profile), $parent, $qualifications);
        }
        catch (DomainException $full)
        {
            $this->refuse($full->getMessage(), $back, 422);   // the new class filled up meanwhile
        }

        $saved = $people->find($person['id']);
        $this->log($user, 'PERSON_UPDATED', $saved['full_name']);
        $this->sendProfile($saved, $role, "{$saved['full_name']} saved.");
    }

    // GET /{role}/people/handover?id=… — the handover pop-up for a teacher with duties, as JSON { ok, html }
    // (people-directory.js opens it when switching them off was refused)
    public function handoverForm(string $role): never
    {
        $this->guard($role);
        $people = new PeopleModel();
        $person = $this->leavingTeacher($people, (int) $this->queryText('id', 11), "/{$role}/people?tab=teacher");
        $rows   = $this->handoverRows($people->duties($person['id']), array_diff_key((new ClassModel())->teachers(), [$person['id'] => true]));

        ob_start();
        $this->view('components/_teacher_handover', [
            'person'    => $person,
            'rows'      => $rows,
            'action'    => "/{$role}/people/handover",
            'csrfField' => $this->csrfField(),
        ]);
        $this->sendJson(['ok' => true, 'html' => ob_get_clean()]);
    }

    // POST /{role}/people/handover — a teacher leaves: each duty goes to the teacher picked for it (or nobody yet:
    // "Assignment pending"; "Clear Roles" = nobody for all), then their account is switched off, all at once
    // (PeopleModel::handOver). The same checks as the Academic page's pickers. A normal form: the page reloads.
    public function handover(string $role): never
    {
        $user     = $this->guard($role);
        $back     = "/{$role}/people?tab=teacher";
        $people   = new PeopleModel();
        $classes  = new ClassModel();
        $person   = $this->leavingTeacher($people, (int) $this->postText('id', 11), $back);
        $teachers = array_diff_key($classes->teachers(), [$person['id'] => true]);
        $duties   = $people->duties($person['id']);
        $clear    = $this->postText('clear', 1) === '1';
        $max      = (int) config('white_label.academic.max_subjects_per_teacher');
        $taking   = [];   // teacher id → the different subjects they would teach

        foreach ($this->handoverRows($duties, $teachers) as $row)
        {
            $to = $clear ? null : ($this->postText($row['field'], 11) === '' ? null : (int) $this->postText($row['field'], 11));

            if ($to !== null && !isset($teachers[$to]))
            {
                $this->refuse('Please choose a ' . strtolower(word('teacher')) . ' from the list.', $back, 422);
            }

            if ($row['type'] === 'homeroom')
            {
                if ($to !== null && ($other = $classes->classTeacherOf($to)) !== null)
                {
                    $this->refuse("{$teachers[$to]['name']} is already the " . strtolower(word('class') . ' ' . word('teacher')) . " of {$other}.", $back, 422);
                }

                $duties['homeroom']['to'] = $to;
                continue;
            }

            foreach ($row['indexes'] as $index)
            {
                $duties[$row['type']][$index]['to'] = $to;

                if ($to !== null && $row['type'] === 'subjects')
                {
                    $item        = $duties['subjects'][$index];
                    $taking[$to] = array_unique(array_merge($taking[$to] ?? [], $classes->subjectsWith($to, (int) $item['class_id'], (int) $item['subject_id'])));
                }
            }
        }

        foreach ($taking as $to => $subjects)
        {
            if (count($subjects) > $max)
            {
                $this->refuse("That would give {$teachers[$to]['name']} more than {$max} different " . strtolower(plural('subject')) . '.', $back, 422);
            }
        }

        $people->handOver($person['id'], $duties);
        $this->log($user, 'TEACHER_HANDED_OVER', $person['full_name']);
        $this->setFlash('notice', "{$person['full_name']} switched off; duties handed over.");
        $this->redirect($back);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // The pop-up's HTML (components/_person_profile.php) inside JSON. $editorRole: the office role editing it, whose
    // addresses Edit Profile uses (null = view only: a teacher's view).
    // $partial: a student's profile with less in it (config/people.php 'partial'), or null (all of it). Public: a
    // programme's Teams & Roster shows its members' club view (ExtracurricularController::member()).
    public function sendProfile(array $person, ?string $editorRole, string $message = '', ?string $partial = null): never
    {
        ob_start();
        $this->view('components/_person_profile', $this->profileData($person, $editorRole, $partial) + ['csrfField' => $this->csrfField()]);
        $this->sendJson(['ok' => true, 'message' => $message, 'html' => ob_get_clean()]);
    }

    // What the pop-up shows: its header, the information sections (config/people.php 'profile': cards, or a list as
    // cards; with an $editorRole each card also gets its edit box), and for a student the record book, their clubs /
    // sports and their achievements. Public: the parent's Child profile shows the same (view only, $editorRole null).
    // $partial (view only): a student's profile with less in it (config/people.php 'partial').
    public function profileData(array $person, ?string $editorRole, ?string $partial = null): array
    {
        $canEdit = $editorRole !== null;
        $role    = $person['role'];
        $values  = PersonDetails::values($person);
        $leave   = $partial === null ? ['cards' => [], 'tabs' => []] : config("people.partial.{$partial}");
        $card    = fn (string $label, ?string $value, bool $wide = false, array $box = []): array =>
            $box + ['label' => $label, 'icon' => null, 'value' => ($value ?? '') !== '' ? $value : '—', 'edit' => null, 'raw' => '', 'wide' => $wide];

        $sections = [];

        foreach (config("people.profile.{$role}") as [$title, $icon, $columns, $content])
        {
            // A list's name, or each card's detail: left out by a partial profile?
            $kept = is_array($content)
                ? array_values(array_filter($content, fn (array $field): bool => !in_array($field[1], $leave['cards'], true)))
                : (in_array($content, $leave['cards'], true) ? [] : $content);

            if ($kept === [])
            {
                continue;   // everything in it is left out
            }

            $cards = is_array($kept)
                ? array_map(fn (array $field): array => $card(words($field[0]), $values[$field[1]] ?? '', ($field[2] ?? '') === 'wide',
                    $canEdit ? $this->editBox($field[3] ?? '', $person) : []), $kept)
                : $this->listCards($kept, $person, $card, $editorRole);

            $sections[] = ['title' => words($title), 'icon' => $icon, 'columns' => $columns, 'cards' => $cards];
        }

        $status = match ($person['activation_status'])
        {
            'INACTIVE' => ['Deactivated', 'terracotta'],
            'PENDING'  => ['Pending', 'sand'],
            default    => ['Active', 'moss'],
        };

        return [
            'id'       => $person['id'],
            'saveUrl'  => $canEdit ? "/{$editorRole}/people/update" : null,
            'header'   => [
                'role'    => $role,
                'tone'    => config("people.tones.{$role}"),
                'name'    => $person['full_name'],
                'photo'   => $person['photo_url'],
                'eyebrow' => match ($role)
                {
                    'student' => words('{Student} Profile'),
                    'teacher' => words('{Teacher} Profile'),
                    'parent'  => words('{Parent} / Guardian Profile'),
                    default   => word('management') . ' Profile',
                },
                'sub'     => match ($role)
                {
                    'student' => trim($values['grade'] . ($values['class'] ? ' · ' . word('class') . " {$values['class']}" : ''), ' ·'),
                    'teacher' => $values['subjects'] ?? '',
                    'parent'  => $values['relationship'] ?? '',
                    default   => $values['job_title'] ?? '',
                },
                'code'    => $values['code'],
                'status'  => $status,
            ],
            'sections' => $sections,
            'student'  => $role !== 'student' ? null : $this->studentTabs($person['id'], $editorRole, $leave['tabs']),
        ];
    }

    // A student's tabs whose module is on, and each one's data (null: that tab is switched off). The record book has
    // no Export PDF here ('book': RecordBook::shown()); the timeline adds the automatic achievements from their marks.
    // 'watch': their Academic Watch with where Lift Lock / Lock goes — only for who may lift it (management, admin), and
    // only while they are on watch.
    // $leave: the tabs a partial profile leaves out.
    private function studentTabs(int $id, ?string $editorRole, array $leave): array
    {
        $tabs  = array_map(fn (array $tab): string => $tab[0], array_filter(self::PROFILE_TABS, fn (array $tab): bool => $tab[1] === null || feature($tab[1])));
        $tabs  = array_diff_key($tabs, array_flip($leave));
        $books = isset($tabs['academics']) ? RecordBook::forStudent($id) : [];
        $watch = $editorRole !== null && feature('extracurricular') && can('extracurricular.watch') ? AcademicWatch::of($id) : null;

        return [
            'tabs'        => $tabs,
            'watch'       => $watch === null ? null : $watch + ['url' => "/{$editorRole}/extracurricular/watch"],
            'book'        => isset($tabs['academics']) ? RecordBook::shown($books, null) : null,
            'memberships' => isset($tabs['extracurriculars']) ? (new PeopleModel())->memberships($id) : null,
            'timeline'    => isset($tabs['achievements'])
                ? AchievementTimeline::byYear(AchievementTimeline::forStudent($id, $books))
                : null,
        ];
    }

    // A list section as cards: a teacher's subjects (subject → classes), qualifications, a parent's children,
    // a student's parents. Nothing yet → one card that says so. While editing ($editorRole), one more wide card shows:
    // the qualification rows, or the parent search to pick another main parent ('editOnly': hidden otherwise).
    private function listCards(string $list, array $person, callable $card, ?string $editorRole): array
    {
        $cards = [];

        if ($list === 'subjects')
        {
            $subjects = (new PeopleModel())->rowLists('teacher', [$person['id']])[$person['id']]['subjects'] ?? [];

            foreach ($subjects as $subject => $classes)
            {
                $cards[] = $card($subject, implode(', ', $classes));
            }
        }

        if ($list === 'qualifications')
        {
            foreach ($person['qualifications'] as $q)
            {
                $cards[] = $card($q['year_obtained'] !== null ? (string) $q['year_obtained'] : 'Qualification', $q['degree'] . ($q['institution'] ? " — {$q['institution']}" : ''));
            }
        }

        if ($list === 'children')
        {
            foreach ($person['children'] as $child)
            {
                $cards[] = $card(word('class') . ' ' . ($child['class_name'] ?? '—'), $child['full_name']);
            }
        }

        if ($list === 'parents')
        {
            foreach ($person['parents'] as $parent)
            {
                array_push($cards, $card('Name', $parent['full_name']), $card('Email', $parent['identifier']), $card('Mobile Number', $parent['phone']));
            }
        }

        $cards = $cards === [] ? [$card('None', 'Nothing added yet.', true)] : $cards;

        if ($editorRole !== null && $list === 'parents')
        {
            $main    = $person['parents'][0]['id'] ?? null;
            $cards[] = $card(words('Change the Main {Parent}'), '', true, ['edit' => 'parent_id', 'box' => 'parent', 'editOnly' => true,
                'raw' => ['row' => $main === null ? null : (new PeopleModel())->parentRow((int) $main), 'url' => "/{$editorRole}/people/parents"]]);
        }

        if ($editorRole !== null && $list === 'qualifications')
        {
            $cards[] = $card('Edit Qualifications', '', true, ['edit' => 'qualifications', 'box' => 'qualifications', 'editOnly' => true, 'raw' => $person['qualifications']]);
        }

        return $cards;
    }

    // A card's edit box from its field (config/people.php 'profile' → 'fields'): [] when the card isn't edited.
    // Its value as saved (dates as YYYY-MM-DD; a list for the two name boxes) and a dropdown's options.
    private function editBox(string $edit, array $person): array
    {
        if ($edit === '')
        {
            return [];
        }

        $names = explode(',', $edit);

        return [
            'edit'    => $edit,
            'raw'     => count($names) > 1 ? array_map(fn (string $name): string => (string) $person[$name], $names) : (string) $person[$edit],
            'box'     => config('people.fields')[$names[0]][0],
            'options' => PersonFields::options($names[0], $person['class_id'] === null ? null : (int) $person['class_id']),
        ];
    }

    // The fields Edit Profile changes for this role (the cards' 4th item in config/people.php 'profile'):
    // [field => its label, for the messages]
    private function editable(string $role): array
    {
        $labels = [];

        foreach (config("people.profile.{$role}") as [, , , $content])
        {
            foreach (is_array($content) ? $content : [] as $card)
            {
                $names = array_filter(explode(',', $card[3] ?? ''));

                foreach ($names as $name)
                {
                    $labels[$name] = count($names) > 1 ? ucfirst(str_replace('_', ' ', $name)) : words($card[0]);
                }
            }
        }

        return $labels;
    }

    // The teacher a handover is for: unknown, not a teacher, or a role without the teachers tab → the same answer
    private function leavingTeacher(PeopleModel $people, int $id, string $back): array
    {
        $person = $people->find($id);

        if ($person === null || $person['role'] !== 'teacher' || !array_key_exists('teacher', $this->tabs()))
        {
            $this->refuse('That account no longer exists.', $back, 404);
        }

        return $person;
    }

    // The handover pop-up's rows, one per duty (PeopleModel::duties()): the class they are class teacher of, each
    // subject (all its classes together), each club / sport. A row: its form field, which duty list it changes
    // ('type' + 'indexes'), its title, tag, tone and the teacher dropdown's options ($teachers: who may take it).
    private function handoverRows(array $duties, array $teachers): array
    {
        $rows = [];

        if ($duties['homeroom'] !== null)
        {
            $rows[] = ['field' => 'homeroom', 'type' => 'homeroom', 'indexes' => [], 'title' => words('{Class} {Teacher}'), 'tag' => $duties['homeroom']['name'],
                'tone' => 'sky', 'options' => TeacherChoices::options($teachers, true)];
        }

        $subjects = [];

        // By name: "Mathematics" of two curricula (two subject ids) is one row; the field is named after its first id
        foreach ($duties['subjects'] as $index => $subject)
        {
            $key                          = mb_strtolower($subject['subject']);
            $subjects[$key]['name']       = $subject['subject'];
            $subjects[$key]['field']    ??= "subject_{$subject['subject_id']}";
            $subjects[$key]['classes'][]  = $subject['class_name'];
            $subjects[$key]['indexes'][]  = $index;
        }

        foreach ($subjects as $subject)
        {
            $rows[] = ['field' => $subject['field'], 'type' => 'subjects', 'indexes' => $subject['indexes'], 'title' => words('{Subject} Teaching'),
                'tag' => $subject['name'] . ' · ' . implode(', ', $subject['classes']), 'tone' => 'terracotta',
                'options' => TeacherChoices::options($teachers, false)];
        }

        foreach ($duties['extracurriculars'] as $index => $extracurricular)
        {
            $rows[] = ['field' => "extracurricular_{$extracurricular['id']}", 'type' => 'extracurriculars', 'indexes' => [$index], 'title' => 'Extracurricular Activity',
                'tag' => $extracurricular['name'], 'tone' => 'moss', 'options' => TeacherChoices::options($teachers, false)];
        }

        return $rows;
    }

    // Signed in with this role, and the role may use the directory (config/permissions.php)
    protected function guard(string $role): array
    {
        $user = $this->requireRole($role);

        if (!can('people.directory'))
        {
            $this->refuse('You are not allowed to see this page.', "/{$role}", 403);
        }

        return $user;
    }

    // The directory's tabs this role sees: [role => label] (the management tab: config/permissions.php)
    protected function tabs(): array
    {
        $tabs = ['student' => plural('student'), 'teacher' => plural('teacher'), 'parent' => plural('parent'), 'management' => word('management') . ' Panel'];

        return can('people.management') ? $tabs : array_diff_key($tabs, ['management' => true]);
    }

    // One page of rows + what the table and the page links need
    private function listData(string $tab, array $filters): array
    {
        $people  = new PeopleModel();
        $perPage = (int) config('people.per_page');
        $page    = max(1, (int) $this->queryText('page'));
        [$rows, $total] = $people->directory($tab, $filters, $page, $perPage);

        return [
            'rows'    => $rows,
            'lists'   => $people->rowLists($tab, array_column($rows, 'id')),
            'total'   => $total,
            'page'    => $page,
            'pages'   => max(1, (int) ceil($total / $perPage)),
            'filters' => $filters,
        ];
    }
}
