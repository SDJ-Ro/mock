<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TermMarkModel;

// The maths of one published term, for a whole grade (all its classes), worked out from the marks every time — never
// stored, so a corrected mark changes every total, position and award at once. The rules: config/white_label/rules.php.
//   a student:  total and out of (only the subjects sat: AB is left out), average (1 decimal; null when every subject
//               is AB), position in the class (1 + how many have a higher average: 1, 2, 2, 4), and the awards
//   a class:    each subject's highest mark and average, and the class's term average
//   the grade:  its term average (the record book's trend chart)
// Used by RecordBook, the dashboards and the Academic page's chart.
final class TermResults
{
    private static array $worked = [];   // each grade + term once per page

    // → ['students' => [id => ['class_id', 'marks' => [subject => mark or null], 'total', 'outOf', 'average',
    //                           'hasAb', 'position', 'awards' => ['commended', 'top_of_class', 'top_of_grade' (those won)]]],
    //    'classes'  => [class id => ['highest' => [subject => mark], 'average' => [subject => average], 'termAverage']],
    //    'gradeAverage' => number or null]
    public static function forGrade(int $year, int $term, int $gradeNumber): array
    {
        return self::$worked["{$year}-{$term}-{$gradeNumber}"] ??= self::work($year, $term, $gradeNumber);
    }

    private static function work(int $year, int $term, int $gradeNumber): array
    {
        $model    = new TermMarkModel();
        $students = [];
        $classes  = [];

        foreach ($model->gradeTerm($year, $term, $gradeNumber) as $row)
        {
            $students[$row['student_id']]['class_id']               = $row['class_id'];
            $students[$row['student_id']]['marks'][$row['subject']] = $row['mark'];
        }

        foreach ($students as &$student)
        {
            $sat                = array_filter($student['marks'], fn (?int $mark): bool => $mark !== null);
            $student['total']   = array_sum($sat);
            $student['outOf']   = count($sat) * 100;
            $student['average'] = $sat === [] ? null : round(array_sum($sat) / count($sat), 1);
            $student['hasAb']   = count($sat) < count($student['marks']);

            foreach ($sat as $subject => $mark)
            {
                $classes[$student['class_id']]['marks'][$subject][] = $mark;
            }
        }
        unset($student);

        // Positions: 1 + how many in the same class have a higher average
        foreach ($students as &$student)
        {
            $student['position'] = $student['average'] === null ? null : 1 + count(array_filter($students,
                fn (array $other): bool => $other['class_id'] === $student['class_id'] && $other['average'] > $student['average']));
        }
        unset($student);

        // Awards: never with an AB; the best = the highest average among those without one
        $eligible  = array_filter($students, fn (array $student): bool => !$student['hasAb'] && $student['average'] !== null);
        $bestOf    = fn (array $group): ?float => $group === [] ? null : max(array_column($group, 'average'));
        $gradeBest = $model->gradePublished($year, $term, $gradeNumber) ? $bestOf($eligible) : null;

        foreach ($students as $id => &$student)
        {
            $classBest         = $bestOf(array_filter($eligible, fn (array $other): bool => $other['class_id'] === $student['class_id']));
            $student['awards'] = !isset($eligible[$id]) ? [] : array_keys(array_filter([
                'commended'    => rule('commended.on') && $student['average'] >= rule('commended.average'),
                'top_of_class' => rule('top_of_class.on') && $student['average'] === $classBest,
                'top_of_grade' => rule('top_of_grade.on') && $student['average'] === $gradeBest,
            ]));
        }
        unset($student);

        foreach ($classes as $classId => $class)
        {
            $averages             = array_filter(array_column(array_filter($students, fn (array $s): bool => $s['class_id'] === $classId), 'average'), 'is_float');
            $classes[$classId]    = [
                'highest'     => array_map('max', $class['marks']),
                'average'     => array_map(fn (array $marks): float => round(array_sum($marks) / count($marks), 1), $class['marks']),
                'termAverage' => $averages === [] ? null : round(array_sum($averages) / count($averages), 1),
            ];
        }

        $averages = array_filter(array_column($students, 'average'), 'is_float');

        return [
            'students'     => $students,
            'classes'      => $classes,
            'gradeAverage' => $averages === [] ? null : round(array_sum($averages) / count($averages), 1),
        ];
    }
}
