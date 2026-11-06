<?php

declare(strict_types=1);

// Feedback (the teacher's Feedback Channel, the parent's and the student's Teacher Feedback pages). Read with
// config('feedback.types') … Who may write / read it: config/permissions.php 'feedback'.
return [
    // The three kinds and how each looks: type → [its name, its icon (icons.php)]; the colours: feedback.css
    // (.c-feedback-card--{type}: positive moss, constructive sunshine, negative maroon). The table's `kind` has the same keys.
    'types' => [
        'positive'     => ['Positive', 'starFilled'],
        'constructive' => ['Constructive', 'trendingUp'],
        'negative'     => ['Negative', 'alertCircle'],
    ],

    // Who reads it (New Feedback's Audience). 'student' is never shown to a parent, 'parent' never to the student.
    // The table's `audience` has the same keys.
    'audiences' => [
        'both'    => word('student') . ' & ' . word('parent'),
        'student' => word('student') . ' only',
        'parent'  => word('parent') . ' only',
    ],

    // The Subject choice that is not one of the class's subjects (the rest: the class's curriculum, read live)
    'general' => 'General Conduct',

    // The longest subject, title and text, in characters (the same as the feedback table's columns, update 045)
    'lengths' => [
        'subject' => 100,
        'title'   => 150,
        'message' => 2000,
    ],
];
