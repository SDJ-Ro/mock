<?php

declare(strict_types=1);

namespace App\Services;

// The data the achievements timeline shows (components/_achievements_timeline.php), worked out once:
// used by the Achievements pages (AchievementController, example data), People's profile pop-up and the parent's
// Child profile (real ones).
final class AchievementTimeline
{
    // The four number cards above a timeline (_metric_card.php): total, the last year, clubs / sports, academic.
    // "Earned in Last Year" = this year and last year (worked out from today, never a typed year).
    public static function metrics(array $items): array
    {
        $count = fn (callable $test): string => (string) count(array_filter($items, $test));

        return [
            ['color' => 'sand',      'icon' => 'trophy',          'label' => 'Total Achievements', 'value' => (string) count($items)],
            ['color' => 'moss',      'icon' => 'award',           'label' => 'Earned in Last Year',
             'value' => $count(fn (array $item): bool => (int) substr($item['date'], 0, 4) >= (int) date('Y') - 1)],
            ['color' => 'maroon',    'icon' => 'extracurricular', 'label' => 'Extracurriculars',
             'value' => $count(fn (array $item): bool => in_array($item['group'], ['sports', 'clubs'], true))],
            ['color' => 'lightblue', 'icon' => 'graduationCap',   'label' => 'Academic Honours',
             'value' => $count(fn (array $item): bool => $item['group'] === 'academic')],
        ];
    }

    // The items grouped by year, newest year first: [2025 => [item + 'month' ('Aug 2025')], …]. Each item has 'date'.
    public static function byYear(array $items): array
    {
        $years = [];

        foreach ($items as $item)
        {
            $years[(int) substr($item['date'], 0, 4)][] = $item + ['month' => date(country('month_format'), strtotime($item['date']))];
        }

        krsort($years);

        return $years;
    }

    // A student's achievements (AchievementModel::ofStudent()) as timeline items: [group, title, date, description].
    // The group: a club's or sport's achievement → its type's (config/extracurricular.php 'types'), else the category's
    // (config/achievements.php 'categories').
    public static function items(array $achievements): array
    {
        return array_map(fn (array $achievement): array => [
            'group'       => $achievement['extracurricular_type'] !== null
                ? config("extracurricular.types.{$achievement['extracurricular_type']}.group")
                : config("achievements.categories.{$achievement['category']}.group", 'clubs'),
            'title'       => $achievement['title'],
            'date'        => $achievement['achieved_on'],
            'description' => $achievement['summary'],
        ], $achievements);
    }
}
