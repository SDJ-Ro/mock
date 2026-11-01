<?php

declare(strict_types=1);

// Settings shared by everything people post (calendar events, notices). Who may post where, and whose posts each role
// may change: config/permissions.php 'posts'.
return [
    // Every role a post can be for (the audience). A post for all of them shows one "All" tag / "All School" pill.
    'audience' => ['student', 'parent', 'teacher', 'management'],
];
