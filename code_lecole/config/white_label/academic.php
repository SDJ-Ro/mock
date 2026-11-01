<?php

declare(strict_types=1);

return [
    // A class's places: what the Academic page accepts (a new class starts with the max)
    'class_size' => [
        'min' => 15,
        'max' => 40,
    ],

    // The grade numbers this school can have: "Add Grade" and a curriculum stage's from / to lists
    'grade_numbers' => [
        'first' => 1,
        'last'  => 13,
    ],

    // A class is named from its grade's number and its section: "6-A". A new grade starts with section 'A'.
    'class_name'    => '{number}-{section}',
    'first_section' => 'A',

    'student_age' => [
        'min' => 9,
        'max' => 19,
    ],

    'max_subjects_per_teacher' => 5,

    'id_formats' => [
        'student'    => 'STU-{year}-{number}',
        'teacher'    => 'TEA-{year}-{number}',
        'management' => 'MAN-{year}-{number}',
        'parent'     => 'PAR-{year}-{number}',
    ],

];
