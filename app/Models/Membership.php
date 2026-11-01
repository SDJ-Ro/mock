<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Which groups a person belongs to: the classes, grades and clubs / sports that calendar events and notices can be for.
// A student: their own · a parent: the child they are viewing (else all their children's) · a teacher: the classes they
// teach and the clubs / sports they run.
// Used by CalendarModel and NoticeModel (who sees what), so both answer the question the same way.
class Membership extends Model
{
    // The classes a person belongs to this year (also used inside other queries, e.g. a teacher's choices in CalendarModel)
    public const MY_CLASSES = [
        'student' => 'SELECT class_id FROM current_class_students WHERE student_id = :me',
        'parent'  => 'SELECT cs.class_id FROM student_parents sp JOIN current_class_students cs ON cs.student_id = sp.student_account_id
                      WHERE sp.parent_account_id = :me',
        'teacher' => 'SELECT class_id FROM current_teaching WHERE teacher_id = :me',
    ];

    // The clubs / sports a person belongs to: a student's, a parent's children's, the ones a teacher is in charge of this year
    private const MY_EXTRACURRICULARS = [
        'student' => "SELECT extracurricular_id FROM extracurricular_members WHERE status = 'member' AND student_id = :me",
        'parent'  => "SELECT em.extracurricular_id FROM extracurricular_members em JOIN student_parents sp ON sp.student_account_id = em.student_id
                      WHERE em.status = 'member' AND sp.parent_account_id = :me",
        'teacher' => 'SELECT extracurricular_id FROM current_teachers_in_charge WHERE teacher_id = :me',
    ];

    // → ['class' => [9], 'grade' => [5], 'extracurricular' => [2]]. Admin and management belong to none: they see everything anyway.
    // A parent who is viewing one child (Controller::requireRole() sets 'child') gets that child's groups only.
    public function groupsOf(array $user): array
    {
        if ($user['role'] === 'parent' && ($user['child'] ?? null) !== null)
        {
            $user = ['role' => 'student', 'id' => $user['child']];
        }

        if (!isset(self::MY_CLASSES[$user['role']]))
        {
            return ['class' => [], 'grade' => [], 'extracurricular' => []];
        }

        $classes = array_values(array_unique($this->ids(self::MY_CLASSES[$user['role']], $user['id'])));   // a class taught twice = once

        return [
            'class'           => $classes,
            'grade'           => $classes === [] ? [] : $this->ids('SELECT DISTINCT grade_id FROM classes WHERE id IN (' . self::idList($classes) . ')'),
            'extracurricular' => $this->ids(self::MY_EXTRACURRICULARS[$user['role']], $user['id']),
        ];
    }

    // The same groups as SQL lists, ready for "IN (…)": ['class' => '9', 'grade' => '5', 'extracurricular' => '0']
    public function idListsOf(array $user): array
    {
        return array_map([self::class, 'idList'], $this->groupsOf($user));
    }
}
