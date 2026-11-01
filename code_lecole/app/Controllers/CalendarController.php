<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CalendarModel;
use App\Services\CalendarFeed;
use Core\Controller;
use Core\Validator;

// Saving and deleting calendar events. The dark calendar on every dashboard shows the
// events; its "Add event" pop-up (calendar/event-editor.js) sends here in the background. Every answer is JSON and carries the
// user's fresh events, so the calendar redraws without reloading.
//
// What the pop-up offered (CalendarModel::choicesFor) is also the rule: only those groups can be saved.
// A clash with an event for the same students is a warning, not a ban: "Allow parallel schedule" saves it anyway.
class CalendarController extends Controller
{
    private const RULES = [
        'title'          => 'required|max:150',
        'details'        => 'max:500',
        'event_date'     => 'required|date',
        'start_time'     => 'time',   // empty = all day
        'end_time'       => 'time',
        'category'       => 'required|max:20',
        'academic_level' => 'in:grade,class',
        'allow_parallel' => 'in:1',
    ];

    // POST /{role}/calendar/save   (no id = a new event)
    public function save(string $role): never
    {
        $user     = $this->guard($role);
        $id       = (int) $this->postText('id') ?: null;
        $existing = $id === null ? null : $this->manageable($user, $id);
        $data     = $this->checkedForm();
        $choices  = (new CalendarModel())->choicesFor($user);
        $lastDay  = CalendarFeed::lastYear() . '-12-31';

        // Only from today to the end of the last year the calendar shows; a past event stays as it was (it can be deleted)
        if ($existing !== null && $existing['event_date'] < date('Y-m-d'))
        {
            $this->fail('Past events can\'t be changed, only deleted.');
        }

        if ($data['event_date'] < date('Y-m-d') || $data['event_date'] > $lastDay)
        {
            $this->fail('Events can be added from today until ' . date(country('date_format'), strtotime($lastDay)) . '.', ['event_date' => 'Choose a date from today on.']);
        }

        if (!in_array($data['category'], config("calendar.categories.{$role}"), true))
        {
            $this->fail('Please choose a category from the list.', ['category' => 'Please choose a category from the list.']);
        }

        // Who it is for, from the part of the pop-up the category opened
        [$scopes, $missing] = match (true)
        {
            $data['category'] === 'General'         => [[['school', null]], ''],
            $data['category'] === 'Extracurricular' => [$this->picked('extracurriculars', 'extracurricular', $choices['extracurriculars']), 'Please select at least one club or sport.'],
            $data['academic_level'] === 'grade'     => [$this->picked('grades', 'grade', $choices['grades']), 'Please select at least one ' . strtolower(word('grade')) . '.'],
            default                                 => [$this->picked('classes', 'class', $choices['classes']), 'Please select at least one ' . strtolower(word('class')) . '.'],
        };

        if ($scopes === [])
        {
            $this->fail($missing);
        }

        // All day (no start time) → no end time either, and nothing to clash with
        if ($data['start_time'] === '')
        {
            $data['end_time'] = '';
        }

        if ($data['end_time'] !== '' && $data['end_time'] <= $data['start_time'])
        {
            $this->fail('End time must be later than start time.', ['time' => 'End time must be later than start time.']);
        }

        $clash = $data['start_time'] === '' ? null
            : (new CalendarModel())->clash($data['event_date'], $data['start_time'], $data['end_time'] ?: null, $scopes, $id);

        if ($clash !== null && $data['allow_parallel'] !== '1')
        {
            $this->fail("Schedule conflict: '{$clash['title']}' is already scheduled for " . event_time($clash)
                . ". Check 'Allow parallel schedule' below to proceed.", [], ['clash' => true]);
        }

        $event = [
            'title'      => $data['title'],
            'details'    => $data['details'] ?: null,
            'category'   => $data['category'],
            'audience'   => implode(',', $this->audience($role)),
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'] ?: null,
            'end_time'   => $data['end_time'] ?: null,
        ];

        if ($existing === null)
        {
            $event += ['author_id' => $user['id'], 'author_role' => $user['role']];
        }

        $id = (new CalendarModel())->save($id, $event, $scopes);
        $this->log($user, $existing === null ? 'EVENT_ADDED' : 'EVENT_UPDATED', "#{$id} {$event['title']}");

        if ($clash !== null)
        {
            $this->log($user, 'EVENT_CLASH_ACCEPTED', "#{$id} {$event['title']} alongside \"{$clash['title']}\"");
        }

        $this->done('Event saved.', $user, $event['event_date']);
    }

    // POST /{role}/calendar/delete
    public function delete(string $role): never
    {
        $user  = $this->guard($role);
        $event = $this->manageable($user, (int) $this->postText('id'));

        (new CalendarModel())->delete((int) $event['id']);
        $this->log($user, 'EVENT_DELETED', "#{$event['id']} {$event['title']}");

        $this->done('Event deleted.', $user, $event['event_date']);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // Signed in with this role, and the role may add events (config/permissions.php)
    private function guard(string $role): array
    {
        $user = $this->requireRole($role);

        if (!can('calendar.create'))
        {
            $this->fail('You are not allowed to change the calendar.', [], [], 403);
        }

        return $user;
    }

    // An event this user may change: can't see it → 404, can see but not change it → 403
    private function manageable(array $user, int $id): array
    {
        $event = (new CalendarModel())->findFor($user, $id);

        if ($event === null)
        {
            $this->fail('That event no longer exists.', [], [], 404);
        }

        if (!can_manage($event))
        {
            $this->fail('You can only change your own events.', [], [], 403);
        }

        return $event;
    }

    // Every field against RULES → the clean values (the CSRF token was already checked by core/Router.php)
    private function checkedForm(): array
    {
        $data = [];

        foreach (array_keys(self::RULES) as $field)
        {
            $data[$field] = $this->postText($field, 1000);
        }

        // The pop-up sends each time as an hour and a minute dropdown → "09:30" (no hour = all day)
        $data['start_time'] = $this->joinedTime('start');
        $data['end_time']   = $this->joinedTime('end');

        $errors = Validator::check($data, self::RULES, ['event_date' => 'Date', 'start_time' => 'Start time', 'end_time' => 'End time']);

        if ($errors !== [])
        {
            $this->fail(reset($errors), $errors);
        }

        return $data;
    }

    // "start" → start_hour + start_minute from the form as "09:30"; '' when no hour was chosen
    private function joinedTime(string $which): string
    {
        $hour = $this->postText("{$which}_hour", 2);

        return $hour === '' ? '' : $hour . ':' . $this->postText("{$which}_minute", 2);
    }

    // The groups of one kind ticked in the pop-up, keeping only ones this user was offered → [['class', 9], …]
    private function picked(string $field, string $type, array $offered): array
    {
        $ids = array_unique(array_map('intval', $this->postList($field)));

        return array_values(array_map(fn (int $id): array => [$type, $id], array_filter($ids, fn (int $id): bool => isset($offered[$id]))));
    }

    // The roles who will see it. 'all' (or nothing chosen) → everyone this role may write to.
    private function audience(string $role): array
    {
        $offered = config("calendar.audience.{$role}");
        $picked  = array_intersect($this->postList('audience'), $offered);

        return in_array('all', $picked, true) || $picked === [] ? array_values(array_diff($offered, ['all'])) : array_values($picked);
    }

    // Success: the message + this user's fresh events (a programme's page: only its own, 'only') + the day to show
    private function done(string $message, array $user, string $date): never
    {
        $only = preg_match('/^extracurricular:\d+$/', $this->postText('only', 30)) ? $this->postText('only', 30) : '';

        $this->sendJson(['ok' => true, 'message' => $message, 'events' => CalendarFeed::around($user, $only), 'date' => $date]);
    }

    // Failure: a message for the pop-up's red banner, the field errors, anything else the script needs (e.g. 'clash')
    private function fail(string $message, array $errors = [], array $extra = [], int $status = 422): never
    {
        $this->sendJson(['ok' => false, 'message' => $message, 'errors' => $errors] + $extra, $status);
    }
}
