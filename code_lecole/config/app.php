<?php

declare(strict_types=1);

return [
    'debug_mode' => env('APP_DEBUG', false),

    'url' => rtrim((string) env('APP_URL', ''), '/'),   // the site's address, for links inside emails (welcome email)

    'session_timeout' => [
        'admin'   => 900,
        'default' => 1800,
    ],

    'roles' => ['student', 'teacher', 'parent', 'management', 'admin'],

    'login' => [
        'max_attempts' => 5,
        'lock_minutes' => 15,
    ],

    // One-time 6-digit codes (activation + forgot password)
    'codes' => [
        'expire_minutes' => 15,   // code expires after 15 minutes
        'max_attempts'   => 5,    // wrong guesses allowed before locking code
        'resend_seconds' => 60,   // seconds to wait before requesting another code
    ],

    // Rules for new passwords
    'password_rules' => [
        ['/.{8,}/',        'at least 8 characters'],
        ['/[A-Za-z]/',     'a letter'],
        ['/[0-9]/',        'a number'],
        ['/[^A-Za-z0-9]/', 'a symbol (like @ or #)'],
    ],
];
