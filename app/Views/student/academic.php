<?php
// Student Academic Records: the Digital Record Book, total marks and the performance trend.
// Data: AcademicController::student() (RecordBook: every published term, per school year).
$tabTitle = 'Academic Records';
$title    = 'Academic Records';
$subtitle = words('Your marks, {subject} results and feedback for every {term}.');
$styles   = ['page-header', 'panel', 'dropdown', 'empty-box', 'export-pdf-button', 'record-book', 'academic-section'];
$scripts  = ['dropdown', 'export-pdf', 'record-book', 'academic-section'];
$grids    = ['academic'];

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_page_header.php';
require dirname(__DIR__) . '/components/_academic_section.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
