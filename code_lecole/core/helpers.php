<?php

declare(strict_types=1);

if (!function_exists('e'))
{
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('env'))
{
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false)
        {
            return $default;
        }

        return match (strtolower($value))
        {
            'true'  => true,
            'false' => false,
            'null'  => null,
            default => $value,
        };
    }
}

if (!function_exists('config'))
{
    function config(string $key, mixed $default = null): mixed
    {
        static $loaded = [];

        $segments = explode('.', $key);
        $path = dirname(__DIR__) . '/config';

        while (count($segments) > 1 && is_dir($path . '/' . $segments[0]))
        {
            $path .= '/' . array_shift($segments);
        }

        $file = $path . '/' . array_shift($segments) . '.php';

        if (!is_file($file))
        {
            return $default;
        }

        $loaded[$file] ??= require $file;
        $value = $loaded[$file];

        foreach ($segments as $segment)
        {
            if (!is_array($value) || !array_key_exists($segment, $value))
            {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('school'))
{
    function school(string $key, mixed $default = null): mixed
    {
        return config("white_label.school.{$key}", $default);
    }
}

// country('timezone') → "Asia/Colombo", country('date_format') → "M d, Y" (config/white_label/country.php)
if (!function_exists('country'))
{
    function country(string $key, mixed $default = null): mixed
    {
        return config("white_label.country.{$key}", $default);
    }
}

if (!function_exists('feature'))
{
    function feature(string $name): bool
    {
        return (bool) config("white_label.features.{$name}", false);
    }
}

// ini_bytes('post_max_size') → 8388608: a php.ini size setting ("8M", "512K", "1G") in bytes (uploads: core/Upload.php,
// and the too-big form check in Controller::checkCsrf())
if (!function_exists('ini_bytes'))
{
    function ini_bytes(string $setting): int
    {
        $size   = trim((string) ini_get($setting));
        $number = (int) $size;

        return match (strtoupper(substr($size, -1)))
        {
            'G'     => $number * 1024 ** 3,
            'M'     => $number * 1024 ** 2,
            'K'     => $number * 1024,
            default => $number,
        };
    }
}

// rule('commended.on') → true, rule('commended.average') → 80 (config/white_label/rules.php). Every school rule is
// read through here, so the admin's own values (Settings, B-13) can later take their place in one spot.
if (!function_exists('rule'))
{
    function rule(string $key): mixed
    {
        return config("white_label.rules.{$key}");
    }
}

if (!function_exists('word'))
{
    function word(string $key): string
    {
        $type = school('type', 'school');

        return config("white_label.wording.{$type}.{$key}")
            ?? config("white_label.wording.school.{$key}")
            ?? ucfirst($key);
    }
}
// asset('css/global.css') → "/assets/css/global.css?v=1760812345" (the file's last change time).
// The browser keeps a file until it changes; after an edit the number changes, so it fetches the new one.
if (!function_exists('asset'))
{
    function asset(string $path): string
    {
        $file = dirname(__DIR__) . '/public/assets/' . $path;

        return '/assets/' . $path . '?v=' . (is_file($file) ? filemtime($file) : 0);
    }
}

// url_with('/student/extracurricular', ['type' => 'sport', 'q' => '']) → "/student/extracurricular?type=sport"
// (empty values are left out; no "?" when nothing is left) — the same list links from two pages' addresses
if (!function_exists('url_with'))
{
    function url_with(string $path, array $params): string
    {
        $query = http_build_query(array_filter($params, fn ($value): bool => $value !== '' && $value !== null));

        return $query === '' ? $path : "{$path}?{$query}";
    }
}

// plural('student') → "Students", plural('class') → "Classes" — in this school's words
if (!function_exists('plural'))
{
    function plural(string $word): string
    {
        return word(match ($word) { 'class' => 'classes', 'management' => 'management', default => "{$word}s" });
    }
}

// words('{Class} Teacher') → "Class Teacher" (a university: "Course Lecturer"); words('your {classes}') → "your classes".
// Every {key} becomes word('key'): {Class} keeps the capital, {class} is all small letters.
if (!function_exists('words'))
{
    function words(string $text): string
    {
        return preg_replace_callback('/\{(\w+)\}/', fn (array $match): string => ctype_upper($match[1][0])
            ? word(strtolower($match[1]))
            : strtolower(word($match[1])), $text);
    }
}

// can('calendar.create') → may the signed-in user's role do this? (config/permissions.php)
if (!function_exists('can'))
{
    function can(string $ability): bool
    {
        $role = $_SESSION['user']['role'] ?? null;

        return $role !== null && in_array($role, config("permissions.{$ability}", []), true);
    }
}

// can_manage($event) → may the signed-in user edit / delete this post? Needs its author_id + author_role.
// Their own always; other people's as config/permissions.php 'posts.manage_others' says.
if (!function_exists('can_manage'))
{
    function can_manage(array $post): bool
    {
        $user = $_SESSION['user'] ?? null;

        return $user !== null && ((int) $post['author_id'] === (int) $user['id']
            || in_array($post['author_role'], config("permissions.posts.manage_others.{$user['role']}") ?? [], true));
    }
}

// event_time($event) → "09:00 – 10:30", "15:00" (no end) or "All Day" (no start)
if (!function_exists('event_time'))
{
    function event_time(array $event): string
    {
        if ($event['start_time'] === null)
        {
            return 'All Day';
        }

        return substr($event['start_time'], 0, 5) . ($event['end_time'] ? ' – ' . substr($event['end_time'], 0, 5) : '');
    }
}

// grade_name(6) → "Grade 6" (a university: "Year 6"). Grades store only their number, so the school's word always shows.
if (!function_exists('grade_name'))
{
    function grade_name(int $number): string
    {
        return word('grade') . " {$number}";
    }
}

// grade_range(6, 9) → "Grades 6–9", grade_range(8, 8) → "Grade 8" — a curriculum stage's title
if (!function_exists('grade_range'))
{
    function grade_range(int $first, int $last): string
    {
        return $first === $last ? grade_name($first) : plural('grade') . " {$first}–{$last}";
    }
}

// class_name(6, 'A') → "6-A" (config/white_label/academic.php 'class_name')
if (!function_exists('class_name'))
{
    function class_name(int $gradeNumber, string $section): string
    {
        return strtr(config('white_label.academic.class_name'), ['{number}' => $gradeNumber, '{section}' => $section]);
    }
}

// class_prefix(6) → "6-" — what class_name() puts before the section (the class editor shows it in front of the box)
if (!function_exists('class_prefix'))
{
    function class_prefix(int $gradeNumber): string
    {
        return explode('{section}', strtr(config('white_label.academic.class_name'), ['{number}' => $gradeNumber]), 2)[0];
    }
}

// class_section(6, '6-A') → "A" — the part class_name() put in (for the class editor)
if (!function_exists('class_section'))
{
    function class_section(int $gradeNumber, string $name): string
    {
        $before = mb_strlen(class_prefix($gradeNumber));
        $after  = mb_strlen(class_name($gradeNumber, '')) - $before;   // what comes after the section, if anything

        return mb_substr($name, $before, mb_strlen($name) - $before - $after);
    }
}

// ordinal(17) → "17th", ordinal(22) → "22nd" (11th, 12th, 13th are the exceptions to the last-digit rule)
if (!function_exists('ordinal'))
{
    function ordinal(int $number): string
    {
        $suffix = in_array($number % 100, [11, 12, 13], true) ? 'th' : (['th', 'st', 'nd', 'rd'][$number % 10] ?? 'th');

        return $number . $suffix;
    }
}

// initials('Alex Benjamin') → "AB" — the first letter of the first two words (the avatar circles, avatar.css)
if (!function_exists('initials'))
{
    function initials(string $name): string
    {
        return implode('', array_map(fn (string $word): string => mb_substr($word, 0, 1), array_slice(explode(' ', trim($name)), 0, 2)));
    }
}

// word_list(['PDF', 'PNG', 'JPG']) → "PDF, PNG or JPG" (a file box's hint, an upload's refusal); one word → that word
if (!function_exists('word_list'))
{
    function word_list(array $words): string
    {
        $words = array_values($words);
        $last  = array_pop($words);

        return $words === [] ? (string) $last : implode(', ', $words) . " or {$last}";
    }
}

if (!function_exists('icon'))
{
    // One icon from components/icons.php: icon('plus', 16) or icon('chevronDown', 14, 'c-dropdown__chevron')
    function icon(string $name, int $size = 16, string $class = ''): string
    {
        return '<svg class="' . e(trim("c-icon {$class}")) . '" width="' . $size . '" height="' . $size . '" aria-hidden="true">'
            . '<use href="#icon-' . e($name) . '"/></svg>';
    }
}

if (!function_exists('icon_image'))
{
    // The same icon as a picture in one colour token, for a PDF: Dompdf can't read the icon sheet (<use>) or colour an
    // icon from CSS. icon_image('graduationCap', 17, 'terracotta') → <img> with the drawing from icons.php in that
    // colour (global.css's --terracotta). Used by the certificate's PDF (components/_certificate_sheet.php).
    function icon_image(string $name, int $size, string $colourToken): string
    {
        static $icons = null, $tokens = null;
        $icons  ??= file_get_contents(dirname(__DIR__) . '/app/Views/components/icons.php');
        $tokens ??= file_get_contents(dirname(__DIR__) . '/public/assets/css/global.css');

        preg_match('/<symbol id="icon-' . preg_quote($name, '/') . '" viewBox="([^"]+)">(.*?)<\/symbol>/s', $icons, $drawing);
        preg_match('/--' . preg_quote($colourToken, '/') . ':\s*(#[0-9A-Fa-f]{3,8})/', $tokens, $colour);

        // Dompdf draws an SVG 4/3 too big for its picture (px read as points) unless the SVG's own size is 4/3 of it
        // (measured: a 22.67 SVG fills a 17px picture exactly, a 17 one spills 4px over the words)
        $drawn = round($size * 4 / 3, 2);
        $svg   = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $drawn . '" height="' . $drawn . '" viewBox="' . $drawing[1] . '" fill="none"'
            . ' stroke="' . $colour[1] . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $drawing[2] . '</svg>';

        return '<img src="data:image/svg+xml;base64,' . base64_encode($svg) . '" width="' . $size . '" height="' . $size . '" alt="">';
    }
}
