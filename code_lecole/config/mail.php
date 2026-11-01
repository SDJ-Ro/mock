<?php

declare(strict_types=1);

// How emails are sent.
// 'log' = written to storage/logs/mail.log (safe for local development & testing offline)
// 'smtp' = sends real emails through an SMTP server (now Resend: .env SMTP_* and MAIL_FROM; Gmail works the same way)
return [
    'driver'       => env('MAIL_DRIVER', 'smtp'),
    'redirect_to'  => env('MAIL_REDIRECT_TO', ''),   // testing: EVERY email goes to this one inbox ('' = to the real person)
    'from_name'    => school('name'),
    'from_address' => env('MAIL_FROM', env('SMTP_USER', '')),   // the sender's address (Gmail: the account itself)
    'log_file'     => dirname(__DIR__) . '/storage/logs/mail.log',

    'smtp' => [
        'host'     => env('SMTP_HOST', 'smtp.gmail.com'),
        'port'     => (int) env('SMTP_PORT', 587),
        'username' => env('SMTP_USER', ''),   // the real ones live in .env only (never in code)
        'password' => env('SMTP_PASS', ''),
    ],
];
