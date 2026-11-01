<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Grades and the classes inside them (grades, classes tables), with who is in them and teaches them this year (the
// current_… views; changing those is App\Models\Assignments).
// A grade stores only its number (shown with grade_name()); a class stores its full name ("6-A", built by class_name()).
class ClassModel extends Model
{
    // Counts a class's students this year; used inside the queries below
    private const STUDENTS = '(SELECT COUNT(*) FROM current_class_students s WHERE s.class_id = c.id)';

    // ---------------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------------

    // Every grade (empty ones too), lowest first, each with its classes and number of students:
    // [['id', 'number', 'students', 'classes' => [['id', 'name', 'capacity', 'students', 'class_teacher_id', 'class_teacher'], …]], …]
    public function grades(): array
    {
        $grades = [];

        foreach ($this->db->query('SELECT id, sort_order AS number FROM grades ORDER BY sort_order')->fetchAll() as $grade)
        {
            $grades[$grade['id']] = $grade + ['students' => 0, 'classes' => []];
        }

        $classes = $this->db->query(
            'SELECT c.id, c.grade_id, c.name, c.capacity, ct.teacher_id AS class_teacher_id, t.full_name AS class_teacher, ' . self::STUDENTS . ' AS students
             FROM classes c
             JOIN grades g ON g.id = c.grade_id
             LEFT JOIN current_class_teachers ct ON ct.class_id = c.id
             LEFT JOIN profiles t ON t.account_id = ct.teacher_id
             ORDER BY g.sort_order, LENGTH(c.name), c.name'
        )->fetchAll();

        foreach ($classes as $class)
        {
            $grades[$class['grade_id']]['classes'][]  = $class;
            $grades[$class['grade_id']]['students'] += $class['students'];
        }

        return array_values($grades);
    }

    // One grade with its number of students this year and in earlier years (those keep it: their records point to it)
    public function findGrade(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT g.id, g.sort_order AS number,
                    (SELECT COUNT(*) FROM current_class_students s JOIN classes c ON c.id = s.class_id WHERE c.grade_id = g.id) AS students,
                    (SELECT COUNT(*) FROM class_students s JOIN classes c ON c.id = s.class_id WHERE c.grade_id = g.id AND s.year < :year) AS past_students
             FROM grades g WHERE g.id = :id'
        );
        $statement->execute(['id' => $id, 'year' => (new SchoolYear())->current()]);

        return $statement->fetch() ?: null;
    }

    // One class with its grade's number, its class teacher and its number of students this year and in earlier years
    // (those keep it: their records point to it)
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT c.id, c.grade_id, c.name, c.capacity, ct.teacher_id AS class_teacher_id, g.sort_order AS grade_number,
                    ' . self::STUDENTS . ' AS students,
                    (SELECT COUNT(*) FROM class_students s WHERE s.class_id = c.id AND s.year < :year) AS past_students
             FROM classes c
             JOIN grades g ON g.id = c.grade_id
             LEFT JOIN current_class_teachers ct ON ct.class_id = c.id
             WHERE c.id = :id'
        );
        $statement->execute(['id' => $id, 'year' => (new SchoolYear())->current()]);

        return $statement->fetch() ?: null;
    }

    public function gradeNumberTaken(int $number): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM grades WHERE sort_order = :number');
        $statement->execute(['number' => $number]);

        return (bool) $statement->fetchColumn();
    }

    // Is this class name already used in the grade? (ignores capitals; $exceptId = the class being edited)
    public function nameTaken(int $gradeId, string $name, ?int $exceptId = null): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM classes WHERE grade_id = :grade AND LOWER(name) = LOWER(:name) AND id <> :except');
        $statement->execute(['grade' => $gradeId, 'name' => $name, 'except' => $exceptId ?? 0]);

        return (bool) $statement->fetchColumn();
    }

    // Who teaches what this year, in every class: [class id => [subject id => teacher id]]
    public function subjectTeachers(): array
    {
        $teachers = [];

        foreach ($this->db->query('SELECT class_id, subject_id, teacher_id FROM current_subject_teachers')->fetchAll() as $row)
        {
            $teachers[$row['class_id']][$row['subject_id']] = $row['teacher_id'];
        }

        return $teachers;
    }

    // ---------------------------------------------------------------------
    // Teachers
    // ---------------------------------------------------------------------

    // Teachers who can be given classes (not switched off), by first name, each with what the pickers show:
    // [id => ['name', 'qualification' (their subjects), 'class_name' (class teacher of this year, or null)]]
    public function teachers(): array
    {
        $rows = $this->db->query(
            "SELECT u.id, p.full_name AS name, sd.subjects AS qualification, c.name AS class_name
             FROM user_accounts u
             JOIN profiles p ON p.account_id = u.id
             LEFT JOIN staff_details sd ON sd.account_id = u.id
             LEFT JOIN current_class_teachers ct ON ct.teacher_id = u.id
             LEFT JOIN classes c ON c.id = ct.class_id
             WHERE u.role = 'teacher' AND u.activation_status <> 'INACTIVE'
             ORDER BY p.first_name, p.last_name"
        )->fetchAll();

        return array_column($rows, null, 'id');
    }

    // Each teacher's workload this year for the hover card: [teacher id => ['subjects' => [subject => [class names]], 'extracurriculars' => [names]]]
    public function workloads(): array
    {
        $workloads = [];

        $taught = $this->db->query(
            'SELECT st.teacher_id, ss.name AS subject, c.name AS class_name
             FROM current_subject_teachers st
             JOIN stage_subjects ss ON ss.id = st.subject_id
             JOIN classes c ON c.id = st.class_id
             ORDER BY ss.name, LENGTH(c.name), c.name'
        )->fetchAll();

        foreach ($taught as $row)
        {
            $workloads[$row['teacher_id']]['subjects'][$row['subject']][] = $row['class_name'];
        }

        $inCharge = $this->db->query(
            'SELECT tic.teacher_id, x.name FROM current_teachers_in_charge tic JOIN extracurriculars x ON x.id = tic.extracurricular_id ORDER BY x.name'
        )->fetchAll();

        foreach ($inCharge as $row)
        {
            $workloads[$row['teacher_id']]['extracurriculars'][] = $row['name'];
        }

        return $workloads;
    }

    // The class this teacher already looks after this year (other than $exceptClassId), or null
    public function classTeacherOf(int $teacherId, ?int $exceptClassId = null): ?string
    {
        $statement = $this->db->prepare(
            'SELECT c.name FROM current_class_teachers ct JOIN classes c ON c.id = ct.class_id
             WHERE ct.teacher_id = :teacher AND ct.class_id <> :except'
        );
        $statement->execute(['teacher' => $teacherId, 'except' => $exceptClassId ?? 0]);

        return $statement->fetchColumn() ?: null;
    }

    // The different subjects (lower case) a teacher would teach this year if they also took $subjectId in $classId
    public function subjectsWith(int $teacherId, int $classId, int $subjectId): array
    {
        $statement = $this->db->prepare(
            'SELECT DISTINCT LOWER(ss.name) FROM stage_subjects ss
             WHERE ss.id = :subject
                OR ss.id IN (SELECT subject_id FROM current_subject_teachers WHERE teacher_id = :teacher AND NOT (class_id = :class AND subject_id = :same))'
        );
        $statement->execute(['subject' => $subjectId, 'teacher' => $teacherId, 'class' => $classId, 'same' => $subjectId]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    // ---------------------------------------------------------------------
    // Saving
    // ---------------------------------------------------------------------

    // A new grade, with its first class ("12-A") so it is ready to use
    public function addGrade(int $number): int
    {
        return $this->transaction(function () use ($number): int
        {
            $this->db->prepare('INSERT INTO grades (sort_order) VALUES (:number)')->execute(['number' => $number]);
            $gradeId = (int) $this->db->lastInsertId();
            $size    = (int) config('white_label.academic.class_size.max');

            $this->saveClass(null, $gradeId, class_name($number, config('white_label.academic.first_section')), $size, null);

            return $gradeId;
        });
    }

    // Removes a grade and its classes (the controller checks nobody is in them, this year or before, first)
    public function deleteGrade(int $id): void
    {
        $this->transaction(function () use ($id): void
        {
            $this->db->prepare('DELETE FROM classes WHERE grade_id = :id')->execute(['id' => $id]);
            $this->db->prepare('DELETE FROM grades WHERE id = :id')->execute(['id' => $id]);
        });
    }

    // Adds ($id = null) or updates a class and its class teacher this year; returns its id
    public function saveClass(?int $id, int $gradeId, string $name, int $capacity, ?int $teacherId): int
    {
        return $this->transaction(function () use ($id, $gradeId, $name, $capacity, $teacherId): int
        {
            $values = ['name' => $name, 'capacity' => $capacity];

            if ($id === null)
            {
                $this->db->prepare('INSERT INTO classes (grade_id, name, capacity) VALUES (:grade, :name, :capacity)')->execute($values + ['grade' => $gradeId]);
                $id = (int) $this->db->lastInsertId();
            }
            else
            {
                $this->db->prepare('UPDATE classes SET name = :name, capacity = :capacity WHERE id = :id')->execute($values + ['id' => $id]);
            }

            (new Assignments())->setClassTeacher($id, $teacherId);

            return $id;
        });
    }

    // Removes classes nobody is in (this year or before); their teachers go with them (ON DELETE CASCADE)
    public function deleteClasses(array $ids): void
    {
        $this->db->exec('DELETE FROM classes WHERE id IN (' . self::idList($ids) . ')');
    }
}
