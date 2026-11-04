<?php

declare(strict_types=1);

// Who may do what — the ONE place to change access. Read with can('calendar.create').
// Each new feature adds its own block here as it is built.
// What goes here: a yes / no about a role being ALLOWED to do something. What doesn't: which options, fields, colours
// or addresses a role gets — those stay in the feature's own config (notices.php 'categories', profile.php 'editable').
return [
    // Shared by everything people post (calendar events, notices)
    'posts' => [
        'any_group'     => ['admin', 'management'],   // the whole school, any grade / class / club; the others: only their own classes and clubs
        'manage_others' => [                          // whose posts each role may edit and delete (their own: always) — can_manage()
            'admin'      => ['admin', 'management', 'teacher'],
            'management' => ['management', 'teacher'],
            'teacher'    => [],
        ],
    ],

    'calendar' => [
        'create'  => ['admin', 'management', 'teacher'],   // teachers: only for their own classes / clubs / sports
        'see_all' => ['admin', 'management'],              // every event, whoever it is for
    ],

    'notices' => [
        'post'              => ['admin', 'management', 'teacher'],   // teachers: only to their own classes / clubs / sports
        'see_all'           => ['admin', 'management'],              // every notice, also scheduled and waiting ones
        'approve'           => ['admin'],                            // approve / reject the waiting ones
        'wait_for_approval' => ['teacher'],                          // their new notices and edits wait (only with feature('approvals'))
    ],

    'complaints' => [
        'send'    => ['parent'],       // about the selected child only (checked by ComplaintController)
        'resolve' => ['management'],   // one answer to an inquiry still in progress
    ],

    'audit' => [
        'view' => ['admin'],   // the Audit Log page
    ],

    'academic' => [
        'edit' => ['admin', 'management'],   // the Academic page: grades, classes, teachers, curriculum stages
    ],

    'people' => [
        'directory'    => ['admin', 'management'],   // People: students, teachers, parents — see, add, edit, switch off
        'management'   => ['admin'],                 // the Management tab and its accounts (only the admin creates them)
        'own_students' => ['teacher'],               // the teacher's details page: only the classes they teach, view only
    ],

    'extracurricular' => [
        'page'    => ['student', 'teacher', 'management', 'admin'],   // the Extracurricular page (parents: the Child profile's tab)
        'join'    => ['student'],                                     // "Interest" (ask to join); the coach's phone only once enrolled
        'manage'  => ['admin', 'management'],  // create programmes, change the teacher in charge, edit any programme and its staff
        'approve' => ['admin'],                 // accept / reject a new programme (theirs need none), archive and restore one
        'watch'   => ['admin', 'management'],  // lift a student's Academic Watch (or lock it again), in People's profile pop-up
    ],

    'profile' => [
        'password' => ['admin', 'management', 'teacher', 'parent'],   // "Change Password" on My Profile (not students)
    ],
];
