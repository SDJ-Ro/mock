# B-5 Feedback — Havindu's log

Branch `feature/b5-feedback` (from `dev`). Built 2026-11-06.

## Decisions (made while building — the lead can change any of them)

| # | Decision | Why |
|---|---|---|
| 1 | Update **045** creates `feedback` (not ALTER) | 016's table was dropped in 027 ("made again by B-5"); 041 = B-6, 042 / 043 = B-3, 044 = B-4 |
| 2 | The table and its demo rows are in one file (045) | The task said 045; 016 did the same. Other features split them (042 + 043) |
| 3 | Kinds stay **positive / constructive / negative** (not praise / concern) | The screen says Positive / Constructive / Negative, and the cards' CSS uses those keys. Rule: names match the screen |
| 4 | Read marks = two columns, `student_read_at` + `parent_read_at` (the first open) | Simple; enough for "read / unread" per reader. A second parent opening it changes nothing |
| 5 | `subject` saved as text (the name), not an id | Old feedback keeps its subject when the curriculum changes (a subject without marks can be deleted) |
| 6 | Subject choices = the teacher's class's subjects now (`TermMarkModel::classOf` + `subjectsFor`) + "General Conduct" (`config/feedback.php` 'general') | Real list, nothing typed in the controller |
| 7 | Title stays required (the form's `*`), max 150; message max 2000 (`config/feedback.php` 'lengths' = the columns) | The card needs a heading |
| 8 | Too long → refused with a message (postText reads one more character than allowed) | Not cut quietly |
| 9 | The parent sees the child chosen in the sidebar ("Viewing"); a parent may mark read any feedback of any of their children (audience parent / both) | Like the rest of the parent portal |
| 10 | Marking read: a hidden form in an unread card, sent once by feedback-card.js (fetch, Accept: JSON) | CSRF through the normal token; no new component |
| 11 | `FeedbackModel::students()` has its own query (with the class name) | `PeopleModel::classStudents()` exists only on the B-3 branch; never merge branches |
| 12 | Student / teacher foreign keys `ON DELETE RESTRICT` | Records are never deleted (WHY.md) |
| 13 | A teacher without a class this year: no "+ New Feedback" button (their page still lists what they wrote) | The student dropdown would be empty |
| 14 | A database error → the usual 500 page (`errors/error.php`), details in `storage/logs/php_errors.log` | The project's one error path |

## Look changes for the lead (in the pull request)

- New page `student/feedback.php` (the parent's board) + a "Feedback" sidebar link for students
- New Feedback: a new **Audience** field (Student & Parent / Student only / Parent only); the Subject label is
  `word('subject')`; the description now says "to a student, their parents, or both"
- The teacher's card: the example data's "Parent: … | Parent of …" line → "To Nethmi Perera · Student: Read · Parent: Unread"
- The card's second line shows the teacher's name • the subject (the example data showed a role, "Mathematics Teacher")

## Parked / for the checklist

- ☐ B-7: "Feedback written" → the student and / or their parents, by audience
- ☐ The reader sees no "new" mark on an unread card (only the teacher sees read / unread) — add one if the lead wants
- ☐ After B-3 is merged: `FeedbackModel::students()` and `PeopleModel::classStudents()` are close; one could use the
  other (the feedback one also needs the class name)
- ☐ `team/HOW-IT-WORKS.md` §7, `ONBOARDING.md` and `WHY.md` still list Feedback as example data (not my files)
- ☐ The teacher's subtitle ("View parent feedback…") and the content placeholder were written for the example data
  (parent inquiries); they still read fine, so left as they are
- ☐ The old checklist lines (Child profile: "Feedback, Complaints follow the chosen child"; section 10: "backend
  later: real feedback / complaints") are shared with B-6: tick them after both are in
- ☐ An unknown address with Accept: JSON (e.g. `/teacher/feedback/read`, no such route) gets the HTML 404 page,
  not JSON — the Router's, project-wide

## rules-reviewer (2026-11-06)

Fixed: the audience rule is in one place (`FeedbackModel::SEES`, used by the lists and `find()`) · the `can()`
refusal says "You may not write feedback." · the subject length is in `config/feedback.php` · the card's classes
are `c-feedback-card__to` / `__title` (feedback.css) · `.catch()` on the read request · feedback-banner.js's comment
example is now another form · one model in `read()` · a long comment wrapped.
Kept: the demo rows inside 045 (decision 2; the task asked for 045).

## Notes from testing

- The demo data has no active teacher who isn't a class teacher, so the page without the New Feedback button was
  not opened (the save refusal for one was tested through Ishara, class teacher of 9-B, writing to 10-A).
- `bin/check.sh` is run by Havindu.
