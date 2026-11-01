<?php

declare(strict_types=1);

namespace App\Services;

// A teacher picker's options (the 'teacher' dropdown), from ClassModel::teachers(): "Assignment pending" (nobody)
// first, then each teacher with their subjects underneath. For a class teacher picker a teacher who already has a
// class is greyed out, and says which (academic.js lets the class being edited keep its own teacher).
// Used by Academic (the class and subject pickers) and People (the teacher handover).
final class TeacherChoices
{
    public static function options(array $teachers, bool $forClassTeacher): array
    {
        $options = [['value' => '', 'label' => 'Assignment pending']];

        foreach ($teachers as $id => $teacher)
        {
            $busy      = $forClassTeacher && $teacher['class_name'] !== null;
            $options[] = [
                'value'    => (string) $id,
                'label'    => $teacher['name'],
                'sub'      => $busy ? words('{Class} {teacher} of ') . $teacher['class_name'] : ($teacher['qualification'] ?? ''),
                'disabled' => $busy,
            ];
        }

        return $options;
    }
}
