<?php
// The menu on the left of every signed-in page (layouts/page_top.php). ONE file for all 5 roles: the role's own
// subtitle and links come from config/portals.php. Its look: sidebar.css; folding + "log out?": sidebar.js.
// From Controller::page(): $currentRole, $currentPath (marks the current link), $userName, $userPhoto, $csrfField,
// $sidebarCollapsed (drawn collapsed from the start when the person collapsed it last time), $children + $chosenChild
// (a parent's children and the one being viewed: "Viewing", shown when there is more than one).
// A link whose 'feature' is switched off (config/white_label/features.php) is not drawn.
$sidebarPortal = config("portals.{$currentRole}");
?>
<aside class="c-sidebar<?= $sidebarCollapsed ? ' c-is-collapsed' : '' ?>" id="j-sidebar" aria-label="Primary navigation"
       data-role="<?= e($currentRole) ?>" data-role-label="<?= e($sidebarPortal['label']) ?>">
  <div class="c-sidebar__brand">
    <a href="/<?= e($currentRole) ?>/dashboard" class="c-sidebar__brand-link">
      <span class="c-sidebar__brand-mark" aria-hidden="true">
        <img src="<?= e(school('logo')) ?>" alt="" class="c-sidebar__brand-logo" />
      </span>
      <span class="c-sidebar__brand-text">
        <span class="c-sidebar__brand-title"><?= e(school('name')) ?></span>
        <span class="c-label c-label--light c-sidebar__brand-subtitle"><?= e($sidebarPortal['subtitle']) ?></span>
      </span>
    </a>
    <button type="button" id="j-sidebar-toggle" class="c-sidebar__collapse-btn"
            aria-label="Collapse the menu" aria-expanded="<?= $sidebarCollapsed ? 'false' : 'true' ?>">
      <?= icon('chevronLeft') ?>
    </button>
  </div>

  <nav class="c-sidebar__nav" aria-label="Main menu">
    <p class="c-label c-label--light c-sidebar__nav-label">Main Menu</p>
    <ul class="c-sidebar__nav-list">
      <?php foreach ($sidebarPortal['menu'] as $item): ?>
        <?php if (isset($item['feature']) && !feature($item['feature'])) continue; ?>
        <li>
          <a href="<?= e($item['href']) ?>" class="c-nav-item<?= $currentPath === $item['href'] ? ' c-is-selected' : '' ?>"
             <?= $currentPath === $item['href'] ? 'aria-current="page"' : '' ?>>
            <span class="c-nav-item__icon" aria-hidden="true"><?= icon($item['icon'], $item['iconSize'] ?? 16) ?></span>
            <span class="c-nav-item__label"><?= e($item['label']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="c-sidebar__profile-wrap">
    <?php // A parent with more than one child: which child the whole portal is showing (POST /parent/child) ?>
    <?php if (count($children) > 1): ?>
      <div class="c-sidebar__child">
        <p class="c-label c-label--light c-sidebar__child-label">Viewing</p>
        <?php
        $pickMenu = ['variant' => 'child', 'label' => $children[$chosenChild], 'title' => words('Choose which {student} you are viewing'),
                     'options' => array_map(fn (int $id, string $name): array => ['value' => $id, 'label' => $name], array_keys($children), $children),
                     'chosen' => $chosenChild, 'action' => '/parent/child'];
        require __DIR__ . '/_pick_menu.php';
        ?>
      </div>
    <?php endif; ?>

    <?php // The person: a link to My Profile, or a plain box while that page is switched off (features.php 'profile') ?>
    <?php if (feature('profile')): ?>
      <a href="/<?= e($currentRole) ?>/profile" class="c-profile-btn<?= $currentPath === "/{$currentRole}/profile" ? ' c-is-selected' : '' ?>">
    <?php else: ?>
      <div class="c-profile-btn">
    <?php endif; ?>
      <img class="c-profile-btn__avatar" alt="" src="<?= e($userPhoto ?? $sidebarPortal['avatar']) ?>" />
      <span class="c-profile-btn__meta">
        <span class="c-profile-btn__name"><?= e($userName) ?></span>
      </span>
      <span class="c-profile-btn__status-dot" aria-hidden="true"></span>
    <?php if (feature('profile')): ?>
      </a>
    <?php else: ?>
      </div>
    <?php endif; ?>

    <!-- Sign Out is a POST with the CSRF token, so another site can't sign people out with a link -->
    <form method="post" action="/logout" class="j-logout-form">
      <?= $csrfField ?>
      <button type="submit" class="c-logout-btn" id="j-logout-btn" title="Sign Out" aria-label="Sign Out">
        <span class="c-logout-btn__icon" aria-hidden="true"><?= icon('logOut') ?></span>
        <span class="c-logout-btn__label">Sign Out</span>
      </button>
    </form>
  </div>
</aside>
<?php unset($sidebarPortal); ?>
