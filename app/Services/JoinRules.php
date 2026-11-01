<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SchoolYear;
use DateTime;

// Who may ask to join a club / sport, in one place. The programme's settings (Edit → Who Can Join), the limit per age
// group (rules.php, or the programme's own), and Academic Watch. Used by the list's cards (the reason shows in place of
// Interest) and by ExtracurricularController::interest() (which asks the same question again before saving).
final class JoinRules
{
    // This school year's cut-off day, worked out once per page (it is asked for every member of every programme)
    private static ?DateTime $cutoff = null;

    // A student's age group: their age on the cut-off day of this school year (rules.php 'age_cutoff', e.g. 31 January),
    // in config/extracurricular.php's groups ("Under 17"), or null without a date of birth
    public static function ageGroup(?string $dateOfBirth): ?string
    {
        if ($dateOfBirth === null)
        {
            return null;
        }

        self::$cutoff ??= new DateTime((new SchoolYear())->current() . '-' . rule('age_cutoff'));
        $age            = (new DateTime($dateOfBirth))->diff(self::$cutoff)->y;

        foreach (config('extracurricular.age_groups') as $group => $under)
        {
            if ($under === null || $age < $under)
            {
                return $group;
            }
        }

        return null;
    }

    // Why this student can't ask to join this programme now (the short words shown instead of Interest), or null: they may.
    //   $programme  its row (ExtracurricularModel)    $student  ['gender', 'grade_number', 'date_of_birth'] (PeopleModel::find())
    //   $watch      AcademicWatch::of()               $birthdays  its members' dates of birth (only members count toward the limit)
    public static function whyNot(array $programme, array $student, ?array $watch, array $birthdays): ?string
    {
        $full = self::isFull($programme, $student, $birthdays);

        return match (true)
        {
            $programme['archived_at'] !== null                                          => 'Archived',
            $programme['review'] !== 'approved'                                         => 'Waiting for approval',
            !$programme['is_open']                                                      => 'Intake closed',
            $programme['open_to'] === 'boys' && $student['gender'] !== 'Male'          => 'Boys only',
            $programme['open_to'] === 'girls' && $student['gender'] !== 'Female'       => 'Girls only',
            $programme['lowest_grade'] !== null && ($student['grade_number'] ?? 0) < $programme['lowest_grade'] => 'From ' . grade_name((int) $programme['lowest_grade']),
            $full !== null                                                              => $full,
            ($watch['locked'] ?? false)                                                 => 'Academic Watch',
            default                                                                     => null,
        };
    }

    // Is the student's age group of this programme full (the whole club, without age groups)? → "Full (Under 17)" / "Full",
    // or null. Only members count; the teacher in charge's Accept asks this again.
    public static function isFull(array $programme, array $student, array $birthdays): ?string
    {
        $group = $programme['uses_age_groups'] ? self::ageGroup($student['date_of_birth']) : null;
        $limit = $programme['age_group_limit'] ?? rule('age_group_limit');
        $taken = $group === null ? count($birthdays) : count(array_filter($birthdays, fn (?string $birthday): bool => self::ageGroup($birthday) === $group));

        return $taken < $limit ? null : 'Full' . ($group !== null ? " ({$group})" : '');
    }
}
