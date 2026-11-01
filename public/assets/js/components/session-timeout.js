/**
 * SESSION TIMEOUT: the server signs a person out after a while without activity (config/app.php 'session_timeout';
 * <body data-session-timeout> says how many seconds). A minute before, this asks "Still There?":
 *   Stay Signed In → asks the server for this page again (that counts as activity) and starts counting again
 *   no answer      → when the time is up the page reloads, and the server shows the sign-in page with its message
 * Sending a form counts as activity too. Every signed-in page loads it (layouts/page_bottom.php).
 */
(function ()
{
  'use strict';

  const seconds = Number(document.body.dataset.sessionTimeout);
  let warnTimer = null;
  let endTimer  = null;

  function startCounting()
  {
    clearTimeout(warnTimer);
    clearTimeout(endTimer);
    warnTimer = setTimeout(warn, Math.max(0, seconds - 60) * 1000);
    endTimer  = setTimeout(() => location.reload(), (seconds + 1) * 1000);
  }

  function warn()
  {
    window.askQuestion({
      title: 'Still There?',
      text: "You'll be signed out in a minute because nothing has happened for a while.",
      button: 'Stay Signed In',
      cancel: 'Close',
      icon: 'clock',
      tone: 'success',
      onConfirm: () => fetch(location.href, { method: 'HEAD', credentials: 'same-origin' }).finally(startCounting)
    });
  }

  if (seconds > 0)
  {
    startCounting();
    document.addEventListener('submit', startCounting);
  }
})();
