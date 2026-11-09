<?php
// The teacher's details page: the students of the classes they teach, with their parents, view only.
// The page itself: components/_people_directory.php. Data: PeopleController::students().
$tabTitle      = words('{Student} Details');
$styles        = ['page-header', 'search-field', 'dropdown', 'panel', 'tag', 'tabs', 'info-card', 'empty-box', 'people-directory', 'pager', 'people-table', 'person-profile', 'record-book', 'achievements-timeline'];
$scripts       = ['dropdown', 'record-book', 'achievements-timeline', 'people-profile'];
$grids         = [];
$directoryView = 'teacher';

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_people_directory.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
