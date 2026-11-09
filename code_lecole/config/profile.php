<?php

declare(strict_types=1);

// My Profile (every role): what each role's page shows and which details a person may change themselves.
// Read in ProfileController.

$nationalId = country('national_id.label');   // the country's name for the ID card (config/white_label/country.php)
$area       = country('address_parts');       // the names of the three address parts a student's address has

return [
    // The details a person may change themselves (the profiles columns; UserModel::updateProfile() only accepts these).
    // Names, birthday, ID numbers and class are changed by the school office on People, so records stay correct.
    // Students change nothing: their page has no Edit Profile button and no password part.
    'editable' => [
        'student'    => [],
        'parent'     => ['phone', 'address', 'emergency_name', 'emergency_phone'],   // their email is their sign-in ID
        'teacher'    => ['phone', 'address', 'emergency_name', 'emergency_phone'],
        'management' => ['phone', 'address', 'emergency_name', 'emergency_phone'],
        'admin'      => ['phone', 'address', 'emergency_name', 'emergency_phone'],
    ],

    // The rules for those details (core/Validator.php) and the names used in error messages
    'rules' => [
        'phone'           => 'required|phone',
        'address'         => 'required|min:6|max:300',
        'emergency_name'  => 'required|name|max:150',
        'emergency_phone' => 'required|phone',
    ],

    'photo_kb' => 1024,   // a profile photo (My Profile, and a new student's on People's Add page): at most 1 MB

    'labels' => [
        'phone'           => 'Phone',
        'address'         => 'Home address',
        'emergency_name'  => 'Emergency contact',
        'emergency_phone' => 'Emergency phone',
    ],

    // The line under "My Profile"
    'subtitles' => [
        'admin'      => 'Manage your account information and credentials.',
        'management' => 'Leadership credentials and institutional administration.',
        'teacher'    => 'Manage your staff profile and teaching assignments.',
        'parent'     => 'View and update your parent account details.',
        'student'    => 'Your personal and academic profile information.',
    ],

    // Each role's page: sections of cards [label, icon, where the value comes from, 'wide' = a full row].
    // {Class}, {Student}, {Institution}… in a label or title become the school's words (words(), in ProfileController).
    // "Where from" is a value App\Services\PersonDetails::values() works out (phone, dob, children…): every card shows a
    // stored value, never an example.
    // 'role' = the tinted section [title, icon, tint colour, cards] (management has none).
    'layout' => [
        'admin' => [
            'contact'  => [['Administrator Email', 'mail', 'identifier'], ['Primary Mobile', 'phone', 'phone']],
            'personal' => [['Full Name', 'user', 'full_name'], ['Gender', 'user', 'gender'], ["{$nationalId} Number", 'lockKeyhole', 'national_id'],
                           ['Date of Birth', 'calendar', 'dob'], ['Joined Date', 'calendar', 'join']],
            'role'     => null,
            'extra'    => [
                ['Residential & Emergency Contact', 'mapPin', [['Home Address', 'mapPin', 'address', 'wide'], ['Emergency Contact', 'phone', 'emergency_name'],
                    ['Emergency Phone', 'phone', 'emergency_phone']]],
            ],
            'account'  => [['Administrator ID', 'lockKeyhole', 'code'], ['Portal Role', 'shieldCheck', 'role_word'], ['Account Created', 'calendar', 'created'],
                           ['Last Sign-In', 'clock', 'last_login']],
        ],
        'management' => [
            'contact'  => [['Institutional Email', 'mail', 'identifier'], ['Direct Mobile', 'phone', 'phone'], ['Office Line', 'phone', 'office_phone']],
            'personal' => [['Full Name', 'user', 'full_name'], ['Designation', 'briefcase', 'job_title'], ['Gender', 'user', 'gender'],
                           ["{$nationalId} Number", 'lockKeyhole', 'national_id'], ['Date of Birth', 'calendar', 'dob'], ['Join Date', 'calendar', 'join'],
                           ['Office Location', 'building2', 'office_address']],
            'role'     => null,
            'extra'    => [
                ['Emergency Contacts & Address', 'mapPin', [['Home Address', 'mapPin', 'address', 'wide'], ['Emergency Contact', 'phone', 'emergency_name'],
                    ['Emergency Phone', 'phone', 'emergency_phone']]],
            ],
            'account'  => [['Staff ID', 'lockKeyhole', 'code'], ['Portal Access', 'shieldCheck', 'portal'], ['Account Created', 'calendar', 'created'],
                           ['Last Sign-In', 'clock', 'last_login']],
        ],
        'teacher' => [
            'contact'  => [['{Institution} Email', 'mail', 'identifier'], ['Mobile Phone', 'phone', 'phone'], ['Department Extension', 'phone', 'office_phone']],
            'personal' => [['Full Name', 'user', 'full_name'], ['Date of Birth', 'calendar', 'dob'], ["{$nationalId} / Passport", 'lockKeyhole', 'national_id'],
                           ['Gender', 'user', 'gender'], ['Joined Date', 'calendar', 'join'], ['Qualifications', 'award', 'qualifications']],
            'role'     => ['Teaching Assignments & {Institution} Roles', 'bookOpen', 'sunshine', [['Primary Subject', 'bookOpen', 'subject'],
                           ['Homeroom {Class}', 'graduationCap', 'homeroom'], ['{Classes} Taught', 'usersRound', 'classes_taught'],
                           ['{Teacher}-in-Charge', 'extracurricular', 'in_charge'], ['Staff Room Desk', 'building2', 'office_address'], ['Employment Status', 'shieldCheck', 'employment_status']]],
            'extra'    => [
                ['Residential & Emergency Contact', 'mapPin', [['Home Address', 'mapPin', 'address', 'wide'], ['Emergency Contact', 'phone', 'emergency_name'],
                    ['Emergency Phone', 'phone', 'emergency_phone']]],
            ],
            'account'  => [['Staff ID', 'lockKeyhole', 'code'], ['Portal Access', 'shieldCheck', 'portal'], ['Account Created', 'calendar', 'created'],
                           ['Last Sign-In', 'clock', 'last_login']],
        ],
        'parent' => [
            'contact'  => [['Personal Email', 'mail', 'identifier'], ['Primary Mobile', 'phone', 'phone'], ['Alternative Phone', 'phone', 'home_phone']],
            'personal' => [['Full Name', 'user', 'full_name'], ['Gender', 'user', 'gender'], ['Relationship', 'heart', 'relationship'],
                           ['Date of Birth', 'calendar', 'dob'], ["{$nationalId} Number", 'lockKeyhole', 'national_id'], ['Occupation', 'briefcase', 'occupation'],
                           ['Employer / Business', 'building2', 'employer']],
            'role'     => ['Linked Children', 'heartHandshake', 'terracotta', [['Linked Children', 'graduationCap', 'children']]],
            'extra'    => [
                ['Residential & Family Information', 'mapPin', [['Home Address', 'mapPin', 'address', 'wide'], ['Emergency Contact', 'phone', 'emergency_name'],
                    ['Emergency Phone', 'phone', 'emergency_phone']]],
            ],
            'account'  => [['{Parent} ID', 'lockKeyhole', 'code'], ['Portal Access', 'shieldCheck', 'portal'], ['Registration Date', 'calendar', 'created'],
                           ['Last Sign-In', 'clock', 'last_login']],
        ],
        'student' => [
            'contact'  => [['{Parent} Email', 'mail', 'guardian_email'], ['{Parent} Contact', 'phone', 'guardian_phone']],
            'personal' => [['Full Name', 'user', 'full_name'], ['Date of Birth', 'calendar', 'dob'], ['Gender', 'user', 'gender'],
                           ['Birth Certificate No', 'award', 'birth_certificate'], ['Blood Group', 'heart', 'blood_group'], ['Nationality', 'mapPin', 'nationality'],
                           ['Religion', 'shield', 'religion'], ['Previous {Institution}', 'bookOpen', 'previous_school']],
            'role'     => ['Academic & Extracurricular Information', 'graduationCap', 'sky', [['Current {Class}', 'graduationCap', 'class'],
                           ['{Class} {Teacher}', 'user', 'class_teacher'], ['House', 'shield', 'house'], ['Primary Sport', 'extracurricular', 'sports'],
                           ['Clubs & Societies', 'usersRound', 'clubs'], ['Prefect Role', 'shieldCheck', 'prefect_role'], ['Admission Date', 'calendar', 'admission']]],
            'extra'    => [
                ['Residential & Regional Details', 'mapPin', [['Residential Address', 'mapPin', 'address', 'wide'], [$area['area']['label'], 'building2', 'address_area'],
                    [$area['district']['label'], 'mapPin', 'address_district'], [$area['region']['label'], 'mapPin', 'address_region']]],
                ['Guardian & Medical Notes', 'heartHandshake', [['Primary Guardian', 'heartHandshake', 'guardian'], ['Guardian Phone', 'phone', 'guardian_phone'],
                    ['Guardian Email', 'mail', 'guardian_email'], ['Medical Notes / Health', 'heart', 'medical_notes', 'wide']]],
            ],
            'account'  => [['{Student} ID', 'lockKeyhole', 'identifier'], ['Portal Role', 'graduationCap', 'role_word'], ['Academic Year', 'calendar', 'school_year'],
                           ['Last Portal Sign-In', 'clock', 'last_login']],
        ],
    ],
];
