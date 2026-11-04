<?php

declare(strict_types=1);

// Inquiries & Complaints (the parent and management pages). Read with config('complaints.categories').
return [
    // Match the one-way complaints table; the controller refuses longer form values.
    'limits' => [
        'subject'  => 150,
        'body'     => 2000,
        'response' => 2000,
    ],

    // The categories a parent chooses from, each with its card's colour: the left edge and the answer's tint
    // (complaints.css: .c-complaint-card--{tone})
    'categories' => [
        'Academic'        => 'sky',
        'Facilities'      => 'flame',
        'Transport'       => 'terracotta',
        'Administration'  => 'maroon',
        'Extracurricular' => 'sunshine',
    ],

    // The two states: key → [its name, its icon (icons.php)]
    'statuses' => [
        'in_progress' => ['In Progress', 'clock'],
        'resolved'    => ['Resolved', 'checkCircle2'],
    ],
];
