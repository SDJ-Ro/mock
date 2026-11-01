/**
 * People's profile pop-up (components/_person_profile.php inside the page's pop-up). View on a row asks the server for
 * that person's profile (JSON { ok, html }: the server draws it, so no person's details wait in the page), puts it in
 * the pop-up and opens it; the record book and trend inside start through 'content:added'. Refused or signed out →
 * the feedback banner says why (or the page reloads to the sign-in page). A row's Edit opens it already editing. Inside:
 *   - the tabs show one panel at a time
 *   - Edit Profile shows the boxes (data-editing="true" on the pop-up's content); Cancel draws the profile as it was
 *   - Save Changes sends the form in the background (PeopleController::update()): saved → the server's new profile
 *     replaces it; refused → the banner says why and the boxes keep what was typed
 *   - closing the pop-up after a save reloads the page, so the table shows the new details
 * window.showInPopUp(url, popUpId) also fills the teacher handover pop-up (people-directory.js).
 */
(function ()
{
  'use strict';

  const popUp = document.getElementById('j-person-profile');
  const content = document.getElementById('j-person-profile-content');

  if (!popUp) return;   // a page that loads the script without the pop-up (a programme's list, one achievement)

  let shown = '';      // the profile as the server last drew it (Cancel goes back to it)
  let saved = false;   // something was saved while the pop-up was open

  // A pop-up's content = the server's own HTML (every value already escaped: e()); then the scripts inside start
  function fill(box, html)
  {
    box.innerHTML = html;
    document.dispatchEvent(new CustomEvent('content:added', { detail: box }));
  }

  // editing: open it already in Edit Profile (a row's Edit button)
  function showInPopUp(url, popUpId, editing = false)
  {
    fetch(url, { headers: { Accept: 'application/json' } })
      .then((response) => response.json())
      .then((answer) =>
      {
        if (!answer.ok)
        {
          window.showFeedbackBanner(answer.message, 'error');
          return;
        }

        if (popUpId === popUp.id)
        {
          shown = answer.html;
          content.dataset.editing = String(editing);
        }

        fill(document.getElementById(`${popUpId}-content`), answer.html);
        window.openPopUp(popUpId);
      })
      .catch(() => window.location.reload());   // not JSON (e.g. the session ended) → the reload shows the sign-in page
  }

  function openTab(name)
  {
    content.querySelectorAll('.j-person-tab').forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.panel === name)));
    content.querySelectorAll('.c-person__panel').forEach((panel) => panel.toggleAttribute('hidden', panel.dataset.panel !== name));
  }

  document.addEventListener('click', (event) =>
  {
    const view = event.target.closest('.j-person-view');
    const tab = event.target.closest('.j-person-tab');

    if (view)
    {
      showInPopUp(view.dataset.url, popUp.id, view.hasAttribute('data-edit'));
    }

    if (tab)
    {
      openTab(tab.dataset.panel);
    }

    if (event.target.closest('.j-person-edit'))
    {
      content.dataset.editing = 'true';
      openTab('information');
    }

    if (event.target.closest('.j-person-cancel'))
    {
      content.dataset.editing = 'false';
      fill(content, shown);
    }
  });

  document.addEventListener('submit', (event) =>
  {
    const form = event.target;

    if (!form.matches('#j-person-form')) return;   // not form.id: the form's <input name="id"> hides that property

    event.preventDefault();
    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } })
      .then((response) => response.json())
      .then((answer) =>
      {
        window.showFeedbackBanner(answer.message, answer.ok ? 'success' : 'error');
        if (!answer.ok) return;

        saved = true;
        shown = answer.html;
        content.dataset.editing = 'false';
        fill(content, answer.html);
      })
      .catch(() => window.location.reload());
  });

  popUp.addEventListener('close', () =>
  {
    if (saved) window.location.reload();
  });

  window.showInPopUp = showInPopUp;
})();
