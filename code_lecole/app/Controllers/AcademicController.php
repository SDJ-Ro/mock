<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Assignments;
use App\Models\ClassModel;
use App\Models\PeopleModel;
use App\Models\SchoolYear;
use App\Models\StageModel;
use App\Services\CalendarFeed;
use App\Services\RecordBook;
use App\Services\TeacherChoices;
use App\Services\TermResults;
use Core\Controller;
use Core\Validator;

// Academic, two parts:
// · the student's Academic Records: the Digital Record Book, the total marks card and the performance trend,
//   plus the record book's printable page (RecordBook: every published term, per school year).
// · the school's structure for admin and management (the Academic Overview): grades and their classes, class and
//   subject teachers, curriculum stages — real data. The page's chart: this year's published marks (TermResults).
// Saving rules: a change that adds, removes or renames something is a normal form → the page comes back at that card;
// picking a subject's teacher saves in the background (background-save.js). Nothing with students or marks is deleted.
class AcademicController extends Controller
{
    // ---------------------------------------------------------------------
    // The student's Academic Records
    // ---------------------------------------------------------------------

    // GET /student/academic
    public function student(): void
    {
        $user = $this->requireRole('student');

        $this->page('student/academic', $user, RecordBook::shown(RecordBook::forStudent($user['id']), '/student/academic/print'));
    }

    // GET /student/academic/print?year=2026&term=1 · /parent/child-profile/print?… — one year + term as an official A4
    // page (export-pdf.js prints it): the student's own, or the child the parent is viewing (published terms only)
    public function recordPrint(string $role): void
    {
        $user      = $this->requireRole($role);
        $studentId = $role === 'parent' ? $user['child'] : $user['id'];
        $back      = $role === 'parent' ? '/parent/child-profile?tab=academics' : '/student/academic';
        $student   = $studentId === null ? null : (new PeopleModel())->find($studentId);
        $books     = $student === null ? [] : RecordBook::forStudent($studentId);
        $year      = (int) $this->queryText('year');
        $term      = (int) $this->queryText('term');
        $record    = $books[$year]['terms'][$term] ?? $this->refuse('That record does not exist.', $back, 404);

        $this->view('print/record_book', [
            'student' => ['name' => $student['full_name'], 'code' => $student['identifier']],
            'book'    => $books[$year],
            'term'    => $term,
            'record'  => $record,
        ]);
    }

    // ---------------------------------------------------------------------
    // The school's structure (admin, management)
    // ---------------------------------------------------------------------

    // GET /{role}/academic[?grade=3&subject=Science&term=2] — the chart's filters reload the page
    public function overview(string $role): void
    {
        $user     = $this->guard($role);
        $classes  = new ClassModel();
        $model    = new StageModel();
        $stages   = $model->all();
        $marks    = $model->marks();
        $grades   = $classes->grades();
        $teachers = $classes->teachers();

        foreach ($grades as &$grade)
        {
            $grade['stage'] = $this->stageFor($grade['number'], $stages);
        }
        unset($grade);

        // How many marks a stage's subjects have (any → its delete question says it can't be deleted), and its subject
        // teachers (its editor asks before a save removes some)
        foreach ($stages as &$stage)
        {
            $stage['marks']    = array_sum(array_intersect_key($marks, $stage['subjects'] + $stage['retired']));
            $stage['assigned'] = $model->assignments($stage['id']);
        }
        unset($stage);

        $numbers = config('white_label.academic.grade_numbers');

        $this->page("{$role}/academic", $user, [
            'pageUrl'             => $this->pageUrl($role),
            'chartConfig'         => $this->chart($grades),
            'calendarConfig'      => feature('calendar') ? CalendarFeed::config($user) : null,
            'grades'              => $grades,
            'stages'              => $stages,
            'teachers'            => $teachers,
            'teacherOptions'      => TeacherChoices::options($teachers, false),
            'classTeacherOptions' => TeacherChoices::options($teachers, true),
            'freeGradeNumbers'    => array_values(array_diff(range($numbers['first'], $numbers['last']), array_column($grades, 'number'))),
            'workloads'           => $classes->workloads(),
            'subjectTeachers'     => $classes->subjectTeachers(),
            'classForm'           => $this->getFlash('class_form'),   // a refused class save: what was typed and where
            'stageForm'           => $this->getFlash('stage_form'),
            'canRunYearEnd'       => can('academic.year_end'),   // the header's Year-End button (YearEndController)
        ]);
    }

    // POST /{role}/academic/grades/add — a new grade with its first class ("12-A")
    public function addGrade(string $role): never
    {
        $user    = $this->guard($role);
        $back    = $this->pageUrl($role);
        $numbers = config('white_label.academic.grade_numbers');
        $data    = ['number' => $this->postText('number', 3)];
        $errors  = Validator::check($data, ['number' => "required|between:{$numbers['first']},{$numbers['last']}"], ['number' => word('grade') . ' number']);
        $classes = new ClassModel();

        if ($errors !== [])
        {
            $this->refuse(reset($errors), $back, 422);
        }

        $number = (int) $data['number'];

        if ($classes->gradeNumberTaken($number))
        {
            $this->refuse(grade_name($number) . ' already exists.', $back, 422);
        }

        $id      = $classes->addGrade($number);
        $covered = $this->stageFor($number, (new StageModel())->all()) !== null;
        $this->log($user, 'GRADE_ADDED', grade_name($number));

        // No curriculum covers it yet → say so, or its classes would just show "No subjects yet"
        $this->done(grade_name($number) . words(' added, with its first {class}.') . ($covered ? '' : words(' Add a curriculum that covers it to choose its {subjects}.')), "{$back}#grade-{$id}");
    }

    // POST /{role}/academic/grades/delete — the whole grade (whole=1), or some of its classes (classes[]).
    // Never while students are in them: they are moved first (a student never silently loses their class).
    public function deleteGrade(string $role): never
    {
        $user    = $this->guard($role);
        $back    = $this->pageUrl($role);
        $classes = new ClassModel();
        $grade   = $classes->findGrade((int) $this->postText('grade_id')) ?? $this->refuse('That ' . strtolower(word('grade')) . ' no longer exists.', $back, 404);
        $name    = grade_name($grade['number']);

        if ($this->postText('whole') === '1')
        {
            if ($grade['students'] > 0)
            {
                $this->refuse("{$name} still has {$grade['students']} " . strtolower($grade['students'] === 1 ? word('student') : plural('student')) . '. Move them first.', $back, 422);
            }

            if ($grade['past_students'] > 0)
            {
                $this->refuse("{$name} stays: earlier years' records point to it.", $back, 422);
            }

            $classes->deleteGrade($grade['id']);
            $this->log($user, 'GRADE_DELETED', $name);
            $this->done("{$name} deleted.", $back);
        }

        // Only this grade's own classes, and only empty ones
        $chosen = [];

        foreach (array_unique(array_map('intval', $this->postList('classes'))) as $classId)
        {
            $class = $classes->find($classId);

            if ($class === null || $class['grade_id'] !== $grade['id'])
            {
                $this->refuse('Please choose ' . strtolower(plural('class')) . " of {$name}.", $back, 422);
            }

            if ($class['students'] > 0)
            {
                $this->refuse("{$class['name']} still has {$class['students']} " . strtolower($class['students'] === 1 ? word('student') : plural('student')) . '. Move them first.', $back, 422);
            }

            if ($class['past_students'] > 0)
            {
                $this->refuse("{$class['name']} stays: earlier years' records point to it.", $back, 422);
            }

            $chosen[$classId] = $class['name'];
        }

        if ($chosen === [])
        {
            $this->refuse('Please choose what to delete.', $back, 422);
        }

        $classes->deleteClasses(array_keys($chosen));
        $this->log($user, 'CLASSES_DELETED', implode(', ', $chosen));
        $this->done(implode(', ', $chosen) . ' deleted.', "{$back}#grade-{$grade['id']}");
    }

    // POST /{role}/academic/classes/save — add (no id) or edit a class: its section ("A" → "6-A"), places, class teacher
    public function saveClass(string $role): never
    {
        $user     = $this->guard($role);
        $classes  = new ClassModel();
        $id       = (int) $this->postText('id') ?: null;
        $class    = $id === null ? null : ($classes->find($id) ?? $this->refuse('That ' . strtolower(word('class')) . ' no longer exists.', $this->pageUrl($role), 404));
        $grade    = $classes->findGrade($class['grade_id'] ?? (int) $this->postText('grade_id')) ?? $this->refuse('That ' . strtolower(word('grade')) . ' no longer exists.', $this->pageUrl($role), 404);
        $size     = config('white_label.academic.class_size');
        $data     = [
            'section'  => mb_strtoupper($this->postText('section', 10)),
            'capacity' => $this->postText('capacity', 3),
            'teacher'  => $this->postText('class_teacher_id', 11),
        ];
        $back     = $this->pageUrl($role) . "#grade-{$grade['id']}";
        $refuse   = function (string $message) use ($data, $id, $grade, $back): never
        {
            $this->setFlash('class_form', $data + ['id' => $id, 'grade_id' => $grade['id'], 'prefix' => class_prefix($grade['number'])]);   // the editor opens again with this
            $this->refuse($message, $back, 422);
        };

        $errors  = Validator::check($data, ['section' => 'required|max:10', 'capacity' => "required|between:{$size['min']},{$size['max']}"], ['capacity' => 'Places']);
        $name    = class_name($grade['number'], $data['section']);
        $teacher = $data['teacher'] === '' ? null : (int) $data['teacher'];

        if ($errors !== [])
        {
            $refuse(reset($errors));
        }

        if (!preg_match('/^[\p{L}\p{N}]+$/u', $data['section']))
        {
            $refuse('The section can only be letters and numbers (e.g. A).');
        }

        if ($classes->nameTaken($grade['id'], $name, $id))
        {
            $refuse(grade_name($grade['number']) . " already has {$name}.");
        }

        if ($class !== null && (int) $data['capacity'] < $class['students'])
        {
            $refuse("{$class['name']} already has {$class['students']} " . strtolower(plural('student')) . '.');
        }

        if ($teacher !== null && !isset($classes->teachers()[$teacher]))
        {
            $refuse('Please choose a ' . strtolower(word('teacher')) . ' from the list.');
        }

        if ($teacher !== null && ($other = $classes->classTeacherOf($teacher, $id)) !== null)
        {
            $refuse('Already the ' . strtolower(word('class') . ' ' . word('teacher')) . " of {$other}.");
        }

        $classes->saveClass($id, $grade['id'], $name, (int) $data['capacity'], $teacher);
        $this->log($user, $id === null ? 'CLASS_ADDED' : 'CLASS_UPDATED', $name);
        $this->done($id === null ? "{$name} added." : "{$name} saved.", $back);
    }

    // POST /{role}/academic/subject-teacher — one subject's teacher in one class, saved in the background (JSON)
    public function saveSubjectTeacher(string $role): never
    {
        $user    = $this->guard($role);
        $back    = $this->pageUrl($role);
        $classes = new ClassModel();
        $class   = $classes->find((int) $this->postText('class_id')) ?? $this->refuse('That ' . strtolower(word('class')) . ' no longer exists.', $back, 404);
        $stage   = $this->stageFor($class['grade_number'], (new StageModel())->all());
        $subject = (int) $this->postText('subject_id');
        $teacher = $this->postText('teacher_id', 11) === '' ? null : (int) $this->postText('teacher_id', 11);
        $chosen  = $teacher === null ? null : ($classes->teachers()[$teacher] ?? null);
        $max     = (int) config('white_label.academic.max_subjects_per_teacher');

        if (!isset($stage['subjects'][$subject]))
        {
            $this->refuse(words("That {subject} isn't part of ") . "{$class['name']}'s curriculum.", $back, 422);
        }

        $name = $stage['subjects'][$subject];

        if ($teacher !== null && $chosen === null)
        {
            $this->refuse('Please choose a ' . strtolower(word('teacher')) . ' from the list.', $back, 422);
        }

        if ($teacher !== null && count($classes->subjectsWith($teacher, $class['id'], $subject)) > $max)
        {
            $this->refuse("That would be more than {$max} different " . words('{subjects} for this {teacher}.'), $back, 422);
        }

        (new Assignments())->setSubjectTeacher($class['id'], $subject, $teacher);
        $this->log($user, 'SUBJECT_TEACHER_SAVED', "{$class['name']} {$name}");

        // Allowed (schools cover gaps), but said: the subject isn't one of the teacher's own ("Science, Physics")
        $theirs = array_map(fn (string $s): string => mb_strtolower(trim($s)), explode(',', (string) ($chosen['qualification'] ?? '')));
        $note   = $chosen !== null && $theirs !== [''] && !in_array(mb_strtolower($name), $theirs, true)
            ? " {$name} isn't one of {$chosen['name']}'s listed " . strtolower(plural('subject')) . '.'
            : '';

        $this->sendJson(['ok' => true, 'message' => "{$class['name']} {$name} saved.{$note}"]);
    }

    // POST /{role}/academic/stages/save — add (no id) or edit a curriculum stage: its grade range, description, subjects
    public function saveStage(string $role): never
    {
        $user    = $this->guard($role);
        $model   = new StageModel();
        $id      = (int) $this->postText('id') ?: null;
        $stage   = $id === null ? null : ($model->find($id) ?? $this->refuse('That stage no longer exists.', $this->pageUrl($role), 404));
        $numbers = config('white_label.academic.grade_numbers');
        $data    = [
            'first_grade' => $this->postText('first_grade', 3),
            'last_grade'  => $this->postText('last_grade', 3),
            'description' => $this->postText('description', 255),
        ];
        $names    = $this->postList('subjects');
        $ids      = $this->postList('subject_ids');   // one per chip, in the same order ('' = a new subject)
        $back     = $this->pageUrl($role) . '#curriculum';

        if (count($names) !== count($ids))
        {
            $this->refuse('Please try again.', $back, 422);   // the form always sends both; a mismatch was not this page
        }

        $subjects = $this->matchSubjects($names, $ids, $stage['subjects'] ?? []);
        $refuse   = function (string $message) use ($data, $subjects, $id, $model, $back): never
        {
            // the editor opens again with this
            $this->setFlash('stage_form', $data + ['id' => $id, 'subjects' => $subjects, 'assigned' => $id === null ? [] : $model->assignments($id)]);
            $this->refuse($message, $back, 422);
        };
        $range    = "required|between:{$numbers['first']},{$numbers['last']}";
        $errors   = Validator::check($data, ['first_grade' => $range, 'last_grade' => $range, 'description' => 'max:255'], [
            'first_grade' => 'From ' . strtolower(word('grade')), 'last_grade' => 'To ' . strtolower(word('grade')),
        ]);

        if ($errors !== [])
        {
            $refuse(reset($errors));
        }

        [$first, $last] = [(int) $data['first_grade'], (int) $data['last_grade']];

        if ($first > $last)
        {
            $refuse('"To" must be the same as or after "From".');
        }

        if (($other = $model->overlapping($first, $last, $id)) !== null)
        {
            $refuse("These overlap with {$other}. Each " . strtolower(word('grade')) . ' can be in only one stage.');
        }

        if ($model->gradesIn($first, $last) === 0)
        {
            $refuse('Add a ' . strtolower(word('grade')) . ' in ' . grade_range($first, $last) . ' first.');
        }

        if ($subjects === [])
        {
            $refuse(words('Add at least one {subject}.'));
        }

        foreach ($subjects as $subject)
        {
            if (mb_strlen($subject['name']) > 100)
            {
                $refuse("\"{$subject['name']}\" is too long (100 characters at most).");
            }
        }

        // A subject with marks keeps its name on old reports: renamed, it becomes a new subject that takes over this
        // year's teachers, and the old one is retired (StageModel::save()); taken out, it is retired
        $marks = $model->marks();

        foreach ($subjects as &$subject)
        {
            $oldName = $stage['subjects'][$subject['id']] ?? null;

            if ($oldName !== null && mb_strtolower($oldName) !== mb_strtolower($subject['name']) && isset($marks[$subject['id']]))   // capitals only: same subject
            {
                $subject = ['id' => null, 'name' => $subject['name'], 'replaces' => $subject['id']];
            }
        }
        unset($subject);

        [$id, $removed] = $model->save($id, ['first_grade' => $first, 'last_grade' => $last, 'description' => $data['description'] ?: null], $subjects);
        $title = grade_range($first, $last);
        $this->log($user, $stage === null ? 'STAGE_ADDED' : 'STAGE_UPDATED', "{$title}: " . implode(', ', array_column($subjects, 'name')));
        $this->done("{$title} curriculum saved." . ($removed > 0 ? " {$removed} " . words('{subject} ') . strtolower($removed === 1 ? word('teacher') : plural('teacher')) . ' removed.' : ''), $back);
    }

    // POST /{role}/academic/stages/delete — refused while its subjects have marks
    public function deleteStage(string $role): never
    {
        $user  = $this->guard($role);
        $model = new StageModel();
        $back  = $this->pageUrl($role) . '#curriculum';
        $stage = $model->find((int) $this->postText('id')) ?? $this->refuse('That stage no longer exists.', $back, 404);
        $title = grade_range($stage['first_grade'], $stage['last_grade']);

        if (array_intersect_key($model->marks(), $stage['subjects'] + $stage['retired']) !== [])
        {
            $this->refuse("The {$title} curriculum" . words(' has {subjects} with marks, so it can\'t be deleted.'), $back, 422);
        }

        $model->delete($stage['id']);
        $this->log($user, 'STAGE_DELETED', $title);
        $this->done("{$title} curriculum deleted.", $back);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // Signed in with this role, and the role may change the school's structure (config/permissions.php)
    private function guard(string $role): array
    {
        $user = $this->requireRole($role);

        if (!can('academic.edit'))
        {
            $this->refuse('You are not allowed to change the academic structure.', "/{$role}", 403);
        }

        return $user;
    }

    private function pageUrl(string $role): string
    {
        return "/{$role}/academic";
    }

    // Saved: back to the page (at the card that changed), where the feedback banner says it
    private function done(string $message, string $url): never
    {
        $this->setFlash('notice', $message);
        $this->redirect($url);
    }

    // The stage that covers this grade number, or null (a grade may be without one for a while)
    private function stageFor(int $gradeNumber, array $stages): ?array
    {
        foreach ($stages as $stage)
        {
            if ($gradeNumber >= $stage['first_grade'] && $gradeNumber <= $stage['last_grade'])
            {
                return $stage;
            }
        }

        return null;
    }

    // The subjects as typed, each with the id it keeps: [['id' => int or null (new), 'name'], …].
    // Names are trimmed, blanks dropped, a name repeated (any capitals) kept once. A chip keeps its id only if it is one
    // of this stage's subjects, used once (so a renamed subject keeps its teachers; one with marks is replaced instead:
    // saveStage()); anything else is new.
    private function matchSubjects(array $names, array $ids, array $existing): array
    {
        $subjects = [];
        $used     = [];

        foreach ($names as $index => $name)
        {
            $name = trim($name);
            $key  = mb_strtolower($name);
            $id   = (int) $ids[$index];

            if ($name === '' || isset($subjects[$key]))
            {
                continue;
            }

            $id = isset($existing[$id]) && !isset($used[$id]) ? $id : null;
            $used[$id ?? 0] = true;

            $subjects[$key] = ['id' => $id, 'name' => $name];
        }

        return array_values($subjects);
    }

    // The "Class section performance" chart: one grade's classes, one subject, one term of this year (the filters reload
    // the page), from the published terms (TermResults). null (no chart): term marks switched off, or no grade has classes.
    private function chart(array $grades): ?array
    {
        if (!feature('term_marks'))
        {
            return null;
        }

        $withClasses = array_values(array_filter($grades, fn (array $grade): bool => $grade['classes'] !== []));

        if ($withClasses === [])
        {
            return null;
        }

        $years    = new SchoolYear();
        $terms    = $years->startedTerms() ?: [1];
        $byId     = array_column($withClasses, null, 'id');
        $grade    = $byId[(int) $this->queryText('grade')] ?? $withClasses[0];
        $subjects = array_values($grade['stage']['subjects'] ?? []);
        $subject  = in_array($this->queryText('subject'), $subjects, true) ? $this->queryText('subject') : ($subjects[0] ?? '');
        $term     = in_array((int) $this->queryText('term'), $terms, true) ? (int) $this->queryText('term') : end($terms);
        $results  = TermResults::forGrade($years->current(), $term, $grade['number'])['classes'];
        $rows     = [];

        // Each class: its average and highest mark in the subject (0 until its term is published)
        foreach ($grade['classes'] as $class)
        {
            $rows[] = ['label' => $class['name'], 'average' => $results[$class['id']]['average'][$subject] ?? 0, 'best' => $results[$class['id']]['highest'][$subject] ?? 0];
        }

        return [
            'variant'    => 'counts',
            'id'         => 'j-academic-chart',
            'title'      => words('{Class} Section Performance'),
            'yAxisTitle' => 'Score',
            'xAxisTitle' => words('{Class} Section'),
            'ticks'      => [0, 20, 40, 60, 80, 100],
            'series'     => [
                ['key' => 'average', 'label' => words('{Class} Average'), 'color' => 'skyblue'],
                ['key' => 'best', 'label' => 'Highest Mark', 'color' => 'maroon'],
            ],
            'data'       => $rows,
            'filters'    => [
                ['name' => 'grade', 'label' => word('grade'), 'value' => (string) $grade['id'],
                 'options' => array_map(fn (array $g): array => ['value' => (string) $g['id'], 'label' => grade_name($g['number'])], $withClasses)],
                ['name' => 'subject', 'label' => word('subject'), 'value' => $subject, 'options' => $subjects],
                ['name' => 'term', 'label' => word('term'), 'value' => (string) $term,
                 'options' => array_map(fn (int $t): array => ['value' => (string) $t, 'label' => word('term') . " {$t}"], $terms)],
            ],
        ];
    }
}
