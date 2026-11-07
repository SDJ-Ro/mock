<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Who is where this school year: a student's class, a class's class teacher, a subject's teacher in a class, a club /
// sport's teacher in charge (class_students, class_teachers, subject_teachers, teachers_in_charge — each row has a year).
// The only place that writes them, and the only place a new school year starts (yearEnd(), B-9). Pages read this
// year's rows through the current_… views (database/updates/028); earlier years stay in the tables untouched.
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

    // Year-end (B-9), all in ONE transaction: the next school year starts (it becomes the current one), each student
    // goes to their class there ($moves: student id => class id), every teacher's place is copied to it unchanged
    // (class, subject, in charge: management changes them later on the usual pages) and the leavers' club / sport
    // memberships end ('left_school'). Earlier years' rows are only read, never changed. Returns the new year.
    // Anything fails → nothing at all is saved (the year doesn't start). Null = $fromYear is no longer the current year
    // (someone ran it a moment ago): nothing done. FOR UPDATE makes a second run at the same moment wait, then see that.
    public function yearEnd(int $fromYear, array $moves, array $leavers, int $by): ?int
    {
        return $this->transaction(function () use ($fromYear, $moves, $leavers, $by): ?int
        {
            if ((int) $this->db->query('SELECT MAX(year) FROM school_years FOR UPDATE')->fetchColumn() !== $fromYear)
            {
                return null;
            }

            $newYear = $fromYear + 1;

            $this->db->prepare('INSERT INTO school_years (year) VALUES (:year)')->execute(['year' => $newYear]);

            foreach ($moves as $studentId => $classId)
            {
                $this->placeStudent($studentId, $classId);   // the current year is now $newYear
            }

            // The three teacher tables: a fixed list in the code, never from a form
            foreach (['class_teachers' => 'class_id', 'subject_teachers' => 'class_id, subject_id', 'teachers_in_charge' => 'extracurricular_id'] as $table => $keys)
            {
                $this->db->prepare("INSERT INTO {$table} (year, {$keys}, teacher_id) SELECT :new, {$keys}, teacher_id FROM {$table} WHERE year = :old")
                    ->execute(['new' => $newYear, 'old' => $fromYear]);
            }

            $clubs = new ExtracurricularModel();

            foreach ($leavers as $studentId)
            {
                $clubs->endMemberships(null, $studentId, 'left_school', $by);
            }

            return $newYear;
        });
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
