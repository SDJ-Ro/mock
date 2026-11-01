<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

$autoloadFile = $projectRoot . '/vendor/autoload.php';

if (!is_file($autoloadFile))
{
    throw new RuntimeException('Composer autoloader not found. Run composer install first.');
}

require_once $autoloadFile;

require_once __DIR__ . '/helpers.php';

$envFile = $projectRoot . '/.env';

if (is_file($envFile))
{
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
    {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '='))
        {
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"\'');

        if (getenv($name) === false)
        {
            putenv("{$name}={$value}");
        }
    }
}

// "Today" is the school's today (without this PHP uses the server's clock, often UTC)
date_default_timezone_set(country('timezone'));

if (session_status() === PHP_SESSION_NONE)
{
    session_start([
        'cookie_httponly' => true,    // JavaScript can't read the session cookie (stops cookie theft by scripts)
        'cookie_samesite' => 'Lax',   // other websites can't send it along with their forms
        'use_strict_mode' => true,    // made-up session IDs are refused
    ]);
}

// Security headers on every answer. Only our own pages may show a page of ours inside a frame (Export PDF does);
// another website can't, so it can't hide our admin page under its own buttons ("clickjacking"). The browser must
// not guess a file's type from its content, other websites aren't told which of our pages someone came from, and
// the PHP version isn't announced.
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header_remove('X-Powered-By');

if (config('app.debug_mode', false))
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}
else
{
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    ini_set('error_log', $projectRoot . '/storage/logs/php_errors.log');

    // Live site: any crash → the details go to the log, visitors see a friendly page (never file paths or code)
    set_exception_handler(function (Throwable $error) use ($projectRoot): void
    {
        error_log((string) $error);
        http_response_code(500);
        $code    = 500;
        $heading = 'Something went wrong';
        $message = "We couldn't load this page. Please try again in a moment.";
        require $projectRoot . '/app/Views/errors/error.php';
    });
}
