<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Graduating certificates. The draft reads live records; finalisation saves the exact words and particulars that
// the issued PDF will use. An issued row is never changed by this model.
class CertificateModel extends Model
{
    public function person(int $studentId, ?int $year = null): ?array
    {
        $statement = $this->db->prepare(
            "SELECT p.account_id, COALESCE(p.code, u.identifier) AS code, p.full_name, sd.admission_date, c.name AS class_name,
                    g.sort_order AS grade_number, cs.year AS class_year
             FROM profiles p JOIN user_accounts u ON u.id = p.account_id AND u.role = 'student'
             LEFT JOIN student_details sd ON sd.account_id = p.account_id
             LEFT JOIN class_students cs ON cs.student_id = p.account_id AND cs.year = :year
             LEFT JOIN classes c ON c.id = cs.class_id
             LEFT JOIN grades g ON g.id = c.grade_id
             WHERE p.account_id = :student"
        );
        $statement->execute(['student' => $studentId, 'year' => $year ?? (new SchoolYear())->current()]);

        return $statement->fetch() ?: null;
    }

    public function saved(int $studentId, int $year): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM certificates WHERE student_id = :student AND school_year = :year');
        $statement->execute(['student' => $studentId, 'year' => $year]);

        return $statement->fetch() ?: null;
    }

    public function latestIssued(int $studentId): ?array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM certificates WHERE student_id = :student AND status = 'issued' ORDER BY school_year DESC LIMIT 1"
        );
        $statement->execute(['student' => $studentId]);

        return $statement->fetch() ?: null;
    }

    // The latest batch this student belongs to, including a future date; null outside the final grade.
    public function batchOf(int $studentId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT b.school_year, b.graduation_date, b.grade_id
             FROM graduation_batches b JOIN class_students s ON s.year = b.school_year AND s.student_id = :student
             JOIN classes c ON c.id = s.class_id AND c.grade_id = b.grade_id
             ORDER BY b.school_year DESC LIMIT 1'
        );
        $statement->execute(['student' => $studentId]);

        return $statement->fetch() ?: null;
    }

    // The teacher's missing-record decisions stay with the student. Pending requests block Finalise.
    public function requestsOf(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT r.*, t.full_name AS teacher_name
             FROM missing_record_requests r LEFT JOIN profiles t ON t.account_id = r.answered_by
             WHERE r.student_id = :student ORDER BY r.created_at DESC, r.id DESC'
        );
        $statement->execute(['student' => $studentId]);

        return $statement->fetchAll();
    }

    // Verified achievement rows (B-3) and dated club/team membership. Only real saved facts become draft points.
    public function recordsOf(int $studentId): array
    {
        $academic = [];
        $activities = [];

        $awards = $this->db->prepare(
            'SELECT a.title, a.category, a.result, a.team, a.achieved_on, x.name AS programme
             FROM achievements a JOIN achievement_students s ON s.achievement_id = a.id
             LEFT JOIN extracurriculars x ON x.id = a.extracurricular_id
             WHERE s.student_id = :student ORDER BY a.achieved_on, a.id'
        );
        $awards->execute(['student' => $studentId]);

        foreach ($awards->fetchAll() as $award)
        {
            $point = $award['title'];
            $facts = array_filter([$award['result'], $award['team'], $award['programme']], fn ($value): bool => $value !== null && $value !== '');

            if ($facts !== [])
            {
                $point .= ' (' . implode(', ', array_unique($facts)) . ')';
            }

            if ($award['category'] === 'Academic')
            {
                $academic[] = $point;
            }
            else
            {
                $activities[] = $point;
            }
        }

        $memberships = $this->db->prepare(
            "SELECT x.name, m.joined_on, NULL AS left_on FROM extracurricular_members m
             JOIN extracurriculars x ON x.id = m.extracurricular_id
             WHERE m.student_id = :student_current AND m.status = 'member'
             UNION ALL
             SELECT x.name, h.joined_on, h.left_on FROM extracurricular_member_history h
             JOIN extracurriculars x ON x.id = h.extracurricular_id WHERE h.student_id = :student_past"
        );
        $memberships->execute(['student_current' => $studentId, 'student_past' => $studentId]);

        foreach ($memberships->fetchAll() as $membership)
        {
            $activities[] = $membership['name'] . self::dates($membership['joined_on'], $membership['left_on']);
        }

        $positions = $this->db->prepare(
            'SELECT t.name AS team, x.name AS programme, m.position, m.since AS from_on, NULL AS to_on
             FROM team_members m JOIN teams t ON t.id = m.team_id
             JOIN extracurriculars x ON x.id = t.extracurricular_id WHERE m.student_id = :student_current
             UNION ALL
             SELECT h.team_name AS team, x.name AS programme, h.position, h.from_on, h.to_on
             FROM team_member_history h JOIN extracurriculars x ON x.id = h.extracurricular_id
             WHERE h.student_id = :student_past'
        );
        $positions->execute(['student_current' => $studentId, 'student_past' => $studentId]);

        foreach ($positions->fetchAll() as $position)
        {
            if ($position['position'] !== null && $position['position'] !== '')
            {
                $activities[] = $position['position'] . ', ' . $position['team'] . ' — ' . $position['programme']
                    . self::dates($position['from_on'], $position['to_on']);
            }
        }

        return ['academic' => array_values(array_unique($academic)), 'activities' => array_values(array_unique($activities)), 'conduct' => []];
    }

    private static function dates(?string $from, ?string $to): string
    {
        if ($from === null || $from === '')
        {
            return '';
        }

        return ' (' . date(country('date_format'), strtotime($from))
            . ($to === null ? '–present' : '–' . date(country('date_format'), strtotime($to))) . ')';
    }

    // Save management's wording without issuing it. $text uses the three fixed section names only.
    public function saveDraft(int $studentId, int $year, array $text): bool
    {
        return $this->transaction(function () use ($studentId, $year, $text): bool
        {
            if (!$this->eligible($studentId, $year))
            {
                return false;
            }

            $statement = $this->db->prepare(
                "INSERT INTO certificates (school_year, student_id, academic_text, activities_text, conduct_text)
                 VALUES (:year, :student, :academic, :activities, :conduct)
                 ON DUPLICATE KEY UPDATE academic_text = IF(status = 'pending', VALUES(academic_text), academic_text),
                     activities_text = IF(status = 'pending', VALUES(activities_text), activities_text),
                     conduct_text = IF(status = 'pending', VALUES(conduct_text), conduct_text)"
            );
            $statement->execute(['year' => $year, 'student' => $studentId] + $text);

            return ($this->saved($studentId, $year)['status'] ?? '') === 'pending';
        });
    }

    // Finalise only when the batch date has arrived and every missing-record request is answered.
    public function finalise(int $studentId, int $year, int $managerId, array $text, array $particulars): bool
    {
        return $this->transaction(function () use ($studentId, $year, $managerId, $text, $particulars): bool
        {
            $lock = $this->db->prepare('SELECT school_year FROM graduation_batches WHERE school_year = :year FOR UPDATE');
            $lock->execute(['year' => $year]);

            if (!$lock->fetch() || !$this->eligible($studentId, $year))
            {
                return false;
            }

            $pending = $this->db->prepare("SELECT COUNT(*) FROM missing_record_requests WHERE student_id = :student AND status = 'pending'");
            $pending->execute(['student' => $studentId]);

            if ((int) $pending->fetchColumn() > 0)
            {
                return false;
            }

            $this->saveDraft($studentId, $year, $text);
            $statement = $this->db->prepare(
                "UPDATE certificates SET status = 'issued', student_name = :name, name_with_initials = :initials,
                    cohort_text = :cohort, study_period = :period, school_name = :school, school_motto = :motto,
                    issued_on = CURDATE(), finalised_by = :manager, finalised_at = NOW()
                 WHERE student_id = :student AND school_year = :year AND status = 'pending'"
            );
            $statement->execute(['name' => $particulars['name'], 'initials' => $particulars['initials'],
                'cohort' => $particulars['cohort'], 'period' => $particulars['period'], 'school' => $particulars['school'],
                'motto' => $particulars['motto'], 'manager' => $managerId, 'student' => $studentId, 'year' => $year]);

            return $statement->rowCount() === 1;
        });
    }

    private function eligible(int $studentId, int $year): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM graduation_batches b JOIN class_students s ON s.year = b.school_year
             JOIN classes c ON c.id = s.class_id AND c.grade_id = b.grade_id
             WHERE b.school_year = :year AND s.student_id = :student AND b.graduation_date <= :today'
        );
        $statement->execute(['year' => $year, 'student' => $studentId, 'today' => date('Y-m-d')]);

        return (bool) $statement->fetchColumn();
    }
}
