<?php
// Management's Year-End (from the Academic page). The page itself: components/_year_end.php. Data: YearEndController::show().
$tabTitle = 'Year-End';
$styles   = ['page-header', 'panel', 'tag', 'dropdown', 'year-end'];
$scripts  = ['dropdown'];
$grids    = ['year-end'];

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_year_end.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
