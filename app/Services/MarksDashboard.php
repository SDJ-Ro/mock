<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\TermMarkModel;

// The dashboards' marks pieces, from this year's published terms (TermResults / RecordBook):
//   student  "Class Position" card · the score chart: my mark and the highest in the class, per subject and term
//   parent   "Average Term Mark" + "Class Position" cards · the chart: their child's mark and the class average
//   teacher  "Average Term Mark" card (their class, as class teacher) · the chart: the class average and the highest
// A card in app/Mock/dashboards.php with 'marks' => 'position' | 'average' gets its number here; before anything is
// published it shows "—" and the chart is left out.
final class MarksDashboard
{
    public static function fill(string $role, array $user, array $board): array
    {
        $results = match ($role)
        {
            'student' => self::ofStudent($user['id'], 'highest'),
            'parent'  => $user['child'] === null ? null : self::ofStudent($user['child'], 'classAverage'),
            'teacher' => self::ofClass($user['id']),
            default   => null,
        };

        foreach ($board['metrics'] as &$card)
        {
            if (isset($card['marks']))
            {
                $card['value'] = $results[$card['marks']] ?? '—';
                $card['label'] = $card['label'] . ($results === null ? '' : ' (' . word('term') . " {$results['term']})");
            }
        }
        unset($card);

        if (($board['chartConfig']['variant'] ?? '') === 'scores')
        {
            $keys                 = array_column($board['chartConfig']['series'], 'key');
            $board['chartConfig'] = $results === null ? null : [
                'title' => $board['chartConfig']['title'] . (isset($results['class']) ? ' (' . word('class') . " {$results['class']})" : ''),
                'terms' => array_map(fn (array $rows): array => array_map(fn (array $row): array => ['label' => $row[0], $keys[0] => $row[1], $keys[1] => $row[2]], $rows), $results['chart']),
                'term'  => word('term') . " {$results['term']}",
            ] + $board['chartConfig'];
        }

        return $board;
    }

    // A student's newest published term this year → ['term', 'position', 'average', 'chart' => ['Term 1' => [[subject,
    // their mark, the class's highest or average]]]], or null before any is published
    private static function ofStudent(int $studentId, string $compare): ?array
    {
        $book = RecordBook::forStudent($studentId)[(new SchoolYear())->current()] ?? null;

        if ($book === null)
        {
            return null;
        }

        $chart = [];

        foreach ($book['terms'] as $term => $record)
        {
            $chart[word('term') . " {$term}"] = array_map(fn (array $row): array => [$row['subject'], is_int($row['mark']) ? $row['mark'] : 0, $row[$compare]], $record['marks']);
        }

        $latest = array_key_last($book['terms']);

        return ['term' => $latest, 'position' => $book['terms'][$latest]['placement'], 'average' => $book['terms'][$latest]['average'], 'chart' => $chart];
    }

    // The class a teacher is class teacher of, in its newest published term this year → ['term', 'average', 'chart' =>
    // ['Term 1' => [[subject, the class average, the highest]]]], or null
    private static function ofClass(int $teacherId): ?array
    {
        $model = new TermMarkModel();
        $class = $model->classOf($teacherId);
        $year  = (new SchoolYear())->current();
        $chart = [];
        $term  = null;

        foreach (array_keys($class === null ? [] : $model->publishedTerms($class['id'], $year)) as $term)
        {
            $results = TermResults::forGrade($year, $term, $class['grade_number'])['classes'][$class['id']] ?? ['average' => [], 'highest' => [], 'termAverage' => null];
            $chart[word('term') . " {$term}"] = array_map(fn (string $subject): array => [$subject, $results['average'][$subject], $results['highest'][$subject]], array_keys($results['average']));
            $average = $results['termAverage'];
        }

        return $term === null ? null : ['term' => $term, 'class' => $class['name'], 'average' => $average === null ? '—' : number_format($average, 1) . '%', 'chart' => $chart];
    }
}
