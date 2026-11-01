<?php

declare(strict_types=1);

namespace Core;

use InvalidArgumentException;

// Checks form values against rules written as text: ['title' => 'required|max:150'].
// To add a new kind of rule, add one line to problem() below.
final class Validator
{
    // Returns ['field' => 'message'] for every field that fails (empty array = everything is fine)
    public static function check(array $data, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleList)
        {
            $value = trim((string) ($data[$field] ?? ''));
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            foreach (explode('|', $ruleList) as $rule)
            {
                [$name, $option] = array_pad(explode(':', $rule, 2), 2, null);

                // An empty optional field skips its other rules ("max:20" doesn't apply to nothing)
                if ($value === '' && $name !== 'required')
                {
                    continue;
                }

                $problem = self::problem($name, $option, $value, $label);

                if ($problem !== null)
                {
                    $errors[$field] = $problem;   // first problem per field is enough
                    break;
                }
            }
        }

        return $errors;
    }

    // One rule → an error message, or null if the value passes
    private static function problem(string $rule, ?string $option, string $value, string $label): ?string
    {
        return match ($rule)
        {
            'required' => $value === '' ? "{$label} is required." : null,
            'min'      => mb_strlen($value) >= (int) $option ? null : "{$label} must be at least {$option} characters.",
            'max'      => mb_strlen($value) <= (int) $option ? null : "{$label} must be {$option} characters or fewer.",
            'in'       => in_array($value, explode(',', (string) $option), true) ? null : "{$label} must be one of the choices.",
            'number'   => ctype_digit($value) ? null : "{$label} must be a whole number.",
            'between'  => self::isBetween($value, (string) $option) ? null : "{$label} must be between " . str_replace(',', ' and ', (string) $option) . '.',
            'date'     => self::isDate($value, 'Y-m-d') ? null : "{$label} must be a real date.",
            'time'     => self::isDate($value, 'H:i') ? null : "{$label} must be a real time.",
            'name'     => preg_match("/^[\p{L} .'-]+$/u", $value) ? null : "{$label} can only contain letters, spaces, dots, hyphens and apostrophes.",
            'phone'    => self::isPhone($value) ? null : "{$label} must be a " . country('phone.digits') . '-digit number (you can start with ' . country('phone.code') . ').',
            'email'    => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "{$label} must be a valid email address.",
            // the country's ID card number (country.php 'national_id') · a passport number (country.php 'passport_pattern')
            'national_id' => preg_match(country('national_id.pattern'), $value) ? null : "{$label} is not a valid " . country('national_id.label') . '.',
            'passport'    => preg_match(country('passport_pattern'), $value) ? null : "{$label} must be 6–20 letters and numbers.",
            default    => throw new InvalidArgumentException("Unknown validation rule: {$rule}"),
        };
    }

    // Spaces, dashes, a leading 0 and the country code (country.php 'phone') are allowed; the rest must be 'digits' long
    private static function isPhone(string $value): bool
    {
        $digits = preg_replace('/\D/', '', $value);
        $code   = preg_replace('/\D/', '', country('phone.code'));

        if (str_starts_with($digits, $code))
        {
            $digits = substr($digits, strlen($code));
        }

        return strlen(ltrim($digits, '0')) === (int) country('phone.digits');
    }

    // "2026-02-30" is not a real date, "2026-02-28" is (same idea for "25:00" as a time)
    private static function isDate(string $value, string $format): bool
    {
        $date = \DateTime::createFromFormat($format, $value);

        return $date !== false && $date->format($format) === $value;
    }

    // "between:15,40" → a whole number from 15 to 40
    private static function isBetween(string $value, string $range): bool
    {
        [$low, $high] = array_map('intval', explode(',', $range));

        return ctype_digit($value) && (int) $value >= $low && (int) $value <= $high;
    }
}
