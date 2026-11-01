/**
 * Sidebar (components/_sidebar.php), on every signed-in page.
 *   1. The « button collapses / opens the menu and remembers it in a cookie (per role), so the server draws the next
 *      page the same way straight away (Controller::page() reads it — no jump on load). On a phone it starts collapsed.
 *   2. Logout, and the browser's Back button, ask first in the shared "Are you sure?" pop-up (question-pop-up.js).
 * (Back after logging out: the server sends signed-in pages with "Cache-Control: no-store", so the browser asks again.)
 */
(function ()
{
  'use strict';

  const sidebar = document.getElementById('j-sidebar');
  if (!sidebar) return;

  /* =========================================================================
     1. COLLAPSE / OPEN (the page moves along by CSS: .c-sidebar.c-is-collapsed ~ .c-main)
     ========================================================================= */
  const toggle = document.getElementById('j-sidebar-toggle');
  const phone  = window.matchMedia('(max-width: 767px)');
  let chosen   = sidebar.classList.contains('c-is-collapsed');   // the remembered choice (the cookie)

  function show(collapsed)
  {
    sidebar.classList.toggle('c-is-collapsed', collapsed);
    toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
  }

  toggle.addEventListener('click', () =>
  {
    const collapsed = !sidebar.classList.contains('c-is-collapsed');

    show(collapsed);

    if (!phone.matches)
    {
      chosen = collapsed;
      document.cookie = `sidebar_${sidebar.dataset.role}=${collapsed ? 'collapsed' : 'open'}; path=/; max-age=31536000; SameSite=Lax`;
    }
  });

  // Phones (under 768px): icons only, so the page keeps its width (the » button still opens it). Not remembered:
  // a wider window shows the menu the way it was chosen there.
  const fitScreen = () => show(phone.matches || chosen);
  phone.addEventListener('change', fitScreen);
  fitScreen();

  /* =========================================================================
     2. LOG OUT: always asked first
     ========================================================================= */
  function askToLogOut()
  {
    window.askQuestion({
      title: 'Leave Your Workspace?',
      text: `Would you like to sign out of your ${sidebar.dataset.roleLabel} account, or stay in your workspace?`,
      button: 'Sign Out & Exit',
      buttonIcon: 'logOut',
      cancel: 'Stay Signed In',
      icon: 'logOut',
      tone: 'warning',
      wide: true,
      onConfirm: () => document.querySelector('.j-logout-form').submit()   // logging out is a POST with the CSRF token
    });
  }

  document.getElementById('j-logout-btn').addEventListener('click', (e) =>
  {
    e.preventDefault();
    askToLogOut();
  });

  // Back button: stay on this page and ask instead (a fresh history entry catches the next Back)
  window.history.pushState({ portalGuard: true }, document.title, window.location.href);
  window.addEventListener('popstate', () =>
  {
    window.history.pushState({ portalGuard: true }, document.title, window.location.href);
    askToLogOut();
  });
})();
