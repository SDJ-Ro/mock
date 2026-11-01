<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AchievementModel;
use App\Models\ExtracurricularModel;
use App\Models\NoticeModel;
use App\Models\TeamModel;

// The real programmes (ExtracurricularModel) shaped for the Extracurricular pages: the list's cards and one
// programme's page (its header, staff cards, members, teams (TeamModel), achievements (AchievementModel), join
// requests, Notice Board, the Edit pop-up's values), and one of its achievements on its own page.
// No decisions here: who may see or change what stays in ExtracurricularController; who may join: JoinRules.
final class Extracurriculars
{
    // The list's cards, filtered (search, type) and in this order: the student's own programmes first, then the
    // approved ones before those still waiting, then by name. $student: whose Interest / Enrolled states to show
    // (PeopleModel::find(): the student, or the child a parent views), or null (staff).
    public static function cards(array $rows, ?array $student, array $filters): array
    {
        $model     = new ExtracurricularModel();
        $states    = $student === null ? [] : $model->statesOf($student['id']);
        $watch     = $student === null ? null : AcademicWatch::of($student['id']);
        $birthdays = $student === null ? [] : $model->memberBirthdays();
        $search    = mb_strtolower($filters['q']);
        $cards     = [];

        foreach ($rows as $row)
        {
            $card = self::card($row, $states[$row['id']] ?? null);

            if ($student !== null && $card['state'] === 'open')
            {
                $card['whyNot'] = JoinRules::whyNot($row, $student, $watch, $birthdays[$row['id']] ?? []);
            }

            $card['watched'] = $card['state'] === 'enrolled' && ($watch['locked'] ?? false);
            $cards[]         = $card;
        }

        $cards = array_filter($cards, fn (array $card): bool => ($filters['type'] === '' || $card['type'] === $filters['type'])
            && ($search === '' || str_contains(mb_strtolower("{$card['name']} {$card['category']} {$card['tic']['name']} {$card['coachName']}"), $search)));

        usort($cards, fn (array $a, array $b): int => [$a['state'] !== 'enrolled', $a['review'] !== 'approved', $a['name']]
            <=> [$b['state'] !== 'enrolled', $b['review'] !== 'approved', $b['name']]);

        return $cards;
    }

    // One programme's page: the card + what its panels show. $current: the student's current row (or null: staff, or
    // never asked); $member: may this person see the members-only parts (the coach's contact, the roster);
    // $studentId: whose row is "You" in the roster; $age: the age-group filter asked for (one not in 'filterGroups' = all).
    public static function page(array $row, ?array $current, bool $member, ?int $studentId, string $age, array $user): array
    {
        $model     = new ExtracurricularModel();
        $card      = self::card($row, $current);
        $members   = array_map(fn (array $person): array => self::person($person), $model->people($row['id'], 'member'));
        $requests  = array_map(fn (array $person): array => self::person($person), $model->people($row['id'], 'teacher'));
        $ageGroups = $row['uses_age_groups'] ? array_keys(config('extracurricular.age_groups')) : [];
        $teams     = array_map(fn (array $team): array => self::team($team, $card['image']), (new TeamModel())->ofProgramme($row['id']));
        $placed    = array_merge([], ...array_map(fn (array $team): array => array_column($team['roster'], 'id'), $teams));

        // The filter's choices: the programme's age groups and its teams' (a team keeps one after the switch goes off)
        $filterGroups = array_values(array_filter(array_unique([...$ageGroups, ...array_column($teams, 'ageGroup')])));
        $age          = in_array($age, $filterGroups, true) ? $age : '';
        $inAge        = fn (array $item): bool => $age === '' || $item['ageGroup'] === $age;

        // Not in a team yet, each with the teams of their own age group to go to (a team with none takes any age)
        $unassigned = array_map(fn (array $person): array => $person + ['teams' => array_map(fn (array $team): array => ['value' => $team['id'], 'label' => $team['name']],
            array_values(array_filter($teams, fn (array $team): bool => in_array($team['ageGroup'], ['', $person['ageGroup']], true))))],
            array_values(array_filter($members, fn (array $person): bool => !in_array($person['id'], $placed, true))));

        // The student's roster: their team(s), or the whole member list while they are in none
        $theirs = array_values(array_filter($teams, fn (array $team): bool => in_array($studentId, array_column($team['roster'], 'id'), true)));
        $squad  = $theirs === [] ? [['title' => $row['name'], 'players' => $members]]
            : array_map(fn (array $team): array => ['title' => $team['name'], 'players' => $team['roster']], $theirs);

        return $card + [
            // Achievements & Gallery (null: the achievements module is off, so the panel goes too)
            'awards'       => !feature('achievements') ? null : array_map(fn (array $award): array => self::awardCard($award, $card['image']),
                (new AchievementModel())->ofExtracurricular($row['id'])),
            'description'  => $row['description'] ?? '',
            'created'      => $row['created_at'],
            'schedule'     => $row['schedule'] ?? '',
            'location'     => $row['venue'] ?? '',
            'ageGroups'    => $ageGroups,      // a team's age group choices ([] = the programme doesn't use them)
            'filterGroups' => $filterGroups,   // Teams & Roster's filter
            'age'          => $age,            // its choice ('' = all)
            'settings'     => array_intersect_key($row, array_flip(['age_group_limit', 'uses_age_groups', 'open_to', 'lowest_grade', 'is_open'])),
            'coach'        => ['name' => $row['coach_name'] ?? '', 'specialty' => $row['coach_specialty'] ?? '', 'email' => $row['coach_email'] ?? '',
                               'phone' => $row['coach_phone'] ?? ''],
            'teams'        => $teams,
            'teamsShown'   => array_values(array_filter($teams, $inAge)),
            'members'      => $members,                                         // every member (a team's roster rows pick from them)
            'unassigned'   => array_values(array_filter($unassigned, $inAge)),   // Teams & Roster's "Unassigned" (staff)
            'joinRequests' => $requests,                                         // Enrollment Processing (the teacher in charge)
            'squad'        => $member ? $squad : null,                           // the student's roster: members only
            'studentId'    => $studentId,
            'noticeCards'  => feature('notices') ? self::noticeCards($user, $row['id']) : null,   // null: no Notice Board
            'enrolment'    => ['members' => count($members), 'capacity' => $row['age_group_limit'] ?? rule('age_group_limit')],
            'cards'        => self::staffCards($row, $current, $member),
        ];
    }

    // What a card needs: its words and photo, and where this person stands with it (archived, waiting = not approved
    // yet, enrolled, requested = waiting for a parent or the teacher in charge, open = may ask), and a person's no
    private static function card(array $row, ?array $current): array
    {
        $status = $current['status'] ?? null;

        return [
            'id'        => $row['id'],
            'name'      => $row['name'],
            'type'      => $row['type'],
            'category'  => $row['category'] ?? config("extracurricular.types.{$row['type']}.pill"),
            'image'     => $row['cover_url'] ?? asset(config('extracurricular.default_cover')),
            'review'    => $row['review'],
            'archived'  => $row['archived_at'] !== null,
            'state'     => match (true)
            {
                $row['archived_at'] !== null                    => 'archived',
                $row['review'] !== 'approved'                   => 'waiting',
                $status === 'member'                            => 'enrolled',
                $status !== null && $status !== 'declined'      => 'requested',   // waits for a parent or the teacher in charge
                default                                         => 'open',        // never asked, or a no: may ask again
            },
            'answer'    => $status !== 'declined' ? null : ($current['declined_by'] === 'teacher'
                ? 'Not accepted' . ($current['teacher_note'] ? ": \"{$current['teacher_note']}\"" : '') . '. You can ask again.'
                : words('A {parent} said no') . ($current['parent_note'] ? ": \"{$current['parent_note']}\"" : '') . '. You can ask again.'),
            'whyNot'    => null,    // cards() fills it for a student who may not ask
            'watched'   => false,   // cards(): an enrolled student on Academic Watch
            'tic'       => ['name' => $row['tic_name'] ?? 'Not assigned yet', 'photo' => $row['tic_photo'] ?? '', 'assigned' => $row['tic_id'] !== null],
            'ticId'     => $row['tic_id'],
            'coachName' => $row['coach_name'] ?? '',
            'phone'     => $row['coach_phone'] ?? '',
        ];
    }

    // A member or a waiting student as the member list, the roster and the Join Request cards show them
    // (a team's roster rows: TeamModel::ofProgramme(), with their position and no code / date asked)
    private static function person(array $person): array
    {
        return [
            'id'        => $person['id'],
            'name'      => $person['name'],
            'photo'     => $person['photo'] ?? '',
            'grade'     => $person['grade_number'],
            'ageGroup'  => JoinRules::ageGroup($person['date_of_birth']) ?? '—',
            'role'      => $person['position'] ?? 'Member',
            'position'  => $person['position'] ?? null,
            'indexNo'   => $person['code'] ?? '',
            'submitted' => $person['asked_on'] ?? null,
        ];
    }

    // A team as its card and pop-up show it: no cover of its own = the programme's; no age group = any age ('')
    private static function team(array $team, string $programmeCover): array
    {
        return [
            'id'       => $team['id'],
            'name'     => $team['name'],
            'ageGroup' => $team['age_group'] ?? '',
            'cover'    => $team['cover_url'] ?? $programmeCover,
            'roster'   => array_map(fn (array $person): array => self::person($person), $team['members']),
        ];
    }

    // One of the programme's achievements for its own page (_award_page.php), or null (none asked, not found, not this
    // programme's, or the module is off). $wanted: its id, or 'new' (an empty one to add — only for who may edit).
    public static function award(array $programme, string $wanted, bool $canEdit): ?array
    {
        if ($programme['awards'] === null || $wanted === '' || ($wanted === 'new' && !$canEdit))
        {
            return null;
        }

        $row = $wanted === 'new' ? null : (new AchievementModel())->find((int) $wanted);

        if ($wanted !== 'new' && ($row === null || (int) $row['extracurricular_id'] !== (int) $programme['id']))
        {
            return null;
        }

        return [
            'id'           => $row['id'] ?? 'new',
            'title'        => $row['title'] ?? '',
            'year'         => isset($row) ? substr($row['achieved_on'], 0, 4) : '',
            'date'         => $row['achieved_on'] ?? '',
            'venue'        => $row['venue'] ?? '',
            'level'        => $row['level'] ?? '',
            'tournament'   => $row['event_name'] ?? '',
            'organisedBy'  => $row['organiser'] ?? '',
            'place'        => $row['result'] ?? '',
            'medal'        => $row['medal'] ?? '',
            'ageGroup'     => $row['age_group'] ?? '',
            'representing' => $row['team'] ?? (isset($row) ? 'Individual' : ''),
            'summary'      => $row['summary'] ?? '',
            'image'        => isset($row) ? ($row['photo_url'] ?? $programme['image']) : '',
            'participants' => $row['students'] ?? [],   // our students: [['id', 'name'], …]
            'others'       => $row['others'] ?? '',      // other names, not in the portal
            'gallery'      => $row['photos'] ?? [],      // [['id', 'url'], …]
        ];
    }

    // An achievement as its card shows it: photo (or the programme's cover), title, year, level, team or individual
    private static function awardCard(array $award, string $programmeCover): array
    {
        return [
            'id'    => $award['id'],
            'title' => $award['title'],
            'year'  => substr($award['achieved_on'], 0, 4),
            'level' => $award['level'],
            'kind'  => $award['team'] === null ? 'Individual' : 'Team',
            'image' => $award['photo_url'] ?? $programmeCover,
        ];
    }

    // What the staff cards show (_staff_card.php): the two people, and the Details / Status / Created Date lists.
    // The coach's contact only for members and staff ($member).
    private static function staffCards(array $row, ?array $current, bool $member): array
    {
        $hidden = 'Shown once you join';
        $value  = fn (?string $text): string => ($text ?? '') !== '' ? $text : '—';
        $status = $current['status'] ?? null;

        return [
            'tic'     => ['title' => words('{Teacher} in Charge'), 'person' => [
                'name' => $row['tic_name'] ?? 'Not assigned yet', 'line' => $value($row['tic_subjects']), 'photo' => $row['tic_photo'] ?? '',
                'assigned' => $row['tic_id'] !== null, 'email' => $value($row['tic_email']), 'phone' => $value($row['tic_phone'])]],
            'coach'   => ['title' => 'Coach / Instructor', 'person' => [
                'name' => $row['coach_name'] ?? 'Not assigned yet', 'line' => $value($row['coach_specialty']), 'photo' => '',
                'assigned' => ($row['coach_name'] ?? '') !== '',
                'email' => $member ? $value($row['coach_email']) : $hidden, 'phone' => $member ? $value($row['coach_phone']) : $hidden]],
            'details' => ['title' => 'Details', 'person' => null, 'items' => [
                ['clock', 'Schedule', $value($row['schedule'])],
                ['mapPin', 'Location', $value($row['venue'])],
            ]],
            'status'  => ['title' => 'Status', 'person' => null, 'items' => [
                ['check', 'Membership', match ($status)
                {
                    'member'  => 'Enrolled',
                    'parent'  => words('Waiting for your {parent}'),
                    'teacher' => words('Waiting for the {teacher} in charge'),
                    default   => 'Not Enrolled',
                }],
                ['calendar', 'Joined', $status === 'member' && $current['joined_on'] !== null ? date(country('date_format'), strtotime($current['joined_on'])) : '—'],
            ]],
            'created' => ['title' => 'Created Date', 'person' => null, 'items' => [
                ['calendar', 'Date Added', date(country('date_format'), strtotime($row['created_at']))],
            ]],
        ];
    }

    // The club's own notices as the shared notice cards read them (display only: no pin / edit / delete here)
    private static function noticeCards(array $user, int $id): array
    {
        return array_map(fn (array $notice): array => $notice + ['canManage' => false, 'canReview' => false, 'isMine' => false, 'isForMe' => true],
            (new NoticeModel())->forExtracurricular($user, $id));
    }
}
