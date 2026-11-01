<?php
// Parent notice board: read and search the notices for this parent's children. Data: NoticeController::board().
$tabTitle = 'Notice Board';
$styles   = ['page-header', 'dropdown', 'search-field', 'empty-box', 'tag', 'notice-board', 'notice-card'];
$scripts  = ['dropdown', 'notice-filter'];
$grids    = ['filter-bar', 'notice'];

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_notice_board.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
