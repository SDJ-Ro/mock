<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SchoolYear;
use App\Models\TermMarkModel;
use Core\Controller;

// The class teacher's Term Marks page: the students of the class they are class teacher of this year, as cards;
// "Edit Marks" opens a student's marks panel (every subject: 0–100 or AB, and the comment), saved in the background.
// "Publish Term" shows the term to the students and parents — only when every student needed has every mark and a
// comment. Only the current year's terms that have started can be typed; earlier years are read only. A change after
// publishing is allowed and goes in the Audit Log. The maths (totals, positions, awards): App\Services\TermResults.
class TermMarkController extends Controller
{
    // The card colours, in turn (student-card.css)
    private const TONES = ['slate', 'steel', 'taupe', 'sand', 'seafoam', 'sage'];

    // GET /teacher/marks?term=2 (no term: the newest that has started)
    public function index(): void
    {
        $user = $this->requireRole('teacher');
        $work = $this->workspace($user, (int) $this->queryText('term'));

        if (is_string($work))
        {
            $this->page('teacher/term_marks', $user, ['emptyText' => $work]);

            return;
        }

        $students = [];

        foreach (array_values($work['sheet']) as $index => $student)
        {
            $students[] = [
                'id'       => $student['id'],
                'name'     => $student['name'],
                'code'     => $student['code'],
                'photo'    => $student['photo'] ?? '',
                'tone'     => self::TONES[$index % count(self::TONES)],
                'class'    => $work['class']['name'],
                'marks'    => array_map(fn (?int $mark): string => (string) ($mark ?? 'AB'), $student['marks']),
                'feedback' => $student['comment'],
            ];
        }

        $this->page('teacher/term_marks', $user, [
            'emptyText' => null,
            'class'     => $work['class'],
            'students'  => $students,
            'subjects'  => $work['subjects'],
            'terms'     => $work['terms'],
            'term'      => $work['term'],
            'published' => $work['publication'] === null ? null : date(country('date_format'), strtotime($work['publication']['published_at'])),
            'complete'  => $work['complete'],
            'needed'    => count($work['needed']),
        ]);
    }

    // POST /teacher/marks/save (student_id, term, mark_{subject id} = 0–100 / AB / empty, feedback) — one student's
    // term, from the marks panel (JSON). A change to a published term is written in the Audit Log.
    public function save(): never
    {
        $user = $this->requireRole('teacher');
        $work = $this->workspace($user, (int) $this->postText('term', 2), true);
        $id   = (int) $this->postText('student_id', 11);

        if (!isset($work['sheet'][$id]))
        {
            $this->refuse('That ' . strtolower(word('student')) . ' is not in your ' . strtolower(word('class')) . '.', '/teacher/marks', 404);
        }

        $marks = [];

        foreach ($work['subjects'] as $subjectId => $subject)
        {
            $typed = strtoupper($this->postText("mark_{$subjectId}", 3));

            if ($typed !== '' && $typed !== 'AB' && !preg_match('/^(100|[1-9]?[0-9])$/', $typed))
            {
                $this->refuse("{$subject}: type a mark from 0 to 100, or AB.", '/teacher/marks', 422);
            }

            $marks[$subjectId] = match ($typed)
            {
                ''      => '',
                'AB'    => null,
                default => (int) $typed,
            };
        }

        $student = $work['sheet'][$id];
        $changes = (new TermMarkModel())->save($id, $work['year'], $work['term'], $marks, $this->postText('feedback', 500), $user['id']);

        if ($work['publication'] !== null)
        {
            foreach ($changes as $subjectId => [$before, $after])
            {
                $this->log($user, 'MARK_CHANGED', "{$work['class']['name']} · {$student['name']} · {$work['subjects'][$subjectId]} "
                    . mb_substr(word('term'), 0, 1) . "{$work['term']}: " . ($before === '' ? '—' : $before) . ' → ' . ($after === '' ? '—' : $after));
            }
        }

        $this->sendJson(['ok' => true, 'message' => 'Marks saved.', 'complete' => $this->workspace($user, $work['term'], true)['complete']]);
    }

    // POST /teacher/marks/publish (term) — only when every student needed has every mark (or AB) and a comment
    public function publish(): never
    {
        $user = $this->requireRole('teacher');
        $work = $this->workspace($user, (int) $this->postText('term', 2), true);
        $back = "/teacher/marks?term={$work['term']}";

        if ($work['publication'] !== null)
        {
            $this->refuse(word('term') . " {$work['term']} is already published.", $back, 422);
        }

        if ($work['needed'] === [])
        {
            $this->refuse(words('There are no {students} to publish marks for.'), $back, 422);
        }

        if ($work['missing'] !== [])
        {
            $names = array_column($work['missing'], 'name');
            $this->refuse('Not complete yet: ' . implode(', ', array_slice($names, 0, 5)) . (count($names) > 5 ? ' and ' . (count($names) - 5) . ' more' : '') . '.', $back, 422);
        }

        (new TermMarkModel())->publish($work['class']['id'], $work['year'], $work['term'], $user['id']);
        $this->log($user, 'TERM_PUBLISHED', "{$work['class']['name']} · " . word('term') . " {$work['term']} {$work['year']}");
        $this->setFlash('notice', word('term') . " {$work['term']} published.");
        $this->redirect($back);
    }

    // Everything the page and both actions work from, for this teacher and term: 'class', 'year', 'terms' (the started
    // ones), 'term', 'subjects', 'sheet' (TermMarkModel::sheet()), 'publication', 'needed' (the students who must have
    // this term: in the class since before it started), 'missing' (those of them still without every mark or the
    // comment) and 'complete' (how many are done). Nothing to work on → the reason as text (a POST is refused with it).
    private function workspace(array $user, int $term, bool $posting = false): array|string
    {
        $model  = new TermMarkModel();
        $years  = new SchoolYear();
        $class  = $model->classOf($user['id']);
        $terms  = $years->startedTerms();
        $reason = match (true)
        {
            $class === null => words('You are not a {class} {teacher} this year, so there are no marks to enter.'),
            $terms === []   => words('No {term} has started yet this year.'),
            default         => null,
        };

        if ($reason !== null)
        {
            return $posting ? $this->refuse($reason, '/teacher/marks', 403) : $reason;
        }

        if ($posting && !in_array($term, $terms, true))
        {
            $this->refuse('That ' . strtolower(word('term')) . ' can\'t be changed.', '/teacher/marks', 422);
        }

        $term     = in_array($term, $terms, true) ? $term : end($terms);
        $year     = $years->current();
        $starts   = $years->terms($year)[$term]['starts_on'];
        $subjects = $model->subjectsFor($class['grade_number']);
        $sheet    = $model->sheet($class['id'], $year, $term);
        $needed   = array_filter($sheet, fn (array $student): bool => $student['admission_date'] <= $starts);
        $missing  = array_filter($needed, fn (array $student): bool => $student['comment'] === '' || array_diff_key($subjects, $student['marks']) !== []);

        return [
            'class'       => $class,
            'year'        => $year,
            'terms'       => $terms,
            'term'        => $term,
            'subjects'    => $subjects,
            'sheet'       => $sheet,
            'publication' => $model->publication($class['id'], $year, $term),
            'needed'      => $needed,
            'missing'     => $missing,
            'complete'    => count($needed) - count($missing),
        ];
    }
}
