<?php

declare(strict_types=1);

// Extracurricular settings (the words and choices). Who may do what: config/permissions.php ('extracurricular').
return [
    // A programme's type: its tab on the list, its name on the detail page, its pill on the card, the card's button tone
    // and the timeline group its achievements belong to (config/achievements.php 'groups')
    'types'      => [
        'sport' => ['tab' => 'Sports', 'name' => 'Sports',              'pill' => 'Sport', 'group' => 'sports'],
        'club'  => ['tab' => 'Clubs',  'name' => 'Clubs and Societies', 'pill' => 'Club',  'group' => 'clubs'],
    ],

    // What the Edit Programme pop-up offers
    'categories' => ['Athletics', 'Creative Arts', 'Academic Club', 'STEM Club', 'Performing Arts', 'Team Sport', 'Aquatics', 'General'],

    // The age groups, youngest first: [name => under this age on the cut-off day (rules.php 'age_cutoff'), null = any age].
    // A student's own group is worked out from their date of birth (App\Services\JoinRules); also a team's and an
    // achievement's age group (Add / Edit Team, Add Achievement).
    'age_groups' => ['Under 13' => 13, 'Under 15' => 15, 'Under 17' => 17, 'Under 19' => 19, 'Open' => null],

    // A programme without its own cover photo shows this one (public/assets/…); uploaded photos (covers, an
    // achievement's gallery): at most this size each; an achievement's gallery: at most this many photos
    'default_cover'  => 'images/schoolyard.jpg',
    'cover_kb'       => 5120,
    'gallery_photos' => 12,

    // The dashboards' donuts: the biggest this many programmes (or grades), the rest as one "Others" slice, coloured
    // in this order (chart.css colour names)
    'donut_slices'  => 5,
    'donut_colours' => ['skyblue', 'midnight', 'sunshine', 'terracotta', 'moss', 'lightblue'],
];
