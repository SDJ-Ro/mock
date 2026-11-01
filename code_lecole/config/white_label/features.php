<?php

declare(strict_types=1);

// Central Feature Flags: Toggle any module ON (true) or OFF (false)
// When false: routes and sidebar links for that feature disappear with zero code breakage.
return [
    // Auth & Accounts
    'student_signup'    => true,
    'parent_signup'     => true,
    'teacher_signup'    => true,
    'management_signup' => true,
    'admin_signup'      => false,   // Admins are created by system provision, not self-signup
    'demo_quickfill'    => true,    // "Fill in demo account" button

    // Public Pages
    'honours_board'     => true,    // Public achievements showcase

    // School Core Modules
    'academic'          => true,    // Classes, subjects, teachers, grading
    'term_marks'        => true,    // Academic term marks & report cards
    'extracurricular'   => true,    // Sports, clubs, and societies
    'achievements'      => true,    // Student achievements & awards
    'certificates'      => true,    // Character certificate requests & issuance
    'notices'           => true,    // Notice board announcements
    'calendar'          => true,    // School calendar & events
    'people'            => true,    // Student, parent, and staff directories
    'profile'           => true,    // User profile: edit details, change password, upload photo

    // Moderation & Governance
    'parent_consent'    => true,    // Parent must approve student joining clubs/activities
    'approvals'         => true,    // Teachers' notices (and later new clubs) wait for the admin's OK where they live
    'feedback'          => true,    // Teachers write term feedback on students
    'complaints'        => true,    // Parents submit queries/complaints to administration
    'audit_log'         => true,    // Admin security & audit trail
];
