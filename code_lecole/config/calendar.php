<?php

declare(strict_types=1);

// Rules for the calendar's "Add event" pop-up. Whose events each role may edit: config/permissions.php 'posts'.
return [
    // The categories each role may choose (the first is the pop-up's default). The category decides who it is for:
    // General → the whole school · Academic → grades or classes · Extracurricular → clubs and sports.
    'categories' => [
        'admin'      => ['General', 'Academic', 'Extracurricular'],
        'management' => ['General', 'Academic', 'Extracurricular'],
        'teacher'    => ['Academic', 'Extracurricular'],
    ],

    // Who each role may show an event to ('all' = every role in the list)
    'audience' => [
        'admin'      => ['all', 'student', 'parent', 'teacher', 'management'],
        'management' => ['all', 'student', 'parent', 'teacher', 'management'],
        'teacher'    => ['student', 'parent'],
    ],

    // The years the calendar shows: last year (a record), this year, next year (planned dates).
    // Events can be added or changed only from today to the end of the last year shown; past ones can only be deleted.
    'years_back'  => 1,
    'years_ahead' => 1,

    // An event with a start but no end time counts as this long when checking for clashes
    'default_minutes' => 60,

    // The dashboards' "Upcoming Events" card: how many events, and each category's tag colour
    'upcoming_count' => 5,
    'tag_colors'     => [
        'General'         => 'sky',
        'Academic'        => 'sand',
        'Extracurricular' => 'terracotta',
    ],
];
