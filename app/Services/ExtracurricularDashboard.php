<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExtracurricularModel;

// The dashboards' extracurricular pieces, from the real memberships (running programmes: approved, not archived):
//   student / parent     "Sports / Clubs Enrolled" · "Sports & Clubs Active": the student's (the child's) memberships
//   teacher              "Total Enrolled Players": the members of the programmes they run · the donuts: members per
//                        programme they run, and the biggest one's members per grade (none run: no donuts)
//   management / admin   the programmes card · the chart: students and sports players per grade · the donuts:
//                        members per sport, members per club
// A card in app/Mock/dashboards.php with 'extracurricular' => 'members' | 'players' | 'programmes' gets its number
// here; a donut with 'donut' => 'sports' | 'clubs' | 'mine' | 'grades' its slices (the biggest few + "Others",
// config/extracurricular.php); the 'counts' chart its grades.
final class ExtracurricularDashboard
{
    public static function fill(string $role, array $user, array $board): array
    {
        $model    = new ExtracurricularModel();
        $running  = $model->memberCounts();
        $runs     = $role !== 'teacher' ? [] : array_column($model->runBy($user['id']), 'id');   // read once
        $theirs   = array_values(array_filter($running, fn (array $row): bool => in_array($row['id'], $runs, true)));
        $student  = match ($role)
        {
            'student' => $user['id'],
            'parent'  => $user['child'],
            default   => null,
        };
        $numbers  = [
            'members'    => $student === null ? 0 : count(array_filter($model->statesOf($student), fn (array $state): bool => $state['status'] === 'member')),
            'players'    => array_sum(array_column($theirs, 'members')),
            'programmes' => count($running),
        ];
        $biggest  = $theirs[0] ?? null;   // memberCounts(): the biggest first
        $byGrade  = $biggest === null ? [] : $model->membersByGrade($biggest['id']);

        foreach ($board['metrics'] as &$card)
        {
            if (isset($card['extracurricular']))
            {
                $card['value'] = number_format($numbers[$card['extracurricular']]);
            }
        }
        unset($card);

        // The donuts: a teacher who runs nothing has none
        $board['donuts'] = array_values(array_filter(array_map(fn (array $donut): ?array => match ($donut['donut'] ?? null)
        {
            'sports' => $donut + ['slices' => self::slices(array_filter($running, fn (array $row): bool => $row['type'] === 'sport'))],
            'clubs'  => $donut + ['slices' => self::slices(array_filter($running, fn (array $row): bool => $row['type'] === 'club'))],
            'mine'   => $biggest === null ? null : $donut + ['slices' => self::slices($theirs)],
            'grades' => $biggest === null ? null : ['title' => "{$donut['title']} {$biggest['name']}"] + $donut + ['slices' => self::slices(array_map(
                fn ($grade, $members): array => ['name' => grade_name((int) $grade), 'members' => (int) $members], array_keys($byGrade), $byGrade))],
            default  => $donut,
        }, $board['donuts'] ?? [])));

        // The chart of counts: every grade's students, and how many of them play a sport
        if (($board['chartConfig']['variant'] ?? '') === 'counts')
        {
            $grades                       = $model->membersByGradeOfType('sport');
            $board['chartConfig']['data'] = array_map(fn (array $grade): array => ['label' => grade_name((int) $grade['grade']),
                'total' => (int) $grade['total'], 'sports' => (int) $grade['members']], $grades);
            $board['chartConfig']['ticks'] = self::ticks(max([0, ...array_column($board['chartConfig']['data'], 'total')]));
        }

        return $board;
    }

    // Rows with 'name' + 'members' as donut slices: the biggest few (none without members), the rest as one "Others",
    // each with the next colour
    private static function slices(array $rows): array
    {
        $rows   = array_values(array_filter($rows, fn (array $row): bool => (int) $row['members'] > 0));
        usort($rows, fn (array $a, array $b): int => (int) $b['members'] <=> (int) $a['members']);
        $keep   = (int) config('extracurricular.donut_slices');
        $shown  = array_slice($rows, 0, $keep);
        $rest   = array_sum(array_column(array_slice($rows, $keep), 'members'));
        $shown  = $rest > 0 ? [...$shown, ['name' => 'Others', 'members' => $rest]] : $shown;
        $colour = config('extracurricular.donut_colours');

        return array_map(fn (array $row, int $i): array => ['name' => $row['name'], 'value' => (int) $row['members'], 'color' => $colour[$i % count($colour)]],
            $shown, array_keys($shown));
    }

    // 5 evenly spaced labels for the side of a chart of counts, from 0 to just above the highest value: the step is a
    // "nice" number, 1, 2 or 5 × a power of ten (13 → 0, 5, 10, 15, 20)
    private static function ticks(int $highest): array
    {
        $rough = max($highest, 4) / 4;
        $power = 10 ** floor(log10($rough));
        $step  = $power * match (true)
        {
            $rough <= $power     => 1,
            $rough <= 2 * $power => 2,
            $rough <= 5 * $power => 5,
            default              => 10,
        };

        return array_map(fn (int $i): int => (int) round($step * $i), range(0, 4));
    }
}
