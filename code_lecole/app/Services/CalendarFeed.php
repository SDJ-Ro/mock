<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CalendarModel;

// The events the dark calendar shows, in the shape calendar.js reads — used by the dashboards, the Academic page and
// sent back after every save / delete in the event pop-up, so the calendar always gets exactly the same thing.
class CalendarFeed
{
    // Everything components/calendar/_calendar.php needs ($calendarConfig): every event this user can see and (staff)
    // the choices for its "Add Event" pop-up. A teacher with no class, club or sport of their own has nothing to add
    // events for, so no pop-up. $only = one group's events ('extracurricular:3': a programme's page); the calendar
    // sends it back with every save, so it keeps showing only those.
    public static function config(array $user, string $only = ''): array
    {
        $choices = can('calendar.create') ? (new CalendarModel())->choicesFor($user) : null;
        $anyGroup = can('posts.any_group');
        $canAdd   = $choices !== null && ($anyGroup || $choices['classes'] !== [] || $choices['extracurriculars'] !== []);

        return [
            'role'      => $user['role'],
            'anyGroup'  => $anyGroup,   // the pop-up: every grade / class, or only "Your {Classes}"
            'events'    => self::around($user, $only),
            'only'      => $only,
            'firstYear' => self::firstYear(),
            'lastYear'  => self::lastYear(),
            'canAdd'    => $canAdd,
            'choices'   => $canAdd ? $choices : null,
        ];
    }

    // Everything this user can see in the years the calendar shows (config/calendar.php: last year → next year),
    // or only one group's events ($only, see config())
    public static function around(array $user, string $only = ''): array
    {
        $events = (new CalendarModel())->between($user, self::firstYear() . '-01-01', self::lastYear() . '-12-31');

        if ($only !== '')
        {
            $events = array_filter($events, fn (array $event): bool => in_array($only, explode(',', $event['scopes'] ?? ''), true));
        }

        return array_map([self::class, 'shape'], array_values($events));
    }

    // The dashboards' "Upcoming Events" card: the next few events this user can see, from today on (same rules as the calendar)
    public static function upcoming(array $user): array
    {
        $events = (new CalendarModel())->between($user, date('Y-m-d'), self::lastYear() . '-12-31');

        return array_map(fn (array $event): array => [
            'date'     => $event['event_date'],                                  // for <time datetime="…">
            'day'      => date('j', strtotime($event['event_date'])),            // 27
            'month'    => date('M', strtotime($event['event_date'])),            // Oct (label.css shows it in capitals)
            'name'     => $event['title'],
            'tag'      => $event['category'],
            'tagColor' => config("calendar.tag_colors.{$event['category']}", 'sand'),
        ], array_slice($events, 0, config('calendar.upcoming_count')));
    }

    // The first and last year the calendar shows (also its year picker)
    public static function firstYear(): int
    {
        return (int) date('Y') - config('calendar.years_back');
    }

    public static function lastYear(): int
    {
        return (int) date('Y') + config('calendar.years_ahead');
    }

    // One database row → one calendar event
    private static function shape(array $event): array
    {
        $audience = explode(',', $event['audience']);
        sort($audience);   // the SET comes back in table order; sorted, the comparisons below are simple
        $canDelete = can_manage($event);
        $canEdit   = $canDelete && $event['event_date'] >= date('Y-m-d');   // past events can only be deleted

        return [
            'id'            => (int) $event['id'],
            'date'          => $event['event_date'],
            'time'          => event_time($event),
            'title'         => $event['title'],
            'details'       => $event['details'] ?? '',
            'category'      => $event['category'],
            'audienceLabel' => self::audienceLabel($audience),
            'authorLabel'   => 'Posted by ' . ($event['author_name'] ?? 'Former staff'),
            'canEdit'       => $canEdit,     // shows the pencil
            'canDelete'     => $canDelete,   // shows the bin
        ] + ($canEdit ? self::forEditing($event, $audience) : []);
    }

    // What the event pop-up needs to open an event for editing: its times, groups and audience as the form's values
    private static function forEditing(array $event, array $audience): array
    {
        $scopes = array_map(function (string $scope): array
        {
            [$type, $id] = explode(':', $scope, 2);
            return ['type' => $type, 'id' => $id];
        }, explode(',', $event['scopes'] ?? 'school:'));

        return [
            'start'    => $event['start_time'] ? substr($event['start_time'], 0, 5) : '',   // '' = all day
            'end'      => $event['end_time'] ? substr($event['end_time'], 0, 5) : '',
            'scopes'   => $scopes,
            'audience' => $audience === self::everyone() ? ['all'] : $audience,
        ];
    }

    // The little pill on each event: "All School", "Students & Parents", "Staff Only"…
    private static function audienceLabel(array $audience): string
    {
        return match ($audience)
        {
            self::everyone()                       => 'All ' . word('institution'),
            ['parent', 'student']                  => plural('student') . ' & ' . plural('parent'),
            ['management', 'teacher'], ['teacher'] => 'Staff Only',
            ['student']                            => plural('student') . ' Only',
            ['parent']                             => plural('parent') . ' Only',
            default                                => implode(', ', array_map('plural', $audience)),
        };
    }

    // Every role an event can be for (config/posting.php), sorted like $audience above
    private static function everyone(): array
    {
        $roles = config('posting.audience');
        sort($roles);

        return $roles;
    }
}
