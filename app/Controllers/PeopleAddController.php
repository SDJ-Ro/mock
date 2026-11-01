<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ClassModel;
use App\Models\PeopleModel;
use App\Services\PersonFields;
use Core\Mailer;
use Core\Upload;
use Core\Validator;
use DomainException;

// People's Add pages (_add_person_page.php): Add Student (with a parent: one already registered, found by the parent
// search, or a new one), Add Teacher, Add Staff (management: the admin only). A parent is added with their child, so
// the parents tab shows a policy card instead. The boxes and their rules: config/people.php 'add' + 'fields';
// the checks: App\Services\PersonFields. A normal form: saved → the directory with "… added."; refused → this page
// again with the reason and what was typed. Each new account gets a welcome email (its sign-in ID + the sign-up link).
// Who may: PeopleController::guard() and tabs() (config/permissions.php 'people').
class PeopleAddController extends PeopleController
{
    // GET /{role}/people/add?tab=student|teacher|parent|management
    public function form(string $role): void
    {
        $user  = $this->guard($role);
        $tab   = array_key_exists($this->queryText('tab'), $this->tabs()) ? $this->queryText('tab') : 'student';
        $typed = $this->getFlash('person_form') ?? ['values' => [], 'mode' => 'existing', 'idType' => 'national_id', 'parent' => null, 'qualifications' => []];

        $this->page("{$role}/add_person", $user, [
            'personForm' => [
                'tab'            => $tab,
                'tone'           => config("people.tones.{$tab}"),
                'title'          => match ($tab)   // the page's title per tab
                {
                    'student'    => words('Add {Student} Account'),
                    'teacher'    => words('Add {Teacher} Account'),
                    'parent'     => words('Add {Parent} / Guardian Account'),
                    default      => 'Add ' . word('management') . ' Panel Account',
                },
                'sections'       => $tab === 'parent' ? [] : $this->sections($tab, $typed['values']),
                'guardian'       => $this->boxes(config('people.guardian'), 'guardian_', 'parent', $typed['values']),
                'idNumber'       => $typed['values']['guardian_id_number'] ?? '',
                'mode'           => $typed['mode'],
                'idType'         => $typed['idType'],
                'parent'         => $typed['parent'] === null ? null : (new PeopleModel())->parentRow((int) $typed['parent']),
                'qualifications' => $typed['qualifications'],
                'error'          => $this->getFlash('error'),
                'action'         => "/{$role}/people/add",
                'back'           => "/{$role}/people?tab={$tab}",
                'studentUrl'     => "/{$role}/people/add?tab=student",
                'searchUrl'      => "/{$role}/people/parents",
            ],
        ]);
    }

    // POST /{role}/people/add — checks every box of the tab's form (+ the parent part / qualifications), then saves
    // it all at once (PeopleModel::add() / addStudentWithNewParent()) and sends the welcome email(s)
    public function add(string $role): never
    {
        $user = $this->guard($role);
        $tab  = $this->postText('tab', 20);

        if ($tab === 'parent' || !array_key_exists($tab, $this->tabs()))
        {
            $this->refuse('Please choose who to add.', "/{$role}/people", 422);
        }

        $back   = "/{$role}/people/add?tab={$tab}";
        $people = new PeopleModel();
        $typed  = ['values' => [], 'mode' => 'existing', 'idType' => 'national_id', 'parent' => null, 'qualifications' => []];

        [$values, $problems] = $this->check($this->fields($tab), '', $tab, $people, $typed);

        // A student's parent: one found by the search, or a new one (with an ID card number or a passport)
        $parentId = null;
        $guardian = [];

        if ($tab === 'student')
        {
            $typed['mode']   = $this->postText('guardian_mode', 10) === 'new' ? 'new' : 'existing';
            $typed['idType'] = $this->postText('guardian_id_type', 15) === 'passport' ? 'passport' : 'national_id';
            $typed['parent'] = $this->postText('parent_id', 11) === '' ? null : (int) $this->postText('parent_id', 11);

            if ($typed['mode'] === 'existing' && ($typed['parent'] === null || $people->parentRow($typed['parent']) === null))
            {
                $problems[] = 'Please choose a ' . strtolower(word('parent')) . ' from the search, or add a new one.';
            }

            if ($typed['mode'] === 'existing')
            {
                $parentId = $typed['parent'];
            }

            if ($typed['mode'] === 'new')
            {
                // The ID is one box for both kinds; its text goes to the chosen one. No home address → the student's.
                $idNumber = $this->postText('guardian_id_number', 30);
                $fields   = $this->fields('guardian') + [$typed['idType'] => [$typed['idType'] === 'passport' ? 'Passport Number' : country('national_id.label'), true]];
                $instead  = [$typed['idType'] => $idNumber] + ($this->postText('guardian_address', 300) === '' ? ['address' => $values['address']] : []);

                $typed['values']['guardian_id_number'] = $idNumber;
                [$guardian, $more] = $this->check($fields, 'guardian_', 'parent', $people, $typed, $instead);
                $problems          = array_merge($problems, $more);
            }
        }

        // A teacher's qualifications (also kept as typed, for a refused form)
        $degrees      = $this->postList('qualification_degree');
        $institutions = $this->postList('qualification_institution');
        $years        = $this->postList('qualification_year');

        [$qualifications, $problem] = $tab === 'teacher' ? PersonFields::qualifications($degrees, $institutions, $years) : [[], null];
        $typed['qualifications']    = $tab !== 'teacher' ? [] : array_map(fn (?string $degree, ?string $institution, ?string $year): array =>
            ['degree' => $degree ?? '', 'institution' => $institution ?? '', 'year_obtained' => $year ?? ''], $degrees, $institutions, $years);

        if ($problem !== null)
        {
            $problems[] = $problem;
        }

        // A student's photo, only once everything else is right (so a refused form leaves no file behind)
        $photo = null;

        if ($problems === [] && $tab === 'student')
        {
            [$photo, $photoProblem] = Upload::image($_FILES['photo'] ?? [], 'photos', (int) config('profile.photo_kb'));

            if ($photoProblem !== null)
            {
                $problems[] = $photoProblem;
            }
        }

        if ($problems !== [])
        {
            $this->setFlash('person_form', $typed);
            $this->refuse(reset($problems), $back, 422);
        }

        [$profile, $details] = $this->split($values);
        $profile            += $photo === null ? [] : ['photo_url' => $photo];

        $newParent = $tab === 'student' && $typed['mode'] === 'new';

        try
        {
            if ($newParent)
            {
                [$id] = $people->addStudentWithNewParent($profile, $details, ...$this->split($guardian));   // [student, parent]
            }
            else
            {
                $id = $people->add($tab, $profile, $details, $parentId, $qualifications);
            }
        }
        catch (DomainException $full)
        {
            Upload::forget($photo);
            $this->setFlash('person_form', $typed);
            $this->refuse($full->getMessage(), $back, 422);   // the class filled up meanwhile
        }

        $person = $people->find($id);
        $this->log($user, 'PERSON_ADDED', "{$person['full_name']} ({$tab})" . ($newParent ? ' + a new ' . strtolower(word('parent')) : ''));
        $this->setFlash('notice', "{$person['full_name']} added. " . ($this->welcome($person, $newParent)
            ? 'A welcome email was sent.'
            : 'The welcome email could not be sent; they can still sign up with their ID.'));

        // Back to the list, where the new person is: a student's own grade and class
        $class = $tab === 'student' ? (new ClassModel())->find((int) $person['class_id']) : null;
        $this->redirect("/{$role}/people?tab={$tab}" . ($class === null ? '' : "&grade={$class['grade_id']}&class={$class['id']}"));
    }

    // GET /{role}/people/parents?q=… — the parent search (parent-picker.js): JSON { ok, html (the rows), message }.
    // Nothing typed → the first 20 parents (the list shows at once).
    public function parents(string $role): never
    {
        $this->guard($role);
        $rows = (new PeopleModel())->searchParents($this->queryText('q', 60));

        ob_start();

        foreach ($rows as $parentRow)
        {
            $this->view('components/_parent_row', ['parentRow' => $parentRow]);
        }

        $html    = ob_get_clean();
        $message = match (true)
        {
            $rows === []        => 'No ' . strtolower(plural('parent')) . ' match your search.',
            count($rows) === 20 => 'Showing the first 20: type a name, code, phone or a child’s name to narrow it.',
            default             => 'Showing ' . count($rows) . ' registered ' . strtolower(count($rows) === 1 ? word('parent') : plural('parent')) . '.',
        };

        $this->sendJson(['ok' => true, 'html' => $html, 'message' => $message]);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // The fields of a tab's form ('guardian': the new parent's), from config/people.php 'add':
    // [field => [label, required here]]
    private function fields(string $tab): array
    {
        $boxes = $tab === 'guardian' ? config('people.guardian') : array_merge(...array_map(
            fn (array $section): array => is_array($section[1]) ? $section[1] : [],
            config("people.add.{$tab}")
        ));
        $fields = [];

        foreach ($boxes as $box)
        {
            $fields[$box[0]] = [words($box[1]), str_contains($box[2] ?? '', 'required')];
        }

        return $fields;
    }

    // Reads $fields from the form (their names start with $prefix; $instead: [field => value] used in place of the
    // box's), checks their rules and PersonFields::problems() as a $role. Remembers what was typed in $typed['values']
    // (with the prefix). Returns [the values, the problems].
    private function check(array $fields, string $prefix, string $role, PeopleModel $people, array &$typed, array $instead = []): array
    {
        $values = [];

        foreach (array_keys($fields) as $field)
        {
            if (config('people.fields')[$field][0] !== 'photo')
            {
                $typed['values'][$prefix . $field] = $this->postText($prefix . $field, 1000);
                $values[$field]                    = $instead[$field] ?? $typed['values'][$prefix . $field];
            }
        }

        $labels   = array_map(fn (array $field): string => $field[0], $fields);
        $required = array_keys(array_filter($fields, fn (array $field): bool => $field[1]));
        $problems = Validator::check($values, PersonFields::rules(array_keys($values), $required), $labels)
            ?: PersonFields::problems($role, 0, $values, $people);

        return [$values, array_values($problems)];
    }

    // [profiles' columns, the role's details], empty boxes saved as "not given"
    private function split(array $values): array
    {
        $columns = array_map(fn (string $value): ?string => $value === '' ? null : $value, $values);
        $profile = array_intersect_key($columns, array_flip(PeopleModel::PROFILE_COLUMNS));

        return [$profile, array_diff_key($columns, $profile)];
    }

    // The tab's sections for the view: [['title', 'boxes' (see boxes()) | 'guardian' | 'qualifications'], …]
    private function sections(string $tab, array $values): array
    {
        return array_map(fn (array $section): array => [
            'title' => words($section[0]),
            'boxes' => is_array($section[1]) ? $this->boxes($section[1], '', $tab, $values) : $section[1],
        ], config("people.add.{$tab}"));
    }

    // The boxes for _field_box.php: name (with $prefix), kind, label, value (what was typed, or a sensible start),
    // options, wide, required (the *), and a date of birth's ages ($role's)
    private function boxes(array $list, string $prefix, string $role, array $values): array
    {
        $start = ['nationality' => country('nationality'), 'admission_date' => date('Y-m-d'), 'join_date' => date('Y-m-d')];
        $boxes = [];

        foreach ($list as $item)
        {
            [$field, $label] = $item;
            $flags           = $item[2] ?? '';
            [$kind, $rules]  = config('people.fields')[$field];
            $ages            = $field === 'date_of_birth' ? PersonFields::ages($role) : [];
            $required        = !str_contains($flags, 'optional') && (str_contains($flags, 'required') || str_contains($rules, 'required'));

            $boxes[] = [
                'name'     => $prefix . $field,
                'box'      => $kind,
                'label'    => words($label),
                'value'    => $values[$prefix . $field] ?? ($start[$field] ?? ''),
                'options'  => PersonFields::options($field, null, $required),
                'wide'     => str_contains($flags, 'wide'),
                'required' => $required,
                'minAge'   => $ages['min'] ?? '',
                'maxAge'   => $ages['max'] ?? '',
            ];
        }

        return $boxes;
    }

    // The welcome email, ONE per addition: staff → their personal email; a student → their parent, with the
    // parent's own account in the same email when the parent is new ($newParent). Each account: what the sign-up
    // page asks except the password (they choose it there, after a code to their email). Returns whether it went
    // (Mailer::send(): false when the mail server refused it).
    private function welcome(array $person, bool $newParent = false): bool
    {
        $school  = school('name') . ' ' . word('institution');
        $account = fn (array $who, string $title): array => ['title' => $title, 'fullName' => $who['full_name'], 'identifier' => $who['identifier'],
            'url' => config('app.url') . "/auth/{$who['role']}/signup"];

        if ($person['role'] !== 'student')
        {
            return Mailer::send((string) $person['personal_email'], "{$school} — Your account is ready", 'welcome', [
                'name'     => $person['first_name'],
                'intro'    => 'Your ' . strtolower(word($person['role'])) . " account at {$school} is ready to be set up.",
                'accounts' => [$account($person, 'Your account')],
            ]);
        }

        $parent = (new PeopleModel())->find((int) $person['parents'][0]['id']);
        $child  = $account($person, "{$person['first_name']}'s " . strtolower(word('student')) . ' account');

        return Mailer::send($parent['identifier'], "{$school} — {$person['full_name']} enrolled", 'welcome', [
            'name'     => $parent['first_name'],
            'intro'    => $newParent
                ? "{$person['full_name']} has been enrolled at {$school}. Your " . strtolower(word('parent')) . " account and {$person['first_name']}'s account are ready to be set up."
                : "{$person['full_name']} has been enrolled at {$school} and linked to your account. Your own sign-in stays the same; only {$person['first_name']}'s account needs setting up.",
            'accounts' => $newParent ? [$account($parent, 'Your ' . strtolower(word('parent')) . ' account'), $child] : [$child],
        ]);
    }
}
