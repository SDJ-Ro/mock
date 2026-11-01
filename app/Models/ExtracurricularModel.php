<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Clubs and sports (extracurriculars) and who is in them (extracurricular_members).
// A programme: made by management (waits for the admin: review 'pending') or the admin (approved at once); a rejected
// one is removed (the audit log keeps it); an approved one is never deleted, only archived (archived_at: hidden from
// lists and joining, its history kept) and maybe restored. Its teacher in charge: teachers_in_charge (per year, Assignments).
// A join request moves through extracurricular_members.status:
//   'parent'  waits for a parent's OK (Parent → Approvals) → yes: 'member' (the school asked) or 'teacher' (the
//             student asked: the teacher in charge accepts next) · no: 'declined' (+ the parent's optional reason)
class ExtracurricularModel extends Model
{
    // The columns a programme's forms may change (the controller passes only these keys)
    private const COLUMNS = ['name', 'type', 'category', 'description', 'cover_url', 'coach_name', 'coach_specialty', 'coach_email',
        'coach_phone', 'schedule', 'venue', 'age_group_limit', 'uses_age_groups', 'open_to', 'lowest_grade', 'is_open'];

    // A programme with its teacher in charge this year (name, photo, email = their sign-in, phone, subjects)
    private const SELECT = 'SELECT x.*, tic.teacher_id AS tic_id, t.full_name AS tic_name, t.photo_url AS tic_photo, tu.identifier AS tic_email,
                                   t.phone AS tic_phone, ts.subjects AS tic_subjects
                            FROM extracurriculars x
                            LEFT JOIN current_teachers_in_charge tic ON tic.extracurricular_id = x.id
                            LEFT JOIN profiles t ON t.account_id = tic.teacher_id
                            LEFT JOIN user_accounts tu ON tu.id = tic.teacher_id
                            LEFT JOIN staff_details ts ON ts.account_id = tic.teacher_id';

    // ---------------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------------

    // The list's programmes, by name: the archived ones, or the others (with or without those still waiting)
    public function all(bool $withWaiting, bool $archived): array
    {
        return $this->db->query(self::SELECT
            . ' WHERE x.archived_at IS ' . ($archived ? 'NOT NULL' : 'NULL')
            . ($withWaiting ? '' : " AND x.review = 'approved'")
            . ' ORDER BY x.name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(self::SELECT . ' WHERE x.id = :id');
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    // The programmes a teacher is in charge of this year (not archived), by name: [['id', 'name'], …]
    public function runBy(int $teacherId): array
    {
        $statement = $this->db->prepare(
            'SELECT x.id, x.name FROM current_teachers_in_charge tic JOIN extracurriculars x ON x.id = tic.extracurricular_id
             WHERE tic.teacher_id = :me AND x.archived_at IS NULL ORDER BY x.name'
        );
        $statement->execute(['me' => $teacherId]);

        return $statement->fetchAll();
    }

    // Where a student stands with each programme, their current row: [programme id => ['status' ('parent' | 'teacher' |
    // 'member' | 'declined'), 'declined_by', 'parent_note', 'teacher_note', 'joined_on']]
    public function statesOf(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT extracurricular_id, status, declined_by, parent_note, teacher_note, joined_on
             FROM extracurricular_members WHERE student_id = :id'
        );
        $statement->execute(['id' => $studentId]);

        return array_column($statement->fetchAll(), null, 'extracurricular_id');
    }

    // A programme's members or the requests waiting for its teacher in charge ($status 'member' | 'teacher'), by name:
    // [['id', 'name', 'code', 'photo', 'date_of_birth', 'grade_number', 'joined_on', 'asked_on'], …]
    public function people(int $id, string $status): array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, p.full_name AS name, p.code, p.photo_url AS photo, p.date_of_birth, g.sort_order AS grade_number,
                    m.joined_on, COALESCE(m.parent_answered_at, m.updated_at) AS asked_on
             FROM extracurricular_members m
             JOIN user_accounts u ON u.id = m.student_id
             JOIN profiles p ON p.account_id = u.id
             LEFT JOIN current_class_students cs ON cs.student_id = u.id
             LEFT JOIN classes c ON c.id = cs.class_id
             LEFT JOIN grades g ON g.id = c.grade_id
             WHERE m.extracurricular_id = :id AND m.status = :status
             ORDER BY p.first_name, p.last_name'
        );
        $statement->execute(['id' => $id, 'status' => $status]);

        return $statement->fetchAll();
    }

    // Every programme's members' dates of birth (to count them per age group): [programme id => ['2010-04-15', …]]
    public function memberBirthdays(): array
    {
        $birthdays = [];

        foreach ($this->db->query("SELECT m.extracurricular_id, p.date_of_birth FROM extracurricular_members m
                                   JOIN profiles p ON p.account_id = m.student_id WHERE m.status = 'member'")->fetchAll() as $row)
        {
            $birthdays[$row['extracurricular_id']][] = $row['date_of_birth'];
        }

        return $birthdays;
    }

    // ---------------------------------------------------------------------
    // Joining and leaving
    // ---------------------------------------------------------------------

    // A student's Interest: their parent is asked first. After a no (anyone's), the same row starts again.
    public function ask(int $id, int $studentId): void
    {
        $this->db->prepare(
            "INSERT INTO extracurricular_members (extracurricular_id, student_id, status, requested_by) VALUES (:id, :student, 'parent', 'student')
             ON DUPLICATE KEY UPDATE status = 'parent', requested_by = 'student', parent_id = NULL, parent_answered_at = NULL,
                                     parent_note = NULL, declined_by = NULL, teacher_note = NULL"
        )->execute(['id' => $id, 'student' => $studentId]);
    }

    // The teacher in charge's answer to a request their parent said yes to → false if it doesn't wait for them any more
    // (someone else answered first). Yes: a member from today (and into $teamId, when one is given); no: declined,
    // with the reason (the student may ask again). All or nothing.
    public function decide(int $id, int $studentId, bool $accept, ?string $note, ?int $teamId = null): bool
    {
        return $this->transaction(function () use ($id, $studentId, $accept, $note, $teamId): bool
        {
            $statement = $this->db->prepare(
                "UPDATE extracurricular_members
                 SET status = IF(:accept, 'member', 'declined'), joined_on = IF(:accept2, CURDATE(), NULL),
                     declined_by = IF(:accept3, NULL, 'teacher'), teacher_note = :note
                 WHERE extracurricular_id = :id AND student_id = :student AND status = 'teacher'"
            );
            $statement->execute(['accept' => (int) $accept, 'accept2' => (int) $accept, 'accept3' => (int) $accept, 'note' => $note, 'id' => $id, 'student' => $studentId]);
            $answered = $statement->rowCount() === 1;

            if ($answered && $accept && $teamId !== null)
            {
                (new TeamModel())->assign($teamId, $studentId);
            }

            return $answered;
        });
    }

    // Memberships end ($why: 'removed' | 'archived' | 'left_school'): each moves to the history with today's date (and
    // its team places to the team history), and the current rows go (waiting requests too). One student in one programme, all of a programme's, or all of a student's.
    public function endMemberships(?int $id, ?int $studentId, string $why, int $by): void
    {
        if ($id === null && $studentId === null)
        {
            throw new \LogicException('endMemberships() needs a programme, a student, or both.');   // never "everyone's"
        }

        $where  = implode(' AND ', array_filter([$id !== null ? 'extracurricular_id = :id' : null, $studentId !== null ? 'student_id = :student' : null]));
        $values = array_filter(['id' => $id, 'student' => $studentId], fn (?int $value): bool => $value !== null);

        $this->transaction(function () use ($where, $values, $why, $by): void
        {
            // Their team places end too (TeamModel keeps them in the team history)
            $members = $this->db->prepare("SELECT extracurricular_id, student_id FROM extracurricular_members WHERE status = 'member' AND {$where}");
            $members->execute($values);

            foreach ($members->fetchAll() as $member)
            {
                (new TeamModel())->leaveAll($member['extracurricular_id'], $member['student_id']);
            }

            $this->db->prepare(
                "INSERT INTO extracurricular_member_history (extracurricular_id, student_id, joined_on, left_on, left_why, ended_by)
                 SELECT extracurricular_id, student_id, joined_on, CURDATE(), :why, :by
                 FROM extracurricular_members WHERE status = 'member' AND {$where}"
            )->execute($values + ['why' => $why, 'by' => $by]);

            $this->db->prepare("DELETE FROM extracurricular_members WHERE {$where}")->execute($values);
        });
    }

    // ---------------------------------------------------------------------
    // Academic Watch's lifts (App\Services\AcademicWatch decides who is on watch)
    // ---------------------------------------------------------------------

    // The lift for this student and published term → ['reason', 'lifted_at', 'lifted_by_name'], or null
    public function watchLift(int $studentId, int $year, int $term): ?array
    {
        $statement = $this->db->prepare(
            'SELECT l.reason, l.lifted_at, p.full_name AS lifted_by_name FROM academic_watch_lifts l LEFT JOIN profiles p ON p.account_id = l.lifted_by
             WHERE l.student_id = :student AND l.year = :year AND l.term = :term'
        );
        $statement->execute(['student' => $studentId, 'year' => $year, 'term' => $term]);

        return $statement->fetch() ?: null;
    }

    // Lift ($reason) or lock again (null) a student's watch for one published term
    public function setWatchLift(int $studentId, int $year, int $term, ?string $reason, int $by): void
    {
        $keys = ['student' => $studentId, 'year' => $year, 'term' => $term];

        $reason === null
            ? $this->db->prepare('DELETE FROM academic_watch_lifts WHERE student_id = :student AND year = :year AND term = :term')->execute($keys)
            : $this->db->prepare('REPLACE INTO academic_watch_lifts (student_id, year, term, reason, lifted_by, lifted_at) VALUES (:student, :year, :term, :reason, :by, NOW())')
                ->execute($keys + ['reason' => $reason, 'by' => $by]);
    }

    // Is this name used by another programme? (any capitals; archived ones count: they keep their name)
    public function nameTaken(string $name, ?int $exceptId = null): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM extracurriculars WHERE LOWER(name) = LOWER(:name) AND id <> :except');
        $statement->execute(['name' => $name, 'except' => $exceptId ?? 0]);

        return (bool) $statement->fetchColumn();
    }

    // ---------------------------------------------------------------------
    // Changing a programme
    // ---------------------------------------------------------------------

    // A new programme (waiting for the admin unless $approved) with its teacher in charge this year; returns its id
    public function add(array $values, ?int $teacherId, bool $approved, int $by): int
    {
        return $this->transaction(function () use ($values, $teacherId, $approved, $by): int
        {
            $values  = array_intersect_key($values, array_flip(self::COLUMNS)) + ['review' => $approved ? 'approved' : 'pending', 'created_by' => $by];
            $columns = array_keys($values);

            $this->db->prepare('INSERT INTO extracurriculars (' . implode(', ', $columns) . ') VALUES (:' . implode(', :', $columns) . ')')->execute($values);
            $id = (int) $this->db->lastInsertId();

            (new Assignments())->setTeacherInCharge($id, $teacherId);

            return $id;
        });
    }

    // Change some of a programme's columns (only those in COLUMNS). A new name is also its achievements' category (the
    // tag the honours board shows), all or nothing.
    public function update(int $id, array $values): void
    {
        $values = array_intersect_key($values, array_flip(self::COLUMNS));

        if ($values === [])
        {
            return;
        }

        $this->transaction(function () use ($id, $values): void
        {
            $sets = implode(', ', array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($values)));
            $this->db->prepare("UPDATE extracurriculars SET {$sets} WHERE id = :id")->execute($values + ['id' => $id]);

            if (isset($values['name']))
            {
                $this->db->prepare('UPDATE achievements SET category = :name WHERE extracurricular_id = :id')->execute(['name' => $values['name'], 'id' => $id]);
            }
        });
    }

    public function approve(int $id, int $by): void
    {
        $this->db->prepare("UPDATE extracurriculars SET review = 'approved', reviewed_by = :by, reviewed_at = NOW() WHERE id = :id AND review = 'pending'")
            ->execute(['by' => $by, 'id' => $id]);
    }

    // A rejected programme goes completely (it never ran: no members, teams or awards); the audit log keeps why
    public function remove(int $id): void
    {
        $this->db->prepare("DELETE FROM extracurriculars WHERE id = :id AND review = 'pending'")->execute(['id' => $id]);
    }

    public function archive(int $id, int $by): void
    {
        $this->db->prepare('UPDATE extracurriculars SET archived_at = NOW(), archived_by = :by WHERE id = :id AND archived_at IS NULL')
            ->execute(['by' => $by, 'id' => $id]);
    }

    public function restore(int $id): void
    {
        $this->db->prepare('UPDATE extracurriculars SET archived_at = NULL, archived_by = NULL WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------------------------------------------------------------------
    // Parent → Approvals
    // ---------------------------------------------------------------------

    // A parent's requests: the ones waiting for an answer from any parent of their children, and the ones this
    // parent already answered. Newest first.
    public function forParent(int $parentId): array
    {
        $statement = $this->db->prepare(
            "SELECT m.extracurricular_id, m.student_id, m.status, m.requested_by, m.parent_note, m.declined_by, m.teacher_note, m.updated_at,
                    m.parent_answered_at, x.name AS extracurricular_name, x.description, x.schedule,
                    t.full_name AS teacher_name, s.full_name AS student_name
             FROM extracurricular_members m
             JOIN student_parents sp ON sp.student_account_id = m.student_id AND sp.parent_account_id = :parent
             JOIN extracurriculars x ON x.id = m.extracurricular_id
             JOIN profiles s ON s.account_id = m.student_id
             LEFT JOIN current_teachers_in_charge tic ON tic.extracurricular_id = x.id
             LEFT JOIN profiles t ON t.account_id = tic.teacher_id
             WHERE m.status = 'parent' OR m.parent_id = :answeredBy
             ORDER BY COALESCE(m.parent_answered_at, m.updated_at) DESC"
        );
        $statement->execute(['parent' => $parentId, 'answeredBy' => $parentId]);

        return $statement->fetchAll();
    }

    // A parent's answer to a request that still waits → false if it doesn't wait any more (the other parent answered
    // first). Yes: a member, or on to the teacher in charge if the student asked; no: declined, with the optional reason.
    public function answer(int $extracurricularId, int $studentId, int $parentId, bool $yes, ?string $note): bool
    {
        $statement = $this->db->prepare(
            "UPDATE extracurricular_members
             SET status = IF(:yes, IF(requested_by = 'student', 'teacher', 'member'), 'declined'),
                 declined_by = IF(:yes2, NULL, 'parent'), joined_on = IF(:yes3 AND requested_by = 'school', CURDATE(), NULL),
                 parent_id = :parent, parent_answered_at = NOW(), parent_note = :note
             WHERE extracurricular_id = :extracurricular AND student_id = :student AND status = 'parent'"
        );
        $statement->execute(['yes' => (int) $yes, 'yes2' => (int) $yes, 'yes3' => (int) $yes, 'parent' => $parentId, 'note' => $note,
            'extracurricular' => $extracurricularId, 'student' => $studentId]);

        return $statement->rowCount() === 1;
    }

    // ---------------------------------------------------------------------
    // The dashboards' numbers (App\Services\ExtracurricularDashboard)
    // ---------------------------------------------------------------------

    // Every running programme (approved, not archived) with its number of members, the biggest first:
    // [['id', 'name', 'type', 'members'], …]
    public function memberCounts(): array
    {
        return $this->db->query(
            "SELECT x.id, x.name, x.type, COUNT(em.student_id) AS members
             FROM extracurriculars x LEFT JOIN extracurricular_members em ON em.extracurricular_id = x.id AND em.status = 'member'
             WHERE x.review = 'approved' AND x.archived_at IS NULL
             GROUP BY x.id, x.name, x.type ORDER BY members DESC, x.name"
        )->fetchAll();
    }

    // Per grade: its students this year and how many of them are members of at least one running programme of this
    // type: [['grade' (its number), 'total', 'members'], …], lowest grade first
    public function membersByGradeOfType(string $type): array
    {
        $statement = $this->db->prepare(
            "SELECT g.sort_order AS grade, COUNT(cs.student_id) AS total,
                    COALESCE(SUM(EXISTS (SELECT 1 FROM extracurricular_members em JOIN extracurriculars x ON x.id = em.extracurricular_id
                                         WHERE em.student_id = cs.student_id AND em.status = 'member' AND x.type = :type AND x.archived_at IS NULL)), 0) AS members
             FROM grades g LEFT JOIN classes c ON c.grade_id = g.id LEFT JOIN current_class_students cs ON cs.class_id = c.id
             GROUP BY g.id, g.sort_order ORDER BY g.sort_order"
        );
        $statement->execute(['type' => $type]);

        return $statement->fetchAll();
    }

    // One programme's members per grade: [grade number => how many], lowest grade first
    public function membersByGrade(int $id): array
    {
        $statement = $this->db->prepare(
            "SELECT g.sort_order AS grade, COUNT(*) AS members
             FROM extracurricular_members em JOIN current_class_students cs ON cs.student_id = em.student_id
             JOIN classes c ON c.id = cs.class_id JOIN grades g ON g.id = c.grade_id
             WHERE em.extracurricular_id = :id AND em.status = 'member'
             GROUP BY g.sort_order ORDER BY g.sort_order"
        );
        $statement->execute(['id' => $id]);

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
