<?php
// The teacher's Extracurricular: the programme they run, with no page header (the hero is the top) — its panels,
// Enrollment Processing (the join requests) and Teams & Roster; or one of its achievements (?award=…). Running several:
// first the list of theirs (with a header); none: the list's empty box.
// Data: ExtracurricularController::index() → $programme (null = the list), $filters, $programmes, $emptyText, $teachers,
// or $programme, $award, $editing, $can, $programmeUrl, $awardUrl, $teachers, $calendarConfig.
$tabTitle = 'Extracurricular';
$styles   = ['page-header', 'panel', 'tag', 'empty-box', 'search-field', 'tabs', 'dropdown', 'form-field', 'image-picker',
             'extracurricular-card', 'extracurricular-hero', 'programme-panel', 'award-card', 'award-page', 'metric-card', 'join-request-card',
             'staff-card', 'notice-card', 'calendar/calendar', 'calendar/day-card', 'calendar/day-card-popup', 'calendar/event-editor',
             'teams-panel', 'team-card', 'team-pop-up', 'danger-zone', 'info-card', 'person-profile', 'achievements-timeline'];
$scripts  = ['dropdown', 'calendar/calendar', 'calendar/event-editor', 'image-picker', 'repeat-list', 'achievements-timeline', 'people-profile'];
$grids    = ['filter-bar', 'extracurricular', 'metric-cards', 'notice'];
require dirname(__DIR__) . '/layouts/page_top.php';

if ($programme === null)
{
    $title    = 'Extracurricular';
    $subtitle = 'The clubs and sports you are in charge of.';
    require dirname(__DIR__) . '/components/_page_header.php';
}

require dirname(__DIR__) . '/components/' . ($programme === null ? '_extracurricular_list.php' : '_programme_view.php');
require dirname(__DIR__) . '/layouts/page_bottom.php';
