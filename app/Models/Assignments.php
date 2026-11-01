<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Who is where this school year: a student's class, a class's class teacher, a subject's teacher in a class, a club /
// sport's teacher in charge (class_students, class_teachers, subject_teachers, teachers_in_charge — each row has a year).
// The only place that writes them. Pages read this year's rows through the current_… views (database/updates/028);
// earlier years stay in the tables untouched.
class Assignments extends Model
{
    // The current school year (SchoolYear::current()): every write below is for it
    private function currentYear(): int
    {
        return (new SchoolYear())->current();
    }

    // Is there a free place in this class this year? (no such class → no)
    // Called inside the transaction that adds or moves the student (PeopleModel). FOR UPDATE holds the class row until
    // that transaction ends, so two people taking the last place at the same moment go one after the other: the second
    // waits, then sees "full". The count is a locking read too (FOR SHARE), so it sees what the first one just saved.
    public function hasRoom(int $classId): bool
    {
        $class = $this->db->prepare('SELECT capacity FROM classes WHERE id = :id FOR UPDATE');
        $class->execute(['id' => $classId]);
        $capacity = $class->fetchColumn();

        $students = $this->db->prepare('SELECT COUNT(*) FROM class_students WHERE class_id = :id AND year = :year FOR SHARE');
        $students->execute(['id' => $classId, 'year' => $this->currentYear()]);

        return $capacity !== false && (int) $students->fetchColumn() < (int) $capacity;
    }

    // A student's class this year (a new place, or a move)
    public function placeStudent(int $studentId, int $classId): void
    {
        $this->db->prepare(
            'INSERT INTO class_students (year, student_id, class_id) VALUES (:year, :student, :class)
             ON DUPLICATE KEY UPDATE class_id = VALUES(class_id)'
        )->execute(['year' => $this->currentYear(), 'student' => $studentId, 'class' => $classId]);
    }

    // A class's class teacher this year (null = nobody yet)
    public function setClassTeacher(int $classId, ?int $teacherId): void
    {
        $this->save('class_teachers', ['class_id' => $classId], $teacherId);
    }

    // One subject's teacher in one class this year (null = nobody yet)
    public function setSubjectTeacher(int $classId, int $subjectId, ?int $teacherId): void
    {
        $this->save('subject_teachers', ['class_id' => $classId, 'subject_id' => $subjectId], $teacherId);
    }

    // A club / sport's teacher in charge this year (null = nobody yet)
    public function setTeacherInCharge(int $extracurricularId, ?int $teacherId): void
    {
        $this->save('teachers_in_charge', ['extracurricular_id' => $extracurricularId], $teacherId);
    }

    // This year's row of a teacher table: replaced by $teacherId, or removed (null). Both steps or neither.
    // $table and the keys of $where come from the three methods above, never from a form.
    private function save(string $table, array $where, ?int $teacherId): void
    {
        $where   += ['year' => $this->currentYear()];
        $columns  = array_keys($where);
        $match    = implode(' AND ', array_map(fn (string $column): string => "{$column} = :{$column}", $columns));

        $this->transaction(function () use ($table, $where, $columns, $match, $teacherId): void
        {
            $this->db->prepare("DELETE FROM {$table} WHERE {$match}")->execute($where);

            if ($teacherId !== null)
            {
                $this->db->prepare("INSERT INTO {$table} (" . implode(', ', $columns) . ', teacher_id) VALUES (:' . implode(', :', $columns) . ', :teacher)')
                    ->execute($where + ['teacher' => $teacherId]);
            }
        });
    }
}
