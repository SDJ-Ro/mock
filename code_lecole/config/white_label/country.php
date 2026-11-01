<?php

declare(strict_types=1);

return [
    'name'        => 'Sri Lanka',
    'timezone'    => 'Asia/Colombo',
    'language'    => 'en-US',         // <html lang> (screen readers, spelling)

    // How dates are written (PHP's date() letters). PHP uses them, and gives them to the scripts (layouts/page_top.php)
    'date_format'      => 'M d, Y',      // Nov 18, 2023: cards, the date picker
    'day_format'       => 'F j',         // October 19: a day in the calendar's month
    'short_day_format' => 'M j',         // Oct 19: a notice's "Expires Oct 19" tag
    'full_date_format' => 'F j, Y',      // October 19, 2026: the calendar's "Add event" pop-up
    'long_date_format' => 'l, F j, Y',   // Monday, October 19, 2026: the calendar's "View all"
    'month_format'     => 'M Y',         // Aug 2025: an achievement on the timeline
    'currency'    => 'LKR',

    'phone' => [
        'code'   => '+94',
        'digits' => 9,
    ],

    'national_id' => [
        'label'   => 'NIC',
        'pattern' => '/^([0-9]{9}[VvXx]|[0-9]{12})$/',
    ],

    // For someone with no national ID (a parent): letters and numbers
    'passport_pattern' => '/^[A-Za-z0-9]{6,20}$/',

    // People: what a new student's nationality starts as, and the religions to choose from
    // An achievement's level (scope), smallest first: what a club's Add / Edit Achievement offers
    'achievement_levels' => ['School', 'Inter-School', 'Zonal', 'District', 'Provincial', 'National', 'All-Island', 'International'],

    'nationality' => 'Sri Lankan',
    'religions'   => ['Prefer not to say', 'Buddhism', 'Catholic', 'Hinduism', 'Islam', 'Other'],

    // The three parts of a student's address the school keeps (student_details.address_area / _district / _region):
    // their names here, and the choices for each
    'address_parts' => [
        'area'     => ['label' => 'Educational Zone', 'choices' => ['Colombo', 'Kandy', 'Galle', 'Gampaha', 'Kurunegala', 'Jaffna', 'Matara']],
        'district' => ['label' => 'District', 'choices' => ['Ampara', 'Anuradhapura', 'Badulla', 'Batticaloa', 'Colombo', 'Galle', 'Gampaha',
            'Hambantota', 'Jaffna', 'Kalutara', 'Kandy', 'Kegalle', 'Kilinochchi', 'Kurunegala', 'Mannar', 'Matale', 'Matara',
            'Monaragala', 'Mullaitivu', 'Nuwara Eliya', 'Polonnaruwa', 'Puttalam', 'Ratnapura', 'Trincomalee', 'Vavuniya']],
        'region'   => ['label' => 'Province', 'choices' => ['Western', 'Central', 'Southern', 'North Western', 'Northern', 'Eastern',
            'Sabaragamuwa', 'Uva', 'North Central']],
    ],
];
