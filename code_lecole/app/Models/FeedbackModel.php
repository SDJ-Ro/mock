<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Feedback (the feedback table, update 045): what a class teacher wrote to a student, who it is for (audience) and
// when the student / a parent first opened it. Who may see which feedback is decided in ONE place, SEES: a student
// sees 'student' + 'both', a parent sees 'parent' + 'both' (never 'student') — the lists and find() both use it.
class FeedbackModel extends Model
{
    // The columns add() fills (a fixed list: never names from a form)
    private const COLUMNS = ['student_id', 'teacher_id', 'kind', 'audience', 'subject', 'title', 'message'];

    // Who reads → the audiences they see
    private const SEES = ['student' => "('student', 'both')", 'parent' => "('parent', 'both')"];

    // Who opened it → its read column
    private const READ_COLUMN = ['student' => 'student_read_at', 'parent' => 'parent_read_at'];

    // A new feedback → its id. $values: every column of COLUMNS.
    public function add(array $values): int
    {
        $statement = $this->db->prepare('INSERT INTO feedback (' . implode(', ', self::COLUMNS) . ')
            VALUES (:' . implode(', :', self::COLUMNS) . ')');
        $statement->execute(array_intersect_key($values, array_flip(self::COLUMNS)));

        return (int) $this->db->lastInsertId();
    }

    // Everything this teacher wrote, newest first (the Feedback Channel)
    public function sentBy(int $teacherId): array
    {
        return $this->rows('f.teacher_id = :id', $teacherId);
    }

    // What a parent may read about one of their children: 'parent' and 'both', newest first
    public function forParent(int $studentId): array
    {
        return $this->rows('f.student_id = :id AND f.audience IN ' . self::SEES['parent'], $studentId);
    }

    // What a student may read about themselves: 'student' and 'both', newest first
    public function forStudent(int $studentId): array
    {
        return $this->rows('f.student_id = :id AND f.audience IN ' . self::SEES['student'], $studentId);
    }

    // One feedback (its own columns) this reader ('student' / 'parent') may read: about one of $studentIds (the
    // student themselves, or a parent's children) and for their audience. Null otherwise, also for an unknown id.
    public function find(int $id, string $reader, array $studentIds): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM feedback WHERE id = :id AND student_id IN (' . self::idList($studentIds) . ')
            AND audience IN ' . self::SEES[$reader]);
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    // The student ('student') or a parent ('parent') opened it: the first time is kept, later ones change nothing
    public function markRead(int $id, string $reader): void
    {
        $column = self::READ_COLUMN[$reader];   // only 'student' / 'parent' (from the controller, never a form)

        $this->db->prepare("UPDATE feedback SET {$column} = NOW() WHERE id = :id AND {$column} IS NULL")->execute(['id' => $id]);
    }

    // The students of the class this teacher is class teacher of this year (not switched off), by first name:
    // [['id', 'name', 'code', 'class_name'], …] — New Feedback's choices, and the only students they may write to
    public function students(int $teacherId): array
    {
        $statement = $this->db->prepare(
            "SELECT u.id, p.full_name AS name, p.code, c.name AS class_name
             FROM current_class_teachers ct
             JOIN classes c ON c.id = ct.class_id
             JOIN current_class_students cs ON cs.class_id = ct.class_id
             JOIN user_accounts u ON u.id = cs.student_id
             JOIN profiles p ON p.account_id = u.id
             WHERE ct.teacher_id = :me AND u.activation_status <> 'INACTIVE'
             ORDER BY p.first_name, p.last_name"
        );
        $statement->execute(['me' => $teacherId]);

        return $statement->fetchAll();
    }

    // Feedback rows with the student's and the teacher's names, newest first. $where is written in this file, never from a form.
    private function rows(string $where, int $id): array
    {
        $statement = $this->db->prepare(
            "SELECT f.*, s.full_name AS student_name, t.full_name AS teacher_name
             FROM feedback f
             JOIN profiles s ON s.account_id = f.student_id
             JOIN profiles t ON t.account_id = f.teacher_id
             WHERE {$where}
             ORDER BY f.created_at DESC, f.id DESC"
        );
        $statement->execute(['id' => $id]);

        return $statement->fetchAll();
    }
}
