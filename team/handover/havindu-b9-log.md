# B-9 Year-end — Havindu's log

Branch `feature/b9-year-end` (from `dev`). Built 2026-11-06.

## Decisions (made while building — the lead can change any of them)

| # | Decision | Why |
|---|---|---|
| 1 | **No update file** (046 not used) | Nothing new to store: the years, classes and teacher tables exist (028). 046 stays free |
| 2 | **A leaver = a final-grade student with no class in the new year** — no leaving column, the account stays as it is | B-4 (`044_graduating_certificates.sql`) keeps no leaving column per student: it keeps one `graduation_batches` row per school year (the final grade + its date, set by the admin). That table isn't in `dev`, so B-9 doesn't write to it. The account stays signed-in-able, so the certificate and the record book still open |
| 3 | The final grade = `config('white_label.academic.grade_numbers.last')` (13) | The same as B-4 |
| 4 | Its own page `/{admin\|management}/academic/year-end`, a **Year-End** button in the Academic page's header | The checklist puts year settings on the Academic page (B-13 "School Year section"); B-9's note says "the year-end page". Its own `YearEndController` (B-4 changes `AcademicController`) |
| 5 | Who: `permissions.academic.year_end` = admin, management | The task |
| 6 | Mapping: only classes with active students need a choice; the next grade's class with the same section is suggested (10-A → 11-A) | Fewer clicks; an empty class has nobody to move |
| 7 | The preview is a GET form (`?preview=1&to_9=11`), the run a POST with the same values | The preview changes nothing; the run checks everything again |
| 8 | Class size: `classes.capacity` (the limit People and Academic already use), not a new rule | One limit in one place |
| 9 | "Already run": the run form carries the page's year; another year → "Year-end for 2026 has already been run." With term marks on, the new year can't end until it has terms and its last term is published | The current year is always the newest row, so "next year exists" can't be seen any other way |
| 10 | Teachers carried over **unchanged**, each in the same class / subject / club (`INSERT … SELECT` for the new year) | The task: management changes them later on the usual pages |
| 11 | "Active" = not switched off (`activation_status <> 'INACTIVE'`): a PENDING student (added, not activated yet) moves up | The rule everywhere else (People's "on") |
| 12 | `startYear` is inside `Assignments::yearEnd()` (the `school_years` row) | "All writes to the yearly tables go through Assignments"; the student placings need the new year to be the current one |
| 13 | Fixed `DashboardController`: `fn (array $piece)` → `fn (?array $piece)` | Outside B-9's own files, but every dashboard crashed after a year-end (a new year has no record book yet → the marks chart is null; the comment above says null is allowed). Remove it from the pull request if the lead wants it separate |

## Held back — PARKED (designed, not built; the owner decides)

- **Rule:** a student with **more than N {subjects} under `pass_mark`** (35) in the **latest published {term}** of the
  year (AB not counted, the same count as Academic Watch) is **held back**: next year they stay in the same grade.
- **Where it goes:** `config/white_label/rules.php`:
  `'held_back' => ['on' => false, 'subjects' => 3]` (the threshold and the on / off switch).
- **Preview:** a third kind of line: "Nethmi Perera stays in Grade 10 (4 {subjects} under 35)". Each held-back student
  gets a dropdown of their own grade's classes (the same section suggested).
- **Allow promotion:** management / admin presses "Allow promotion" next to the student, with a reason (required,
  max 300). That is saved as an Audit Log line (`YEAR_END_PROMOTION_ALLOWED`) and passed with the run. It needs no
  table: the decision lives only until the run.
- **Run:** a held-back student is placed in a class of the **same grade** (`placeStudent`). Their club memberships stay.
- **RecordBook** already labels the second book of the same grade "Grade 10 (Repeat)" (`RecordBook::forStudent`).
  The last term's report would show "Promoted to Grade 11" / "Repeats Grade 10" (new, in the print view).
- **Tests to add:** threshold edges (N and N + 1), AB not counted, switch off → nobody held back, allowed with a reason →
  moves up, no reason → refused.

## Look changes for the lead

- The Academic page (admin, management): a **Year-End** button in the header (the sky pill, a graduation cap)
- The new Year-End page: two panels ("1 · Next Year's Classes" with a row per class, "2 · Preview"); new CSS
  `components/year-end.css`, `layout/year-end-grid.css`

## Parked / for the checklist

- ☐ B-7: "leaving date set" → the class teacher, teachers in charge, management
- ☐ B-13: the new year gets the next name (2026 → 2027) and **no terms**: until B-13 lets someone set its dates,
  Term Marks shows none for it and the next year-end is refused ("2027 has no terms yet, so it can't end.")
- ☐ After B-4 is merged: show `graduation_batches.graduation_date` on the preview's leavers line, and maybe refuse a
  year-end before that date
- ☐ Two people pressing Run at the same moment: the second fails on the new year's key (the 500 page, nothing saved).
  Fine for once a year
- ☐ Subject teachers are copied as they are, also for a subject the next grade's curriculum doesn't have (they belong
  to the same class, which keeps its grade)
- ☐ `team/HOW-IT-WORKS.md` §9 says "Year-end (B-9) adds the next one" — now true; nothing to change (not my file)
- ☐ (seen in the shared PHP log, not reproduced, `dev`'s code) `_people_directory.php` line 20: "Undefined array key
  status" warnings

## rules-reviewer (2026-11-06)

Fixed:
- **"Run again" with term marks off:** the "the year has no terms" check now applies to every school. A new year
  has none until B-13, so the same year-end can't run twice.
- **Switched-off students:** the preview warns how many there are ("not moved, so no class next year").
- **Year-End button:** it is `_page_header`'s own button with a new optional `'icon'` (the "+" stays the default),
  not a copied link.
- **Teacher counts:** read once.
- **A second run at the same moment:** inside the transaction, `SELECT MAX(year) … FOR UPDATE` checks the year is
  still the one shown. Otherwise "Year-end for 2026 has already been run." with nothing saved (tested: null, years
  unchanged).
- **A missing choice:** `?? ''`.
- **Question pop-up:** removed the repeated `data-question-tone`, because danger is already the default.

Kept: the DashboardController fix (decision 13).

## Notes from testing

- Your local database was backed up first (`database/l_ecole_backup_before_b9.sql`, git-ignored). No year-end was run
  on it: every run was on a throw-away database, dropped afterwards.
- The demo data has no final-grade class, so the throw-away database got one (13-A) with Dinuk in it (a Football member
  and team captain, with a request waiting) to test the leavers.
- `bin/check.sh` is run by Havindu.
