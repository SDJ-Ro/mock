<?php

declare(strict_types=1);

return [
    'type'          => 'school',

    'name'          => "L'École",
    'tagline'       => 'Where passions become purpose',
    'logo'          => '/assets/images/logo.png',
    'campus_image'  => '/assets/images/schoolyard.jpg',

    // Each role's photo: the sign-in pages, the landing page's cards and (until a person adds their own) the sidebar
    'photos'        => [
        'student'    => ['src' => '/assets/images/students.jpg',         'alt' => 'Student boy and girl in school uniform'],
        'parent'     => ['src' => '/assets/images/parents.jpg',          'alt' => 'Mother and father holding their baby'],
        'teacher'    => ['src' => '/assets/images/teacher.jpg',          'alt' => 'Welcoming teacher holding a book'],
        'management' => ['src' => '/assets/images/management.jpg',       'alt' => 'School leadership team talking in a campus corridor'],
        'admin'      => ['src' => '/assets/images/Schoolbackground.jpg', 'alt' => "The L'École building and front lawn"],
    ],

    'email_domain'  => 'lecole.edu',
    'contact_email' => 'hello@lecole.edu',
    'support_email' => 'office@lecole.edu',
    'phone'         => '+1 (234) 567-890',
    'address'       => ['123 Education Lane', 'Knowledge City, KC 10000'],
    'motto'         => 'Curious minds. Generous hearts. Brave futures.',
    'about'         => 'A school community where academic focus meets the joy of making, performing, competing, contributing, and growing.',
];
