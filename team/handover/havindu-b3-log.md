# B-3 Achievements — decisions and open items (Havindu)

Branch `feature/b3-achievements` (from `dev` at 5f10827). Started 2026-11-03.
This log keeps every B-3 decision and everything parked for later. Its open items are copied into
`team/CHECKLIST.md` when B-3 is ticked.

## Approved by the lead (SDJ-Ro), 2026-11-03

| # | Decision |
|---|---|
| Q1 | This log, at `team/handover/havindu-b3-log.md` |
| Q2 | The teacher in charge records a club / sport achievement through B-2's "Add achievement" form |
| Q3 | Students send missing-record requests from the existing pop-up on `student/certificates` (Mansala agreed) |
| Look | Level + Result fields · the proof hint from config · Reject + reason · "No file attached" · an Evidence box on the student's missing-record pop-up (the lead and Mansala approved) |
| Core | `Upload::document()` · `keepingUploads()` moved into the core Controller · one shared timeline builder · a 1-line `CertificateController` change |
| PR #2 | `practice/havindu` and PR #2 stay as they are (open, not merged) until the lead closes them |

## Decided with Havindu, 2026-11-03

| Decision | Detail |
|---|---|
| Who records | The class teacher → students of their own class · the teacher in charge → their own sport / club (B-2's form) |
| Proof OPTIONAL when a teacher records (changed 2026-11-04; was compulsory) | The Record pop-up's Supporting Proof loses its red `*`, no `required`; the server accepts a record without a file. B-2's form: no change |
| Proof COMPULSORY on the student's request | An "Evidence" box on the missing-record pop-up, the same look as Supporting Proof, `required`; the server refuses a request without a valid file |
| No proof | A record without proof shows "No file attached" wherever its proof would appear |
| Files | PDF, PNG, JPG, WebP; max 5 MB; types and size in `config/achievements.php` (nothing typed in a view); saved privately in `storage/uploads/`, never public |
| The proof box | ONE shared component used by both pop-ups (its own JS); its look stays in `form-field.css` |
| Where a request goes | The student's current class teacher, also for a club / sport achievement; the pop-up's banner says only that |
| The class teacher's answer | Approve (it becomes an achievement on the student's timeline) or Reject with a reason |
| B-3's actions | Record (class teacher) · record via B-2's form (teacher in charge) · send a request (student) · approve / reject (class teacher). No verify step, no delete |
| Where an achievement shows | The student's Achievements page, the parent's Child profile, People's pop-up (they read `achievement_students` already). The character certificate = B-4 (Mansala): B-3 only saves the rows B-4 reads |
| Git | Havindu runs `git add` / `commit` / `push`; commit messages without a Co-Authored-By line |
| Demo data | A separate demo update: removes demo row 6 if it duplicates the live automatic award (014 is never edited) and adds 1 pending request from Amaya (id 9), whose class teacher is Alex (id 3) — both ids checked in the data first |
| Example data | `app/Mock/achievements.php` and the two `data-mock-message` attributes are deleted at the end; face photos are NOT deleted — the unused ones are listed for the owner |

## Waiting for the lead

| # | Question | Recommended |
|---|---|---|
| L1 | ~~The teacher in charge: is proof compulsory for B-2's form too?~~ Settled 2026-11-04: proof is optional for teachers, B-2's form unchanged | — |
| L2 | `CHECKLIST.md` B-3 says "verification (teacher in charge → management)"; B-3 now has no verify step | Update the CHECKLIST line: a teacher's record counts as verified (like B-2's awards); management's check stays in B-4 (the certificate) |
| L3 | **"My Requests" panel** (idea): the student sees their requests and a rejection's reason | A separate approved look change; until then the student sees only the banner (and a B-7 notification later) |
| L4 | The demo request (043) points at `storage/uploads/proofs/demo-appointment-letter.pdf`, which can't be in git (`storage/uploads/` is ignored), so its View File says "That request does not exist." | Keep that answer for a missing file; the lead decides whether a tracked demo folder is wanted |
| L5 | Update numbers: B-6 has 041 on its branch, B-3 took 042 / 043 | Merge B-6 first (or renumber whichever comes second before its merge) |

## Built (2026-11-05)

| Part | What |
|---|---|
| Database | 042 `missing_record_requests` + `achievements.issued_by / proof_path / proof_name` · 043 demo: row 6 removed (duplicate of the live award), 1 pending request from Amaya (9) to Alex (3). Fresh install: 42 updates, no error |
| File box | `_file_box.php` + `file-box.js` (shared; the caller passes name, required, types, size); `c-field__file--sand` in `form-field.css` |
| Private files | `Upload::document()` (real type by content, size, random name, `storage/uploads/proofs/`), `exists()`, `send()` (nosniff, inline, the uploaded name), `forget()` also deletes a private file; `keepingUploads()` moved into `core/Controller.php` (B-2 unchanged: add + delete re-tested) |
| Models | `AchievementModel::add()` · `MissingRecordModel` (send, pendingOf, pendingFor, find, approve, reject) · `PeopleModel::classTeacherOfStudent()`, `classStudents()` |
| Timeline | `AchievementTimeline::forStudent($id, $books = null)`: the student page, the Child profile and People's pop-up (same output, checked) |
| Pages | student Achievements (real) · teacher Achievements: class students ("Class") + current members of their programmes ("Extracurricular", Record → B-2's "Add achievement" form; a student in both = "Class"), red ring = a pending request, View = the real timeline |
| Forms | Record (class teacher, proof optional) · missing-record request (student, Evidence required; the banner names the real class teacher) · Review: View File (`GET /teacher/achievements/file`), Approve & Record (one transaction), Reject + reason |
| Permissions | `config/permissions.php` 'achievements': record, request, review |
| Audit Log | ACHIEVEMENT_ADDED, MISSING_RECORD_SENT, MISSING_RECORD_APPROVED, MISSING_RECORD_REJECTED |

## Parked (log only, not built)

- The honours board picks (CHECKLIST's B-3 line: which achievements the office puts on the public landing page,
  `on_honours_board`): not in this round's scope — still open

- The teacher in charge → management verification chain (CHECKLIST's old B-3 line): every `achievements` row counts
  as verified; management's check stays in B-4 (the certificate)
- A programme's past members on the teacher's Achievements page (only current members show)
- "My Requests" panel (L3): the student sees no answer to their request yet (B-7 notifications could tell them)
- The student opening their own request's evidence: no page lists their requests, so the route would be dead code —
  add it with L3
- Where an achievement's own proof shows ("No file attached"): no B-3 page shows an achievement's proof; B-4's
  certificate page is the first one that will (`proof_name` null → "No file attached")
- The teacher in charge's Record opens B-2's "Add achievement" form with no participant chosen: that form can't
  pre-select one (no `?participant=`); they pick the student there

## Found — for the checklist (not fixed in B-3)

- `app/Mock/certificates.php`: missing records are assigned to subject teachers ("Mr. Silva (Science)"), while the
  pop-up says they go to the class teacher — example data, replaced by B-4
- The Record pop-up types "up to 10MB" in the view → replaced by the hint from config (B-3)
- Ticking `CHECKLIST.md` makes `team/pdf/AI-PACK-*.pdf` stale (LEAD.md: the lead rebuilds `team/pdf/`)
- `.claude/hooks/check-edit.sh` needs `jq`, which ONBOARDING doesn't list (also noted in PR #2)
- Checked locally on PHP 8.2 (XAMPP); GitHub's Rule check uses PHP 8.4
- The sand file box becomes a box modifier (`c-field__file--sand`) so it can sit in a non-sand form; the notice
  form's sky box stays styled through its form (`.c-fields--sky .c-field__file`) — two ways for one thing, to tidy
  later
- `_file_box.php` is general; B-12 notice attachments can reuse it
- `team/ONBOARDING.md`'s "What the project has so far" table and `HOW-IT-WORKS.md` §7 still list Achievements as
  example data (B-3 may only change the log and CHECKLIST.md in team/) — the lead updates them (and `team/pdf/`)
- Demo row 6 removed in 043 (it duplicated the live Top of the Grade, Term 1 award): Amaya's timeline still shows
  that award once, from her marks ("Top of Grade — Grade 10, Term 1", RecordBook::awards)
- Demo students STU-2026-0010 and 0011 are PENDING (never signed up), so they can't sign in to try the student side
- `postText()` doesn't check that text is valid UTF-8: a non-UTF-8 byte (only possible by hand, e.g. curl from a
  Windows shell; a browser on our UTF-8 pages always sends UTF-8) makes MySQL refuse the insert → the error page
  (500, nothing saved, the uploaded file deleted again by `keepingUploads()`). Project-wide (B-2 too) — a one-line
  `mb_check_encoding()` in `postText()` would turn it into "is required"
- The missing-record pop-up sits on the Character Certificate page (module `certificates`) but posts to an
  `achievements` route: with `achievements` switched off, the pop-up stays and its form gets a 404 — B-4 could hide
  the "Something missing?" card when `feature('achievements')` is off
- The student card tones are listed twice: `TermMarkController::TONES` and `config/certificates.php` 'tones'
  (B-3 reads the config one) — one list would do
- `app/Mock/achievements.php` is no longer read; the owner deletes it (`git rm`). Its 6 example photos are now unused
  (nothing else uses them, checked with grep): `public/assets/images/examples/face-01, 02, 03, 05, 08, 09 .jpg` —
  for the owner to delete by hand
- Testing left files in git-ignored folders: 4 test proofs in `storage/uploads/proofs/` and the error-log lines in
  `storage/logs/php_errors.log` (the test server ran with APP_DEBUG=false) — for the owner to delete by hand
