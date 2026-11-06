<?php

declare(strict_types=1);

// Everything that differs between the 5 roles, in one place: the sign-in pages' texts AND the sidebar after sign-in.
// Read with config('portals'), config('portals.teacher'), config('portals.teacher.label').
// The order here is the order on the "Who's signing in?" page (Student, Parent, Teacher, Management, Admin).
// Names and emails come from the white-label settings: word('teacher'), school('name')…
//
// Sidebar keys:
//   'subtitle' the small word under the school name      'avatar' the picture until the person adds a photo (mock)
//   'menu'     the links, top to bottom (label, icon, address). 'feature' => 'notices' hides the link while that
//              switch is off (config/white_label/features.php); 'iconSize' makes one icon bigger.

$domain   = school('email_domain');
$footnote = 'Need access or lost your credentials? Contact ' . school('support_email');

return [
    'student' => [
        'label'              => word('student'),
        'chooser_name'       => word('student'),
        'tone'               => 'tan',
        'icon'               => 'graduationCap',
        'tagline'            => words('Timetable, {grades}, and report cards, plus the clubs, teams, and achievements you build along the way.'),
        'eyebrow'            => 'Your learning journey',
        'desc'               => 'Pick up your academic day, activities, teams, and next opportunities.',
        'headline'           => 'Follow academics, discover clubs, and build the skills that shape your next chapter.',
        'image'              => school('photos.student.src'),
        'alt'                => school('photos.student.alt'),
        'field'              => words('{Student} Index Number'),
        'placeholder'        => 'e.g. STU-2026-0001',
        'demo'               => 'STU-2026-0001',
        'footnote'           => null,
        'can_signup'         => feature('student_signup'),
        'signup_title'       => words('Start Your {Student} Access'),
        'signup_desc'        => words('Request access to your academics, activities, and {institution} community.'),
        'signup_field'       => words('{Student} Index Number'),
        'signup_placeholder' => 'e.g. STU-2026-0001',

        'subtitle' => word('student') . ' Portal',
        'avatar'   => school('photos.student.src'),
        'menu'     => [
            ['label' => 'Dashboard',             'href' => '/student/dashboard',       'icon' => 'dashboard'],
            ['label' => 'Academic Records',      'href' => '/student/academic',        'icon' => 'bookOpen',        'feature' => 'term_marks'],
            ['label' => 'Extracurricular',       'href' => '/student/extracurricular', 'icon' => 'extracurricular', 'feature' => 'extracurricular', 'iconSize' => 19],
            ['label' => 'Achievements',          'href' => '/student/achievements',    'icon' => 'achievements',    'feature' => 'achievements'],
            ['label' => 'Character Certificate', 'href' => '/student/certificates',    'icon' => 'certificate',     'feature' => 'certificates'],
            ['label' => 'Notice Board',          'href' => '/student/notice',          'icon' => 'mail',            'feature' => 'notices'],
            ['label' => 'Feedback',              'href' => '/student/feedback',        'icon' => 'feedback',        'feature' => 'feedback'],
        ],
    ],

    'parent' => [
        'label'              => word('parent'),
        'chooser_name'       => word('parent'),
        'tone'               => 'lightblue',
        'icon'               => 'heartHandshake',
        'tagline'            => words('Follow progress, activities, and achievements, approve {institution} forms, and stay in touch with {teachers}.'),
        'eyebrow'            => 'Part of the team',
        'desc'               => "Stay connected to your child's academics, activities, moments, and progress.",
        'headline'           => 'See academic milestones and the activities helping your child grow in confidence.',
        'image'              => school('photos.parent.src'),
        'alt'                => school('photos.parent.alt'),
        'field'              => 'Email Address',
        'placeholder'        => 'parent@email.com',
        'demo'               => 'suresh_perera@gmail.com',
        'footnote'           => null,
        'can_signup'         => feature('parent_signup'),
        'signup_title'       => words('Activate {Parent} Portal Access'),
        'signup_desc'        => 'Verify your registered email and create your account password.',
        'signup_field'       => 'Registered Email',
        'signup_placeholder' => 'parent@email.com',

        'subtitle' => word('parent') . ' Portal',
        'avatar'   => school('photos.parent.src'),
        'menu'     => [
            ['label' => 'Dashboard',     'href' => '/parent/dashboard',     'icon' => 'dashboard'],
            ['label' => 'Child Profile', 'href' => '/parent/child-profile', 'icon' => 'usersRound'],
            ['label' => 'Approvals',     'href' => '/parent/approvals',     'icon' => 'checkCircle2', 'feature' => 'parent_consent'],
            ['label' => 'Notice Board',  'href' => '/parent/notice-board',  'icon' => 'mail',         'feature' => 'notices'],
            ['label' => 'Feedback',      'href' => '/parent/feedback',      'icon' => 'feedback',     'feature' => 'feedback'],
            ['label' => 'Complaints',    'href' => '/parent/complaints',    'icon' => 'complaints',   'feature' => 'complaints'],
        ],
    ],

    'teacher' => [
        'label'              => word('teacher'),
        'chooser_name'       => word('teacher'),
        'tone'               => 'maroon',
        'icon'               => 'bookOpen',
        'tagline'            => words('Lessons, {grades}, and attendance beside the clubs, teams, and {student} development you guide.'),
        'eyebrow'            => 'Guide the journey',
        'desc'               => words('Move between your {classes}, activities, and {student} progress with clarity.'),
        'headline'           => words('Bring learning, clubs, teams, and {student} development into one thoughtful view.'),
        'image'              => school('photos.teacher.src'),
        'alt'                => school('photos.teacher.alt'),
        'field'              => words('{Institution} Email'),
        'placeholder'        => "name@{$domain}",
        'demo'               => "alex_benjamin@{$domain}",
        'footnote'           => null,
        'can_signup'         => feature('teacher_signup'),
        'signup_title'       => words('Activate Your {Teacher} Account'),
        'signup_desc'        => 'Set your permanent password to access teaching schedules and marks.',
        'signup_field'       => words('{Institution} Email'),
        'signup_placeholder' => "name@{$domain}",

        'subtitle' => word('teacher'),
        'avatar'   => school('photos.teacher.src'),
        'menu'     => [
            ['label' => 'Dashboard',                      'href' => '/teacher/dashboard',       'icon' => 'dashboard'],
            ['label' => word('student') . ' Details',     'href' => '/teacher/students',        'icon' => 'usersRound',      'feature' => 'people'],
            ['label' => word('term') . ' Marks',          'href' => '/teacher/marks',           'icon' => 'bookOpen',        'feature' => 'term_marks'],
            ['label' => 'Feedback',                       'href' => '/teacher/feedback',        'icon' => 'feedback',        'feature' => 'feedback'],
            ['label' => 'Extracurricular',                'href' => '/teacher/extracurricular', 'icon' => 'extracurricular', 'feature' => 'extracurricular', 'iconSize' => 19],
            ['label' => 'Achievements',                   'href' => '/teacher/achievements',    'icon' => 'achievements',    'feature' => 'achievements'],
            ['label' => 'Notice Board',                   'href' => '/teacher/notice',          'icon' => 'mail',            'feature' => 'notices'],
        ],
    ],

    'management' => [
        'label'              => word('management'),
        'chooser_name'       => words('{Management} Panel'),
        'tone'               => 'terracotta',
        'icon'               => 'building2',
        'tagline'            => words('Approvals, enrolment, staffing, curriculum, and {institution}-wide reporting in one leadership workspace.'),
        'eyebrow'            => 'Restricted access',
        'desc'               => words('Approvals, enrolment, staffing, and {institution}-wide performance in one leadership workspace.'),
        'headline'           => words('Lead the {institution} with a clear view of every decision.'),
        'image'              => school('photos.management.src'),
        'alt'                => school('photos.management.alt'),
        'field'              => 'Institutional Email',
        'placeholder'        => "name@{$domain}",
        'demo'               => "sarah_vance@{$domain}",
        'footnote'           => $footnote,
        'can_signup'         => feature('management_signup'),
        'signup_title'       => 'Leadership Account Activation',
        'signup_desc'        => words('Set your permanent credentials to access the {management} workspace.'),
        'signup_field'       => 'Institutional Email',
        'signup_placeholder' => "name@{$domain}",

        'subtitle' => word('management'),
        'avatar'   => school('photos.management.src'),
        'menu'     => [
            ['label' => 'Dashboard',             'href' => '/management/dashboard',             'icon' => 'dashboard'],
            ['label' => 'People Directory',      'href' => '/management/people',                'icon' => 'usersRound',      'feature' => 'people'],
            ['label' => 'Academic',              'href' => '/management/academic',              'icon' => 'bookOpen',        'feature' => 'academic'],
            ['label' => 'Extracurricular',       'href' => '/management/extracurricular',       'icon' => 'extracurricular', 'feature' => 'extracurricular', 'iconSize' => 19],
            ['label' => 'Notice Board',          'href' => '/management/notice',                'icon' => 'mail',            'feature' => 'notices'],
            ['label' => 'Character Certificate', 'href' => '/management/character-certificate', 'icon' => 'certificate',     'feature' => 'certificates'],
            ['label' => 'Complaints',            'href' => '/management/complaints',            'icon' => 'complaints',      'feature' => 'complaints'],
        ],
    ],

    'admin' => [
        'label'              => 'Admin',
        'chooser_name'       => 'Admin',
        'tone'               => 'midnight',
        'icon'               => 'shield',
        'tagline'            => 'Accounts, permissions, records, and the full audit trail behind the platform.',
        'eyebrow'            => 'Restricted access',
        'desc'               => 'Manage accounts, permissions, records, and the full audit trail of ' . school('name') . '.',
        'headline'           => 'The system behind every record, role, and safeguard.',
        'image'              => school('photos.admin.src'),
        'alt'                => school('photos.admin.alt'),
        'field'              => 'Administrator Email',
        'placeholder'        => "name_admin@{$domain}",
        'demo'               => "david_silva_admin@{$domain}",
        'footnote'           => $footnote,
        'can_signup'         => feature('admin_signup'),

        'subtitle' => 'Admin',
        'avatar'   => '/assets/images/admin.jpg',
        'menu'     => [
            ['label' => 'Dashboard',                 'href' => '/admin/dashboard',       'icon' => 'dashboard'],
            ['label' => 'Academic',                  'href' => '/admin/academic',        'icon' => 'bookOpen',        'feature' => 'academic'],
            ['label' => 'Extracurricular',           'href' => '/admin/extracurricular', 'icon' => 'extracurricular', 'feature' => 'extracurricular', 'iconSize' => 19],
            ['label' => 'People Directory',          'href' => '/admin/people',          'icon' => 'usersRound',      'feature' => 'people'],
            ['label' => 'Notice Board',              'href' => '/admin/notice',          'icon' => 'mail',            'feature' => 'notices'],
            ['label' => 'Audit Log',                 'href' => '/admin/audit',           'icon' => 'lockKeyhole',     'feature' => 'audit_log'],
        ],
    ],
];
