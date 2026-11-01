<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// The calendar: the events table + event_scopes (who each event is for: the whole school, grades, classes, clubs / sports).
// Who may SEE an event is decided in one place: visibility(). Who may CHANGE one: can_manage() (config/posting.php).
// Which groups a person is in: Membership (shared with the notice board).
class CalendarModel extends Model
{
    // Each event with its author's name and its groups as one text: "grade:5,class:9" ("school:" = the whole school)
    private const SELECT = "SELECT e.*, a.full_name AS author_name,
        (SELECT GROUP_CONCAT(CONCAT(s.scope_type, ':', COALESCE(s.scope_id, '')) ORDER BY s.id)
         FROM event_scopes s WHERE s.event_id = e.id) AS scopes
        FROM events e
        LEFT JOIN profiles a ON a.account_id = e.author_id";

    // ---------------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------------

    // The events this user may see between two dates (inclusive), by day; all-day events first each day
    public function between(array $user, string $from, string $to): array
    {
        $statement = $this->db->prepare(self::SELECT . ' WHERE e.event_date BETWEEN :from AND :to AND ' . $this->visibility($user)
            . ' ORDER BY e.event_date, e.start_time IS NOT NULL, e.start_time, e.id');
        $statement->execute(['from' => $from, 'to' => $to] + $this->roleValue($user));

        return $statement->fetchAll();
    }

    // One event, only if this user may see it
    public function findFor(array $user, int $id): ?array
    {
        $statement = $this->db->prepare(self::SELECT . ' WHERE e.id = :id AND ' . $this->visibility($user));
        $statement->execute(['id' => $id] + $this->roleValue($user));

        return $statement->fetch() ?: null;
    }

    // What the "Add event" pop-up offers this user — and so the only groups they may save an event for.
    // Not 'posts.any_group' (teachers): their own classes and the clubs / sports they run; no whole grades.
    public function choicesFor(array $user): array
    {
        $ownOnly = !in_array($user['role'], config('permissions.posts.any_group'), true);

        return [
            'grades'           => $ownOnly ? [] : array_map('grade_name', $this->pairs('SELECT id, sort_order FROM grades ORDER BY sort_order')),
            'classes'          => $this->pairs('SELECT c.id, c.name FROM classes c JOIN grades g ON g.id = c.grade_id'
                . ($ownOnly ? ' WHERE c.id IN (' . Membership::MY_CLASSES['teacher'] . ')' : '') . ' ORDER BY g.sort_order, c.name', $ownOnly ? $user['id'] : null),
            'extracurriculars' => $this->pairs('SELECT id, name FROM extracurriculars WHERE '
                . ($ownOnly ? 'id IN (SELECT extracurricular_id FROM current_teachers_in_charge WHERE teacher_id = :me)' : "review = 'approved'")
                . ' ORDER BY type, name', $ownOnly ? $user['id'] : null),
        ];
    }

    // A timed event that would clash with this one: same day, overlapping times, and some of the same students
    // ($scopes = [['school', null]] or [['grade', 5], ['class', 9], ['extracurricular', 2]…]). Null = no clash.
    // A grade event also clashes with its own classes' events (not only with events for the very same group).
    public function clash(string $date, string $start, ?string $end, array $scopes, ?int $exceptId): ?array
    {
        $minutes   = (int) config('calendar.default_minutes');
        $statement = $this->db->prepare(
            "SELECT e.id, e.title, e.start_time, e.end_time FROM events e
             WHERE e.event_date = :date AND e.id <> :except AND e.start_time IS NOT NULL
               AND e.start_time < :end AND COALESCE(e.end_time, ADDTIME(e.start_time, SEC_TO_TIME({$minutes} * 60))) > :start
               AND EXISTS (SELECT 1 FROM event_scopes s WHERE s.event_id = e.id AND (s.scope_type = 'school' OR "
                   . $this->sameStudents($scopes) . '))
             ORDER BY e.start_time LIMIT 1'
        );
        $statement->execute([
            'date'   => $date,
            'start'  => $start,
            'end'    => $end ?? date('H:i', strtotime($start) + $minutes * 60),
            'except' => $exceptId ?? 0,
        ]);

        return $statement->fetch() ?: null;
    }

    // ---------------------------------------------------------------------
    // Changing
    // ---------------------------------------------------------------------

    // Adds ($id = null) or updates an event and its groups, all or nothing → its id
    public function save(?int $id, array $event, array $scopes): int
    {
        return $this->transaction(function () use ($id, $event, $scopes): int
        {
            if ($id === null)
            {
                $columns = implode(', ', array_keys($event));
                $holders = ':' . implode(', :', array_keys($event));
                $this->db->prepare("INSERT INTO events ({$columns}) VALUES ({$holders})")->execute($event);
                $id = (int) $this->db->lastInsertId();
            }
            else
            {
                $sets = implode(', ', array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($event)));
                $this->db->prepare("UPDATE events SET {$sets} WHERE id = :id")->execute($event + ['id' => $id]);
                $this->db->prepare('DELETE FROM event_scopes WHERE event_id = :id')->execute(['id' => $id]);
            }

            $insert = $this->db->prepare('INSERT INTO event_scopes (event_id, scope_type, scope_id) VALUES (:event, :type, :scope)');

            foreach ($scopes as [$type, $scopeId])
            {
                $insert->execute(['event' => $id, 'type' => $type, 'scope' => $scopeId]);
            }

            return $id;
        });
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM events WHERE id = :id')->execute(['id' => $id]);   // its scopes go with it (ON DELETE CASCADE)
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // The WHERE part that decides who sees what. 'see_all' roles (config/permissions.php) see everything. Everyone else
    // sees events for their role (the audience) that are for the whole school or for one of their groups.
    // Teachers also see every event for their own classes, grades and clubs / sports (whoever it is for), and their own.
    private function visibility(array $user): string
    {
        if ($this->seesAll($user))
        {
            return '1 = 1';
        }

        $in = (new Membership())->idListsOf($user);   // ['class' => '9', 'grade' => '5', 'extracurricular' => '2']

        // "The event is for one of my groups"
        $mine = "EXISTS (SELECT 1 FROM event_scopes s WHERE s.event_id = e.id AND (
                    (s.scope_type = 'grade' AND s.scope_id IN ({$in['grade']}))
                    OR (s.scope_type = 'class' AND s.scope_id IN ({$in['class']}))
                    OR (s.scope_type = 'extracurricular' AND s.scope_id IN ({$in['extracurricular']}))))";

        // Someone who adds events (a teacher) also sees every event for their groups, whoever it is for, and their own
        $forTeacher = in_array($user['role'], config('permissions.calendar.create'), true) ? " OR {$mine} OR e.author_id = " . (int) $user['id'] : '';

        return "((FIND_IN_SET(:role, e.audience) AND (EXISTS (SELECT 1 FROM event_scopes s WHERE s.event_id = e.id AND s.scope_type = 'school') OR {$mine}))
                 {$forTeacher})";
    }

    // The :role value visibility() needs (none when it doesn't ask)
    private function roleValue(array $user): array
    {
        return $this->seesAll($user) ? [] : ['role' => $user['role']];
    }

    private function seesAll(array $user): bool
    {
        return in_array($user['role'], config('permissions.calendar.see_all'), true);
    }

    // The part of clash() that asks "does this existing group share students with ours?"
    private function sameStudents(array $scopes): string
    {
        $wanted = ['grade' => [], 'class' => [], 'extracurricular' => []];

        foreach ($scopes as [$type, $id])
        {
            if ($type === 'school')
            {
                return '1 = 1';   // a whole-school event involves everyone
            }

            $wanted[$type][] = (int) $id;
        }

        // A grade event involves its classes; a class event involves its grade's grade-wide events
        $classes = array_merge($wanted['class'], $this->ids('SELECT id FROM classes WHERE grade_id IN (' . self::idList($wanted['grade']) . ')'));
        $grades  = array_merge($wanted['grade'], $this->ids('SELECT grade_id FROM classes WHERE id IN (' . self::idList($wanted['class']) . ')'));

        return "(s.scope_type = 'grade' AND s.scope_id IN (" . self::idList($grades) . "))
             OR (s.scope_type = 'class' AND s.scope_id IN (" . self::idList($classes) . "))
             OR (s.scope_type = 'extracurricular' AND s.scope_id IN (" . self::idList($wanted['extracurricular']) . '))';
    }

}
