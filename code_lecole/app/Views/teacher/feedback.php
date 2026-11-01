<?php
// The teacher's Feedback Channel: search + type filter, the feedback cards (click → the card in full),
// "+ New Feedback" → its pop-up. Data: FeedbackController::teacher() (example data until the feedback backend
// is built; New Feedback saves nothing yet).
$tabTitle = 'Feedback Channel';
$styles   = ['page-header', 'search-field', 'dropdown', 'panel', 'tag', 'empty-box', 'form-field', 'feedback'];
$scripts  = ['dropdown', 'feedback-card'];
$grids    = ['filter-bar', 'feedback'];

require dirname(__DIR__) . '/layouts/page_top.php';

$title        = 'Feedback Channel';
$subtitle     = words('View {parent} feedback, manage {student} progress, and post updates.');
$headerButton = ['label' => 'New Feedback', 'popUp' => 'j-new-feedback'];
require dirname(__DIR__) . '/components/_page_header.php';

require dirname(__DIR__) . '/components/_new_feedback_pop_up.php';
require dirname(__DIR__) . '/components/_feedback_board.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
