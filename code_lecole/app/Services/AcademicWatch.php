<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExtracurricularModel;

// "Academic Watch" (config/white_label/rules.php): more than 'subjects' marks under the pass mark in a student's latest
// published term (any year; AB doesn't count) → they can't join new clubs / sports. Worked out from the marks every
// time, never stored; management / admin may lift it for that term (academic_watch_lifts) — a newer term decides again.
final class AcademicWatch
{
    private static array $known = [];   // each student once per page

    // → null (not on watch, or the rule / term marks are off), or ['low' (how many), 'year', 'term', 'lifted' (null or
    //   ['reason', 'lifted_at', 'lifted_by_name']), 'locked' (on watch and not lifted)]
    public static function of(int $studentId): ?array
    {
        return array_key_exists($studentId, self::$known) ? self::$known[$studentId] : self::$known[$studentId] = self::work($studentId);
    }

    private static function work(int $studentId): ?array
    {
        if (!rule('academic_watch.on') || !feature('term_marks'))
        {
            return null;
        }

        $books = RecordBook::forStudent($studentId);
        $year  = array_key_last($books);

        if ($year === null)
        {
            return null;
        }

        $term = array_key_last($books[$year]['terms']);
        $low  = count(array_filter(array_column($books[$year]['terms'][$term]['marks'], 'mark'), fn (int|string $mark): bool => is_int($mark) && $mark < rule('pass_mark')));

        if ($low <= rule('academic_watch.subjects'))
        {
            return null;
        }

        $lifted = (new ExtracurricularModel())->watchLift($studentId, $year, $term);

        return ['low' => $low, 'year' => $year, 'term' => $term, 'lifted' => $lifted, 'locked' => $lifted === null];
    }
}
