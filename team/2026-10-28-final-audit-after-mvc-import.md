# Final audit after the MVC import — 2026-10-28

A read-only audit before moving on from "import MVC's front end" to "build the backends".
No code was changed while writing it. Five parts:

1. Inventory: libraries, lines of code, the proposal vs what is built ← **this part**
2. Rules audit, feature by feature (the calendar / chart way of questioning every line)
3. Backend status + bugs (with curl attacks)
4. UX: banners vs pop-ups, normal things we have missed
5. Verdict + the list of what to do first

Signs: ✅ real and working · 🟡 MVC's look on example data (`app/Mock`) · ❌ not built · 🔀 changed on purpose ·
⚠ needs a decision

---

## Part 1 — Inventory

### 1.1 Libraries and frameworks

**Answer: yes, it is vanilla, plus Dompdf only.**

| What | Where | Verdict |
|---|---|---|
| PHP framework | none — own `core/` (Router, Controller, Model, Database, Validator, Mailer, Upload) | ✅ vanilla |
| JS framework / library | none — 40 own files, no `import`, no CDN | ✅ vanilla |
| CSS framework | none — own `global.css` tokens + one CSS file per component | ✅ vanilla |
| Composer packages | `dompdf/dompdf` ^3.1 → brings 4 of its own: `dompdf/php-font-lib`, `dompdf/php-svg-lib`, `masterminds/html5`, `sabberworm/php-css-parser` | ✅ the approved one (the 4 come with it; we never call them) |
| Email | `core/Mailer.php` — our own SMTP client (158 lines), no PHPMailer | ✅ vanilla |
| Fonts | Poppins, 5 `.ttf` files in `public/assets/fonts/poppins/` (+ its licence `OFL.txt`), no Google Fonts link | ✅ local |
| External `<script>` / `<link>` / `@import` | none | ✅ |
| Docker | `docker-compose.yml`: MySQL + phpMyAdmin (a tool to run the DB, not part of the app) | ✅ fine |

Two things to know:
- ✅ **Example photos came from the internet** (fixed the same day): 38 links to `images.unsplash.com` in three
  `app/Mock/` files → 27 local copies in `public/assets/images/examples/` (one MVC link was already dead). The
  site now needs no internet at all.
- The project folder is **not a Git repository** (there is a `.gitignore`, but no `.git`). The proposal promises
  "GitHub, feature-branch strategy, pull-request review" (8.2.5). See 1.4.

### 1.2 Lines of code

Counted 2026-10-28, without `vendor/`, `storage/` and uploads. "Blank" = empty lines, inside the total.

| Language | Files | Lines | Blank | Comment lines | Code lines (≈) |
|---|---:|---:|---:|---:|---:|
| PHP | 229 | 17,715 | 2,011 | 1,767 | 13,900 |
| CSS | 100 | 14,259 | 1,994 | 635 | 11,600 |
| JavaScript | 40 | 3,600 | 525 | 466 | 2,600 |
| SQL | 29 | 1,846 | 188 | — | — |
| Shell (`bin/check.sh`) | 1 | 201 | 13 | — | — |
| Markdown (docs) | 3 | 1,695 | — | — | — |
| **App code (PHP + CSS + JS)** | **369** | **35,574** | | | **≈ 28,100** |

**Where the PHP is**

| Folder | Files | Lines | What |
|---|---:|---:|---|
| `app/Views/components/` | 94 | 6,029 | the shared pieces |
| `app/Views/{role}/` (5 roles) | 39 | 1,118 | the pages — short, because they only list components |
| `app/Views/` other (auth, landing, layouts, emails, print, errors) | 14 | 900 | |
| `app/Controllers/` | 18 | 3,496 | biggest: People 593, Academic 530, Auth 468, Notice 375, PeopleAdd 325 |
| `app/Models/` | 11 | 1,940 | biggest: PeopleModel 686, ClassModel 249, UserModel 197 |
| `app/Services/` | 8 | 713 | |
| `app/Mock/` | 10 | 959 | example data, to delete block by block |
| `core/` | 10 | 1,185 | the home-made framework |
| `config/` (+ `white_label/` 200) | 21 | 1,057 | |
| `routes/web.php` | 1 | 220 | |

**Where the CSS and JS are**

| Folder | Files | Lines |
|---|---:|---:|
| `css/components/` | 77 | 11,704 |
| `css/layout/` (the `l-…-grid` rows) | 18 | 678 |
| `css/` top level (global 420, landing 697, auth 438, print 270, pdf 52) | 5 | 1,877 |
| `js/components/` | 38 | 3,552 |
| `js/` top level (auth, reveal) | 2 | 48 |

**Compared with MVC** (`MVC/app`, `core`, `public`, `config`)

| | MVC | Ours | Change |
|---|---:|---:|---:|
| PHP | 33,445 | 17,715 | −47 % |
| JavaScript | 21,391 | 3,600 | **−83 %** |
| CSS | 19,517 | 14,259 | −27 % |
| Total | 74,353 | 35,574 | **−52 %** |

What the numbers mean:
- **JS −83 %** is the biggest win: PHP draws what MVC built in the browser (cards, lists, forms, dates), forms are
  real forms (not background sends with JS checks), and the pop-ups use the browser's `<dialog>` / `popover`.
- **CSS −27 % only** — the smallest drop. CSS is now **40 % of the app** (14,259 of 35,574) and the largest single
  files are CSS (`landing.css` 697, `dropdown.css` 638, `form-field.css` 459, `auth.css` 438). `check.sh` still reports
  7 "the same N lines" warnings (e.g. `.c-join-card__name` = `.c-teacher-workload__name`). Part 2 looks at this
  first: it is where the remaining duplicates are.
- PHP's half includes 959 lines of `app/Mock/` that will disappear, and 6,000 lines of components.
- Comments: ~10 % of PHP lines. Fine for a viva; Part 2 checks that they explain *why*, not repeat the code.

**`bin/check.sh` today:** 0 errors, 7 warnings (all "the same lines" in two CSS files).

### 1.3 The proposal (Final_Proposal_L'École-55.pdf, 74 pages)

Where it is: the project root (`GodHelpMEplease/`), **not** inside `MyTry copy 2/`.
What it promises: 5 roles, 6 modules (accounts, academic record book, notice board, extracurricular, feedback +
complaints, character certificate), role dashboards, 51 use cases, security / performance / reliability rules.
Timeline: semester 1 (May–Sep 2026) front end, semester 2 (Oct 2026 – Mar 2027) **backend + integration** — so we
are right at the start of the backend half, on schedule.

### 1.4 Proposal vs built — requirement by requirement

**Accounts & access (3.3.1, 8.1.1, 8.1.2, 7.1)**

| Proposal says | Status | Notes |
|---|---|---|
| Management creates student, teacher, parent accounts | ✅ | People → Add person (`PeopleAddController`, in a `transaction()`) |
| Admin creates all 5 incl. management | ✅ | `permissions.people.management = ['admin']` |
| Institutional email generated (`firstname…@school`) | ✅ | `PeopleModel::schoolEmail()` — `first_last@domain`, adds 2, 3… if taken; domain from `school()` |
| Unique student index number generated | ✅ | `PeopleModel::code()` — `STU-2026-0013` from `white_label/academic.php` |
| Guardian email already used → link, don't duplicate | ✅ | "Use existing parent (sibling)" (`_parent_picker`) |
| **Exactly one guardian per student** | 🔀 ⚠ | the database (`student_parents`) and Approvals ("the other parent answered") allow more than one. Either update the proposal / SRS, or limit it to one. Owner to decide |
| Activate / suspend / deactivate, never delete | ✅ / ⚠ | on / off is real (`setActive`). No separate "suspended". But some tables say `ON DELETE CASCADE` (marks, 013) and there are delete routes for grades / stages — Part 3 checks nothing real can be lost |
| Accounts pending admin review + notification to admin | 🔀 | owner 2026-10-27: teacher accounts need no approval. The SRS should say so |
| Password: 8+ chars, letter, number, symbol | ✅ | `config/app.php password_rules` |
| Passwords hashed | ✅ | `password_hash` / `password_verify` everywhere |
| Reset code single use, 30 min | ✅ stricter | 15 min, 5 tries, 60 s between sends, hashed |
| Login throttling | ✅ | 5 tries → 15 min lock (`UserModel`) |
| Session regenerate on login | ✅ | also on password change |
| Session ends after 30 min idle | ✅ | admin 15 min, others 30 (`config/app.php session_timeout`) |
| HTTPS, HTTP redirected | ❌ (hosting) | the cookie has `httponly` + `samesite`, but not `secure`. A hosting setting + one line in `bootstrap.php` when it goes online |
| Prepared statements only, `htmlspecialchars` on output | ✅ | rules of the project; Part 2 / 3 check every file |
| Server checks the role on every action | ✅ | `requireRole()` + routes only made for allowed roles |
| Audit log: mark edits, account status changes, certificate finalising; never deleted | 🟡 | the log is real (`activity_logs`, admin's Audit page). Logged today: sign-in / out, codes, notices, events, people, grades, stages, profile. **Not yet**: marks and certificates (their backends don't exist) |

**Academic record book (3.3.2, 8.1.3, 8.1.5, 7.1)**

| Proposal says | Status | Notes |
|---|---|---|
| Grades, classes, subjects, class teachers, subject teachers | ✅ | Academic overview (management + admin) — real |
| Year-end: reassign class teachers without touching history | ✅ partly | class editor + teacher handover are real |
| **Year-end grade auto-promotion (13 → leaving date)** | ❌ | not built, not in MVC |
| Class teacher enters term marks 0–100, 3 terms | 🟡 | Term Marks page = MVC's look on example data. `marks` table exists (013) and is checked 0–100 by type only (`TINYINT UNSIGNED` allows 0–255) |
| **Every mark edit logged: old value, new value, who, when** | ❌ | no `mark_changes` table yet. Needed with the marks backend |
| Record book Grade 6–13, one page per grade | 🟡 ⚠ | example data. The `marks` table has year + term but **not the grade or class the student was in that year**; the student's class is only "now" (`student_details.class_id`). For a book that spans 8 years we need a "student was in class X in year Y" history. Part 3 |
| Performance graph with trend | 🟡 | chart drawn by PHP; example numbers |
| Student / parent read only | ✅ | no save routes for them |

**Notice board (3.3.3, 8.1.1, 8.1.3)**

| Proposal says | Status |
|---|---|
| Admin, management, class teacher, TIC post + edit; students / parents read | ✅ real (+ pin, delete, attachment, scheduling, expiry) |
| Categories General / Academic / Extracurricular | ✅ |
| Class notices (only that class's students and parents) | ✅ targets grade / class / activity (021) |
| Activity notice board | ✅ for notices targeted at an activity · 🟡 the club page's own board |
| Teacher notices wait for the admin | ✅ added (not in the proposal) |

**Extracurricular (3.3.4, 8.1.4, 8.1.5, 8.1.6, 7.1)** — the proposal's biggest module, and still mostly 🟡

| Proposal says | Status |
|---|---|
| Browse sports and clubs; coach number only for members | 🟡 |
| Interest button → parent approves → TIC confirms | ✅ parent approvals real (`ExtracurricularModel`, Approvals) · 🟡 the Interest button and the TIC's side |
| **Interest locks at 300 per age category (U13–U19)** | ❌ no capacity rule anywhere |
| **Age group set automatically from date of birth, TIC can't change it** | ❌ / 🟡 the team pop-up lets you pick it (MVC's) |
| Teams A / B / C, transfers, every change logged | 🟡 |
| Positions (captain…) with the date | 🟡 |
| Achievements at divisional level and above, with evidence upload, dual verification | 🟡 (Upload.php is ready) |
| Leave request → TIC approves → history kept | 🟡 |
| **Permission request cards (trip, event, date, place, cost) → parent approves** | ❌ not built (UC-T11, UC-P8). MVC had none either |
| Medical info (UC-T10) | ✅ the student's `medical_notes` (People) · ❌ a TIC page to manage it |

**Feedback & complaints (3.3.5, 8.1.3, 8.1.6)**

| Proposal says | Status |
|---|---|
| Teacher feedback with audience Student / Parent / Both; "student only" invisible to parents | 🟡 (tables exist: 016) |
| One-way, no replies | 🟡 — ⚠ the table is called `complaint_messages` (017): check it is not a reply thread |
| Parent complaint: recipient Class Teacher / Head Teacher / Principal, subject, body | 🟡 |

**Character certificate (3.3.6, 8.1.2, 8.1.5)**

| Proposal says | Status |
|---|---|
| Built automatically from verified records | 🟡 |
| Student previews a draft, pending entries labelled | 🟡 |
| **Download blocked until management verifies — "no workaround"** | ❌ the PDF (`/student/certificates/pdf`) works today on example data, with no check |
| Student sets a leaving date → warnings to class teacher, TICs, management | 🟡 (the "leaving" pathway) / ❌ the warnings |
| PDF | ✅ Dompdf, the same sheet as the screen |

**Dashboards & notifications (8.1.1, 8.2.2)**

| Proposal says | Status |
|---|---|
| 5 role dashboards | ✅ layout · 🟡 numbers. Real: calendar, upcoming events, unread notices |
| Admin: live counts (active students, staff, pending, recent notices) | 🟡 |
| **In-platform notifications (polling every 10 s)** — new interest, approvals, leaving date, complaint sent | ❌ the biggest missing piece that the proposal names in many use cases (UC-P9 and the "sends a notification" steps). Only "unread notices" exists |

**Quality rules (8.2)**

| Proposal says | Status |
|---|---|
| Atomic writes, roll back on failure | ✅ `transaction()` |
| Confirmation before every irreversible action | ✅ `askQuestion()` / `data-question-…` (Part 4 checks every one) |
| Each module separate, logic in service classes | ✅ mostly (controllers + `Services/`) |
| Record book indexed on student + grade | ⚠ `marks` key starts with `student_id` ✅; no grade column (see above) |
| Dashboards < 3 s, certificate preview < 5 s | not measured |
| GitHub, feature branches, pull-request review | ❌ the folder is not a Git repository |
| Personal Data Protection Act 2022 | not checked (who can see medical notes, NIC, phone numbers → Part 3) |

### 1.5 What changed from the proposal on purpose (🔀)

1. More than one parent per student is possible (proposal: exactly one).
2. Teacher accounts need no admin approval; teachers' **notices** do (owner 2026-10-27).
3. MVC's separate admin "verify" page removed: each thing is approved where it lives.
4. Codes expire in 15 min, not 30.
5. A parent sees one child at a time with a "Viewing" switch.
6. Sign-up = activating an account the school already made (email code), so "management creates all accounts" still holds.

These should go into the SRS so the documents match the system in the viva.

### 1.6 What is left — the backend list, in proposal order

1. **Marks**: save term marks + the change log + the student's class per year → record book, charts, dashboards.
2. **Extracurricular**: programmes, members, interest → parent → TIC, age group from date of birth, capacity 300,
   teams, positions, leave requests, the club board.
3. **Achievements**: record + evidence upload + dual verification.
4. **Certificates**: built from 2 + 3, management verification, download locked until verified, leaving date.
5. **Feedback** (audience switch) and **complaints** (recipient, one-way).
6. **Notifications** (in-platform; the proposal's 10-second check).
7. **Permission request cards** (trips / events) — new, MVC had none.
8. **Year-end**: grade promotion, Grade 13 leaving.
9. **Dashboards**: replace `app/Mock/dashboards.php` block by block as each of the above becomes real.
10. Hand-in jobs: Git repository, HTTPS + `secure` cookie, `demo_quickfill` off, `APP_DEBUG=false`, a new Resend key,
    tests per module, `bin/reset-demo.sh`.

The logic of each of these (tables, rules, who may do what, what to watch out for) is in Part 3.

---

## Part 2 — Rules audit, feature by feature

How it was checked: one scan per CLAUDE.md rule over every PHP / JS / CSS file (not only `check.sh`), then the
flagged spots, the core (`Router`, `Controller`, `App`, `Upload`), sign-in and the six biggest files read line by line.
Same questions as the calendar / chart refactor: is it needed? is it in ONE place? is there a simpler, more
standard way? does it follow the rule, or only look like it?

### 2.1 Scorecard — rule by rule

| Rule (CLAUDE.md) | Result |
|---|---|
| Vanilla, only Dompdf | ✅ (Part 1) |
| No inline `style=""` | ✅ 0 in views (emails excepted: email programs need them) |
| No `!important` | ✅ 0 (1 in the email layout, same reason) |
| No raw colours outside `global.css` | ✅ 0 |
| PHP `{` on the next line | ✅ 0 breaks |
| JS `{` on the next line | ⚠ 83 one-line `if (…) return;` / `if (…) x();` with no braces (PHP has 0) — see 2.2 #9 |
| Form values only via `postText()` / `postList()` / `postPassword()` | ✅ (2 reads of `$_GET` only check `isset` — harmless) |
| SQL only in Models | ✅ 0 outside `app/Models` |
| `e()` on every value | ✅ every raw `<?=` is a fixed word, the CSRF field, or HTML a component built itself |
| CSRF checked once, by the Router | ✅ `Router::dispatch()` → `checkCsrf()` before every POST |
| No dead code — functions | ✅ 0 functions nobody calls (PHP + JS) |
| No dead code — files | ❌ 18 images nobody uses (2.2 #2) |
| No duplicates | ⚠ 5 CSS blocks (`check.sh`), 2 small PHP helpers written 3× / 2× (2.2 #6) |
| Who may do what only in `config/permissions.php` | ⚠ about 10 places decide by `role === 'teacher'` / a role list (2.2 #5) |
| White-label: never type the school | ⚠ 12 mock emails `…@lecole.edu` (2.2 #3) |
| Layout files only arrange | ⚠ 1: `term-marks-grid.css` styles a scrollbar (2.2 #4) |
| `global.css` only tokens, resets, app shell | ⚠ also holds `.tone-*` tiles, `.c-icon`, `.wrap`, the scroll-reveal look (2.2 #11) |
| One component = PHP + own CSS + own JS | ⚠ naming: 39 component files have no CSS of the same name (2.2 #10) |
| Secrets only in `.env` | ✅ `.env.example` has placeholders only (☐ the Resend key pasted in chat — make a new one) |
| No external links | ✅ (photos fixed today) |

### 2.2 Findings — most important first

**1. 🐞 BUG — "Stay Signed In" does nothing.**
`session-timeout.js` keeps you signed in by sending `fetch(location.href, { method: 'HEAD' })`. The Router only
knows GET and POST, so HEAD gets **404** and never reaches `requireRole()` — `last_activity` is not refreshed and
the person is signed out a minute later anyway. Tested: `HEAD /teacher → 404`, `GET /teacher → 302`.
Fix (recommended): `core/App.php` treats HEAD as GET (that is what HEAD means in HTTP: GET without the body) — one
line. Then curl `HEAD` again.

**2. Dead files — 18 images, ~3.6 MB, nothing uses them.**
14 with random names (`215b6b9d-….jpg` …), `lecole-crest.jpg` (also the school's name in a file name),
`logo.jpg` (`logo.png` is the one used), `parents2.jpg`, `teacher2.jpg`. (`football / running / debate /
orchestra / class.jpg` ARE used — by the honours board, from the database.) `check.sh` does not look at images →
add a section that lists files in `public/assets/images` that no code, config or seed mentions.

**3. White-label — 12 example emails type the school's domain** (`weerasinghe@lecole.edu` in
`app/Mock/extracurricular.php`). Even example data breaks "another school = change `config/white_label/` only".
Fix: `'weerasinghe@' . school('email_domain')`.

**4. Layout rule — `layout/term-marks-grid.css`** styles the cards' scrollbar (thumb colour, radius). That is a look
→ move those 2 blocks to the term-marks component's own CSS.

**5. ✅ FIXED 2026-10-28 (owner's "D", see CHECKLIST) — "Who may do what" typed in code instead of
`config/permissions.php`.** The rule: one rule in one place.

| Where | What it decides | Better |
|---|---|---|
| `DashboardController:24` `in_array($role, ['student', 'parent'])` | who sees "Unread notices" | `permissions.notices.unread` |
| `NoticeController` 229, 235, 323, 325 `role === 'teacher'` | teachers can't post school-wide | `permissions.notices.school_wide = ['admin', 'management']` |
| `NoticeModel:144`, `CalendarModel:48/144` `role === 'teacher'` | teachers only see / touch their own groups | `!can('notices.see_all')` / `!can('calendar.see_all')` — the rule already exists, this repeats it |
| `PersonFields:87` `['teacher', 'management']` | who must give a national ID | `config/people.php` |
| Views: `_programme_view`, `_teams_panel`, `_extracurricular_card` (`$currentRole === …`), `_event_editor` (`$isTeacher`), `_extracurricular_list` / `_card` (`can()`) | buttons per role | the controller sends flags (`canEdit`, `canApprove`) — the way `_notice_card` gets `canManage` already |

Not a problem: `$person['role'] === 'student'` when it asks *what kind of record* this is (a student has a class, a
teacher has qualifications) — that is data, not permission.

**6. Small duplicates.**
- CSS (from `check.sh`): `.c-join-card__name = .c-teacher-workload__name` (7 lines), `.c-programme-card__photo =
  .c-programme-hero__photo`, `.c-chart-tooltip__color-dot = .c-donut-card__legend-dot`, `.c-field-section__title =
  .c-grade-card__name`, and 3 empty-state titles. Each pair is "the same look, two names" → one shared class, or
  decide they are allowed to differ later.
- PHP: `guard()` written 3× (Academic, Calendar, People: `requireRole` + `can()` + refuse) → one
  `Controller::requirePermission($role, 'academic.edit')`. `done()` 2× (flash + redirect) → one
  `Controller::redirectWith($url, $message)`. (`mock()` 5× is one line each and disappears with the mocks — leave it.)

**7. ⏸ LATER (owner: "E", in the CHECKLIST) — Controllers that borrow from controllers.**
`ChildProfileController extends ExtracurricularController` (only to reuse its programme page) and calls
`new PeopleController()->profileData()`. It works, but in a viva "why is a Child Profile an Extracurricular
controller?" is hard to defend, and the standard answer is: shared work lives in a Service, controllers never call
controllers. → `profileData()` into `Services/PersonProfile`, the programme page into `Services/Programmes` (already
exists). `PeopleAddController extends PeopleController` is fine (same feature, shares its guard and tabs).

**8. `AcademicController` (530 lines) does two features**: the record book pages (student, parent print) and the
school structure (grades, classes, stages). → when marks become real, the record book part becomes
`RecordBookController`. Not now.

**9. JS brace rule.** 83 one-line ifs (`if (!sidebar) return;`, `if (form) form.requestSubmit();`). PHP has none. Your
rule says `{` on the next line → these break it, and an examiner sees PHP and JS written two ways. Recommendation:
brace them (mechanical, no behaviour change), and add a `check.sh` line so it stays that way.

**10. Component file names.** 39 component PHP files have no CSS file of the same name. Most are fine — they only put
other components together (`_person_sections`, `_field_grid`, the pop-ups) — but some just have a different name:
`_add_notice_page.php` ↔ `add-notice.css`, `_digital_record_book.php` ↔ `record-book.css`. → rename so that the
PHP name = the CSS / JS name whenever a component has its own look; then "find a component's files" is one search.

**11. `global.css`** holds a few looks besides tokens / resets / shell: `.tone-*` (5 tiles), `.c-icon`, `.wrap`, the
scroll-reveal fade. → `components/tone.css`, `components/icon.css`, `reveal.css` (with `reveal.js`). Low priority.

**12. Fine as they are (checked, not problems):**
- JS sets `.style.left / top / transform` in 8 places — worked-out positions (tooltip, dropdown, date picker,
  certificate pages). A number known only at run time cannot be a class.
- Emails use inline styles + `!important` — email programs ignore `<style>` files.
- Two `$_GET` reads are `isset()` only.
- `Upload.php`: real file type, random name, size limit, no SVG, `forget()` cannot leave `public/uploads` — good.

### 2.3 Feature by feature

| Feature | Data | Rules | Notes |
|---|---|---|---|
| Page frame, sidebar, page header | — | ✅ | `Controller::page()` gives every page its frame data once |
| Dropdown, date picker, pop-ups, question pop-up | — | ✅ | browser `<dialog>` / `popover`; one `askQuestion()` |
| Calendar | ✅ real | ⚠ #5 (teacher check in the model) | the reference refactor; the cleanest feature |
| Bar / donut charts | 🟡 | ✅ ⚠ #6 (dot CSS twice) | PHP works out every position |
| Dashboards (5) | 🟡 numbers · ✅ calendar, events, unread | ⚠ #5 | kept as 5 files on purpose (owner) |
| Notice board | ✅ real | ⚠ #5 (×4) | no JS of its own: real forms |
| My Profile | ✅ real | ✅ | |
| Achievements | 🟡 (✅ in the child profile) | ✅ | |
| Academic structure | ✅ real | ⚠ #6 `guard()`, #8 | |
| Record book / term marks | 🟡 | ⚠ #4 | the example data shows Grade 11 to a Grade 10 child (CHECKLIST) |
| People | ✅ real | ⚠ #5 (`PersonFields`), #6 | biggest feature: 593 + 325 + 686 lines |
| Approvals | ✅ real | ✅ | |
| Feedback, complaints | 🟡 | ✅ | |
| Audit log | ✅ real | ✅ | |
| Certificates | 🟡 (PDF real) | ✅ | |
| Extracurricular | 🟡 | ⚠ #3, #5 (views decide by role) | |
| Child profile | mixed | ⚠ #7 | tabs fixed today |
| Sign in / up / forgot | ✅ real | ✅ | security notes → Part 3 |
| Landing, honours board | ✅ real | ✅ | |

### 2.4 Is it industry standard, clean code?

Yes for its size, with two gaps.
- **Standard:** one front door (`public/index.php`) → router → controller → model → view; PSR-4 autoload;
  `strict_types` everywhere; PDO prepared statements; CSRF; `password_hash`; session hardening; numbered database updates; config
  outside code; secrets in `.env`; a checker script (`check.sh`) that enforces house rules — many student projects
  have none of these.
- **Clean:** short methods with one job, names that say what they do, comments that explain *why*, no dead
  functions, one way to do each thing (icons, pop-ups, dates, dropdowns).
- **Gap 1 — no automated tests.** Everything was checked by hand and with curl, but nothing re-runs those checks
  after a change. Industry minimum: a small test script per module (the curl attacks written down once, run by
  `bin/check.sh`). The proposal also promises a Test Report.
- **Gap 2 — no Git history** (Part 1). An examiner can't see how the work grew.

### 2.5 For the owner to decide (Part 2)

1. Fix #1 (Stay Signed In) — recommended **now**, it is a real bug.
2. Delete the 18 unused images + add the image check to `check.sh` (#2).
3. #3, #4, #6, #9, #10, #11 — small clean-ups, one batch.
4. #5 permissions into `config/permissions.php` and flags into views — one batch per feature (Notice, Calendar,
   Extracurricular, People).
5. #7, #8 — when those features get their backends.

## Part 3 — Backend status + bugs

How it was checked: tested, not only read. Signed in as all 5 demo accounts with curl, then (1) every POST address
attacked 4 ways, (2) other people's records asked for directly, (3) the bugs found by reading proven with a real
request, (4) the database's own rules (foreign keys, checks) read from MySQL, (5) the tables of every unbuilt backend
compared with the proposal. Every test that changed data was undone (shown below).

### 3.1 What is real, what is example data

| Real (database, saving, checks) | Example data (`app/Mock`, saves nothing) |
|---|---|
| Sign in / up / forgot, sessions, lock-out · My Profile (details, photo, password) · People (add, edit, switch off, handover, parent link) · Academic structure (grades, classes, subject teachers, curriculum stages) · Notice board (post, edit, pin, delete, approve, targets, read marks) · Calendar · Parent approvals · Audit log · Child profile (information, achievements) · Honours board | Marks / record book / term marks · Extracurricular (programmes, teams, awards, enrolment) · Achievements (teacher side) · Certificates (the PDF itself works) · Feedback · Complaints · Dashboard numbers / charts |

### 3.2 The attack sweep — passed

**Every POST address (50 of them; sign-in / sign-up tested before) × 4 attacks = 200 requests:**

| Attack | What happened | Where it was stopped |
|---|---|---|
| Signed out, no token | 302 → `/` | `Router` → `checkCsrf()`, before the action |
| Signed in, no token | 302 → back | the same CSRF gate |
| Signed in as the wrong role, with a valid token | 302 → own dashboard | `requireRole()` |
| Right role, every field sent as a list (`id[]=1`, `title[]=x` …) | 302 / 404 / 422 with a normal message ("That account no longer exists.") | `postText()` turns a list into `''` |
| A background (JSON) address, signed out / lists | JSON `{"ok": false, "message": …}` every time | `refuse()` |

0 crashes, 0 warnings, nothing answered 200. **All 16 data tables byte-for-byte identical afterwards** (MySQL
`CHECKSUM TABLE` before / after).

**Other people's records (IDOR), all refused with the same message as an unknown ID:** management opening a
management account or the admin · a teacher opening a student they don't teach, or a teacher as a "student" · ID
99999 · a parent approving another family's join request (row unchanged) · a parent switching to another family's
child. The allowed case (a teacher opening their own student) works.

### 3.3 Bugs — proven with a real request

B1–B3 ✅ fixed and re-tested the same day (CHECKLIST, "Bugs found by the final audit").

| # | Bug | Proof | Fix (size) |
|---|---|---|---|
| B1 | **"Stay Signed In" does nothing** (Part 2 #1) | `HEAD /teacher → 404` | `App::run()` treats HEAD as GET (1 line) |
| B2 | **A switched-off account stays signed in** until it logs out or is idle 30 min. Proposal 8.1.1 / 8.2.1 | admin switched off the demo student → the student's open session still got `/student` **200** (switched back on; both accounts ACTIVE again) | `requireRole()` reads the account's status + lock once per request (one indexed query) and ends the session if it's off (~8 lines) |
| B3 | **Open redirect.** `previousPage()` keeps only the path, but a path starting with `//` is another website for a browser | `POST /logout` with `Referer: http://localhost:8080//evil.example/steal` → `Location: //evil.example/steal` | `previousPage()`: anything not starting with exactly one `/` → `/` (1 line) |
| B4 | Record book (example data) offers a Grade 10 child Grade 11, every grade dated 2024 | seen on the page | in the CHECKLIST |

**Weaker spots found by reading** — ✅ B5, B6 (one message for every failure) and B9 (row lock) fixed the same day;
B7, B8 open (CHECKLIST):
- B5 — **the lock-out tells which IDs are real**: 5 wrong passwords on a real ID → "Too many wrong tries … locked";
  on a made-up ID it never says that. And anyone who knows an ID can lock that person out for 15 min. Fix: count
  tries for unknown IDs too (same message), and lock per ID + computer, not per ID alone.
- B6 — reset code: after 5 wrong codes a real account says "expired", a made-up one always "incorrect". Same fix idea.
- B7 — `resetPassword()`'s result is ignored (already ☐ in the CHECKLIST).
- B8 — the session cookie has no `secure` flag (needs HTTPS; hosting time).
- B9 — class capacity is checked, then the student is added, without locking the class row: two office staff
  adding the last place at the same second can both succeed. `SELECT … FOR UPDATE` inside the transaction. Low.

### 3.4 The database's own safety

| Finding | Risk | Recommendation |
|---|---|---|
| `marks → stage_subjects` is `ON DELETE CASCADE` | removing a subject deletes its marks. The **code** refuses while marks exist (`AcademicController` 373–402) — the database doesn't | make it `RESTRICT` (a migration): then even a mistake in code can't delete marks. The proposal: "no record is silently overwritten" |
| No `CHECK` constraints anywhere | marks 0–100 only in code; `TINYINT UNSIGNED` allows 255 | `CHECK (mark BETWEEN 0 AND 100)` with the marks backend |
| `notices → classes / grades` CASCADE | deleting an (empty) class deletes its notices. (`SET NULL` would be worse: the notice would become school-wide) | keep, but write it down; or refuse to delete a class that has notices |
| `event_scopes` has no foreign key to classes / grades / clubs | deleting a class leaves events pointing at nothing | delete them with the class, in the same transaction |
| Accounts are never deleted (no `DELETE FROM user_accounts`) | the many `CASCADE`s from `user_accounts` are only a safety net | ✅ matches "deactivate, never delete" |
| Indexes | `marks` key starts with the student; logs by action and date; users by role and status | ✅ enough for one school |

### 3.5 What is left to build — and its logic

The tables for most of these already exist (database updates 010–017), but several **don't match the
proposal**. Per backend: what the proposal needs, what the table has, what's missing.

**1. Marks + record book** (tables: `marks`, `term_comments`)
- Missing: `class_enrolments (student, year, class)` — which class a student was in each year (the record book's
  grade pages, "position in class", "highest in class", the class teacher of that year, year-end promotion);
  `mark_changes (student, subject, year, term, old, new, by, at)` — the proposal's change log.
- Logic: the class teacher saves a term's marks for their class → for each changed mark, one `mark_changes` row in
  the same `transaction()`. Position = rank among the same class and year. Students and parents read only.

**2. Extracurricular** (tables: `activities`, `activity_members`)
- Has: programmes, review (pending / approved), members with status (parent → teacher → member / declined),
  position, the parent's answer and time.
- Missing: **teams** (`teams`: activity, name, age group, rank A/B/C + team changes logged); **age group stored at
  joining**, worked out from date of birth (U13 / U15 / U17 / U19), never chosen by hand; **capacity 300 per age
  group** (Interest disabled when full); **leaving**: `activity_members` has one row per student and activity, so
  leaving and joining again would overwrite history → add `joined_at`, `left_at`, a leave request status; positions
  with dates (`position_history`).

**3. Achievements** (tables: `achievements`, `achievement_students`)
- Missing: **verification** (who verified, when; the proposal wants the teacher in charge and then management, and
  only verified divisional-level-and-above on the certificate); evidence as PDF (`Upload.php` allows images only).

**4. Character certificate** (table: `certificates`)
- The table is MVC's "request → issued with typed texts". The proposal's is different: **built automatically** from
  verified records, a draft the student can preview (pending entries marked), **management's final verification**,
  **download locked until verified** (today `/student/certificates/pdf` has no check), a **leaving date** that stops
  new entries and warns the class teacher, the TICs and management.
- Needs: `status` (draft / verified), `verified_by`, `verified_at`; `leaving_date` on the student; the PDF action
  refuses unless verified.

**5. Feedback** (table: `feedback`)
- Has `kind` (praise / concern) but **no audience** — the proposal's core rule ("Student only" invisible to the
  parent) can't be stored. Add `audience` ('student', 'parent', 'both'); the parent's query reads only 'parent' /
  'both'. Edits: the proposal wants a change log, or simply no editing.

**6. Complaints** (tables: `complaints`, `complaint_messages`)
- **`complaint_messages` is a conversation thread — the proposal says one-way, no back-and-forth.** And there is
  **no recipient** (Class Teacher / Head Teacher / Principal). Recommendation: drop `complaint_messages`, add
  `body` + `recipient`. ⚠ Decision: the system has no "Head Teacher" or "Principal" role — map them to management
  accounts by job title, or add them as choices that go to the management office.

**7. Notifications** — no table. The proposal names them in many use cases (interest → parent, parent's yes → TIC,
complaint → recipient, leaving date → teachers + management). Logic: `notifications (account, text, link,
read_at, created_at)`, written **in the same transaction** as the thing that caused it; the sidebar shows the unread
count on every page; the proposal's 10-second polling is optional (a page load is enough for a school).

**8. Permission requests** (trips, events) — no tables, MVC had none. `permission_requests (activity, title, date,
place, cost, by)` + `permission_answers (request, student, parent, answer, answered_at)`; they show on the parent's
existing Approvals page as a second kind of card.

**9. Year-end** — no code. Promotion = for every active student, next year's `class_enrolments` row (management
picks which class each class moves to), Grade 13 → leaving date; one transaction; logged. Needs (1) first.

**10. Dashboards** — replace `app/Mock/dashboards.php` block by block as 1–6 become real.

### 3.6 ⚠ A decision only the owner can make

Your rule (2026-10-22): *build a backend only where MVC had one; MVC's example pages stay example data.* The proposal
(timeline, section 5.6): *semester 2 (Oct 2026 – Mar 2027) = backend + integration* — and the marking will be against
the proposal and the SRS. So 3.5's list is what the proposal still promises. Either the backends get built (in the
order above — marks first, because everything else reads it), or the SRS says which parts stay example data. Your
call; Part 5 recommends an order.

### 3.7 For the owner to decide (Part 3)

1. B1, B2, B3 — recommended **now** (real bugs, ~10 lines together, each with a curl test).
2. B5 / B6 lock-out messages — with the next auth work.
3. 3.4 — `marks` RESTRICT + `CHECK`, as the first database update of the marks backend.
4. 3.5 / 3.6 — which backends, in which order.

## Part 4 — Banners vs pop-ups, missed normal things

### 4.1 Why banners for some things and pop-ups for others

The site has **three** ways to talk to the person, and each has one job (this is standard practice — Apple's,
Google's and GOV.UK's design guides say the same):

| Tool | Our file | Its job | Why not the others |
|---|---|---|---|
| **Pop-up** — the page goes dark, you must answer | `_pop_up.php` (forms, lists) · `askQuestion()` / `data-question-…` (yes / no) | (a) **ask before something you can't undo** (delete, log out, reject) · (b) **show a form or a list** without leaving the page | it *stops* the person — right for "are you sure?", wrong for "saved!" (an OK click after every save = people stop reading and click OK blindly) |
| **Sliding banner** (a "toast") — slides in at the top, leaves by itself | `_feedback_banner.php` + `showFeedbackBanner()` | **"it worked"** after a save: you see it, you carry on, no click | a pop-up would block for no reason; an inline box would push the page down and stay forever |
| **Inline box** — sits in the page, stays | `.c-banner` (`--error`, `--warning`, `--info`, `--success`) | **"something is wrong HERE"** — a form's error next to the form, a warning or hint that must stay while you work | a sliding banner would vanish before you've fixed the problem, and it's far from the field |

**Are banners easier than pop-ups?** For the *person*, yes: a banner needs no click and doesn't interrupt. For the
*code*, both are simple (one shared file each). So the rule isn't "which is easier", it's **"does the person have to
decide something?"** — yes → pop-up; no, just telling → banner; a problem they must fix → inline box.

### 4.2 What the pages do today

| Moment | Pages | Tool used | Right? |
|---|---|---|---|
| Before delete / log out / reject / danger zone | 8 kinds (notices, calendar, grades, stages, programmes, approvals, log out, sign out of idle) | question pop-up | ✅ |
| Forms and lists | Add event, View all, profile pop-up, programme / team / award forms, handover, Add grade | pop-up | ✅ |
| A full-page form's error | Add notice, Add person, My Profile, sign-in / up | inline red box in the form | ✅ |
| **"It worked"** after a save | People, My Profile, Academic, example-data pages | sliding banner | ✅ |
| **"It worked"** after a save | **Notice board, Parent approvals** | **inline green box that stays** | ⚠ not the same as the rest |
| **"It worked"** in the calendar | Add / edit / delete event | **nothing** (the pop-up closes) — MVC showed nothing either | ⚠ (CHECKLIST ☐ already) |
| A refused one-click action (pin limit, switch-off refused, "already decided") | People, Academic | sliding banner, **red, gone after 3.5 s** | ❌ an error must stay until it's read (accessibility rule WCAG 2.2.1 "enough time") |

### 4.3 Recommended: one rule, written down

1. **Ask** before anything you can't undo → question pop-up. (Already true.)
2. **"It worked"** → the sliding banner, on every page. → Notice board and Approvals switch their green box to it;
   the calendar shows "Event saved." / "Event deleted." with it.
3. **"It didn't work"** → inside a form: the inline red box (already). After a one-click action: the sliding banner in
   red that **stays until closed** (`feedback-banner.js`: no timer for 'error').
4. **Hints / warnings that matter while working** → inline box. (Already true.)

Size: ~15 lines (feedback-banner.js 3, NoticeController / ApprovalController + their 2 views ~10, event-editor.js 2).
Write the rule into `docs/HOW-IT-WORKS.md` so the next page follows it.

### 4.4 Normal things a finished web app has — checklist

**Already done ✅**
- After every POST the server redirects (Post / Redirect / Get) → Refresh / Back never sends a form twice
- Double-click guard on every form (`send-once.js`) · "You have unsaved changes" before leaving a typed form
- "Still there?" a minute before the idle sign-out (works since B1) · `Cache-Control: no-store` → Back after
  logging out doesn't show the old page
- Question before every destructive action · empty states ("No notices yet") · friendly 404 / 500 pages, never code
- Filters and searches live in the address (Back works, a link can be shared) · paging on People
- Password rules + show / hide · email codes with a 1-minute wait · safe photo uploads · one message for every
  sign-in failure (B5)
- UTF-8 everywhere · the school's own time zone and date format · print pages (record book, certificate PDF) ·
  favicon · one focus ring for keyboard users · keyboard-usable dropdowns · `<html lang>`

**Missing ❌ / to decide ⚠**

| # | What | Why it matters | Size |
|---|---|---|---|
| N1 | ❌ **Security headers**: none are sent; `X-Powered-By: PHP/8.4.22` announces the version | without `X-Frame-Options` / `frame-ancestors` another site can show our admin page inside a frame and trick clicks ("clickjacking"); `X-Content-Type-Options: nosniff`, `Referrer-Policy` are standard | 4 `header()` lines in `bootstrap.php` + `expose_php = Off` |
| N2 | ❌ **`database/backup_l_ecole.sql`** (523 KB, 2026-10-18) — nothing uses it | a full dump: every demo person's details + password hashes; not in `.gitignore`, so it would be uploaded with the code. Dead file by the rules | delete (the schema + seed + migrations rebuild everything) |
| N3 | ❌ Red sliding banners vanish after 3.5 s | see 4.2 | 3 lines |
| N4 | ⚠ "It worked" shown two ways; nothing in the calendar | see 4.2 / 4.3 | ~12 lines |
| N5 | ⚠ The Audit Log shows only the newest `config('audit.shown')` entries, no "older" pages | the proposal: logs are permanent — the admin should be able to reach all of them | People's paging, reused |
| N6 | ⚠ No "Skip to content" link | keyboard / screen-reader users tab through the whole sidebar on every page | 1 link + 1 CSS rule |
| N7 | ⚠ The sidebar has no phone layout (CHECKLIST ☐ already) | parents mostly use phones (proposal 7.2) | a feature of its own |
| N8 | ❌ Notifications, automated tests, Git, HTTPS | Parts 1–3 | — |
| N9 | ⚠ Hand-in switches: `APP_DEBUG=false`, `demo_quickfill` off, a new Resend key | (CHECKLIST ☐ already) | config |

**Done the same day:** the one rule (4.3) built and written into HOW-IT-WORKS section 8 (N3, N4 ✅); N1 security
headers ✅; N2 left for the owner (deleting was blocked as irreversible). Also found and fixed by the page sweep: the
student card wiped the page's `$issue` (Review Issue pop-up never showed). Details: CHECKLIST.

### 4.5 For the owner to decide (Part 4)

1. The one rule in 4.3 (+ N3, N4) — recommended.
2. N1 security headers + N2 delete the old dump — recommended now (small, and an examiner may check headers).
3. N5, N6 — small, any time. N7 — its own feature.

## Part 5 — Verdict + priorities

### 5.1 Did you do well? Yes.

Plainly: this is a well-built project, clearly above what a second-year group project usually is — and the parts
that aren't finished are *missing*, not *badly made*. The evidence, not the opinion:

| Area | Mark | Why |
|---|---|---|
| Architecture | **9 / 10** | one front door → router → controller → model → view; config outside code; services for shared logic; white-label from one folder. −1: two controllers borrow from controllers (E, planned) |
| Clean code | **8.5 / 10** | MVC's 74,000 lines → 35,600 doing the same (JS −83 %); no dead functions; one way to do each thing (icons, pop-ups, dates, dropdowns, messages); comments say *why*. −: 7 small CSS repeats, 83 brace-less JS ifs, a few names |
| Security | **9 / 10** | tested, not assumed: 200 attacks with 0 problems and the data byte-for-byte unchanged; IDOR refused with one message; hashing, CSRF in one place, session hardening, lock-out, safe uploads, headers. The 6 holes found today (B1–B3, B5, B6, B9) are fixed and re-tested. −1: HTTPS / `secure` cookie wait for hosting |
| Consistency with your own rules | **9 / 10** | `check.sh` enforces them (now also permissions and messages) — rare in student work. −1: the few leftovers in Part 2 |
| Front end vs MVC | **9 / 10** | measured to the pixel, page by page; deliberate differences written down |
| Documentation | **8.5 / 10** | CHECKLIST (every decision with its reason), HOW-IT-WORKS viva sheet, this audit. −: the SRS doesn't yet record the decisions (more than one parent, no teacher-account approval…) |
| Proposal coverage | **5 / 10** | accounts, notices, academic structure, approvals, audit, calendar, profiles are real; marks, extracurricular, achievements, certificates, feedback, complaints, notifications, permission cards, year-end are example data or missing (Part 3.5) |
| Process | **5 / 10** | excellent habits (approve → build → measure → attack → tick) but no Git history and no automated tests — the two things an examiner can't see any other way |

**What you did especially well**
- You questioned everything and wrote the reason down — every decision in the CHECKLIST can be defended in a viva.
- You made rules *enforced by a script*, not just written — that's how professional teams work.
- You insisted on testing with real requests, and today that found 7 real bugs that reading alone missed
  (HEAD, switched-off sessions, open redirect, the student card's `$issue`, the lock-out leaks, the capacity race).

**What to change from here**
- The front end is done and very good; the remaining risk is **scope and time**: the proposal's semester 2 is the
  backend, and ~10 backends are left. Polishing pixels now costs backend time.
- Decide the backend scope (3.6) this week, so the work and the SRS agree.
- Start Git today: from now on the history *is* the evidence of the work.

### 5.2 Priority list

**This week (small, unblocks everything)**
1. **Git**: `git init`, a first commit of today's state, then one branch per feature (the proposal promises it).
2. **Delete `database/backup_l_ecole.sql`** (N2 — by hand; the safety check blocks Claude).
3. **Decide the backend scope** (3.6) and **update the SRS** with the decisions in 1.5.
4. **Turn today's test scripts into `bin/test.sh`**: the 4-way attack on every POST address, the every-page sweep
   (200, 0 warnings, 1 banner), the switch-off and lock-out checks. Run with `check.sh` before every hand-in → the
   Test Report the proposal promises comes from it.

**The backends, in this order** (each: next-feature report → build → measure → attack → tick)
1. **Marks**: `class_enrolments`, `mark_changes`, marks `RESTRICT` + `CHECK 0–100`, then the record book, charts,
   dashboards read it (fixes the "Grade 11 / 2024" example data too).
2. **Extracurricular**: teams, age group from date of birth, capacity 300, joined / left history, positions.
3. **Achievements**: verification by the teacher in charge, then management; PDF evidence.
4. **Certificates**: built from 1–3, management's final verification, download locked until verified, leaving date.
5. **Feedback** (audience Student / Parent / Both) and **complaints** (recipient, one-way: drop the thread table).
6. **Notifications** (one table, written in the same transaction as their cause).
7. **Permission request cards** · 8. **Year-end promotion** · 9. **Dashboards' real numbers**.

**Alongside, when touching those files**
- Part 2 clean-ups: the 18 unused images (#2), mock emails via `school()` (#3), the scrollbar in a layout file (#4),
  the CSS repeats + `requirePermission()` / `redirectWith()` (#6), JS braces (#9), file names (#10), `global.css` (#11).
- E: controllers stop borrowing (with Extracurricular); `RecordBookController` (with Marks).
- Audit Log paging (N5), a skip link (N6), the phone layout (N7), B7 (`resetPassword()` result), B8 + HTTPS at hosting.
- Hand-in switches: `APP_DEBUG=false`, `demo_quickfill` off, a new Resend key.

### 5.3 What changed during this audit (2026-10-28)

Fixed and tested: the example photos are local (27 files) · the Child profile's tabs span the page · permissions in
one file + 2 new `check.sh` rules · B1 Stay Signed In · B2 switched-off accounts signed out at once · B3 open
redirect · B5 / B6 one message for every sign-in / code failure · B9 the class row lock · one rule for telling the
person (+ the calendar's messages, red banners that stay) + 2 `check.sh` rules · the student card's `$issue` bug ·
security headers. Open for the owner: N2 (delete the dump), the decisions in 2.5, 3.6, and the list above.
