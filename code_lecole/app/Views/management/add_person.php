<?php
// Management's Add page: Add Student / Teacher, the parents' policy card (staff: the admin).
// The page itself: components/_add_person_page.php. Data: PeopleAddController::form().
$tabTitle = 'Add Person';
$styles   = ['dropdown', 'datepicker', 'search-field', 'tag', 'form-card', 'form-field', 'add-person', 'parent-picker', 'qualification-rows'];
$scripts  = ['dropdown', 'datepicker', 'add-person', 'parent-picker', 'qualification-rows'];

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_add_person_page.php';
require dirname(__DIR__) . '/layouts/page_bottom.php';
