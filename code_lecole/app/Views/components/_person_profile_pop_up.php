<?php
// The profile pop-up's frame: empty until a "View" (.j-person-view) fills it — people-profile.js asks the button's
// data-url for the profile (PeopleController::profile() / studentProfile(), ExtracurricularController::member()).
// Used by People / Student Details (_people_directory.php) and a programme's Teams & Roster (_teams_panel.php).
// The page also loads: people-profile.js, person-profile.css (+ what the profile's tabs use).
$popUpContent = '<div id="j-person-profile-content"></div>';
$popUpShape   = 'plain';
$popUpId      = 'j-person-profile';
$popUpSize    = 'large';
require __DIR__ . '/_pop_up.php';
