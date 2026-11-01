<?php

declare(strict_types=1);

$isDocker = env('DB_HOST') === null && gethostbyname('db') !== 'db';

return [
    'host'     => env('DB_HOST', $isDocker ? 'db' : '127.0.0.1'),
    'port'     => (int) env('DB_PORT', $isDocker ? 3306 : 3308),
    'database' => env('DB_NAME', 'l_ecole'),
    'username' => env('DB_USER', 'root'),
    'password' => (string) env('DB_PASS', 'root'),
    'charset'  => 'utf8mb4',

    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
