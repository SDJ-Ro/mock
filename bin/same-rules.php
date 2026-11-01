<?php

// Used by bin/check.sh (section 7): finds CSS rules that repeat each other — the same 4 or more lines (in any order)
// under different names, e.g. .c-grade-card and .c-section-box both typing the white card. One of them should use
// the other's class (or a shared one) instead.
// Rules that only arrange (a flex / grid row: display, gap, align…) may repeat: each component keeps its own row
// (owner, 2026-10-28) — only a repeated LOOK (colours, fonts, borders, sizes) is reported.
//     php bin/same-rules.php file.css other.css …   → one line per group of repeats

declare(strict_types=1);

const ARRANGING = '/^(display|gap|flex|flex-direction|flex-wrap|align-items|justify-content|grid-template-columns|min-width|margin-top|margin-bottom):/';

$groups = [];   // [the lines, sorted → ["file: selector", …]]

foreach (array_slice($argv, 1) as $file)
{
    $css = preg_replace('#/\*.*?\*/#s', '', (string) @file_get_contents($file));
    preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);

    foreach ($rules as [, $selector, $body])
    {
        $lines = array_filter(array_map('trim', explode(';', $body)), fn (string $line): bool => str_contains($line, ':'));

        $onlyArranging = $lines === preg_grep(ARRANGING, $lines);

        if (count($lines) >= 4 && !$onlyArranging)
        {
            sort($lines);
            $groups[implode('; ', $lines)][] = $file . ': ' . trim(preg_replace('/\s+/', ' ', $selector));
        }
    }
}

foreach ($groups as $lines => $where)
{
    if (count($where) > 1)
    {
        echo '  ! the same ' . (substr_count($lines, ';') + 1) . ' lines: ' . implode('  =  ', $where) . "\n";
    }
}
