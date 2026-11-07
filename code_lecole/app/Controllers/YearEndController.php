<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Assignments;
use App\Models\SchoolYear;
use App\Models\TermMarkModel;
use App\Models\YearEndModel;
use Core\Controller;

// Year-End (B-9), opened from the Academic page (who: config/permissions.php 'academic.year_end'), once a school year:
//   1 mapping: every class of this year with its active students; for each one, next year's class (a class of the
//     next grade, the same section suggested). The final grade (white_label/academic.php 'grade_numbers.last') leaves.
//   2 preview (GET, changes nothing): who goes where, who leaves, the teachers carried over, every warning and problem
//   3 run (POST, after the question pop-up): the same checks again, then Assignments::yearEnd() in one transaction
// A leaver is a student of the final grade who has no class in the new year (no column of its own: the account stays,
// so the certificate and the record book still open). Switched-off students are not moved.
class YearEndController extends Controller
{
    // GET /{role}/academic/year-end[?preview=1&to_9=11…] — the mapping, and the preview under it
    public function show(string $role): void
    {
        $user    = $this->guard($role);
        $year    = (new SchoolYear())->current();
        $classes = (new YearEndModel())->classes();
        $preview = $this->queryText('preview') === '1';
        $chosen  = $this->chosen($classes, $preview ? fn (string $field): string => $this->queryText($field, 11) : null);

        $this->page("{$role}/year_end", $user, [
            'pageUrl' => "/{$role}/academic/year-end",
            'year'    => $year,
            'rows'    => $this->rows($classes, $chosen),
            'plan'    => $preview ? $this->plan($year, $classes, $chosen) : null,
        ]);
    }

    // POST /{role}/academic/year-end/run (year, to_{class id} for each class that moves up) — from the preview's
    // "Run Year-End" (asked first). Every check of the preview again; a problem → back with its message, nothing saved.
    public function run(string $role): never
    {
        $user = $this->guard($role);
        $back = $this->previousPage();
        $year = (new SchoolYear())->current();
        $sent = (int) $this->postText('year', 4);

        // The page was made for another year: year-end for it has already been run (or the form is not ours)
        if ($sent !== $year)
        {
            $this->refuse($sent > 0 && $sent < $year ? "Year-end for {$sent} has already been run." : 'Please open Year-End again.', $back, 409);
        }

        $classes = (new YearEndModel())->classes();
        $plan    = $this->plan($year, $classes, $this->chosen($classes, fn (string $field): string => $this->postText($field, 11)));

        if ($plan['errors'] !== [])
        {
            $this->refuse($plan['errors'][0], $back, 422);
        }

        $counts  = $plan['carried'];
        $newYear = (new Assignments())->yearEnd($year, $plan['moves'], $plan['leavers'], (int) $user['id'])
            ?? $this->refuse("Year-end for {$year} has already been run.", $back, 409);

        $this->log($user, 'YEAR_END_RUN', "{$year} → {$newYear}: " . count($plan['moves']) . ' moved up, ' . count($plan['leavers'])
            . " left; carried over: {$counts['class']} class, {$counts['subject']} subject, {$counts['in_charge']} in charge");
        $this->setFlash('notice', "Year-end done: {$newYear} has started.");
        $this->redirect("/{$role}/academic");
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // Signed in with this role, and the role may run year-end (config/permissions.php)
    private function guard(string $role): array
    {
        $user = $this->requireRole($role);

        if (!can('academic.year_end'))
        {
            $this->refuse('You are not allowed to run year-end.', "/{$role}", 403);
        }

        return $user;
    }

    // Next year's class chosen for each class that moves up: [class id => the sent value ('' = none)], read with
    // $sent (queryText / postText). $sent null (the page's first look) → the next grade's class with the same
    // section (10-A → 11-A), when there is one.
    private function chosen(array $classes, ?callable $sent): array
    {
        $chosen = [];

        foreach ($this->movingUp($classes) as $class)
        {
            if ($sent !== null)
            {
                $chosen[$class['id']] = $sent("to_{$class['id']}");
                continue;
            }

            $section = class_section((int) $class['grade_number'], $class['name']);
            $same    = array_filter($this->nextClasses($classes, $class), fn (array $next): bool =>
                $next['name'] === class_name((int) $next['grade_number'], $section));

            $chosen[$class['id']] = (string) (array_key_first($same) ?? '');
        }

        return $chosen;
    }

    // The page's rows: every class, with its active students, whether it leaves, and next year's choices
    private function rows(array $classes, array $chosen): array
    {
        $last = (int) config('white_label.academic.grade_numbers.last');

        return array_map(fn (array $class): array => [
            'id'       => (int) $class['id'],
            'name'     => $class['name'],
            'students' => (int) $class['students'],
            'leaves'   => (int) $class['grade_number'] >= $last,
            'chosen'   => $chosen[$class['id']] ?? '',
            'options'  => array_map(fn (array $next): array => ['value' => (string) $next['id'], 'label' => $next['name']],
                array_values($this->nextClasses($classes, $class))),
        ], array_values($classes));
    }

    // What year-end would do, and why it can't yet: ['moves' => [student => class], 'leavers' => [student ids],
    // 'lines' (the preview's sentences), 'carried' (teacher counts), 'warnings', 'errors' (any → it can't run)]
    private function plan(int $year, array $classes, array $chosen): array
    {
        $model    = new YearEndModel();
        $students = $model->students();
        $last     = (int) config('white_label.academic.grade_numbers.last');
        $plan     = ['moves' => [], 'leavers' => [], 'lines' => [], 'carried' => $model->teacherCounts(), 'warnings' => [],
                     'errors' => [...($students === [] ? [words('There are no active {students} this year.')] : []), ...$this->termProblems($year, $classes)]];
        $into     = [];   // next year's class id → the classes going into it

        foreach ($classes as $class)
        {
            $mine = array_keys($students, (int) $class['id'], true);

            if ($mine === [])
            {
                continue;
            }

            if ((int) $class['grade_number'] >= $last)
            {
                $plan['leavers'] = [...$plan['leavers'], ...$mine];
                $plan['lines'][] = "{$class['name']}: " . $this->count(count($mine)) . (count($mine) === 1 ? ' leaves' : ' leave');
                continue;
            }

            $nextGrade = grade_name((int) $class['grade_number'] + 1);
            $choice    = $chosen[$class['id']] ?? '';
            $target    = $classes[(int) $choice] ?? null;

            if ($this->nextClasses($classes, $class) === [])
            {
                $plan['errors'][] = "{$nextGrade} has no " . strtolower(plural('class')) . ' yet: add one on the Academic page first.';
            }
            elseif ($choice === '')
            {
                $plan['errors'][] = words("Choose next year's {class} for ") . "{$class['name']}.";
            }
            elseif ($target === null || (int) $target['grade_number'] !== (int) $class['grade_number'] + 1)
            {
                $plan['errors'][] = "Choose a {$nextGrade} " . strtolower(word('class')) . " for {$class['name']}.";
            }
            else
            {
                $plan['moves']        += array_fill_keys($mine, (int) $target['id']);
                $into[$target['id']][] = $class['name'];
                $plan['lines'][]       = "{$class['name']}: " . $this->count(count($mine)) . " → {$target['name']}";
            }
        }

        // Switched off: not moved, so they have no class next year (switched on again → People puts them in one)
        $off = $model->switchedOff($last);

        if ($off > 0)
        {
            $plan['warnings'][] = $this->count($off) . " switched off: not moved, so no " . strtolower(word('class')) . ' next year.';
        }

        foreach ($into as $targetId => $from)
        {
            $target = $classes[$targetId];
            $moving = count(array_keys($plan['moves'], $targetId, true));

            if (count($from) > 1)
            {
                $plan['warnings'][] = implode(' and ', $from) . " go to {$target['name']} together (" . $this->count($moving) . ').';
            }

            if ($moving > (int) $target['capacity'])
            {
                $plan['errors'][] = "{$target['name']} has room for " . $this->count((int) $target['capacity']) . ", but {$moving} would move in.";
            }

            if ($target['class_teacher_id'] === null)
            {
                $plan['warnings'][] = "{$target['name']} has no " . words('{class} {teacher}') . ' yet.';
            }
        }

        return $plan;
    }

    // The year must have terms (right after a year-end the new year has none until its dates are set, B-13: so the
    // same year-end can't run twice), and with term marks on, every class with students must have published its last
    private function termProblems(int $year, array $classes): array
    {
        $terms = (new SchoolYear())->terms($year);

        if ($terms === [])
        {
            return [words("{$year} has no {terms} yet, so it can't end.")];
        }

        if (!feature('term_marks'))
        {
            return [];
        }

        $lastTerm = max(array_keys($terms));
        $marks    = new TermMarkModel();
        $missing  = array_column(array_filter($classes, fn (array $class): bool =>
            (int) $class['students'] > 0 && $marks->publication((int) $class['id'], $year, $lastTerm) === null), 'name');

        return $missing === [] ? [] : [words('Publish {Term} ') . "{$lastTerm} first: " . implode(', ', $missing) . '.'];
    }

    // The classes with active students that move up (not the final grade)
    private function movingUp(array $classes): array
    {
        $last = (int) config('white_label.academic.grade_numbers.last');

        return array_filter($classes, fn (array $class): bool => (int) $class['students'] > 0 && (int) $class['grade_number'] < $last);
    }

    // The classes of the grade after this class's: [class id => class]
    private function nextClasses(array $classes, array $class): array
    {
        return array_filter($classes, fn (array $next): bool => (int) $next['grade_number'] === (int) $class['grade_number'] + 1);
    }

    // "1 student" / "12 students"
    private function count(int $number): string
    {
        return "{$number} " . strtolower($number === 1 ? word('student') : plural('student'));
    }
}
