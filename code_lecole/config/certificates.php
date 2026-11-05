<?php

declare(strict_types=1);

// Character certificates (the management and student pages). Read with config('certificates.…').
return [
    // A certificate's state → [its name, its tag colour (tag.css)]
    'statuses' => [
        'pending' => ['Pending Review', 'flame'],
        'issued'  => ['Issued', 'moss'],
    ],

    // Why the student needs it → [the tab's name, the certificate's words]
    'pathways' => [
        'graduating' => ['Graduating', 'Graduating student'],
    ],

    // The certificate's three sections, in order → [heading, icon (icons.php)]
    'sections' => [
        'academic'   => ['Special recognition', 'graduationCap'],
        'activities' => ['Extracurricular achievements', 'fileCheck'],
        'conduct'    => ['Conduct & character', 'user'],
    ],

    'max_section_chars' => 5000,

    // The capsule cards' colours (the student card tones, student-card.css's palette), picked from the student's ID
    'tones' => ['slate', 'steel', 'taupe', 'sand', 'seafoam', 'sage'],

    // A request's state → its tag colour
    'request_tones' => ['Pending' => 'flame', 'Approved' => 'moss', 'Rejected' => 'maroon'],

    // The student's missing-record pop-up (B-3)
    'record_types' => ['Academic Achievement', 'Sports / Club Enrollment or Position', 'Certificate or Award', 'Attendance Correction', 'Other'],
];
