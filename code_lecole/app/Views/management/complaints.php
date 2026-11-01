<?php
// Management's Inquiries & Complaints: search + category + status filters and every inquiry as a card with
// the parent's name; "Mark as Resolved" opens a message box under the card (complaints.js). Data:
// ComplaintController::management() (example data until the complaints backend is built; resolving saves
// nothing yet).
$tabTitle = 'Inquiries & Complaints';
$styles   = ['page-header', 'search-field', 'dropdown', 'panel', 'tag', 'empty-box', 'form-field', 'complaints'];
$scripts  = ['dropdown', 'complaints'];
$grids    = ['filter-bar', 'complaints'];

require dirname(__DIR__) . '/layouts/page_top.php';

$title    = 'Inquiries & Complaints';
$subtitle = words('Review, investigate, and officially resolve {parent} questions, grievances, and service requests.');
require dirname(__DIR__) . '/components/_page_header.php';

$searchText = words('Search by {parent}, {student}, or issue…');
$canResolve = true;
require dirname(__DIR__) . '/components/_complaints_board.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
