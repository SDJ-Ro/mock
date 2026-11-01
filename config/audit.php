<?php

declare(strict_types=1);

// The Audit Log page (admin/audit): who may open it is config/permissions.php ('audit.view').
return [
    'shown' => 350,   // the newest events that match the filters

    // Each person's role → their card's colour; an event with no account (an unknown ID signing in) = 'system'
    'tones' => [
        'admin'      => 'moss',
        'management' => 'maroon',
        'teacher'    => 'sunshine',
        'parent'     => 'terracotta',
        'student'    => 'sky',
        'system'     => 'bronze',
    ],

    // Short names for the action codes, in the filter and on the cards. Any code not listed shows its own words
    // ("NOTICE_POSTED" → "Notice Posted"), so only the ones that read badly are here.
    'names' => [
        'LOGIN_SUCCESS'          => 'Sign In',
        'LOGIN_FAILED'           => 'Failed Sign In',
        'LOGIN_WRONG_PORTAL'     => 'Wrong Portal',
        'LOGOUT'                 => 'Sign Out',
        'ACTIVATE_CODE_SENT'     => 'Sign-Up Code',
        'ACCOUNT_ACTIVATED'      => 'Signed Up',
        'SIGNUP_REJECTED'        => 'Sign-Up Refused',
        'RESET_REQUESTED'        => 'Reset Asked',
        'RESET_CODE_SENT'        => 'Reset Code',
        'PASSWORD_CHANGE_FAILED' => 'Wrong Password',
        'ACCOUNT_SWITCHED_ON'    => 'Account On',
        'ACCOUNT_SWITCHED_OFF'   => 'Account Off',
        'EVENT_CLASH_ACCEPTED'   => 'Event Clash',
        'SUBJECT_TEACHER_SAVED'  => words('Subject {Teacher}'),
        'TEACHER_HANDED_OVER'    => 'Handover',
    ],
];
