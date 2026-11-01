<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClassModel;
use App\Models\PeopleModel;
use DateTime;

// The rules of a person's details, ONE place for People's Edit Profile (PeopleController::update()) and Add pages
// (PeopleAddController): each field's rules (config/people.php 'fields'), its dropdown's choices, and the checks the
// rules can't make alone (ages, dates in order, numbers and emails nobody else has…).
final class PersonFields
{
    // [field => rules] for these fields; $required: fields this form needs even where the field's rules don't
    public static function rules(array $fields, array $required = []): array
    {
        $rules = [];

        foreach ($fields as $field)
        {
            $own           = config('people.fields')[$field][1];
            $rules[$field] = in_array($field, $required, true) && !str_contains($own, 'required') ? rtrim("required|{$own}", '|') : $own;
        }

        return array_filter($rules, fn (string $rule): bool => $rule !== '');   // a photo has no text rules
    }

    // A dropdown's options: a 'choice' field's own 'in:' list ("Not given" first when it may stay empty — not when its
    // rules or this form ($required) need it), a 'class' field's classes (a full one greyed out, unless it is $ownClassId)
    public static function options(string $field, ?int $ownClassId = null, bool $required = false): array
    {
        [$box, $rules] = config('people.fields')[$field];
        $options       = [];

        if ($box === 'choice')
        {
            preg_match('/(?:^|\|)in:([^|]*)/', $rules, $list);
            $options = array_merge($required || str_contains($rules, 'required') ? [] : [['value' => '', 'label' => 'Not given']], explode(',', $list[1]));
        }

        if ($box === 'class')
        {
            foreach ((new ClassModel())->grades() as $grade)
            {
                foreach ($grade['classes'] as $class)
                {
                    $full      = $class['students'] >= $class['capacity'] && (int) $class['id'] !== $ownClassId;
                    $options[] = ['value' => (string) $class['id'], 'label' => $class['name'], 'sub' => $full ? 'Full' : '', 'disabled' => $full];
                }
            }
        }

        return $options;
    }

    // The ages a role's date of birth may give: ['min' => …, 'max' => … (optional)]
    public static function ages(string $role): array
    {
        return $role === 'student' ? config('white_label.academic.student_age') : config("people.ages.{$role}");
    }

    // The checks a field's rules can't make alone: an age that fits the role, dates in the right order, numbers
    // and emails nobody else has, staff need an ID card number and a parent an ID card or a passport. $id: the person
    // being edited (0 = someone new). A list of messages (empty = all fine).
    public static function problems(string $role, int $id, array $values, PeopleModel $people): array
    {
        $today    = date('Y-m-d');
        $value    = fn (string $field): string => $values[$field] ?? '';
        $birth    = $value('date_of_birth');
        $age      = $birth === '' ? null : (new DateTime($birth))->diff(new DateTime($today))->y;
        $ages     = self::ages($role);
        $idLabel  = country('national_id.label');
        $email    = strtolower($value('personal_email'));
        $problems = [];

        if ($birth !== '' && $birth >= $today)
        {
            $problems[] = 'Date of birth must be in the past.';
        }
        elseif ($age !== null && ($age < $ages['min'] || $age > ($ages['max'] ?? $age)))
        {
            $problems[] = 'Date of birth: the age must be ' . (isset($ages['max']) ? "{$ages['min']}–{$ages['max']}" : "at least {$ages['min']}") . '.';
        }

        if (in_array($role, config('people.national_id_required'), true) && $value('national_id') === '')
        {
            $problems[] = "{$idLabel} is required.";
        }

        if (in_array($role, config('people.national_id_or_passport'), true) && $value('national_id') === '' && $value('passport') === '')
        {
            $problems[] = "Please give a {$idLabel} or a passport number.";
        }

        if ($value('national_id') !== '' && $people->nationalIdTaken($value('national_id'), $id))
        {
            $problems[] = "That {$idLabel} is already registered to someone else.";
        }

        if ($value('birth_certificate') !== '' && $people->birthCertificateTaken($value('birth_certificate'), $id))
        {
            $problems[] = 'That birth certificate number is already registered.';
        }

        // A parent signs in with their email; staff get mail at theirs, so it can't be the school's own address
        if ($email !== '' && ($role === 'parent' ? $people->identifierTaken($email, $id) : $people->personalEmailTaken($email, $id)))
        {
            $problems[] = 'That email is already used by another account.';
        }

        if ($email !== '' && $role !== 'parent' && str_ends_with($email, '@' . school('email_domain')))
        {
            $problems[] = 'Please give a personal email, not one of the ' . strtolower(word('institution')) . "'s.";
        }

        if ($value('emergency_phone') !== '' && preg_replace('/\D/', '', $value('emergency_phone')) === preg_replace('/\D/', '', $value('phone')))
        {
            $problems[] = "The emergency contact must be someone else's number.";
        }

        if ($value('experience_years') !== '' && $age !== null && (int) $value('experience_years') > max(0, $age - 18))
        {
            $problems[] = 'Experience can be at most ' . max(0, $age - 18) . ' years at this age.';
        }

        if ($value('join_date') !== '' && $value('join_date') > date('Y-m-d', strtotime('+' . config('people.join_months_ahead') . ' months')))
        {
            $problems[] = 'Join date can be at most ' . config('people.join_months_ahead') . ' months ahead.';
        }

        if ($value('join_date') !== '' && $birth !== '' && $value('join_date') < date('Y-m-d', strtotime("{$birth} +18 years")))
        {
            $problems[] = 'Join date must be after their 18th birthday.';
        }

        if ($value('admission_date') !== '' && ($value('admission_date') > $today || $value('admission_date') < $birth))
        {
            $problems[] = 'Admission date must be between the date of birth and today.';
        }

        if ($value('class_id') !== '' && (new ClassModel())->find((int) $value('class_id')) === null)
        {
            $problems[] = 'Please choose a ' . strtolower(word('class')) . ' from the list.';
        }

        return $problems;
    }

    // A teacher's qualifications as typed (three lists, one row each): [the rows (empty ones left out), a problem or null]
    public static function qualifications(array $degrees, array $institutions, array $years): array
    {
        $rows = [];

        foreach ($degrees as $index => $degree)
        {
            $row = ['degree' => trim($degree), 'institution' => trim($institutions[$index] ?? ''), 'year_obtained' => trim($years[$index] ?? '')];

            if ($row['degree'] === '' && $row['institution'] === '' && $row['year_obtained'] === '')
            {
                continue;
            }

            if ($row['degree'] === '' || mb_strlen($row['degree']) > 150 || mb_strlen($row['institution']) > 150)
            {
                return [[], 'Each qualification needs its name (150 characters at most), and the institution 150 at most.'];
            }

            if ($row['year_obtained'] !== '' && (!ctype_digit($row['year_obtained']) || (int) $row['year_obtained'] < 1950 || (int) $row['year_obtained'] > (int) date('Y')))
            {
                return [[], 'A qualification year must be between 1950 and ' . date('Y') . '.'];
            }

            $rows[] = $row;
        }

        return [$rows, null];
    }
}
