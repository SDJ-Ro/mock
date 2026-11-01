<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PeopleModel;

// Every detail a profile card can show about one person (PeopleModel::find()), worked out once and written the way
// people read it: dates in the country's format, names joined, the school's words. A card points at one of these keys
// (config/profile.php 'layout' for My Profile, config/people.php 'profile' for the People pop-up).
final class PersonDetails
{
    public static function values(array $person): array
    {
        $date     = fn (?string $value): string => $value ? date(country('date_format'), strtotime($value)) : '';
        $role     = $person['role'];
        $guardian = $person['parents'][0] ?? null;   // the primary one
        $login    = $person['last_login_at'];

        $values = [
            'identifier'        => $person['identifier'],
            'code'              => $person['code'] ?: $person['identifier'],
            'full_name'         => $person['full_name'],
            'personal_email'    => $person['personal_email'],
            'phone'             => $person['phone'],
            'address'           => $person['address'],
            'emergency_name'    => $person['emergency_name'],
            'emergency_phone'   => $person['emergency_phone'],
            'gender'            => $person['gender'],
            'national_id'       => $person['national_id'],
            'dob'               => $date($person['date_of_birth']),
            'join'              => $date($person['join_date'] ?? $person['created_at']),
            'created'           => $date($person['created_at']),
            'last_login'        => $login === null ? '' : (substr($login, 0, 10) === date('Y-m-d') ? 'Today, ' . date('H:i', strtotime($login)) : $date($login)),
            'portal'            => word($role) . ' Portal',
            'role_word'         => word($role),
            'job_title'         => $person['job_title'],
            'subject'           => trim(explode(',', (string) $person['subjects'])[0]),
            'subjects'          => $person['subjects'],
            'experience'        => $person['experience_years'] === null ? '' : (string) $person['experience_years'],
            'office_address'    => $person['office_address'],
            'relationship'      => $person['relationship'],
            'occupation'        => $person['occupation'],
            'employer'          => $person['employer'],
            'passport'          => $person['passport'],
            'home_phone'        => $person['home_phone'],
            'office_phone'      => $person['office_phone'],
            'birth_certificate' => $person['birth_certificate'],
            'blood_group'       => $person['blood_group'],
            'religion'          => $person['religion'],
            'nationality'       => $person['nationality'],
            'address_area'      => $person['address_area'],
            'address_district'  => $person['address_district'],
            'address_region'    => $person['address_region'],
            'previous_school'   => $person['previous_school'],
            'admission'         => $date($person['admission_date']),
            'medical_notes'     => $person['medical_notes'],
            'grade'             => $person['grade_number'] === null ? '' : grade_name((int) $person['grade_number']),
            'class'             => $person['class_name'],
            'class_teacher'     => $person['class_teacher'],
            'guardian'          => $guardian['full_name'] ?? '',
            'guardian_phone'    => $guardian['phone'] ?? '',
            'guardian_email'    => $guardian['identifier'] ?? '',   // parents sign in with their email
            'children'          => implode(', ', array_map(fn (array $child): string => "{$child['full_name']} (" . ($child['class_name'] ?? '—') . ')', $person['children'])),
        ];

        if ($role === 'teacher')
        {
            $teaching  = (new PeopleModel())->teaching($person['id']);
            $runs      = (new PeopleModel())->extracurriculars($person);
            $values   += [
                'homeroom'       => $teaching['homeroom'] ? word('class') . " {$teaching['homeroom']} (" . words('{Class} {Teacher}') . ')' : '',
                'classes_taught' => implode(', ', $teaching['classes']),
                'in_charge'      => implode(', ', [...$runs['club'], ...$runs['sport']]),
            ];
        }

        if ($role === 'student')
        {
            $member  = (new PeopleModel())->extracurriculars($person);
            $values += ['sports' => implode(', ', $member['sport']), 'clubs' => implode(', ', $member['club'])];
        }

        return $values;
    }
}
