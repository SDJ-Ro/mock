<?php
// The student's Teacher Feedback: search + type filter and the feedback written for them (click → the card in full;
// the first open marks it read). The same board as the parent's page. Data: FeedbackController::student().
$tabTitle = words('{Teacher} Feedback');
$styles   = ['page-header', 'search-field', 'dropdown', 'panel', 'tag', 'empty-box', 'feedback'];
$scripts  = ['dropdown', 'feedback-card'];
$grids    = ['filter-bar', 'feedback'];

require dirname(__DIR__) . '/layouts/page_top.php';

$title    = words('{Teacher} Feedback');
$subtitle = words('Read updates and feedback from your {teachers}.');
require dirname(__DIR__) . '/components/_page_header.php';

require dirname(__DIR__) . '/components/_feedback_board.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
