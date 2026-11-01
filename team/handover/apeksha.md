# Apeksha — your tasks

Read `team/ONBOARDING.md` first: it has the overview of what the project has so far.
Every code path below is inside `code_lecole/`.

Each task: branch from `dev` → `/next-feature` plan → the lead approves → small commits → pull request into `dev`.
The full decisions are in `team/CHECKLIST.md` → "Backends left to connect".

## 1. B-11 Profile details — first (the simplest)
- **What:** My Profile still shows example values for details nobody stores yet.
- **Now:** `ProfileController::profile()` reads `app/Mock/profiles.php`; the cards each role sees: `config/profile.php`
  (`layout`). Find which fields are example data, store them (update file + `PeopleModel` / `UserModel`), delete the
  mock file.

## 2. B-12 Smaller ones
- **Notice attachments:** the notice form already has the upload box — save the file through `core/Upload.php`
  (the size the form shows is `config/notices.php` → `max_file_mb`; add the allowed types there), show it on the
  notice card, delete it with the notice.
- **Audit Log paging:** only the newest rows are reachable — add pages (`AuditController`, `AuditLog` model; the
  People directory's paging is the example: `per_page`).
- **"Changed your password" email:** after `ProfileController::password()` and the reset in `AuthController`, send
  an email (`core/Mailer.php`, the templates in `app/Views/emails/`).

## 3. B-13 Settings, edited where they belong
- **What:** admin edits school settings on screen; `config/white_label/` stays the starting values; a change is
  saved in a `school_settings` table that overrides the file value; every change in the Audit Log.
  - a Settings page (admin, new sidebar item): **School** tab (name, logo, emails, phone, address) · **Words** tab
  - the Academic page: **School Year** section (term start + end dates per year, `school_terms`) · **Marks rules**
    section (Commended %, Top of Class, Top of Grade on / off)
  - the Calendar: a **Holiday** category (whole school; admin, management)
- **Each design is shown to the lead first.** Read the full B-13 entry in the checklist ("One school timeline").
- B-9 Year-end needs the School Year dates from this task.

## 4. B-10 Dashboards — last (it reads everyone's data)
- **What:** the 5 dashboards' number cards, bar chart and donuts from real data (B-1 … B-6).
- **Now:** `DashboardController::show()` reads `app/Mock/dashboards.php`; every block there names its `feature`.
  Marks and extracurricular numbers are real already (`Services/MarksDashboard`, `ExtracurricularDashboard`) — they
  are the examples to follow. Replace the rest block by block; delete each mock block when it's real.

## Practice (before B-11)

Make one small change on a branch `practice/apeksha` (for example: 250 members per age group instead of 300 —
`config/white_label/rules.php`, `age_group_limit`), open a **draft** pull request into `dev`, and ask the lead to
review it. It is closed without merging — it's only to practise the whole Git flow once.

## Ask the lead

Anything outside your tasks · any decision marked ☐ or "discuss" in the checklist · a new table or column ·
a change to how a page looks.
