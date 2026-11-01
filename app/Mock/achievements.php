<?php

declare(strict_types=1);

// Example data for the Achievements pages: every student sees the same timeline.
// Read in AchievementController. Replaced by real data when the achievements backend is built.
// Photos: public/assets/images/examples/ (delete with this file).
return [
    // The timeline: [group (config/achievements.php 'groups'), title, date, description], newest first
    'timeline' => [
        ['group' => 'academic',   'title' => 'Top of the Grade — Term 2',              'date' => '2025-08-01',
         'description' => 'Ranked 1st out of 142 students in Grade 10, Term 2, with a subject average of 91%.'],
        ['group' => 'clubs',      'title' => 'Best Mural — Inter-School Art Fest',     'date' => '2025-06-01',
         'description' => 'Awarded Best Mural for a collaborative piece submitted with the Art & Design Club at the annual Inter-School Art Fest.'],
        ['group' => 'sports',     'title' => 'Zonal Runners-Up — Cricket 2025 Season', 'date' => '2025-04-01',
         'description' => 'Helped the U17 Cricket squad finish as Zonal Runners-Up in a closely contested final.'],
        ['group' => 'sports',     'title' => 'Player of the Series',                   'date' => '2025-03-01',
         'description' => 'Named Player of the Series at the Inter-School U17 Cricket Tournament for consistent performances with bat and ball.'],
        ['group' => 'leadership', 'title' => 'House Prefect — Teal House',             'date' => '2025-01-01',
         'description' => 'Appointed House Prefect for Teal House, responsible for coordinating inter-house sports and events for the year.'],
        ['group' => 'sports',     'title' => 'Regional Winner — Cricket',              'date' => '2024-10-01',
         'description' => 'Won first place representing the school at the Regional Inter-School Cricket Championship, U17 division.'],
        ['group' => 'academic',   'title' => 'Distinction — Grade 9 Year-End Exams',   'date' => '2024-03-01',
         'description' => 'Achieved distinction-level passes across all subjects in the Grade 9 year-end examinations.'],
        ['group' => 'clubs',      'title' => 'Selected — Inter-School Art Exhibition', 'date' => '2023-09-01',
         'description' => 'Two original pieces selected for display at the Colombo Inter-School Art Exhibition.'],
        ['group' => 'academic',   'title' => 'Merit Award — Science Quiz',             'date' => '2023-05-01',
         'description' => 'Placed among the top three teams at the Inter-School Science Quiz, earning a merit award for the school.'],
        ['group' => 'leadership', 'title' => 'Captain — Junior Cricket Team',          'date' => '2023-02-01',
         'description' => 'Appointed Captain of the Junior Cricket Team, leading the squad through the inter-house tournament season.'],
    ],

    // The teacher's student cards: 'from' = in their Class or an Extracurricular activity; 'tone' = the card colour;
    // 'issue' = the student reported a missing record (red ring, "View Issue")
    'students' => [
        ['name' => 'Amara Silva',           'code' => '2021/0203', 'from' => 'Class',           'tone' => 'slate',   'issue' => true,
         'photo' => asset('images/examples/face-02.jpg')],
        ['name' => 'Dilan Jayasuriya',      'code' => '2021/0501', 'from' => 'Class',           'tone' => 'steel',   'issue' => false,
         'photo' => asset('images/examples/face-01.jpg')],
        ['name' => 'Ishara Wickramasinghe', 'code' => '2021/0620', 'from' => 'Extracurricular', 'tone' => 'taupe',   'issue' => false,
         'photo' => asset('images/examples/face-09.jpg')],
        ['name' => 'John Doe',              'code' => '2021/0892', 'from' => 'Class',           'tone' => 'sand',    'issue' => false,
         'photo' => asset('images/examples/face-03.jpg')],
        ['name' => 'Maya Kapoor',           'code' => '2022/0118', 'from' => 'Extracurricular', 'tone' => 'seafoam', 'issue' => false,
         'photo' => asset('images/examples/face-08.jpg')],
        ['name' => 'Nethmi Perera',         'code' => '2021/0456', 'from' => 'Class',           'tone' => 'sage',    'issue' => false,
         'photo' => asset('images/examples/face-05.jpg')],
    ],

    // The issue a student reported (Review Issue pop-up + the evidence preview)
    'issue' => [
        'message'     => 'Student reported that their House Prefect appointment was missing from their profile records.',
        'group'       => 'leadership',
        'title'       => 'House Prefect — Teal House',
        'date'        => '2025-01-15',
        'issuer'      => 'Teal House Master',
        'description' => 'Appointed House Prefect for Teal House, responsible for coordinating inter-house sports and events for the year.',
        'file'        => 'Appointment_Letter_2025.pdf',
        'file_meta'   => 'PDF Document • 1.2 MB • Attached by student',
        'file_title'  => 'Official Appointment as Teal House Prefect (Jan 2025)',
        'issued_by'   => 'Teal House Master, ' . school('name') . ' International',
        'issued_on'   => '15th January 2025',
        'content'     => '"This is to certify that Amara Silva has been duly appointed as House Prefect for Teal House for the academic year 2025, with responsibilities including co-curricular leadership and event coordination."',
    ],
];
