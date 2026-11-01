<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TermMarkModel;

// A student's Digital Record Book: one book per school year (a repeated grade gets its own: "Grade 7 (Repeat)"), each
// with its PUBLISHED terms and what the page shows worked out (TermResults): the marks (AB for absent), highest in the
// class, total, out of, average ("80.3%"), class position ("14th"), the comment and who wrote it, and the date it was
// published in the country's format. A term the student has no marks in (they joined later) is left out.
// Used by the student's Academic Records, its printable page, People's pop-up and the parent's Child profile.
final class RecordBook
{
    // → [year => ['grade', 'label' ("Grade 7" / "Grade 7 (Repeat)"), 'class', 'terms' => [term => […]]]], oldest first
    public static function forStudent(int $studentId): array
    {
        $model = new TermMarkModel();
        $books = [];
        $seen  = [];

        foreach ($model->yearsOf($studentId) as $year)
        {
            $terms = [];

            foreach ($model->publishedTerms($year['class_id'], $year['year']) as $term => $publication)
            {
                $results = TermResults::forGrade($year['year'], $term, $year['grade_number']);
                $me      = $results['students'][$studentId] ?? null;

                if ($me !== null)
                {
                    $terms[$term] = self::term($me, $results, $model->comment($studentId, $year['year'], $term), $publication['published_at']);
                }
            }

            if ($terms !== [])
            {
                $books[$year['year']] = [
                    'grade' => $year['grade_number'],
                    'label' => grade_name($year['grade_number']) . (isset($seen[$year['grade_number']]) ? ' (Repeat)' : ''),
                    'class' => $year['class_name'],
                    'terms' => $terms,
                ];
                $seen[$year['grade_number']] = true;
            }
        }

        return $books;
    }

    // What the record book's views need (_digital_record_book.php): the books, and where it opens — the newest year,
    // its newest published term. $exportUrl: the printable page, or null (no Export PDF button)
    public static function shown(array $books, ?string $exportUrl): array
    {
        $year = array_key_last($books);

        return ['records' => $books, 'year' => $year, 'term' => $year === null ? null : array_key_last($books[$year]['terms']), 'exportUrl' => $exportUrl];
    }

    // The automatic achievements in a student's books, as achievements timeline items (AchievementTimeline):
    // Commended, Top of Class, Top of Grade — on the day the term was published
    public static function awards(array $books): array
    {
        $titles = ['commended' => 'Commended', 'top_of_class' => 'Top of ' . word('class'), 'top_of_grade' => 'Top of ' . word('grade')];
        $items  = [];

        foreach ($books as $book)
        {
            foreach ($book['terms'] as $term => $record)
            {
                foreach ($record['awards'] as $award)
                {
                    $items[] = [
                        'group'       => 'academic',
                        'title'       => "{$titles[$award]} — {$book['label']}, " . word('term') . " {$term}",
                        'date'        => $record['publishedOn'],
                        'description' => word('term') . " average {$record['average']}" . match ($award)
                        {
                            'top_of_class' => ", the best in {$book['class']}.",
                            'top_of_grade' => ', the best in ' . grade_name($book['grade']) . '.',
                            default        => '.',
                        },
                    ];
                }
            }
        }

        return $items;
    }

    // One term as the book shows it
    private static function term(array $me, array $results, ?array $comment, string $publishedAt): array
    {
        $class = $results['classes'][$me['class_id']];
        $rows  = [];

        foreach ($me['marks'] as $subject => $mark)
        {
            $rows[] = ['subject' => $subject, 'mark' => $mark ?? 'AB', 'highest' => $class['highest'][$subject] ?? '—', 'classAverage' => $class['average'][$subject] ?? 0];
        }

        return [
            'marks'        => $rows,
            'total'        => $me['average'] === null ? '—' : $me['total'],
            'outOf'        => $me['average'] === null ? '—' : $me['outOf'],
            'average'      => $me['average'] === null ? '—' : number_format($me['average'], 1) . '%',
            'placement'    => $me['position'] === null ? '—' : ordinal($me['position']),
            'mine'         => (int) round($me['average'] ?? 0),                    // the trend chart's bars
            'gradeAverage' => (int) round($results['gradeAverage'] ?? 0),
            'awards'       => $me['awards'],
            'teacher'      => $comment['teacher'] ?? '—',
            'feedback'     => $comment['comment'] ?? '',
            'publishedOn'  => substr($publishedAt, 0, 10),
            'date'         => date(country('date_format'), strtotime($publishedAt)),
        ];
    }
}
