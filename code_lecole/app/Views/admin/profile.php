<?php
// Admin's My Profile: their details, change contact details and password. Data: ProfileController::show().
$tabTitle = 'My Profile';
$title    = 'My Profile';
$styles   = ['page-header', 'panel', 'tag', 'form-field', 'profile-hero', 'info-card', 'password-field', 'profile-page'];
$scripts  = ['password-field', 'profile-page'];
$grids    = ['profile'];

require dirname(__DIR__) . '/layouts/page_top.php';
require dirname(__DIR__) . '/components/_page_header.php';
?>

<div class="l-profile-grid">
  <?php require dirname(__DIR__) . '/components/_profile_page.php'; ?>
</div>

<?php require dirname(__DIR__) . '/layouts/page_bottom.php'; ?>
