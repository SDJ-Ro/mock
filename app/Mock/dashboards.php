<?php

declare(strict_types=1);

// Example numbers for the five dashboards.
// Each block is replaced by real data when its part is built (donuts → extracurricular), and then deleted from here.
// Real already: Upcoming Events (2026-10-20), Unread Notices (2026-10-21, NoticeModel::unread), the marks cards and
// score charts (2026-10-29: a card with 'marks' => 'position' | 'average', and the 'scores' chart's terms, are filled
// by App\Services\MarksDashboard from the published terms), the sports and clubs pieces (2026-11-01: a card with
// 'extracurricular' => …, a donut with 'donut' => …, and the 'counts' chart's grades are filled by
// App\Services\ExtracurricularDashboard from the memberships).
// Read in DashboardController::show(). Colours are names ('maroon'): chart.css.
// Every card, chart and donut names the module it belongs to ('feature', config/white_label/features.php): a module
// switched off hides its pieces on every dashboard.

// The score chart students, parents and teachers see (bar chart 'scores' variant: 0–100 % with the Term picker);
// its terms come from MarksDashboard
$scoreChart = fn (string $id, string $title, array $series): array => [
    'feature'    => 'term_marks',
    'variant'    => 'scores',
    'id'         => $id,
    'title'      => $title,
    'xAxisTitle' => 'SUBJECTS',
    'yAxisTitle' => 'SCORE (%)',
    'series'     => $series,
];

// Admin + management: students and sports players per grade (bar chart 'counts' variant; its grades and ticks:
// ExtracurricularDashboard)
$schoolChart = fn (string $id): array => [
    'feature'    => 'extracurricular',   // its point is sports participation next to the totals
    'variant'    => 'counts',
    'id'         => $id,
    'title'      => words('Total {Students} vs Sports Participation by {Class}'),
    'yAxisTitle' => strtoupper(plural('student')),
    'xAxisTitle' => strtoupper(word('grade')) . ' LEVEL',
    'series'     => [
        ['key' => 'total',  'label' => words('Total {Students}'), 'color' => 'skyblue'],
        ['key' => 'sports', 'label' => 'Sports Participation',  'color' => 'maroon'],
    ],
];

// Admin + management: the two donuts ('unit' = the word after the number in the tooltip; slices: ExtracurricularDashboard)
$schoolDonuts = [
    ['feature' => 'extracurricular', 'donut' => 'sports', 'title' => 'Sports Participation', 'unit' => strtolower(plural('student'))],
    ['feature' => 'extracurricular', 'donut' => 'clubs',  'title' => 'Clubs & Societies',    'unit' => 'members'],
];

return [
    'student' => [
        'metrics' => [
            ['feature' => 'extracurricular', 'color' => 'maroon',    'icon' => 'extracurricular', 'extracurricular' => 'members', 'label' => 'Sports / Clubs Enrolled'],
            ['feature' => 'achievements',    'color' => 'lightblue', 'icon' => 'achievements',    'value' => '5',    'label' => 'Total Achievements'],
            ['feature' => 'term_marks',      'color' => 'sand',      'icon' => 'podium',          'marks' => 'position', 'label' => 'Class Position'],
            ['feature' => 'notices',         'color' => 'moss',      'icon' => 'mail',            'value' => '2',    'label' => 'Upcoming Notices / Exams'],
        ],
        'chartConfig' => $scoreChart('j-student-perf-chart', 'Academic Performance', [
            ['key' => 'mine', 'label' => 'My Score',      'color' => 'maroon'],
            ['key' => 'best', 'label' => 'Highest Score', 'color' => 'skyblue'],
        ]),
    ],

    'parent' => [
        'metrics' => [
            ['feature' => 'term_marks',      'color' => 'maroon',    'icon' => 'trendingUp',   'marks' => 'average', 'label' => 'Average Term Mark'],
            ['feature' => 'extracurricular', 'color' => 'lightblue', 'icon' => 'usersRound',   'extracurricular' => 'members', 'label' => 'Sports & Clubs Active'],
            ['feature' => 'term_marks',      'color' => 'sand',      'icon' => 'podium',       'marks' => 'position', 'label' => 'Class Position'],
            ['feature' => 'achievements',    'color' => 'lightblue', 'icon' => 'achievements', 'value' => '5',     'label' => 'Student Achievements'],
        ],
        'chartConfig' => $scoreChart('j-parent-perf-chart', "Child's Academic Performance", [
            ['key' => 'student', 'label' => 'Student Score', 'color' => 'maroon'],
            ['key' => 'average', 'label' => 'Class Average', 'color' => 'skyblue'],
        ]),
    ],

    'teacher' => [
        'metrics' => [
            ['feature' => 'academic',        'color' => 'maroon',    'icon' => 'bookOpen',   'value' => '4',   'label' => 'Active Subject Classes'],
            ['feature' => 'academic',        'color' => 'lightblue', 'icon' => 'users',      'value' => '168', 'label' => 'Total Assigned Students'],
            ['feature' => 'term_marks',      'color' => 'sand',      'icon' => 'trendingUp', 'marks' => 'average', 'label' => 'Average Term Mark'],
            ['feature' => 'extracurricular', 'color' => 'sunshine',  'icon' => 'usersRound', 'extracurricular' => 'players', 'label' => 'Total Enrolled Players'],
        ],
        'chartConfig' => $scoreChart('j-teacher-perf-chart', 'Academic Performance', [
            ['key' => 'avg',  'label' => 'Class Average', 'color' => 'maroon'],
            ['key' => 'high', 'label' => 'Highest Score', 'color' => 'skyblue'],
        ]),
        // The programmes they run (slices: ExtracurricularDashboard; the second one's title gets the biggest one's name)
        'donuts' => [
            ['feature' => 'extracurricular', 'donut' => 'mine',   'title' => 'Extra-curricular Activity Engagement', 'unit' => strtolower(plural('student'))],
            ['feature' => 'extracurricular', 'donut' => 'grades', 'title' => 'Grade wise engagement of',             'unit' => 'players'],
        ],
    ],

    'management' => [
        'metrics' => [
            ['feature' => 'people',          'color' => 'sand',     'icon' => 'graduationCap',   'value' => '2,845', 'label' => 'Total Students'],
            ['feature' => 'people',          'color' => 'maroon',   'icon' => 'usersRound',      'value' => '157',   'label' => 'Total Staff Members'],
            ['feature' => 'extracurricular', 'color' => 'moss',     'icon' => 'extracurricular', 'extracurricular' => 'programmes', 'label' => 'Extracurricular Programs'],
            ['feature' => 'approvals',       'color' => 'sunshine', 'icon' => 'shieldCheck',     'value' => '12',    'label' => 'Pending Verifications'],
        ],
        'chartConfig'    => $schoolChart('j-management-enrollment-chart'),
        'donuts'         => $schoolDonuts,
    ],

    'admin' => [
        'metrics' => [
            ['feature' => 'people',          'color' => 'sand',      'icon' => 'graduationCap',   'value' => '2,845', 'label' => 'Total Students'],
            ['feature' => 'people',          'color' => 'maroon',    'icon' => 'presentation',    'value' => '145',   'label' => 'Total Teachers'],
            ['feature' => 'people',          'color' => 'sunshine',  'icon' => 'usersRound',      'value' => '2,102', 'label' => 'Total Parents'],
            ['feature' => 'people',          'color' => 'lightblue', 'icon' => 'briefcase',       'value' => '12',    'label' => 'Management Panel'],
            ['feature' => 'extracurricular', 'color' => 'moss',      'icon' => 'extracurricular', 'extracurricular' => 'programmes', 'label' => 'Extracurriculars'],
        ],
        'chartConfig'    => $schoolChart('j-admin-enrollment-chart'),
        'donuts'         => $schoolDonuts,
    ],
];
