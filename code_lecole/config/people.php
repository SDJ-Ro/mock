<?php

declare(strict_types=1);

// People (the users directory): the choices and limits its forms and checks use. Read by PeopleController / PeopleModel.
// Who may do what is in config/permissions.php ('people'); a country's lists (religions, address parts) in
// config/white_label/country.php; a student's age range and the ID formats in config/white_label/academic.php.
$nationalId = country('national_id.label');   // the country's name for the ID card
$area       = country('address_parts');       // the three address parts' names
$relations  = ['Father', 'Mother', 'Guardian'];

return [
    // The profile pop-up's information, per role: [title, icon, columns, the cards].
    // A card: [label, the detail it shows (App\Services\PersonDetails::values()), 'wide' (a whole row) or '',
    //          what Edit Profile changes there: a field of 'fields' below, or 'first_name,last_name' (two boxes)].
    // No field = shown, not edited here (codes, sign-in emails, class teacher: other pages decide those).
    // Instead of cards a section may show a list: 'subjects' (what a teacher teaches), 'qualifications',
    // 'children' (a parent's), 'parents' (a student's; Edit Profile picks another). Labels may use {Student}… (words()).
    'profile' => [
        'student'    => [
            ['Account', 'usersRound', 2, [['Reg. Number', 'code'], ['{Parent} Email', 'guardian_email'], ['Full Name', 'full_name', 'wide', 'first_name,last_name']]],
            ['{Student} Information', 'user', 4, [['{Grade}', 'grade'], ['{Class} / Section', 'class', '', 'class_id'],
                ['Birth Certificate No.', 'birth_certificate', '', 'birth_certificate'], ['Date of Birth', 'dob', '', 'date_of_birth'],
                ['Gender', 'gender', '', 'gender'], ['Admission Date', 'admission', '', 'admission_date'], ['Blood Group', 'blood_group', '', 'blood_group'],
                ['Nationality', 'nationality', '', 'nationality'], ['Religion', 'religion', '', 'religion'], ['Previous {Institution}', 'previous_school', '', 'previous_school']]],
            ['Residential Details', 'mapPin', 2, [['Residential Address', 'address', 'wide', 'address'], [$area['area']['label'], 'address_area', '', 'address_area'],
                [$area['district']['label'], 'address_district', '', 'address_district'], [$area['region']['label'], 'address_region', '', 'address_region']]],
            ['Medical Notes', 'heart', 2, [['Medical Conditions / Notes', 'medical_notes', 'wide', 'medical_notes']]],
            ['Connected {Parent} / Guardian Account', 'userCheck', 2, 'parents'],
        ],
        'teacher'    => [
            ['Contact & Account', 'mail', 2, [['Reg. Number', 'code'], ['{Institution} Mail', 'identifier'], ['Personal Mail', 'personal_email', '', 'personal_email'],
                ['Mobile Number', 'phone', '', 'phone']]],
            ['{Class} {Teacher} Responsibility', 'graduationCap', 2, [['{Class} {Teacher} Role', 'homeroom'], ['Leadership Designation', 'job_title', '', 'job_title']]],
            ['Extracurricular Assignment ({Teacher} in Charge)', 'trophy', 2, [['Assigned Programmes', 'in_charge', 'wide']]],
            ['Personal & Employment', 'briefcase', 2, [['Full Name', 'full_name', '', 'first_name,last_name'], [$nationalId, 'national_id', '', 'national_id'],
                ['Date of Birth', 'dob', '', 'date_of_birth'], ['Experience (Years)', 'experience', '', 'experience_years'], ['Join Date', 'join', '', 'join_date'],
                ['Office Address', 'office_address', '', 'office_address']]],
            ['Emergency Contact Details', 'phone', 2, [['Emergency Contact Name', 'emergency_name', '', 'emergency_name'],
                ['Emergency Contact Number', 'emergency_phone', '', 'emergency_phone']]],
            ['{Subject} Teaching Assignments', 'bookOpen', 2, 'subjects'],
            ['Qualifications & Experience', 'award', 2, 'qualifications'],
        ],
        'parent'     => [
            ['Contact & Account', 'mail', 2, [['Reg. Number', 'code'], ['Email', 'identifier', '', 'personal_email'], ['Mobile Number', 'phone', '', 'phone'],
                ['Home Number', 'home_phone', '', 'home_phone']]],
            ['Personal Details', 'user', 2, [['Full Name', 'full_name', '', 'first_name,last_name'], ['Relationship', 'relationship', '', 'relationship'],
                [$nationalId, 'national_id', '', 'national_id'], ['Passport', 'passport', '', 'passport'], ['Date of Birth', 'dob', '', 'date_of_birth'],
                ['Emergency Name', 'emergency_name', '', 'emergency_name'], ['Emergency Contact', 'emergency_phone', '', 'emergency_phone']]],
            ['Work & Home', 'briefcase', 2, [['Occupation', 'occupation', '', 'occupation'], ['Employer / Workplace', 'employer', '', 'employer'],
                ['Office Contact Number', 'office_phone', '', 'office_phone'], ['Office Address', 'office_address', '', 'office_address'],
                ['Residential Address', 'address', 'wide', 'address']]],
            ['Linked {Student} Accounts', 'usersRound', 2, 'children'],
        ],
        'management' => [
            ['Contact & Account', 'mail', 2, [['Reg. Number', 'code'], ['{Institution} Mail', 'identifier'], ['Mobile Number', 'phone', '', 'phone']]],
            ['Employment', 'briefcase', 2, [['Job Title', 'job_title', '', 'job_title'], ['Joining Date', 'join', '', 'join_date'], ['Office Address', 'office_address', '', 'office_address']]],
            ['Personal Information', 'user', 2, [['Full Name', 'full_name', '', 'first_name,last_name'], [$nationalId, 'national_id', '', 'national_id'],
                ['Date of Birth', 'dob', '', 'date_of_birth'], ['Personal Email', 'personal_email', '', 'personal_email'], ['Residential Address', 'address', 'wide', 'address']]],
            ['Emergency Contact', 'phone', 2, [['Emergency Name', 'emergency_name', '', 'emergency_name'], ['Emergency Contact', 'emergency_phone', '', 'emergency_phone']]],
        ],
    ],

    // A student's profile for who needs only part of it: what it leaves out — the cards (by the detail they show, or a
    // list's name) and the tabs. A section left with no cards goes too.
    'partial' => [
        // a teacher who teaches the student a subject but is not their class teacher: no medical notes
        'subject_teacher' => ['cards' => ['medical_notes'], 'tabs' => []],
        // the teacher in charge of a club / sport the student is in (Teams & Roster): what they need to look after them
        'club'            => ['cards' => ['birth_certificate', 'admission', 'nationality', 'religion', 'previous_school', 'address', 'address_area',
                                          'address_district', 'address_region'], 'tabs' => ['academics']],
    ],

    // Every field the office can change, ONE list for Edit Profile (stage D) and the add forms (stage E):
    // [its box (components/_field_box.php): 'text' · 'area' (several lines on the Add page) · 'phone' (the country's
    //  code in front on the Add page) · 'date' (the date picker) · 'choice' (a dropdown of its 'in:' list) · 'class'
    //  (a dropdown of the classes) · 'photo' (a picture file, no rules), its rules (core/Validator.php)].
    // Which table it lives in: PeopleModel::PROFILE_COLUMNS. Checks that need the database or other fields (ages,
    // numbers already used…): PeopleController::problems().
    'fields' => [
        'first_name'        => ['text', 'required|name|max:75'],
        'last_name'         => ['text', 'required|name|max:75'],
        'date_of_birth'     => ['date', 'required|date'],
        'gender'            => ['choice', 'required|in:Female,Male,Prefer not to say'],
        'national_id'       => ['text', 'national_id|max:30'],
        'personal_email'    => ['text', 'required|email|max:191'],
        'phone'             => ['phone', 'required|phone'],
        'address'           => ['area', 'required|min:6|max:300'],
        'emergency_name'    => ['text', 'name|max:150'],
        'emergency_phone'   => ['phone', 'phone'],
        'class_id'          => ['class', 'required|number'],
        'birth_certificate' => ['text', 'required|min:3|max:25'],
        'admission_date'    => ['date', 'required|date'],
        'previous_school'   => ['text', 'max:150'],
        'blood_group'       => ['choice', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
        'medical_notes'     => ['area', 'max:1000'],
        'photo'             => ['photo', ''],
        'religion'          => ['choice', 'in:' . implode(',', country('religions'))],
        'nationality'       => ['text', 'name|max:50'],
        'address_area'      => ['choice', 'in:' . implode(',', $area['area']['choices'])],
        'address_district'  => ['choice', 'in:' . implode(',', $area['district']['choices'])],
        'address_region'    => ['choice', 'in:' . implode(',', $area['region']['choices'])],
        'relationship'      => ['choice', 'required|in:' . implode(',', $relations)],
        'occupation'        => ['text', 'max:100'],
        'employer'          => ['text', 'max:150'],
        'passport'          => ['text', 'passport'],
        'home_phone'        => ['text', 'phone'],
        'office_phone'      => ['text', 'phone'],
        'office_address'    => ['text', 'max:300'],
        'job_title'         => ['text', 'max:100'],
        'subjects'          => ['text', 'max:255'],   // the subjects a teacher may teach ("Science, Physics")
        'join_date'         => ['date', 'date'],
        'experience_years'  => ['text', 'number|between:0,50'],
    ],

    // The Add page's form per role (_add_person_page.php): [section title, its boxes]. A box: [field of 'fields'
    // above, label, flags: 'wide' (a whole row), 'required' (needed here even if Edit may leave it empty),
    // 'optional' (no * although its rules need it: the controller fills it in)].
    // Instead of boxes: 'qualifications' (a teacher's list), 'guardian' (a student's parent: an existing one or a new
    // one, the boxes of 'guardian' below). A parent is added with their child, so 'parent' has no form.
    'add' => [
        'student'    => [
            ['{Student} Personal Data', [['first_name', 'First Name'], ['last_name', 'Last Name'], ['date_of_birth', 'Date of Birth'],
                ['gender', 'Gender'], ['birth_certificate', 'Birth Certificate No.'], ['class_id', '{Class} / Section'],
                ['address', 'Residential Address', 'wide'], ['admission_date', 'Admission Date'], ['nationality', 'Nationality', 'required'],
                ['address_area', $area['area']['label'], 'required'], ['address_district', $area['district']['label'], 'required'],
                ['address_region', $area['region']['label'], 'required'], ['blood_group', 'Blood Group', 'required'], ['religion', 'Religion'],
                ['photo', 'Profile Photo'], ['medical_notes', 'Medical Notes / Allergies', 'wide']]],
            ['{Parent} / Legal Guardian Information', 'guardian'],
        ],
        'teacher'    => [
            ['Personal Information', [['first_name', 'First Name'], ['last_name', 'Last Name'], ['national_id', $nationalId, 'required'], ['date_of_birth', 'Date of Birth'],
                ['phone', 'Mobile Number'], ['personal_email', 'Personal Email'], ['address', 'Residential Address', 'wide']]],
            ['Professional Details', [['subjects', '{Subjects} Qualified to Teach', 'wide required'], ['experience_years', 'Years of Experience', 'required'],
                ['join_date', 'Join Date', 'required']]],
            ['Qualifications & Experience', 'qualifications'],
            ['Emergency Contact', [['emergency_name', 'Contact Name', 'required'], ['emergency_phone', 'Contact Mobile Number', 'required']]],
        ],
        'management' => [
            ['Personal Information', [['first_name', 'First Name'], ['last_name', 'Last Name'], ['national_id', $nationalId, 'required'], ['date_of_birth', 'Date of Birth'],
                ['phone', 'Contact Number'], ['personal_email', 'Personal Email'], ['address', 'Residential Address', 'wide']]],
            ['Employment Details', [['job_title', 'Job Title'], ['join_date', 'Join Date', 'required']]],
            ['Emergency Contact', [['emergency_name', 'Contact Name', 'required'], ['emergency_phone', 'Contact Number', 'required']]],
        ],
    ],

    // A new student's new parent (the Add Student form's guardian part): the same kind of boxes; their names in the
    // form start with "guardian_". The ID card number or passport is one choice above these.
    'guardian' => [['relationship', 'Relationship to {Student}'], ['first_name', 'First Name'], ['last_name', 'Last Name'],
        ['date_of_birth', 'Date of Birth'], ['occupation', 'Occupation', 'required'], ['employer', 'Employer / Organisation'],
        ['phone', 'Primary Mobile Phone'], ['personal_email', 'Personal Email (their sign-in)'],
        ['address', 'Home / Residential Address (empty = the {student}’s)', 'wide optional'], ['office_phone', 'Office Phone'], ['home_phone', 'Home Landline'],
        ['office_address', 'Office Address'], ['emergency_name', 'Emergency Contact Name', 'required'],
        ['emergency_phone', 'Emergency Contact Phone', 'required']],

    // Each role's colour: its directory tab, toolbar, profile pop-up header and add form
    'tones' => [
        'student'    => 'sky',
        'teacher'    => 'sunshine',
        'parent'     => 'terracotta',
        'management' => 'maroon',
    ],

    'per_page' => 25,   // rows on one page of the directory

    'relationships' => $relations,   // the parents tab's filter (and the 'relationship' field above)

    // Ages that make sense: a parent from 15; teachers 21–65; management 21–70
    'ages' => [
        'parent'     => ['min' => 15],
        'teacher'    => ['min' => 21, 'max' => 29],
        'management' => ['min' => 21, 'max' => 70],
    ],

    // Who must give their national ID, and who may give a passport number instead
    'national_id_required'    => ['teacher', 'management'],
    'national_id_or_passport' => ['parent'],

    // A new staff member's join date: at most this far ahead (in months), and never before they were 18
    'join_months_ahead' => 3,
];
