<?php

declare(strict_types=1);

// The school's rules: what the system works out by itself, each with an on / off switch. Read with rule('…').
// Later features add theirs here (B-9: pass mark and holding back; B-2: clubs and a low average).
return [
    // Term marks (B-1). Every award is worked out from a PUBLISHED term, and never for a term with an AB.

    // "Commended": a term average of at least this much
    'commended' => [
        'on'      => true,
        'average' => 80,
    ],

    // "Top of Class": the best average in the class (a shared first place gives it to each)
    'top_of_class' => [
        'on' => true,
    ],

    // "Top of Grade": the best average in the whole grade, once every class of that grade has published the term
    'top_of_grade' => [
        'on' => true,
    ],

    // Extracurricular (B-2): how many members one age group of a programme may have, unless the programme sets its own
    // ("Limit of Students per Age Group"; a club without age groups: the whole club). Requests waiting don't count.
    'age_group_limit' => 250,

    // A student's age group is their age on this day (month-day) of the school year
    'age_cutoff' => '01-31',

    // The pass mark (a {term}'s mark below it is a low one): Academic Watch now, holding back later (B-9)
    'pass_mark' => 35,

    // "Academic Watch": more than this many low marks in the student's latest published {term} → they can't join new
    // clubs / sports (their memberships stay, tagged). Management / admin may lift it until the next published {term}.
    'academic_watch' => [
        'on'       => true,
        'subjects' => 3,
    ],
];
