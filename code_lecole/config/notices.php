<?php

declare(strict_types=1);

// The notice board. Who may post, approve, and whose notices wait: config/permissions.php 'notices'. Whose
// notices each role may pin, edit and delete: permissions.php 'posts' (shared with the calendar). Who sees a notice:
// NoticeModel::visibility().
return [
    // Each role's board: its address and its words. {student}, {Institution}… → words() (the school's words)
    'board' => [
        'student'    => ['url' => '/student/notice',      'subtitle' => 'Stay up to date with official notices, schedules, and club activities.',            'search' => 'Search notices or programmes…'],
        'parent'     => ['url' => '/parent/notice-board', 'subtitle' => 'Stay up to date with notices across all your child’s academic programmes.',       'search' => 'Search notices or programmes…'],
        'teacher'    => ['url' => '/teacher/notice',      'subtitle' => 'Curriculum notices, department circulars, and {student} notices.',                 'search' => 'Search notices…'],
        'management' => ['url' => '/management/notice',   'subtitle' => '{Institution} notices, administrative circulars, and official directives.',       'search' => 'Search notices…'],
        'admin'      => ['url' => '/admin/notice',        'subtitle' => 'Stay up to date with notices across all {institution} programmes.',                'search' => 'Search notices…'],
    ],

    // The categories each poster may choose (the first is the form's default). The category decides what a notice can
    // be for: Academic → everyone, a grade or a class · Extracurricular → every club or one · the others → everyone.
    // Teachers post only to their own classes and clubs, so they get the two categories that have one.
    'categories' => [
        'admin'      => ['Academic', 'Extracurricular', 'General', 'Administrative'],
        'management' => ['Academic', 'Extracurricular', 'General', 'Administrative'],
        'teacher'    => ['Academic', 'Extracurricular'],
    ],

    // Who each poster may write to ('all' = every role in its list). Management doesn't post to management.
    // A teacher's "Students" / "Parents" = the students of their class or club, and those students' parents.
    'audience' => [
        'admin'      => ['all', 'student', 'parent', 'teacher', 'management'],
        'management' => ['all', 'student', 'parent', 'teacher'],
        'teacher'    => ['all', 'student', 'parent'],
    ],

    // The boards with the "For Me" / "Posted by Me" tabs (the posters who also receive notices; admin has none)
    'tabs' => ['teacher', 'management'],

    // The dashboards with the "Unread Notices" card (the readers')
    'unread_card' => ['student', 'parent'],

    // Each audience's tag colour on a card (tag.css tones); 'all' = a notice for every role
    'audience_tones' => ['all' => 'linen', 'student' => 'sky', 'teacher' => 'sunshine', 'parent' => 'terracotta', 'management' => 'maroon'],

    'max_pinned'  => 5,   // pinned notices at the top at one time (live ones)
    'max_file_mb' => 2,   // the attachment limit (the form shows it; Core\Upload::document() enforces it)
];
