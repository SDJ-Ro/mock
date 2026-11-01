# Havindu — your tasks

Read `team/ONBOARDING.md` first: it has the overview of what the project has so far.
Every code path below is inside `code_lecole/`.

Each task: branch from `dev` → `/next-feature` plan → the lead approves → small commits → pull request into `dev`.
The full decisions are in `team/CHECKLIST.md` → "Backends left to connect".

## 1. B-3 Achievements — first (B-4 Certificates waits for it)
- **What:** the teacher records an achievement (student(s), level, result, evidence upload); verification (teacher in
  charge → management); the student's timeline + number cards; a student's "missing record" request; the honours
  board picks.
- **Pages now on example data:** `teacher/achievements.php`, `student/achievements.php` ← `AchievementController`
  reads `app/Mock/achievements.php` (the exact shape your real data must have).
- **Reuse:** the `achievements` + `achievement_students` tables and `AchievementModel` already exist — club awards
  are saved in them (`saveAward()` in `ExtracurricularController` is a working example: participants, photos, Audit
  Log). `Services/AchievementTimeline` already builds the timeline from real rows.
- **Open:** the Achievements pages' own records need verification; the student's own Achievements page doesn't show
  club awards yet (People's pop-up and the Child profile do).
- **Tell people (B-7 list):** an award is recorded → every participant + their parents.

## 2. B-5 Feedback
- **What:** the class teacher writes to a student: audience **Student / Parent / Both** (student-only is invisible to
  parents), kinds (praise / constructive / concern), one-way, read marks.
- **Pages now on example data:** `teacher/feedback.php`, `parent/feedback.php` ← `FeedbackController` reads
  `app/Mock/feedback.php`. Components: `_feedback_board`, `_feedback_card`, `_new_feedback_pop_up`.
- **Needs:** a new `feedback` table (update file), `FeedbackModel`, a save route (POST), who may write
  (`config/permissions.php`), the audience rule.
- **Tell people:** feedback written → the student and/or their parents (by audience).

## 3. B-9 Year-end — last (the biggest)
- **What:** promote every active student one grade (class mapping chosen by management), the final grade → leaving,
  class teachers reassigned, history untouched.
- **Ideas to discuss with the lead first:** a student with too many low marks is **held back** (stays in the same
  grade next year); management / admin can **allow promotion** anyway (button + reason, Audit Log). Thresholds and the
  on / off switch in `config/white_label/rules.php` (`pass_mark` is already there: 35). The record book then shows
  "Grade 7 (Repeat)" and the last term's report shows "Promoted to Grade 8" / "Repeats Grade 7".
- **Reuse:** `Assignments` is the only writer of the per-year tables; `SchoolYear` (current = newest row);
  `RecordBook` already labels a repeat.
- **Needs first:** the School Year dates (B-13).

## Practice (before B-3)

Make one small change on a branch `practice/havindu` (for example: show 5 upcoming events on the dashboard instead
of 3 — `config/calendar.php`, `upcoming_count`), open a **draft** pull request into `dev`, and ask the lead to review
it. It is closed without merging — it's only to practise the whole Git flow once.

## Ask the lead

Anything outside your tasks · any decision marked ☐ or "discuss" in the checklist · a new table or column ·
a change to how a page looks.
