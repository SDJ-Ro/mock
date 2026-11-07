<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// What the Year-End page reads about this school year (reading only: every write is App\Models\Assignments::yearEnd()).
// "Active" = not switched off (a switched-off student is not moved).
class YearEndModel extends Model
{
    // Every class with its grade number, places, class teacher (null = none) and active students this year, in order:
    // [class id => ['id', 'name', 'capacity', 'grade_number', 'class_teacher_id', 'students']]
    public function classes(): array
    {
        $classes = $this->db->query(
            "SELECT c.id, c.name, c.capacity, g.sort_order AS grade_number, ct.teacher_id AS class_teacher_id,
                    (SELECT COUNT(*) FROM current_class_students s JOIN user_accounts u ON u.id = s.student_id
                     WHERE s.class_id = c.id AND u.activation_status <> 'INACTIVE') AS students
             FROM classes c
             JOIN grades g ON g.id = c.grade_id
             LEFT JOIN current_class_teachers ct ON ct.class_id = c.id
             ORDER BY g.sort_order, LENGTH(c.name), c.name"
        )->fetchAll();

        return array_column($classes, null, 'id');
    }

    // The active students this year and their class: [student id => class id]
    public function students(): array
    {
        return array_map('intval', $this->pairs(
            "SELECT s.student_id, s.class_id FROM current_class_students s JOIN user_accounts u ON u.id = s.student_id
             WHERE u.activation_status <> 'INACTIVE'"
        ));
    }

    // How many switched-off students have a class this year below $lastGrade (they get no class next year)
    public function switchedOff(int $lastGrade): int
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM current_class_students s JOIN user_accounts u ON u.id = s.student_id
             JOIN classes c ON c.id = s.class_id JOIN grades g ON g.id = c.grade_id
             WHERE u.activation_status = 'INACTIVE' AND g.sort_order < :last"
        );
        $statement->execute(['last' => $lastGrade]);

        return (int) $statement->fetchColumn();
    }

    // How many teachers' places this year carry over: ['class' => …, 'subject' => …, 'in_charge' => …]
    public function teacherCounts(): array
    {
        return [
            'class'     => (int) $this->db->query('SELECT COUNT(*) FROM current_class_teachers')->fetchColumn(),
            'subject'   => (int) $this->db->query('SELECT COUNT(*) FROM current_subject_teachers')->fetchColumn(),
            'in_charge' => (int) $this->db->query('SELECT COUNT(*) FROM current_teachers_in_charge')->fetchColumn(),
        ];
    }
}
