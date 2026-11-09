# Checklist

✅ done and checked · ☐ to do · ⏸ on hold
Newest findings go at the end of their section. The owner picks what we do next.

## Build order (2026-10-21)
Feature by feature; inside a feature, the inner pieces first, each measured inside its REAL page (no test pages).
A piece used by a later feature is built in the first feature that needs it. The "When building it" lists further
down say what to fix in each one (find the feature by its NAME there: those sections have older numbers).

**Every feature, the same 6 steps**
1. `next-feature` report → owner approves
2. Pieces, inner first (each = its PHP + own CSS + own JS; MVC's pop-ups → `_pop_up.php` / `askQuestion()`)
3. Wire each page: route (`routes/web.php`, behind its `feature()`) → controller method (`requireRole`, `page()`) →
   model (from Clean/MyTry) → view in `app/Views/{role}/` (`page_top`, `_page_header`, its pieces, `page_bottom`)
4. Measure every role's page next to MVC (8040 vs 8080)
5. `bin/check.sh` = 0 errors + curl refusal tests (no token, `field[]=x`, wrong role, signed out)
6. Tick here; new findings → the next features' "When building it"

**0. Shared pieces** (used by several features; each built in the first feature that needs it, listed there)
- `_datepicker` (Notice) · `_form_card` (Extracurricular, Certificates) · `_export_pdf_button` (Academic) ·
  `_image_picker` (Extracurricular) · reason box for `askQuestion()` = MVC's `_reject_modal` (Approvals)
- Already built: dropdown, pop-up, question pop-up, page header, metric card, calendar, charts, sidebar

**1. Notice Board** (also makes the dashboards' Unread Notices real)
- Pieces: `_datepicker` → `_notice_card` → `_add_notice_page`
- Pages: student/notice · parent/notice-board · teacher/notice · management/notice · admin/notice
  (students / parents read; teacher, management, admin also post + delete)

**2. Profile** ✅ (2026-10-22)
- Pieces: `_info_card` → `_profile_hero` → `_profile_page`
- Pages: student/profile · parent/profile · teacher/profile · management/profile · admin/profile
- Moved (2026-10-22): `_profile_information_tab` + `_profile_activities_tab` are NOT on My Profile (MVC: only the
  People pop-up and child profile) → built with 5. People, reusing `_info_card`

**3. Achievements** ✅ (2026-10-22, front end on MVC's example data — see "6. Achievements" below)
- Pieces: `_achievements_timeline` → `_teacher_achievement_card` (renamed `_student_card` with Academic B) →
  `_record_achievement_pop_up` (+ shared `_feedback_banner`)
- Pages: student/achievements (metric cards + timeline) · teacher/achievements (cards + record pop-up)

**4. Academic** (owner's choices 2026-10-22: stages may not overlap · save → reload · chart filters reload the page ·
  curriculum words via `word('grade')` / `plural('grade')`). Built in 3 stages, each checked next to MVC:
- A ✅ student/academic (2026-10-22): `_export_pdf_button` → `_digital_record_book` → `_academic_section`
- B ✅ teacher/term_marks (2026-10-22): `_term_marks_panel` (uses `_student_card` from 3, renamed)
- C ✅ management/academic · admin/academic (2026-10-26) (bar chart + calendar + grades): `_grade_teacher_hover` → `_grade_class_row` →
  `_grade_class_editor` → `_grade_card` · `_curriculum_card` · `_add_grade_modal`

**5. People** (needs 2 + 4: the profile pop-up shows the profile tabs and `_academic_section`)
- Pieces: `_profile_information_tab` → `_profile_activities_tab` (moved here from 2; both reuse `_info_card`) →
  `_people_directory_row` → `_parent_picker` → `_deactivate_parent_dialog` → `_teacher_handover_modal` →
  `_person_profile_modal` → `_people_directory_panel` · `_admission_guardian` → `_add_person_page`
- Pages: management/people · admin/people · teacher/students
- Owner 2026-10-26 — 5 stages, each its own next-feature report → build → MVC check → curl → tick:
  A ✅ base (data, config, model) · B ✅ directory · C ✅ profile pop-up · D ✅ edit + switch off + handover · E ✅ add
  person. Decisions: the server filters (GET, page reload) · the pop-up fetches its HTML from the server · Add Person
  is its own page address · photo upload in (Clean's Upload.php) · no saved drafts · MVC's extra columns stored (024)

**6. Approvals** ✅ (2026-10-27) (owner 2026-10-27: MVC's admin/verify page is REMOVED — each thing is approved where it lives:
  teachers' notices on the admin's Notice Board ✅, new clubs on the admin's Extracurricular page ✅ (example data, 10); teacher
  accounts need no approval)
- Pieces: reason box ✅ (built with the notice approval: an `askQuestion` extra `<template>` with a `required` field) →
  `_approval_card` (only for parent/approvals)
- Pages: parent/approvals

**7. Feedback & complaints** ✅ (2026-10-27, MVC's example data — owner: keep it mock, backend later)
- Pieces: `_feedback_card` · `_complaint_card`
- Pages: teacher/feedback · parent/feedback · parent/complaints · management/complaints

**8. Audit log** ✅ (2026-10-27)
- Pieces: `_audit_card` · Page: admin/audit

**9. Character certificates** ✅ (2026-10-27, MVC's example data — owner: keep it mock, backend later; PDF by Dompdf)
- Pieces: `_certificate_sheet` (screen + PDF) · `_certificate_card` · `_certificate_request_card` · `_activity_list` ·
  `_evidence_preview_pop_up` (shared with Record Achievement) · the three pop-ups
- Pages: student/certificates · management/character_certificate

**10. Extracurricular** ✅ (2026-10-27, MVC's example data — owner: mock, no backend connected yet)
- Pieces: `_extracurricular_card` (+ `_pick_menu`) · `_extracurricular_list` · `_extracurricular_hero` + `_programme_pop_up`
  (+ `_staff_fields`, `_image_picker`) · `_extracurricular_awards_panel` · `_award_page` · `_staff_card` + `_staff_pop_up` ·
  `_extracurricular_schedule_panel` · `_squad_roster` · `_club_notice_board` · `_enrolment_panel` + `_join_request_card` ·
  `_teams_panel` + `_team_card` + `_team_pop_up` + `_roster_row` · `_danger_zone` · `_programme_view` · `_reason_template`
- Pages: student/extracurricular · teacher/extracurricular · management/extracurricular · admin/extracurricular
- ☑ The dashboards' donut charts and "Sports & Clubs Active" stay example data until the backend — real since B-2 2e
  (ExtracurricularDashboard)

**11. Child profile** ✅ (2026-10-28) — parent/child-profile + the "Viewing" child switch
- The switch (owner): a parent sees one child at a time and the whole parent portal follows it. The chosen child is
  kept in the session (`Controller::requireRole()` → `$user['child']`, the first child by default; a parent with one
  child sees no switch). Sidebar "Viewing ▾" (`_pick_menu` 'child', a real POST form) → POST /parent/child (only one of
  their own children; the same message for any other ID). Follows it: the Child profile, Approvals (that child's
  requests), the Notice Board + the dashboard's calendar and Unread Notices (`Membership::groupsOf()` treats the parent
  as that child). Example-data pages (Feedback, Complaints, the dashboard's cards / chart) show the same examples for
  every child until their backends.
- The page (`ChildProfileController`, extends ExtracurricularController): the child's header (`_person_header`, page
  version) + our tabs as links (`?tab=`; a tab whose module is switched off is hidden):
  Information = People's profile sections (`_person_sections`, view only — real) · Academics = `_academic_section`
  (+ Export PDF: /parent/child-profile/print, `AcademicController::recordPrint()` for both roles) · Sports & Clubs =
  the student's Extracurricular list + programme page (`_student_programme_view`; no Accept / Reject — owner: that is
  Approvals' job) · Achievements = `AchievementTimeline::metrics()` cards + the timeline (the child's real ones).
- Reuse made for it: `_person_header` + `_person_sections` (out of `_person_profile`) · `_student_programme_view` (out of
  student/extracurricular) · `App\Services\Programmes` (Extracurricular's example data, out of its controller) ·
  `AchievementTimeline::metrics()` (out of AchievementController) · `PeopleModel::childrenOf()` (replaces
  `childFirstNames()`) · `url_with()` (helpers) · `PeopleController::profileData()` public (view only for a parent).
- Removed: MVC's unused copies profile-modal.css (1,189), profile-modal.js (2,291), parent-child-profile.css (296)
  and .js (103).
- Checked: every tab, a club's page and achievement, the switch (from Approvals: stays there, Amaya → Sasha; the
  Notice Board 3 → 2 notices), the print (the chosen child's name). curl: another family's child / an unknown ID /
  `choice[]` → refused, no token → refused, a student / signed out → refused, `tab=zzz` / `tab[]` / `id[]` → the
  Information tab, a waiting programme / unknown or 'new' award → back. People's pop-up unchanged (header, ×, sections,
  Edit Profile). Spacing next to MVC: header → tabs → tab 20px, cards → timeline 24px, search → cards 28px.
- Different from MVC (decided): one child chosen in the sidebar (MVC: a fixed example child) · real details · our tabs
  (D2) · no Accept / Reject on club cards · the Sports & Clubs tab has no extra "Sports & Clubs" heading or general
  notice board under the cards (each club's page has its notices)
- ☐ backend later: marks, clubs, the dashboard's cards / chart, Feedback, Complaints follow the chosen child when their
  data is real (they already ask for it: nothing else to change)
- ✅ Tabs across the page (2026-10-28, owner chose A): the tab pill was stretched by the column layout → a full-width
  white bar with the 4 tabs bunched on the left. New `c-tabs--wide` (tabs.css): each tab an equal share, name centred
  (MVC's layout, our look). Only the Child profile uses it; the other 5 tab rows unchanged. Checked: tabs line up with
  the header (288 → 1406), 4 × 275px; at 900px wide 4 × 138px, one line each; check.sh 0 errors
- ✅ Permissions in ONE place (2026-10-28, audit Part 2 #5 — "D", owner chose A: one central file). The sorting rule
  (top of `config/permissions.php`): a yes / no about a role being ALLOWED → permissions.php; which options, fields,
  colours, addresses → the feature's own config. Moved in: `posts.any_group` (new: whole school / any group — was
  `role === 'teacher'` ×7 in NoticeController, CalendarModel, CalendarFeed, _event_editor), `posts.manage_others`
  (was posting.php), `notices.wait_for_approval` (was notices.php `needs_approval`), `extracurricular.join` (new:
  Interest + hidden coach phone). Stayed in feature configs (not yes / no): `notices.unread_card` (was
  `['student', 'parent']` in DashboardController), `people.national_id_required` / `national_id_or_passport` (were
  typed in PersonFields). Models ask the list with the user they get (no session in a model). Extracurricular views
  get flags (`$can` manage / approve / join, `$runsProgramme`) instead of `$currentRole` / `can()`. check.sh section 3:
  ✗ on a view deciding by role name, ✗ on a typed role list (`$person['role']` — data — still allowed; both rules
  tested on sample lines). Checked: php -l, check.sh 0 errors; curl as all 5 roles — dashboards' Unread card
  (student, parent only), calendar pop-up (teacher: "Select Your Classes", no grades), notice form (teacher's
  "waits for approval" banner only), Extracurricular list (create / Change: office; Accept: admin; Interest + hidden
  phone: student; parent's tab: none), programme page (Back: not on the teacher's; Enrollment: teacher; Danger:
  admin), NIC rule per role; attacks: teacher's school-wide notice → refused, teacher / management deleting the
  admin's notice → refused (notice kept); edit links admin 5 / management 4 / teacher 2 (own) — as before
- ☐ LATER (owner 2026-10-28, audit Part 2 #7 / #8 — "E"): controllers must not borrow from controllers.
  `ChildProfileController extends ExtracurricularController` + `new PeopleController()->profileData()` →
  `profileData()` + its helpers into `Services/PersonProfile`, the programme page into `Services/Programmes`,
  ChildProfileController `extends Controller`. Do it with the Extracurricular backend. Same time as the marks backend:
  `AcademicController`'s record book pages → `RecordBookController`
- ☐ Owner to decide: the People profile pop-up's tabs (`c-tabs--sub`) sit bunched left in a full-width well too —
  give them equal widths so the pop-up matches the Child profile?
- ☐ Record book example data (seen 2026-10-28): a Grade 10 child is offered Grade 6–11 (a grade not reached yet) and
  every grade is dated 2024 → show Grade 6 up to the current grade, each dated by its year (`RecordBook` + the mock).
  Backend: a `class_enrolments` (student, year, class) table — see the audit, Part 3

## Backends left to connect (owner, 2026-10-29 — discussed one by one: the owner explains the logic, Claude adds
## logic, validations and rules; then next-feature report → build → measure → attack → tick)
Real already: sign in / up / forgot, My Profile, People, Academic structure, Notice board, Calendar, Parent
approvals, Audit log, Child profile (information, achievements), honours board.
- ☑ B-1 **Marks + record book — built 2026-10-29.** Updates 030 (`school_terms`, `term_publications`, mark
  0–100 or empty = AB with a CHECK, subject RESTRICT, `stage_subjects.retired_on`) and 031 (demo: 2022–2026, four
  students, one AB). `config/white_label/rules.php` + `rule()`; `grade_letters` / `term_starts` deleted (nothing read
  them; term dates live per year in `school_terms`). Models `SchoolYear`, `TermMarkModel`; services `TermResults`
  (all maths, live), `RecordBook` (per school year, "(Repeat)", `awards()`, `shown()`), `MarksDashboard`.
  `TermMarkController` (page, save JSON, publish), routes `/teacher/marks/save`, `/publish`. Views: Term Marks (bar
  with Term · status tag · Publish Term; empty box when not a class teacher; AB boxes), record book + academic section
  + print keyed by year (`data-year`), People pop-up / Child profile (`book`), dashboards' marks cards + score charts,
  Academic chart (legend "Highest Mark", real). Curriculum: a subject with marks is retired / replaced, never deleted
  or renamed. A class move is refused after the year's first publish. Mocks term_marks / academic /
  class_performance removed. Tested: maths (ties, AB, awards on real data), 11 refusal paths by curl, save → count →
  publish gate → visible after publish → correction logged, retire / rename / rename back, no-class page, browser
  (record book switching, panel save with AB, Publish question), 51 pages, 52 POST routes × 4 attacks, fresh install
  (36 tables, 31 updates). Behaviour changes from MVC: the book opens on the newest year + term (was the first);
  the Academic chart's second bar is the highest mark (MVC's label said "Highest Average").
  - ☐ The face photos only `Mock/term_marks.php` used may now be unused (images/examples) → the image clean-up item
  - ☐ The record book shows a repeat as a second "Grade 7 (Repeat)" section — first real case comes with B-9
  - ☐ "Marks published" notification → B-7 (uses `term_publications.published_at`)
  - ☐ Dashboard: the teacher's chart and card are for the class they are class teacher of; a subject-only teacher
    sees "—" (B-10 can add their subjects' averages)
- (the original B-1 note) — teacher's Term Marks saves; the record book (Grade 6 → now), totals, average,
  position, highest in class, teacher's comment; trend charts; marks change log. Has: `marks`, `term_comments`.
  Needs: `class_enrolments` (student, year, class), `mark_changes`, marks RESTRICT + CHECK 0–100. Example data now:
  Mock/term_marks, academic, class_performance. Everything else that shows marks reads this → first
  - **Decided (owner, 2026-10-29):** the class teacher enters and corrects every subject of their class · a mark is
    0–100 or AB (left out of total / average) · students and parents see a term only after the class teacher
    presses **Publish Term** (every student has every subject + a comment); no unpublish; later edits allowed,
    logged in the Audit Log ("10-A · Nethmi · Science T2: 63 → 68") · totals, averages (1 decimal), positions
    (ties share and skip: 1, 2, 2, 4), highest in class, grade average and automatic achievements are worked out
    live, never stored · Commended (average ≥ 80, on / off), Top of Class (on / off), Top of Grade (on / off; only
    when every class of the grade has published); no award with any AB · rules in `config/white_label/rules.php`,
    read through one function (B-13 moves them to the Academic page) · only started terms of the current year can
    be typed; earlier years are read only · a mid-year joiner needs only terms that started after admission; a
    leaver counts in the terms they sat; accounts are never deleted · a class move is refused after the year's
    first term is published · each term shows the teacher who wrote its comment · all-AB → "—" · a subject with
    marks is never deleted or renamed: removed = retired (stays on old reports), renamed = a new one + the old one
    retired · old reports read the marks, not today's curriculum · one record-book section per school year; a
    repeated grade is labelled "Grade 7 (Repeat)" · no pass mark shown (B-9 adds it) · `grade_letters` deleted
    (nobody reads it) · term start + end dates per year in `school_terms` (academic.php's `term_starts` = the
    defaults for a new year) · "marks published" notification → B-7 (the publication's time is saved for it)
  - **Publish bar:** search · Term ▾ · status tag (DRAFT · 3 of 8 complete / PUBLISHED · date) · Publish Term
    (question pop-up first; refuses naming who is missing)
- ☑ **2a rename — built 2026-11-01.** Update 032: `activities` → `extracurriculars`, `activity_members` →
  `extracurricular_members`, every `activity_id` → `extracurricular_id` (achievements, notices, teachers_in_charge),
  event scope `'activity'` → `'extracurricular'` (3 events moved), foreign keys / indexes renamed, view recreated.
  Real code: Membership, CalendarModel, NoticeModel, ExtracurricularModel, PeopleModel (`extracurriculars()`,
  `extracurricularChoices()`), ClassModel, Assignments; Calendar / Notice / People / Approval controllers;
  CalendarFeed, PersonDetails; the event editor + `event-editor.js`, notice form + card, approval card, People
  directory + table, teacher workload; form / address names (`extracurricular`, `extracurriculars[]`,
  `?extracurricular=`). Screen words unchanged ("Activity" filter, "Extra-Curricular Activities", "Programme").
  Example-data code keeps "programme" until 2b–2e rewrite it (one key in `Programmes::notice()` changed: it feeds
  the shared notice card). Tested: 51 pages, 52 POST routes × 4 attacks (data unchanged), fresh install (36 tables,
  32 updates), and by hand: club events reach only members · a parent's approval (with the child switch) saved as
  member + logged · a club notice reaches only its member with its "Chess Club" tag · a teacher adds an event for
  his own club, is refused for another · People filter by club + the club tag · Assigned Programmes · handover rows ·
  Academic workload. Data restored after each test.
- ☑ **2b Programmes — built 2026-11-01.** Updates 033 (category, cover, coach specialty / email / phone, the two
  limits, uses age groups, open to, lowest grade, archived_at / by) + 034 (small demo: Drama Society keeps no teacher
  in charge or coach). `rule('age_group_limit')` = 300 (a blank limit uses it); `config/extracurricular.php`:
  default cover, cover 5 MB. `ExtracurricularModel` (all, find, runBy, statesOf, memberCount, nameTaken, add, update,
  approve, remove, archive, restore); services `Extracurriculars` (real cards + page) and `Programmes` (only the
  example parts left: squad, teams, join requests, awards — 2c–2e); `ExtracurricularController` save (one action, a
  `part` per form: new · programme · teacher · coach · details), review, archive (+ restore); routes for the four
  roles. Rules: management's new programme waits (greyed), the admin's is approved; reject = removed + the audit log
  keeps why (the name is free again); archive / restore by the admin (Archived tab; the page says read only);
  the teacher in charge edits only description, cover, schedule, venue of their own; who can join + limits by
  management / admin; the teacher in charge is a pick (email, phone, subjects from People); coach initials only;
  nobody assigned → a person icon. The teacher's page: one programme, a list of theirs, or "You are not in charge
  of any programme this year." The programme calendar shows only that club's events (`only`, also after a save).
  The Notice Board shows the club's real notices. Shared changes: `_pick_menu` (field + hidden), `_danger_zone`
  (real action or example message), `_staff_card` (initials / icon), `CalendarFeed::config($user, $only)`.
  Found while testing and fixed: a missing setting crashed a save (now left as it was) · PHP's own 2 MB upload limit
  beat our 5 MB → README starts PHP with `-d upload_max_filesize=6M -d post_max_size=8M` (**owner: restart the
  server with the new README command**), a too-big form now says "That file is too big" (not "session expired"),
  and PHP's size errors name the real limit (`ini_bytes()`) · a replaced cover's old file is deleted.
  Tested: 16 page states (every role, waiting, archived, no teacher, not a member / a member), 30 save / refusal
  paths with their messages, cover uploads (good, replace, 5.5 MB, 9 MB, SVG, PHP named .jpg) on both PHP limits,
  the browser (list, programme page, Edit pop-up saved for real), 51 pages, 64 POST routes × 4 attacks (data
  unchanged), fresh install (36 tables, 34 updates). Data restored after each test.
  - ☐ The Status card shows one "Membership" line (Enrolled / Waiting… / Not Enrolled) until 2c stores the joined
    date (MVC showed "Enrolled Since" + "Enrollment Date")
  - ☑ Interest still only says so (2c) · rosters / teams / join requests / awards are example data (2c–2e) — done
    in 2c / 2d; only the awards wait for 2e
- ☑ **2c Joining — built 2026-11-01.** Update 035: `extracurricular_members` = the CURRENT row (+ joined_on,
  declined_by parent / teacher, teacher_note); `extracurricular_member_history` (every ended membership: joined,
  left, why removed / archived / left_school, by whom); `academic_watch_lifts`. 036: small demo (Dinuk a Football
  member + waits for Swimming's teacher). `rules.php`: `age_cutoff` 01-31, `pass_mark` 35, `academic_watch` (on,
  more than 3); `config/extracurricular.php` age groups as [name => under age]. Services `JoinRules` (age group,
  whyNot, isFull — one place) and `AcademicWatch` (latest published term, any year; lift lasts until a newer one).
  Model: statesOf (current rows), people (members / requests), memberBirthdays, ask, decide, endMemberships, watch
  lifts. Controller: interest, decide (teacher in charge or management / admin; Accept rechecks room), remove (no
  reason, owner Q2), watch (permissions `extracurricular.watch`). Owner's choices: Q1 B only members count toward the
  limit · Q2 Remove without a reason · Q3 Lift Lock line in People's pop-up · Q4 latest published term · Q5 no
  "school adds" screen · Q6 a teacher's no works like a parent's. Pages: card shows why not (greyed + lock), a
  person's no ("Not accepted: …", "A parent said no: …"), Academic Watch tag on memberships; Enrollment Processing
  real (also for management / admin: a programme may have no teacher); "Unassigned Students" = real members + ×
  Remove; the student's roster = members only ("The roster shows once you join."); Status card + Joined date;
  parent's Approvals card shows the teacher's no; archiving / switching a student off end memberships (history).
  Found and fixed: the panel was unstyled on management / admin pages (its CSS only on the teacher's) · a real index
  number pushed the join card's date under it (now wraps) · `memberCount()` became dead → removed.
  Tested: the whole flow by curl (Interest → parent yes → teacher reject with reason → ask again → accept → roster
  "You" → remove → history), every rule (Full per age group, intake closed, boys / girls only, from Grade 11,
  Academic Watch + lift / lock), age groups on the cut-off day, archive + switch-off history, refusals (wrong
  teacher, wrong role, asking twice, a waiting programme, arrays, no reason), browser (join card, member row,
  pop-up line, Lift Lock for real), 51 pages, 78 POST routes × 4 attacks, fresh install (38 tables, 36 updates).
  Data restored after each test.
  - ☐ Swimming's demo intake is closed (update 011, "try-outs only"): so it shows "Intake closed" — real data, fine
  - ☑ Positions and teams are example data until 2d; "Assign" still only says so — real in 2d
- ☑ **2d Teams & positions — built 2026-11-01.** Update 037: `team_limit` gone (owner: "remove it wherever it is" —
  replaces 2b's "both fields": the Edit / New pop-up keeps only the age-group limit) · positions leave the membership
  rows · `teams` (name, age group or none, cover), `team_members` (position, since), `team_member_history` (team
  name kept, position, from → to). 038: demo — Football's "Under 15 Squad", Dinuk Captain since February.
  `TeamModel` (ofProgramme, find, nameTaken, forAgeGroup, save = team + whole roster in one transaction, assign,
  delete, leaveAll). Owner's choices: **Q1 C** a member only in a team of their own age group (worked out on 31 Jan;
  a team with no age group takes any) · **Q2 A** the roster row picks a member from a dropdown + a free-text
  position, no grade box · **Q3 A** the student sees their team(s), else the members list · **Q4** only the class
  teacher sees medical notes in Student Details (not subject teachers); the teacher in charge (and management /
  admin) gets a **club view** from Teams & Roster: name, photo, grade, class, date of birth, gender, blood group,
  parent contacts, medical notes, Extracurriculars, Achievements — no address, birth certificate, nationality,
  religion, previous school, admission date or Academics (`config/people.php` 'partial') · **Q5 A** a deleted team's
  members go back to Unassigned, their places kept in the history. Controller: saveTeam, assign, deleteTeam, member
  (JSON); accepting a request places the student at once when exactly one team fits their age group. A position
  change, leaving a team, a removed / archived / switched-off member or a deleted team writes the old place to the
  history. People's pop-up club list shows "Under 15 Squad · Captain". The filter's choices = the programme's age
  groups + its teams'. Shared changes: `_person_profile_pop_up.php` (the frame, used by People and Teams & Roster),
  `PeopleController::profileData(…, $partial)`, `PeopleModel::isClassTeacherOf()`, `repeat-list.js` (copies drop the
  template's ids), `_pick_menu` (the example-data branch removed: every pick menu is real now), `people-profile.js`
  (does nothing on a page without the pop-up). The example file `Mock/extracurricular.php` now holds only the
  awards (its squads / teams / unassigned / teachers were dead).
  Found and fixed while testing: the same member twice in a roster was accepted when the position was empty
  (`isset` on a null) → `array_key_exists` · "1 Members" / "1 Players" → singular.
  Tested: the whole flow by curl (accept → Unassigned → refused into the wrong age group → create with photo +
  position → position change → history → same save twice = no new history → club view → each student's roster →
  delete team (history kept, photo deleted) → auto-place on accept → remove ends team places), refusals (wrong age,
  unknown / another programme's team, non-member, twice, no / taken name, no / made-up age group, arrays, Ishara not
  in charge, student, no token, signed out), club view refusals (non-member, unknown id, other teacher, student —
  one message; signed out → JSON), Student Details medical notes (class teacher yes, subject teacher no), browser
  (Teams & Roster, Assign menu = own age group only and saved, club view pop-up, Edit Team pop-up saved for real),
  51 pages, 16 extracurricular states, 90 POST routes × 4 attacks, fresh install (41 tables, 38 updates), check.sh
  0 errors. Data and uploads restored after each test.
  - ☐ The Edit / New pop-up's age-group limit now sits alone on its row (full width) where the pair was
  - ☐ The profile pop-up on a programme page reloads the page when closed only after a save — the club view has no
    save, so nothing reloads (fine)
  - ☑ 2e: awards, gallery, award on each participant's timeline, dashboard "Sports & Clubs"; then delete
    `Mock/extracurricular.php` and its example photos — done in 2e (below)
- ☑ **2e Achievements & Gallery + dashboards — built 2026-11-01. B-2 is complete.** A club's or sport's
  achievement is an `achievements` row with its `extracurricular_id` (the table B-3 uses: no second awards table);
  its members in `achievement_students` (it shows on their timeline: People pop-up, club view, Child profile), names
  not in the portal in `others`, the team in `team` (empty = Individual). Update 039 `achievement_photos` (the
  gallery, in order); 040 demo: Football's "Under 15 Zonal Champions" (Dinuk + 2 other names, 1 gallery photo).
  Owner's choices (recommended, 2026-11-01): **Q1** the teacher in charge, management, admin record them, counted as
  verified · **Q2 A** participants picked from the members (+ "Other names" typed, shown only) · **Q3** delete by the
  admin or the teacher in charge, Audit Log, photos deleted · **Q4** levels from `country('achievement_levels')` ·
  **Q5** 12 photos × 5 MB; README now starts PHP with `post_max_size=64M` (**owner: restart the server with the
  README command**) · **Q6** donuts: biggest 5 + "Others", none → "No members yet." · **Q7** the teacher's second
  donut = their biggest programme; running none → no donuts · **Q8** `achievements` off → the panel, the award page
  and its routes go · **Q9** no age-group box for a programme without age groups.
  Built: `AchievementModel` (ofExtracurricular, find, save = row + students + gallery in one transaction, delete;
  ofStudent also returns the programme's type → its timeline group: a sport's award is "sports", not "clubs");
  `Extracurriculars::award()` / `awardCard()` (year from the date, Team / Individual from "Representing");
  `ExtracurricularController::saveAward` / `deleteAward` (members only — or someone already on it, who may have
  left; a team of the programme or the one it was saved with; age group, level from the lists; date not in the
  future; every photo checked first — one refused → the ones already saved are deleted again; replaced / removed
  photos deleted); `Upload::each()` (a "choose several" box one file at a time); `_award_page` a real form
  (dropdowns for level / age group / representing, participants from a member list, kept photos `keep_photo[]`, new
  ones `gallery[]`); `image-picker.js` keeps every photo chosen (choosing again adds) and × takes one out;
  `repeat-list.js` adds a dropdown's choice once. Dashboards: `ExtracurricularDashboard` (like MarksDashboard) +
  model counts (`memberCounts`, `membersByGradeOfType`, `membersByGrade`): student / parent memberships, teacher
  players + 2 donuts, management / admin programmes, sports-by-grade chart (ticks worked out), sports / clubs donuts;
  their example numbers deleted from `Mock/dashboards.php` (chart words through `words()` now).
  Removed (dead after 2e): `app/Services/Programmes.php`, `app/Mock/extracurricular.php`, `_danger_zone`'s example
  `message`, `feedback-banner.js`'s `data-mock-go` and its pick-menu close, the `?saved=1` trick, `repeat-list.js`'s
  Enter-in-a-text-box (no text box uses it now), 2d's `dropUpload()` (the same as `Upload::forget()`).
  Found and fixed while testing: keeping another achievement's photo said "at most 12 photos" → "That photo is not
  in this gallery." · the participant list read a hidden input the member dropdown doesn't have (no form name) → it
  reads the chosen option.
  Tested: curl flow (add with cover + 2 photos + a participant + other names → its card → Dinuk's timeline, group
  "sports" → edit: keep 1, add 1, participant off → the removed file deleted → delete: rows, photos and files gone,
  Audit Log), refusals (no title, bad / future date, made-up level / team / age group, non-member, another award's
  photo, 13 photos, a fake photo among good ones = nothing left behind, arrays, not in charge, student, another
  programme's award, management deleting, no token), `achievements` off (panel gone, page refused, routes 404),
  dashboards against counts from the database (4 programmes; Football 1, Chess 1; Alex 1 player, Grade 9 1; the
  chart per grade), browser (view, edit, gallery add / remove, participant add once, saved for real, no console
  errors), 51 pages, 16 extracurricular states, 98 POST routes × 4 attacks, fresh install (42 tables, 40 updates),
  check.sh 0 errors. Data and uploads restored after each test.
- ☑ **B-2 recheck (owner, 2026-11-01)** — 13 findings fixed: (1) Teams & Roster's age filter on the teacher's page
  dropped the programme id (a teacher running two programmes landed on their list) → always sent; `$runsProgramme`
  removed (dead) · (2) a refused achievement edit went back to the view page → back to the edit form · (3) the age
  cut-off day was read from the database for every member → once per page (`JoinRules::$cutoff`) · (4) the
  dashboard read the teacher's programmes once per programme → once · (5) "who may" typed in 4 places → `runs()`,
  `looksAfter()`, `mayDeleteAwards()` · (6) `$can['members']` = `$can['edit']` → one key · (7) accept + auto-place
  are one transaction (`ExtracurricularModel::decide(…, $teamId)`), and a request answered meanwhile is refused ·
  (8) `trim()` around `postText()` removed (it trims already; also in TermMarkController) · (9) "Other names" saved
  tidy ("A,B" → "A, B") · (10) renaming a programme renames its achievements' category too · (11)
  `endMemberships()` refuses "everyone" · (12) a failed save deletes the photos just uploaded for it
  (`keepingUploads()`) · (13) the stale comment went with (1). Re-run: joining, teams and achievements flows by curl,
  51 pages, 16 extracurricular states, 98 POST routes × 4 attacks, fresh install (42 tables, 40 updates), check.sh
  0 errors; data and uploads restored.
  - ☐ Still noted: Academic Watch reads the student's whole record book once per page (about 42 queries for 5
    years × 3 terms on the student's list) — a fixed number per student, not per member; could read only the latest
    published term (B-10, with the dashboards' numbers)
  - ☐ check.sh's 4 "same lines" CSS warnings (one from 2c: `.c-join-card__name`) — a shared look to decide with the owner
  - ☐ The PHP server still runs with 2 MB / 8 MB: until it is restarted with the README command, a photo over 2 MB
    says "That file is too big" (checked)
  - ☐ B-3: verification of the Achievements pages' own records; the student's own Achievements page (example data:
    a club award shows in People's pop-up and the Child profile, not there yet); honours-board picks
  - ☐ B-4: certificates read these achievements (their `extracurricular_id`, team, result)
  - ☐ "Programme" stays the screen word, so the PHP names (`$programme`, `programmeUrl`…) match it and stay
  - ☐ **Owner (by hand):** delete these unused example photos from `public/assets/images/examples/` (checked with
    grep: nothing uses them): basketball-court, basketball-cover, cricket-cover, cricket-stadium, debate-cover,
    face-04, 06, 07, 10, 11, 12, 13, 14, 15, football-night-match, robotics-cover `.jpg`
- ☑ B-2 (built 2a–2e, 2026-11-01) **Decided (owner, 2026-10-29):** management or admin creates; management's waits for the admin (greyed for
    management; approved → colour; rejected → hidden, kept as rejected); admin's own is approved at once · never
    deleted: **Archive** (hidden from lists and joining, memberships end that day, all history kept; admin can
    Restore) · capacity **300 per age group** (a club without age groups: 300 per club) · sports: age group always,
    from the date of birth, nobody changes it; clubs: a **"Uses age groups"** switch · the programme card's
    settings (management, admin): Uses age groups · Boys / Girls / Everyone · lowest {grade} · intake open / closed
    · age cut-off date: a setting in rules.php, default **31 January** (the usual Sri Lankan school-sports date — to
    be confirmed with the school) · any number of programmes, no maximum · after a parent's no the student may ask
    again · full → Interest locks ("Full"), no waiting list · members carry over each year, age groups re-worked out
    · teams + free-text positions by hand (teacher in charge); each position's start / end date is saved (B-4) ·
    leaving: the teacher in charge removes the student with a short reason (the student tells the teacher in person
    — differs from the proposal's leave request: update the proposal / SRS); joined / left dates kept · a switched-off
    student's memberships end that day · an award shows on the programme AND every participant's timeline · the
    teacher in charge sees their members' medical notes (read only, from People) · who sees what: everyone signed in
    → programme info, staff, schedule, achievements; members + their parents + teacher in charge + management +
    admin → rosters / members; members → the coach's contact · **Academic Watch** (rules.php, on / off): more than
    3 {subjects} under the pass mark (**35**, one pass mark for this and B-9's holding back) in the latest published
    {term}, AB not counted, worked out live → can't join, current memberships greyed with an "Academic Watch" tag
    (seen by the student, their parents, teacher in charge, management, admin — not other students); re-checked
    every published term; management / admin can Lift Lock / Lock in the student's People pop-up (reason, Audit
    Log), lasting until the next published term · the teacher sees waiting students in "Enrollment Processing"
    (exists: Join Request cards) + a count on their sidebar · club events already reach members, parents, teacher
    in charge once members are real · trip permissions (B-8) = a notice of kind "Ask parents for permission" (date,
    place, cost, reply-by) that creates an Approvals card per parent; replies counted on the notice
    · non-members see a showcase: header, teacher in charge, coach's name only, schedule, Achievements & Gallery read
    only (the portal's award cards — the honours board's own design stays on the public site, B-3), Interest or why
    not · demo data: only enough for each page to show something true · build in steps 2a rename → 2b programmes →
    2c joining → 2d teams / positions → 2e awards, gallery, dashboards · rename: **database + PHP → "extracurricular",
    CSS class names stay** (`c-programme-…`: not seen by users; no risk to the look) · photo uploads (owner,
    2026-11-01): JPG / PNG / WebP, max **5 MB** each, `public/uploads/extracurricular/`, through `core/Upload.php`
- (the original B-2 note) **Extracurricular** — programmes (create, edit, approve / reject, delete), teacher in charge + coach,
  schedule / place, members: Interest → parent → teacher in charge, join requests, teams (A / B / C per age group),
  age group from date of birth, capacity 300 per age group, positions with dates, leave requests (history kept),
  the club's own notices + events (calendar / notices already target activities), awards + gallery.
  Has: `activities`, `activity_members`. Example data now: Mock/extracurricular
- ☐ B-3 **Achievements** — the teacher records one (student(s), level, result, evidence upload), verification
  (teacher in charge → management), the student's timeline + number cards, a student's "missing record" issue,
  the honours board picks. Has: `achievements`, `achievement_students`. Example data now: Mock/achievements
- ☐ B-4 **Character certificates** — built from verified records (B-2, B-3), draft preview (pending marked),
  management's final verification, PDF download locked until verified, leaving date (stops new entries, warns),
  certificate requests + missing-record requests. Has: `certificates` (old shape). Example data now: Mock/certificates
- ☐ B-5 **Feedback** — the class teacher writes to a student: Student / Parent / Both (student-only invisible to
  parents), kinds (praise / constructive / concern), one-way, read marks. Has: `feedback` (no audience yet).
  Example data now: Mock/feedback
- ☐ B-6 **Complaints** — the parent writes (recipient: class teacher / head teacher / principal, category, subject,
  body, child), one-way, management marks resolved with an answer. Has: `complaints`, `complaint_messages` (a
  thread: the proposal says one-way). Example data now: Mock/complaints
  - **Notification list** (owner, 2026-10-29: every feature adds who is told what, plus notes on how to build it;
    nothing is built yet — no table, no bell. B-7 is built **last**: the owner has another idea for it, to hear first):
    | When | Who is told |
    |---|---|
    | A class's term is published (B-1) | its students + their parents |
    | A published mark is corrected (B-1) | that student + their parents |
    | A student shows Interest (B-2) | their parents (an Approvals card too) |
    | A parent says yes (B-2) | the teacher in charge (a Join Request card too) · the student |
    | A parent says no / the teacher accepts or rejects (B-2) | the student + their parents |
    | A student is removed from a programme (B-2) | the student + their parents |
    | Academic Watch locks / unlocks (automatic or by hand) (B-2) | the student, their parents, their teachers in charge |
    | A new programme waits for approval (B-2) | admin · approved / rejected → whoever created it |
    | A programme is archived (B-2) | its members + their parents |
    | Team / position changes (B-2): placed in a team (by hand or on accept), a position set / changed, taken out of a team, their team deleted | that student (+ their parents?) |
    | An award is recorded (B-2 / B-3) | every participant + their parents |
    | A permission request (trip / event) is sent; its reply-by date is near (B-8) | the students' parents |
    | Feedback written (B-5) · complaint sent / resolved (B-6) · leaving date set (B-4 / B-9) | as each item says |
- ☐ B-7 **Notifications** — in-platform only: interest → parent, parent's yes → teacher in charge, complaint →
  recipient, leaving date → class teacher / TICs / management, new notice … No table yet
- ☐ B-8 **Permission request cards** — trips / events (name, date, place, cost) → parents approve / reject with
  time; the teacher in charge can't take a student without a yes. No tables yet (new; not in the old design)
- ☐ B-9 **Year-end** — promote every active student a grade (class mapping chosen by management), Grade 13 →
  leaving, class teachers reassigned, history untouched. Needs B-1's `class_enrolments`
  - Owner's ideas (2026-10-29, discuss in detail at B-9): **held back** — a student with too many low marks (e.g.
    more than 3 subjects under the pass mark, or a very low average) is not promoted automatically: next year they
    stay in the same grade. Management / admin can **allow promotion** anyway (a button on the year-end page, with a
    reason, logged). The thresholds and the on / off switch go in `config/white_label/rules.php`. The record book
    then has two books for the same grade (one per year), and the last term's report shows the result
    ("Promoted to Grade 8" / "Repeats Grade 7"). B-1 already keys marks and books by year, not grade.
  - Owner's idea for B-2: a student whose average is very low may be blocked from joining new clubs / sports (a
    switch in rules.php; existing memberships stay unless the school decides otherwise).
- ☐ B-10 **Dashboards** — the 5 dashboards' number cards, bar chart, donuts from B-1 … B-6. Example data now:
  Mock/dashboards
- ☐ B-11 **Profile details nobody stores yet** — My Profile's example values (Mock/profiles.php)
- ☑ **B-12 Smaller ones — built 2026-11-08 (Apeksha).**
  - Notice attachments: `Core\Upload::document()` saves a PDF, DOC, DOCX, JPG or PNG in `storage/uploads/notices/`
    (not public: nobody opens it by its address); the real content decides the type (a renamed .php / .exe / .svg /
    fake image / a zip that is not a Word file → refused), random saved name, original name shown, 2 MB
    (`notices.max_file_mb`). `NoticeController::save()` keeps the file on an edit without one, replaces it (old file
    deleted) and deletes it with the notice. `GET {board}/attachment?id=` (route for every board) → a download only for
    someone who may see the notice (same `NoticeModel::findFor()` as the board), `Content-Disposition: attachment`,
    `nosniff`. No ZipArchive needed.
  - Audit Log paging: 25 events a page (`audit.per_page`, was "newest 350"); `AuditLog::page()` returns the page and the
    total; the page links are a new shared `components/_pager.php` + `pager.css`, and People's directory uses it too
    (its own copy of the markup / CSS is gone).
  - "Your password was changed" email: `App\Services\AccountEmails::passwordChanged()` + `emails/password_changed.php`,
    sent after My Profile's change and after a reset with a code (never when the code or current password is wrong);
    a student's goes to the parent's address, like their other emails. No password in it.
  Tested: valid and hostile files (13 cases), edit keep / replace / bad file, delete (disk files = DB references),
  download for admin / teacher / management / student / parent / signed out / bad ids, paging with filters and odd
  `page` values, the three email cases and the no-email cases, `bin/check.sh` 0 errors.
  - ☐ Not built (the owner decides the look): a "remove the attachment" box on the notice form — a notice's file can be
    replaced, not removed except by deleting the notice
- ☐ B-13 **Settings, edited where they belong** (owner, 2026-10-29; right after B-1; each design shown first).
  `config/white_label/` stays the starting values (a new install still only changes those files); an admin change is
  saved in a `school_settings` table that overrides the file value; `school()`, `word()`, the rules read through one
  function; every change in the Audit Log.
  - Settings page (admin, new sidebar item): **School** tab (name, logo, emails, phone, address) · **Words** tab
    (Grade, Class, Term, Student, Teacher, Subject…) · view-only: modules, sign-ups, country (installer only:
    switching a module off hides pages that hold data; a timezone change shifts every date)
  - Academic page: **School Year** section (term start + end dates per year: `school_terms`, made in B-1) ·
    **Marks rules** section (Commended %, Top of Class, Top of Grade, on / off) · class size / naming
  - Calendar: a **Holiday** category (whole school; admin, management)
  - **One school timeline** (owner, 2026-10-29): each date stays in the one table that owns it (`school_terms`,
    `events` incl. holidays, `term_publications`, `class_students`, admission / leaving dates, `audit_log`) — never
    copied into one big table, never typed into files. A `SchoolTimeline` service reads the PLANNED dates together:
    clash warnings when adding an event ("a public holiday (Poya)", "outside {Term} 2", "exams that day"), term start /
    end markers on the calendar, and the dates B-9 / B-4 need. Public holidays change every year (Poya follows the
    moon): admin enters them once a year in the School Year section — one by one or pasted as a list
    (`2027-01-13 Duruthu Full Moon Poya Day`), saved as Holiday events. No online holiday service (a library, and
    unreliable for Poya). Nothing country-specific in the code.

### B-0 Tidy-up before B-1 (proposed 2026-10-29; owner picks the steps)
Owner, 2026-10-29: yes to 1 (like a normal school: each year's assignments carry over, management re-assigns by
hand when needed; past years kept), 2, 4 and 6. Names say what / why / where and match the words on the screen.
Step 3 (rules list) not answered yet.
- ☑ 1 + 2 **Assignments per year + one Assignments model** (2026-10-29) — update 028:
  `school_years` (current = newest), `class_students` (was `student_details.class_id`), `class_teachers` (was
  `classes.class_teacher_id`), `subject_teachers` (was `class_subject_teachers`), `teachers_in_charge` (was
  `activities.teacher_id`), each with `year`; views `current_…` + `current_teaching`. `App\Models\Assignments`
  is the only writer (`ClassModel::hasRoom` / `saveSubjectTeacher` moved there). Reads changed in ClassModel,
  PeopleModel, Membership, CalendarModel, StageModel, ExtracurricularModel; the `:teacher2` / `:me2` workarounds
  gone. A class / grade with earlier years' students can't be deleted (`past_students`, refused with a message;
  the database also refuses: RESTRICT). Tested: rows moved 4/2/6/4; subject teacher, class teacher (and "already
  class teacher of"), move, add, full class, handover; a new year starts empty with 2026 kept; 51 pages clean.
  HOW-IT-WORKS §9.
  - ☐ Year-end carry-over (teachers copied to the new year, students promoted) → B-9
  - ☐ `activity_members` keeps one row per student and club (no year / joined–left dates) → B-2 rebuilds it
  - ☐ Academic page: the delete tick-boxes don't yet grey out a class kept for earlier years (the save refuses it
    with a message) — only matters after the first year-end
- ☐ 3 `config/white_label/rules.php`: school rules as a list with on / off switches (commended 80 %, top of class /
  grade, which achievements are automatic) → with B-1
- ☑ 4 **Unused tables dropped** (update 027): `school_settings`, `certificates`, `complaints`,
  `complaint_messages`, `feedback` (demo rows only, nothing read them) — each is made again by its B-item
- ◐ 6 **Names**: ☑ `activity_logs` → `audit_log`, `ActivityLog` → `AuditLog` (update 029) ·
  ☑ "programme" / "activity" → "extracurricular" (~770 uses, mostly the example-data code B-2 replaces) → in B-2:
  done for the database and the PHP classes; "programme" stays where it is the screen's word (see 2e)
- ☑ 5 **Feature sweep** (2026-10-29) — a module switched off in `features.php` now hides its pieces everywhere,
  not only its routes and menu link:
  - Dashboards: each card / chart / donut in `Mock/dashboards.php` names its `feature`; `DashboardController::show()`
    drops the ones that are off; Upcoming Events (calendar) and Unread Notices (notices) arrive as null when off.
    The 5 views draw a row only if something is in it.
  - A piece left alone in its row takes the whole width (`chart-calendar-grid.css`, `events-notices-grid.css`,
    `donuts-events-grid.css`: "first, nothing shown after it" — the calendar / notices card are followed by their
    pop-up `<template>` / `<dialog>`, so `:only-child` didn't work).
  - People pop-up (student): tabs from `PeopleController::PROFILE_TABS` with their module; data skipped when off;
    no tab bar when only Information is left. (Child profile already did this.)
  - A programme's Notice Board hides with `notices` (`Programmes::find` → `noticeCards` null).
  - Academic: the class performance chart hides with `term_marks`.
  - check.sh ✗ a dashboard piece without a `feature` (tested: removing one is caught).
  - Checked: all on → 51 pages clean, layout unchanged (chart 2/3 + calendar 1/3, events / notices half each).
    term_marks + achievements + notices (+ extracurricular) off → their pages 404, every other page clean, pop-up
    shows only the tabs left, calendar and events fill their rows. calendar off → chart and notices fill their rows.
    `features.php` restored to all on.
  - ☐ Later (owner, 2026-10-29: not needed now, too complex for a rare case): admin / management with calendar
    off → the two donuts sit in 2 of 3 columns (one empty column on wide screens). If ever wanted: rows that
    re-place what is left to fill the width, without changing any piece's own size.
  - ☑ Not covered: a club's awards / gallery inside Extracurricular don't hide with `achievements` — they do since
    B-2 2e (owner's Q8): the panel, the award page and its routes go.

## Done so far
- ✅ Landing page + honours board
- ✅ Sign in, sign up (email code), forgot password
- ✅ Student dashboard layout (MVC look) — data is still mock, see below
- ✅ Bar chart component (SVG kept, Y-axis pinned so it doesn't scroll) + term dropdown
- ✅ Dropdown component with variants (`cream`, `calendar`)
- ✅ Calendar split into `components/calendar/` (calendar, day card, pop-up, event editor)
- ✅ All 5 dashboards (2026-10-19): `student/ parent/ teacher/ management/ admin/dashboard.php`, MVC layout.
  Shared frame: `layouts/page_top.php` + `page_bottom.php`. Cards, chart, donuts, upcoming events, notices = MVC's
  examples in `app/Mock/dashboards.php` (delete each block when its real data is built). Checked in the browser for all 5.
- ✅ Calendar backend (2026-10-19), from Clean/MyTry, fitted to this project: `CalendarController` (save / delete, JSON),
  `CalendarModel` (all calendar SQL + the pop-up's choices), `Services/CalendarFeed` (rows → what calendar.js reads),
  `config/calendar.php`, `permissions.php`, `posting.php`, `core/Validator.php`. Checked in the browser:
  each role sees only its events · add / edit / delete · clash warning + "Allow parallel" · teacher limited to own
  classes / clubs, can't touch others' events · forged class / grade / General, bad CSRF, bad date all refused ·
  parent has no save route (404)

## Bugs found (2026-10-18)
- ✅ Logout is a link (GET) but the route only accepts POST → now a POST form with the CSRF token (sidebar + its confirm pop-up)
- ✅ Calendar JS calls `/{role}/getCalendarEvents` and `/{role}/deleteCalendarEvent` — no such routes → now `/{role}/calendar/save` and `/delete`
- ✅ `docker-compose.yml` said ports 3307 / 8085; the running DB, `.env` and CLAUDE.md use 3308 / 8086 (3307 is MVC's
  DB) → fixed 2026-10-26 (a fresh `docker compose up` would have clashed with MVC)
- ✅ Sidebar logo and name are hard-coded → `school('logo')` and the signed-in user (checked 2026-10-26)

## Bugs found by the final audit (2026-10-28 — docs/2026-10-28-final-audit-after-mvc-import.md, Part 3)
- ✅ B1 "Stay Signed In" did nothing: session-timeout.js sends HEAD, the Router only knew GET / POST → 404, the idle
  clock wasn't reset. Fixed: `App::run()` treats HEAD as GET. Checked: `HEAD /teacher` signed in 200 (no body),
  signed out 302 → sign-in
- ✅ B2 A switched-off account stayed signed in. Fixed: `requireRole()` asks `UserModel::canStaySignedIn()` (ACTIVE and
  not locked by the admin; a wrong-password lock doesn't count) every request → ends the session, "This account has
  been deactivated…". Checked: admin switches the demo student off → the student's next page 302 → sign-in with
  the message, stays out, JSON request gets JSON; switched on → signs in again (both accounts ACTIVE)
- ✅ B3 Open redirect via the Referer. Fixed: `previousPage()` → `/` unless the path is "/" + a name (`//x` and `/\x`
  are other websites to a browser). Checked: `//evil.example` → `/`, `/\evil.example` → `/`, `/teacher/notice?x=1`
  unchanged. After B1–B3: the 200-request attack sweep again 0 problems, data unchanged, check.sh 0 errors
- ✅ B5 (2026-10-28, owner chose the simple fix: one message for every failure) Sign-in: unknown ID, not activated,
  wrong password, the 5th wrong password and a paused account all say "Incorrect ID or password. After 5 wrong tries,
  sign-in for that ID pauses for 15 minutes — use "Forgot Password?" to get in straight away." (numbers from
  config/app.php). The pause still works, silently; a reset already clears it, so locking someone out gains nothing.
  A paused try is logged now. Checked: 6 tries on a made-up ID and 6 + the right password on the demo student →
  the same text every time, the DB shows the pause; the demo pause cleared by hand afterwards, signs in again
- ✅ B6 One message for every failed 6-digit code (sign-up and reset): `AuthController::CODE_FAILED` "That code is wrong
  or has expired…" — a made-up account can never be told apart. Checked: 7 codes on a made-up ID → the same text
  (a real account's reset wasn't run: it emails a real code; it reaches the same line)
- ✅ B9 Class capacity race (2026-10-28): `ClassModel::hasRoom()` now locks the class row (`FOR UPDATE`) and counts
  with a locking read (`FOR SHARE`, so it sees the newest saved rows, not the transaction's older snapshot — update()
  reads before it). Both callers (PeopleModel add / update) already run in `transaction()`. Checked with two real
  connections: A holds class 10-A 3 s → B on 10-A waited 2.5 s, C on another class 0 s; all 12 classes give the same
  yes / no as before; unknown class → no; check.sh 0 errors
- ☐ Database — owner 2026-10-28: "we will build as we go", with the marks backend: `marks → stage_subjects` CASCADE →
  RESTRICT; `CHECK (mark BETWEEN 0 AND 100)`; `event_scopes` left pointing at a deleted class
- ✅ Telling the person, ONE rule (2026-10-28, audit Part 4, owner: "yes"): decide → question pop-up · fill in / look
  at → pop-up · "it worked" → the sliding banner (green, leaves) · "it didn't" after a one-click action → the sliding
  banner (red, STAYS until ×) · a form's problem → red box inside the form · hints → info / warning box. Written in
  docs/HOW-IT-WORKS.md section 8. Built: `Controller::page()` reads the leftover 'notice' / 'error' flash and
  `page_bottom.php` draws the page's one banner (feedback-banner CSS / JS now load on every signed-in page) → 21 pages
  lost their own banner wiring ('message' / 'error' keys in 9 controllers, the includes, the list entries). Changed on
  screen: Notice board + Approvals (green box → banner; `.l-approvals-message` gone), term marks' "Marks saved."
  (box → banner; `.c-marks-panel__done` gone), teacher achievements' unknown student (red box → red banner;
  `.l-student-cards-message` gone), the calendar now says "Event saved." / "Event deleted.", My Profile uses 'notice'
  (was 'saved'), Extracurricular's ?saved=1 → setFlash. `.c-banner--success` deleted (nothing used it). check.sh:
  ✗ a green box in a page, ✗ a page drawing the banner itself. Checked: curl — pinned / unpinned (green), pin a
  waiting notice / bad approval answer / unknown programme / unknown student / unknown grade (red), shown once,
  My Profile saved (green) vs refused save + wrong password (inside the form, no banner), ?saved=1 (green); 1 banner
  on every page; browser — "Marks saved." leaves, a red banner still there after 4.5 s, the calendar's "Event saved."
  / "Event deleted." (test event removed again), console clean; 51 pages 0 warnings; attack sweep 0 problems
- ✅ Bug found by the page sweep (was there before): `_student_card.php` used and unset its own `$issue`, wiping the
  page's → teacher achievements for a student with a reported issue warned "Undefined variable $issue" and the Review
  Issue pop-up never appeared. Renamed `$cardIssue`. Checked: 0 warnings, the pop-up is drawn, the red-ring card stays
- ✅ N1 Security headers (2026-10-28, core/bootstrap.php): `X-Frame-Options: SAMEORIGIN` + CSP `frame-ancestors 'self'`
  (not DENY: Export PDF loads our print page in a hidden frame), `X-Content-Type-Options: nosniff`, `Referrer-Policy:
  same-origin` (previousPage() still gets our own pages), `X-Powered-By` removed. Checked: on pages, redirects, the PDF
  and the 404; our own page in a frame loads, another origin's frame of /auth/admin is refused (screenshot)
- ☐ N2 Owner: delete `database/backup_l_ecole.sql` by hand (523 KB dump of 2026-10-18: every demo person + password
  hashes; nothing uses it; not in .gitignore). Claude's delete was blocked by the safety check (can't be undone)
- ✅ This folder is the original (owner, 2026-10-29): names and comments never say where something came from.
  "migrations" → "updates": `database/updates/` (26 files), `php database/update.php`, the table `database_updates`
  (live table renamed, all 26 rows kept), README / HOW-IT-WORKS / this checklist. composer.json `mytry/application`
  → `school-portal/app` (not the school's name: white-label), lock file refreshed (package versions unchanged).
  Every "MVC's…", "(MVC)", "like MVC", "from Clean/MyTry" in code comments reworded to say what the code does or why
  (~440 lines, ~250 files: config, database, controllers, models, services, mock data, views, CSS, JS); also fixed
  on the way: 2 comments typing the country ("NIC" in Sri Lanka) and 3 that still named config/posting.php for
  whose posts may change (now permissions.php 'posts'). History documents (this checklist, the audit file) keep
  their history. Checked: 0 mentions left in code, every PHP / JS file passes its syntax check, a fresh install
  (schema + seed + update.php) = the live tables (30), check.sh 0 errors, 51 pages + landing + honours + sign-in 200
  with 0 warnings. Still named after the old setup: the folder `MyTry copy 2/` and the Docker container
  `mytrycopy2-db-1` (owner renames the folder later, by hand); the database name `l_ecole` (the school's name — ☐
  owner: a neutral name like `school_portal` with the folder rename)
- ✅ Passed (2026-10-28): 50 POST addresses × 4 attacks (signed out, no token, wrong role, every field a list) = 200
  requests, 0 crashes, nothing answered 200, all 16 data tables identical after (CHECKSUM); IDOR: management → a
  management account / the admin, teacher → a student they don't teach, parent → another family's request / child,
  unknown ID — all refused with the "doesn't exist" message

## Clean-ups found (2026-10-18)
- ✅ Two icon files → only `components/icons.php` (checked 2026-10-26)
- ✅ `components/_calendar.php` and `_calendar_event_modal.php` only forward to `components/calendar/` → removed
- ✅ `config/brand.php` → gone; colours only in `global.css` (checked 2026-10-26)
- ✅ `core/Model.php` wrappers → one `transaction()` (checked 2026-10-26)
- ✅ Every page repeats the `<head>` / `?v=time()` → `layouts/page_top.php` + `asset()` (checked 2026-10-26: no `time()` left)
- ✅ Empty files (2026-10-28): the empty `Dockerfile` deleted (compose doesn't use it); `README.md` has the setup
  (`.env`, `composer install`, `docker compose up -d`, `php database/update.php`, `php -S localhost:8080 -t public`)
- ✅ Example photos offline (2026-10-28): the 38 internet links (images.unsplash.com) in `app/Mock/` term_marks,
  achievements, extracurricular → 27 local copies in `public/assets/images/examples/` (15 faces `face-01…15`, 12
  pictures named after what they show: `football-cover`, `cricket-stadium`…; ~1 MB) via `asset()`. One of MVC's links
  was already dead (football Team B cover + "Best Striker" award) → `football-night-match` / `football-penalty-kick`.
  Delete the folder with the mocks. Checked: 0 links left, every page's photos load (admin Extracurricular ×6,
  award page, teacher Term Marks + Achievements), check.sh 0 errors

## Calendar plan (from the Antigravity chat)
- ✅ A: remove dead CSS in `calendar.css` (`.c-select__menu-header…`)
- ✅ B: event category comes from the database, not guessed in `calendar.js`
- ✅ C: event editor CSS only on pages where the user can add events
- ✅ Phase 7: shared modal close code (dialogs-and-popups.js), one date helper, audience label made in PHP.
  First month drawn by PHP: skipped (owner's decision — one place draws the grid)

## Recheck of bar chart, dropdown, calendar (2026-10-18)
Calendar
- ✅ `tmpl-calendar-modal-item` was defined twice → only in the pop-up file now
- ✅ No calendar backend → built (see "Done so far")
- ✅ Made-up event data (time, id, author, audience) → every field comes from `CalendarFeed`
- ✅ Month names and year range written twice → PHP draws them and passes the years (`data-first-year` / `data-last-year`)
- ✅ Default scope `'school'` vs `'schoolwide'` → gone (the server decides scopes)
- ✅ `calendar.js` closed every modal → left to `dialogs-and-popups.js`
- ✅ Inline styles in the day-card templates / pop-up, `style.display` on top of `hidden` → classes; `hidden` only
- ✅ `canUserModify` only in the browser → the server sends `canEdit` and checks again on save / delete
- ✅ Unused CSS: `.c-modal__icon-badge--sky`, `--sky-solid` (calendar.css) — gone
- ✅ `calendar-editor.css` raw colours → names (new in global.css: `--stone`, `--bronze`, `--ivory`, `--danger`, `--danger-soft`);
  unused `.c-event-form__body` / `__field-group` removed
- ✅ Bug found while doing it: the pop-up's red messages (error banner, "Enter an event title") had `display: none` and never showed → fixed
- ✅ Dates (2026-10-19): the calendar shows last year → next year (`config/calendar.php`); events can be added / changed only
  from today to the end of next year; past events: bin only (no pencil), no "Add event" on past days; checked on the server too
- ✅ "All day" is the first Start Time choice and the default; End Time shows once a start time is picked (one hour later)
- ✅ Teachers see every event for their own classes, grades and clubs / sports, whoever it is for
- ✅ The pop-up's CSS / JS moved into the staff dashboards' `$styles` / `$scripts` lists
- ✅ "Add event" pop-up layout (2026-10-19): MVC had its padding, gaps and footer as inline styles, which were dropped →
  now in calendar-editor.css (`c-event-form__body`, footer, actions); base `.c-btn` (missing from our global.css) → new
  `components/button.css`, with `--ghost` / `--solid` moved there from calendar.css. Checked next to MVC's pop-up.
- ✅ Shared pop-up component (2026-10-19): `components/_pop_up.php` + `pop-up.css` (shell: backdrop, box, header with icon,
  eyebrow, title, description, ×; tone sand / sky; size normal / wide; `$popUpVariant` for the pop-up's own CSS).
  Its rules were in global.css + calendar.css → moved there. "View all" and "Add event" use it; MVC's white icon box and
  the 36rem width of "Add event" are back
- ✅ Time: hour (All day, 00–23) : minute (00–59) dropdowns, 24-hour; dropdown `$variant = 'time'` (the default look on the same
  rules); the form sends start_hour / start_minute…, `CalendarController::joinedTime()` makes "14:45"; a new start sets End one hour later
- ✅ Calendar split into folders: css/components/calendar/ (calendar, day-card, day-card-popup, event-editor) and
  js/components/calendar/ (calendar, event-editor); `_event_editor_modal.php` → `_event_editor.php`
- ✅ `asset()` helper (file change time instead of `?v=time()`): page frame, sidebar logo, delete pop-up
- ✅ Two icon files → `components/icons.php` only (`_icon_logos.php` deleted, it was a copy)
- ☐ Pop-up still differs from MVC in wording ("EVENT CATEGORY", "General / School-wide", MVC's subtitle); the "All" chip has an ×
- ✅ Pop-ups, one frame + 2 shapes (2026-10-19): `_pop_up.php` `$popUpShape` = 'panel' (header bar + content: forms, lists)
  or 'plain' (the content brings its own top). One shared "Are you sure?" pop-up for every page: `_question_pop_up.php` +
  `question-pop-up.css/js` → `askQuestion({title, text, button, cancel, icon, tone, onConfirm})`, tones danger / warning /
  success. Delete (calendar) and Log out (sidebar.js, was HTML strings with inline styles) use it; `_delete_modal.php` +
  `delete-modal.css` deleted. Unread notices: the frame, plain shape, keeps its own white header (same look, checked).
  Frame files load on every signed-in page (page_top / page_bottom). Checked in the browser next to MVC.
- Rule for new pop-ups: a yes/no question → `askQuestion()`; anything with content → `_pop_up.php` panel (or plain).
  Each page's pop-ups are listed under its feature ("When building it") in the tick list below
- ✅ Log out pop-up vs MVC (2026-10-20): full-width buttons + log-out icon (`wide`, `buttonIcon`). Not copied: MVC's
  text says "You pressed the back button…" even after clicking Logout, with the role name in bold; ours fits both ways
- ✅ `asset()` on the sign-in, sign-up, chooser, landing and honours pages too
- ✅ Page rows (2026-10-19): the 4 dashboard grids were written twice (global.css + a component, different values) →
  `css/layout/metric-cards-grid.css`, `chart-calendar-grid.css`, `donuts-events-grid.css`, `events-notices-grid.css`
  (`l-…-grid` classes, listed per page in `$grids`); removed from global.css, metric-card, bar-chart, donut-chart,
  unread-notices; `_bar_chart.php` no longer carries a layout class. Measured before / after at 500, 900, 1440px
  (student + management): every block in the same place; admin still 5 cards across
- ✅ `academic.css` mentioned `.c-primary-grid` → the page uses `l-chart-calendar-grid--tight`; academic.css deleted (2026-10-26)
- ✅ Class names `c-modal…` → `c-pop-up…` (2026-10-20, with the `<dialog>` change)
Dropdown
- ✅ Fired two events for one change → only `dropdown:change` now (calendar + bar chart switched to it)
- ✅ One class name per part (`c-dropdown__…`); `c-select…` and the old cream classes are gone
- ✅ Old parameter names (`$selectId`, `$selectLabel`, `$extraClass`, `$labelPrefix`, `$dropdownClass`) removed
- ✅ Menu shown by `.c-is-open` only — the `hidden` attribute and all 16 `!important` are gone; looks: `$variant` default / cream / calendar
- ✅ `dropdown.js` rewritten (536 → ~390 lines): chip drawing was written twice → once; chips built as elements (names can't add HTML);
  "All" works with value `all` (it only worked with `All` before)
- ✅ Every dropdown in MVC listed (2026-10-19: 77 uses in 27 files + 4 hand-written + 3 plain `<select>`) and its look built as a variant:
  `tinted` + `$tone` sky / sunshine / terracotta / maroon (add-person forms), `status` + `$tone` active / deactivated (people rows),
  `toolbar` (people filters), `small` with a tick (term marks filter), `cream` also for the Academic filters; `$prefix` ("Grade: 10")
- ✅ Measured next to MVC (8040): tinted, status, toolbar, prefix identical. MVC's `compact` and the status "smaller" rule
  never applied in MVC (stronger selectors won) → not built; those dropdowns are `default` / the white pill
- ✅ Old copies deleted from `form-card.css`, `people-directory.css`, `academic.css`, `profile-page.css`, `teacher-term-marks.css`,
  `complaints.css`, `team-card.css` (incl. 13 `!important`); in-table menu rule moved into `dropdown.css`
- ✅ JS: `setDropdownOptions(id, options, chosen)` (lists filled later), `resetDropdown(id)` uses the dropdown's own placeholder,
  ↓ / ↑ keys, Escape returns focus, a table menu closes on scroll; dead `c-is-disabled` check and row-lifting classes removed
- ✅ (2026-10-28: no `<select>` and no `c-select` left anywhere) When each page is built: switch its dropdowns to the component — hand-written ones in `_teacher_term_marks_panel` (`small`),
  `_extracurricular_card_achievement_page` (3, start empty → `setDropdownOptions`; the hidden one → wrap in an element with `hidden`),
  the 3 plain `<select>`s in `_teacher_handover_modal`; page JS still using `c-select…` names (10 files, none loaded yet:
  add-notice-page, complaints, extracurricular-achievement, extracurricular-card, grade-card, notice-filter,
  parent-deactivation, people-directory, profile-modal, teacher-term-marks)
- ✅ (2026-10-28: every copy rebuilt or deleted — the last two, parent-deactivation.js and teacher-students.js, deleted;
  check.sh section 9 now fails on a component file no page lists; B `no-store` is in `Controller::page()`; C gone)
  Recommended (2026-10-19, waiting for owner): A delete the ~30 CSS + ~30 JS unbuilt MVC copies (rebuilt one by one with
  their pages); B `Cache-Control: no-store` header instead of sidebar.js's reload-on-Back; C dead `window.escapeHtml`
  (sidebar.js), `applyCardDecisionOverlay` / `showFormBottomToast` (dialogs-and-popups.js); D `bin/check.sh`;
  E finish dialogs-and-popups.js; sidebar open/closed in a cookie so PHP draws it (no flash); chart tooltips CSS-only? (owner's call)
- ☐ Arrow keys show the browser's blue outline on a focused option (MVC has no style) → owner to choose: keep, or match the hover colour
- ☐ MVC gave default dropdowns inside a `.c-form-card` 14px / 500 text (ours: 13px / 600) — decide when the first form-card page is built
- ✅ (2026-10-28: 0 `!important` left) Older `!important` still in `form-card.css`, `people-directory.css`, `teacher-term-marks.css`, `team-card.css`, `complaints.css`,
  `profile-page.css` (not dropdown rules) → fix when those pages are built
Bar chart
- ✅ Reworked (2026-10-19): `$variant` `scores` (0–100 %, Term picker) / `counts` (your ticks); PHP works out every position,
  each bar carries its values per term; `bar-chart.js` 200 → ~140 lines (tooltip, wheel, term swap — no maths, no guessing)
- ✅ Colours: `.c-series--{name}` (bar, legend dot, tooltip dot); axis greys named in global.css (`--chart-axis`, `--chart-tick`, `--chart-label`)
- ✅ No inline styles left in `_bar_chart.php`; header comment rewritten; global `escapeHtml` / `brandToken` gone
- ✅ Term list and selected term hard-coded in `DashboardController` → the chart draws its own Term picker from `termsData` + `term`
- ✅ Short charts (admin / management, 6 grades) were squeezed so the bars didn't match the Y-axis → MVC's widths again (126px per group, scrolls)
- ☐ Real chart data (term marks for scores, students per grade for counts) — still MVC's mock in `app/Mock/dashboards.php`

## Backend to bring in from Clean/MyTry (when its page is built)
- ☐ Core: `Upload.php`, Controller tools (`validateForm`, `requirePermission`, `notFound`, `saved`), helpers (`format_date`, `asset`, `current_term`, `grade_letter`, `ordinal`)
  (done: `Validator` with required / max / in / date / time, `postText`, `postList`, `can`, `can_manage`, `event_time`, `plural`)
- ☐ Config: `notices.php` ✅, `audit.php` ✅ (no `verifications.php`: each feature's config has its own
  `needs_approval`) (done: `permissions.php`, `posting.php`, `calendar.php`)
- ✅ Calendar: `CalendarModel`, `CalendarFeed`, `CalendarController` (`EventChoices` folded into `CalendarModel::choicesFor`)
- ☐ Each module's model when its pages are built (notices, people, academic, extracurricular, approvals, verifications,
  term marks, achievements, certificates, feedback, complaints, audit)
- ✅ `database/update.php` (2026-10-26, Clean's; was `migrate.php` + `migrations/`, renamed 2026-10-28 by the owner) + `schema.sql` / `seed.sql` replaced by Clean's (ours were an old
  MVC-era copy without `profiles`, `student_parents`, `verification_codes` → a fresh install broke). Tested: an empty
  database + schema + seed + the runner (23 updates) = the live database's tables exactly. 022 had been run by hand
  → recorded in `database_updates` (date approximate). The runner's own SQL is a command-line tool, not app code (the edit
  hook flags it). MVC's own database files (academic_schema.sql, its update files 001 / 002) are all covered by ours —
  nothing to bring over
- ☐ `bin/reset-demo.sh`, tests per module (README.md ✅ 2026-10-28)
- ✅ `docs/HOW-IT-WORKS.md` (viva sheet, 2026-10-28): one request start to finish, folders, how a page is made,
  security rule by rule, white-label, the browser scripts, example data

## Refactor tick list — all of MVC, feature by feature (2026-10-19)
A feature is ✅ when: its pages + components are rebuilt to our rules (no dead / duplicate code, simplest vanilla way,
own CSS / JS, multi-variant where they differ by role), real data from the backend, and checked in the browser next to MVC.
MVC has 69 components and 38 pages. ◐ = started, not finished.

**0. Shared (every page)**
- ✅ `_sidebar` (2026-10-20): ONE file for all 5 roles; role data in ONE place → `config/portals.php` (from Clean/MyTry:
  sign-in texts that sat inside `AuthController::portals()` + the sidebar's subtitle / avatar / menu; `config/roles.php`
  deleted). Menu links hide when their `feature()` is off (features.php promised it; the sidebar never checked). School
  name / logo via `school()`; the 2nd `<h1>` → span; `aria-current` (was `aria-pressed`); `aria-expanded` on «; `icon()`
  (Extracurricular 19px via `iconSize`); `e()`; no leaking variables. `Controller::page()` gives every signed-in page
  role, path, first name, photo, CSRF and the collapsed state (each controller passed 4 of them by hand) and sends
  `Cache-Control: no-store` (replaces the JS reload trick). First name + photo saved in the session at sign-in
  (`startSession()`, was written twice). Collapsed state: a cookie the server reads → drawn collapsed, no jump.
  CSS: 7 `!important` + dead rules gone, orange pill = `::before` (no empty span), one rule moves the page, tokens
  (+ `--online`). JS: braces, the log-out text uses the school's word for the role. Measured next to MVC (brand, logo,
  item, pill, icons, profile, logout, page position): identical (Extracurricular icon 19 vs 19.2px)
- ✅ Regression fixed: `.tone-maroon / -terracotta / -midnight` were deleted as "unused" on 2026-10-20 but the role
  chooser builds them from data → restored; `bin/check.sh` now knows classes built from data (`tone-<?= … ?>`)
- ✅ Role chooser = MVC: order Student, Parent, Teacher, Management, Admin; "Management panel"; graduation-cap badge
- ✅ Owner's choice (2026-10-20, differs from MVC): no fixed info box on the sign-in pages ("You're entering the management
  panel…", "…system admin panel. Every action here is logged.") — the `notice` setting removed from all 5 roles + its
  block in signin.php. One-off messages ("You've been signed out", "Please sign in to continue") still show
- Note (MVC behaviour kept): the Back button never moves between pages — it always asks "Leave your workspace?"
- ☐ Later (after Thursday): the sidebar has no phone layout (MVC neither) — 16rem fixed, cramped on phones
- ✅ `_page_header` (2026-10-19): each view requires it where it belongs (`page_top` no longer draws it — notice pages keep it
  inside the board view, teacher/extracurricular has none); 3 settings `$title`, `$subtitle`, `$headerButton`
  (['label', 'hook'] — no HTML strings), cleared after; `e()`; dead `:has` rule gone; one `gap`; letter-spacing −0.01em
  was missing → added; tokens; prints hidden (`@media print` here, removed from both certificate CSS files).
  Measured next to MVC (title, subtitle, 8px between, 28px below, the Post Notice pill): identical
- ✅ Button: `c-btn--sky` + `c-btn--pill` (MVC's Post Notice look); "+ New Feedback" (teacher feedback) will use the same
  pill instead of MVC's second button style; new token `--skyblue-deep` (sky hover)
- ✅ Term marks: header overrides with `!important` removed — the section's 1.75rem gap was the header's own space below it
- ✅ `people-directory.js` no longer hides the header with inline styles → hides `#j-directory-view` (`hidden`)
  (the rest: "When building it" under 8)
- ✅ Two `<h1>` on every page: the sidebar's school name fixed; the others are listed under 3 and 8
- ✅ icons (`_icon_logos` → `icons.php`)
- ✅ Icons cleaned (2026-10-20): 101 → 75 drawings — 25 second names (`book-open` → `bookOpen`, `delete` → `trash`,
  `pencil` → `edit`, `medal` → `achievements`…) and 1 unused drawing (`image`) removed; ONE camelCase name per icon; our
  two callers switched (teacher card `bookOpen`, honours rows `calendar`); the `global.css` rule that "filled" the
  Extracurricular icon did nothing (its shapes are filled by themselves) → removed. The mapping for MVC's old names is in
  icons.php's comment. Checked: every icon on the dashboard (45) and honours board (21) finds its drawing
  — ☐ when building MVC pages that used `pencil` / `medal` / kebab names: use `edit` / `achievements` / camelCase
  (profile activities tab, teacher achievement card, certificate workspace, profile-modal.js)
- ✅ `_dropdown` (variants default / time / tinted / status / toolbar / cream / calendar / small; `$tone`, `$prefix`)
- ✅ pop-ups (2026-10-20): the frame is the browser's `<dialog>` — backdrop, Esc, staying on top, Tab kept inside and
  focus given back are the browser's job now. `dialogs-and-popups.js` (166 lines) → `pop-up.js` (~60); one name everywhere:
  `c-pop-up…`, `j-pop-up-close`, `data-pop-up-open`, `openPopUp` / `closePopUp`; `::backdrop`; scroll lock is CSS
  (`body:has(.c-pop-up[open])`); opening AND closing fade (`@starting-style`; Firefox closes without the fade); dead
  `modal:opened/closed`, `data-modal-target`, fake `showFeedbackBanner` (+ its 2 calls), z-index 2010 rule removed.
  Measured next to MVC: the header was off since the calendar build (MVC set it inline: 1.75rem padding, `#E5DFD7` line,
  11px eyebrow, 800 title, 13px description, round ×) → fixed in the frame; MVC's day schedule header is CREAM (our "sky"
  never showed in MVC) → sky tone + `$popUpTone` removed. "Are you sure?": `buttonIcon` + `wide` (MVC's log-out look: two
  equal buttons, white Cancel, 56px icon); Cancel hover = MVC's grey-beige (new token `--pebble`, also the header line).
  Forgot password (sign-in) = the shared frame + 'forgot' variant: its own overlay CSS / JS + 6 inline styles gone;
  **bug fixed**: a wrong password opened the forgot pop-up (both used `$error`) → `$isResetStep` / `$resetError`.
  `body [hidden]` (was `.c-app-shell [hidden]`) so `hidden` also works in pop-ups (they sit outside the app shell)
- ✅ `_form_card` (form shell, used inside panel pop-ups)
- ✅ `_datepicker` (2026-10-21, first piece of the Notice Board): PHP draws the button + the whole calendar (a
  `popover`: the browser opens it from the button, keeps it on top, closes it on Esc / outside click); JS (392 → 255
  lines) only fills the month, 42 days, year list and limits, and places it; it listens once on the page, so pickers
  added later need no setup. Settings like the dropdown's (`$datepickerId`, `$name`, `$selectedValue`, `$placeholder`,
  `$tone`) + `$minDate` / `$maxDate` / `$minAge` / `$maxAge`; all cleared after (MVC's leaked into the next dropdown).
  Fixed: `e()`, raw `$extraAttributes`, dead `$disabled` / `$ariaLabel` / `required` (ignored on hidden inputs),
  17 `!important`, inline styles, HTML strings, "Today" ignoring the limits, year list ignoring min/max dates, guessed
  heights. White-label: the button shows `country('date_format')` / `<html lang>` ("Oct 21, 2026"; MVC "21 Oct 2026").
  New tone `sand` (MVC had none). API: `setDatepickerValue(id, iso)`, `refreshDatepicker(id)`, `datepicker:change`.
  Needs dropdown.js on the page (`getSafeViewportBounds`). Measured on a temp page (deleted) next to MVC's admin notice
  form: button, calendar 320×383, header buttons, days 39×36, weekdays, footer — identical
  — recheck (2026-10-21): PHP now decides every date (owner's rule: what PHP can do, PHP does), see "School dates"
    below. Closes when the page scrolls (not when its year list scrolls); the year list scrolls itself only
    (scrollIntoView could scroll the page and close it).
- ✅ School dates, one place for every script (2026-10-21): `layouts/page_top.php` prints the school's today, its date
  styles and PHP's month / weekday names (JSON); `school-dates.js` (loaded first by `page_bottom.php`) gives
  `schoolToday` + `formatSchoolDate(iso, 'date' | 'day' | 'full' | 'long')` with PHP's date() letters. country.php:
  `date_format` + new `day_format` (October 19), `full_date_format` (October 19, 2026), `long_date_format`
  (Monday, October 19, 2026); `language` now only feeds `<html lang>`. Fixed: the calendar and the date picker used
  the computer's clock for "today" (past days, "Add event", the today ring, "Today") while the server uses the
  school's → could offer "Add event" the server then refuses; and they wrote dates with `<html lang>`, a second
  setting that had to match `date_format`. Checked: teacher dashboard (today ring Oct 21, card "October 14 · 09:00 –
  10:30", no "Add event" on a past day, "View all" "Thursday, October 22, 2026", pop-up "October 22, 2026") and the
  date picker inside the real page frame (PHP "Mar 05, 2026" = JS "Mar 09, 2026", Today = the school's) — 0 errors
    Left on purpose (owner): needs dropdown.js · no reduced-motion rule · screen readers hear only the day number ·
    English month / day names (the app has no translation system; all words come from PHP, so one could be added there)
  — until a page loads it, `check.sh` warns "class c-datepicker__… no loaded CSS" (19 warnings): expected
  — later uses: Notice passes `$minDate = today` (publish, expiry; the server checks too); `add-notice-page.js` →
    `setDatepickerValue(id, '')` instead of resetting the label by hand · People: "unlock" sets `data-unlocked="true"`
    + `refreshDatepicker(id)`, the required check moves to the server · Profile: `setDatepickerValue()` ·
    Achievements: the record pop-up's sand picker now has colours (differs from MVC: a bug fix)
- ✅ `_notice_card` (2026-10-21, Notice Board piece 2): reads a NoticeModel row (Clean/MyTry's SELECT) +
  `canManage` (the controller decides; MVC's view read `$_SESSION` and had its own permission maths). **No script of
  its own** (MVC's notice-card.js, 209 lines, deleted): pin = a POST form, edit = a link `?edit={id}`, delete = a POST
  form that asks first via the shared question pop-up (new: any form with `data-question-title/-text/-button` asks
  before it is sent, question-pop-up.js). The server reloads the board, pinned notices first — MVC moved the card
  in place without a reload (visible difference). Own class names (`c-notice-card__tag`, `__avatar`, `__icon-btn`):
  MVC's `.c-tag` / `.c-avatar` were defined differently in 3 files. Category = a class modifier, not data-*.
  Removed: 16 `!important`, inline styles, `htmlspecialchars`, a dead animation delay (no animation existed), dead
  `$cardIndex` / `$hidePinBadge`, the "Urgent" colours (not a category), `tabindex` on a card that does nothing.
  MVC's real look kept: its `!important` beat its own inline colours, so the expiry tag and the attachment are
  grey (checked by measuring MVC). White-label: `word('class')`, `word('institution') . '-Wide'`, `plural()`,
  dates via `country('date_format')` / new `short_day_format` (Oct 18). New tokens `--linen`, `--wheat`, `--teal`.
  New `config/posting.php` `audience` (every role a post can be for), also used by CalendarFeed now.
  New in the card (Clean/MyTry): "Publishes …", "Expired …", "Awaiting approval" tags so staff can tell those apart.
  Measured next to MVC's admin board (temp page, deleted): card 545 wide, 6px edge, radius 16, padding 20, tags,
  audience colours, title 17/800, body 13, footer tints, avatar 34, buttons 24×24, attachment 141×29 — identical
  — until a page loads it, `check.sh` warns "class c-notice-card__… no loaded CSS" (15): expected
  — for the board: the 2-column row (`css/layout/notice-grid.css`, MVC's `.c-notice-grid`); routes POST
    `/{role}/notice/pin`, `/delete` (id in the form), GET `?edit=` and `/attachment?id=` (for every role, also
    the parent); the controller sets `canManage = can_manage($notice)` (pinning follows the same rule — MVC let
    management pin, but not unpin, an admin's notice); the flash message after pin / delete; the board's filters
    decide whether cards need data-* again
  — ✅ audit log: uses `--teal` instead of its local `--theme-icon: #207C82`
- ✅ `_add_notice_page` (2026-10-21, Notice Board piece 3, owner chose "A"): a normal form the board page shows for
  `?post` / `?edit=5`; PHP fills it (edit, or after an error with what was typed), the server checks + saves, then
  the board. Settings: one `$noticeForm` (id, values, choices, banner, error, maxFileMb) from the controller.
  MVC's add-notice-page.js (676 lines: view switching, edit pre-fill from the card's data-*, JS checks, background
  send, a 2nd copy of the card in HTML strings) → `add-notice.js` 29 lines (the target field follows the category,
  the file's name shows). Native checkbox + a `<label>` file box (MVC: a fake div checkbox, a hidden input opened
  by JS). ~40 inline styles, ~15 `!important` → `add-notice.css`. Shared: `c-btn--back` (moss, also People and other
  forms), `c-btn--large` ("Publish"). Tokens `--moss-deep`, `--aqua`. White-label: `word()` / `plural()` in the
  labels and options. Target choices reuse `CalendarModel::choicesFor()` shapes (grades / classes / activities).
  Measured next to MVC's admin form (temp page, deleted): form 768×1050, header 104, every field at MVC's y,
  heights 47 / 42 / 43 / 152 / 143, bottom row 59, Publish 37.5 high — identical (Publish 2px wider)
  Differences: opening / saving / Back reload the page (address changes, Back works); errors show in red inside
  the form (MVC: a pop-down banner); the textarea gets the input's focus ring (MVC had none)
  — until the board loads it, `check.sh` warns "c-add-notice__… no loaded CSS" (23): expected
  — for the board step (NoticeController): mode from `?post` / `?edit=`; choices per role (categories, audience,
    `schoolWide` = false for teachers: no "All …" option); the banner per role (a teacher's notice waits for an
    admin: say so); save with checks (title 3–200, body 10–5000, publish / expiry from today, expiry after publish,
    file type + size from config, 5-pin limit); keep what was typed after an error; `target` 'all' | 'grade:5' |
    'class:9', `activity` 'all' | id, only ids that were offered
- ✅ `_image_picker` (Extracurricular), `_export_pdf_button` (Academic)
- ✅ page frame (`layouts/page_top`, `page_bottom`), `asset()`, page rows (`css/layout/*-grid.css`)
- ✅ Clean-up (2026-10-20): 8 empty files deleted; dead `isAuthenticated`, `spendTry`, `useLatest`, `applyCardDecisionOverlay`,
  `showFormBottomToast`, 2× `escapeHtml`, `.icon--*`, `.visually-hidden`, 3 unused `.tone-*`; `.c-main` once (global.css);
  bar-chart's copy of the dropdown rule removed; **icons one way: `icon('name', size)`** everywhere (outputs `c-icon`);
  `achievement_row` → `_achievement_row`; icon sprite's inline style → `.c-icon-sprite`; `e()` on every printed number;
  `c-font-display` (no CSS here) → letter-spacing on the donut centre and pop-up title; event pop-up's leftover
  `c-form-body`, `c-form-footer`, `c-btn--danger-zone` removed
- ✅ `bin/check.sh` — the rule check (syntax, banned things, security, one-way rules, braces, dead code, duplicates) on the
  rebuilt files only (it reads each page's `$styles` / `$scripts` / `$grids`); run it before every commit
- ✅ (2026-10-28) Still reported by `bin/check.sh` (not rebuilt yet): auth (AuthController / UserModel / Mailer / routes braces;
  inline styles in signup); UserModel's Profile methods stay for the team
- ☐ A visible "Saved" / "Deleted" message (toast): MVC showed none on the dashboard; build a toast component when the first
  page needs one (then event-editor.js calls it after save / delete)
- ✅ Auth pages (sign up): the inline styles → classes (2026-10-20)
- ☐ MVC quirk (owner's call): the multi-select's placeholder ("Select classes…") sits on the right — same as MVC

**1. Dashboard** (all 5 roles) — layout ✅, data still mock
- ✅ `_metric_card` (2026-10-19): only `$card` (no loose `$label` / `$value` that could leak in), `e()`, one icon-name style,
  plain `<svg class="c-icon">`; the fade-in stagger is CSS `:nth-child` (no inline style, no delay maths in 5 views);
  colour `lightblue` (one name); `--white` token; reduced-motion respected; dead hover lift removed (the animation always
  held it — MVC never moved either); number letter-spacing −0.01em was missing → added. Measured next to MVC: identical
  — ☐ real numbers (mock now; Clean/MyTry's `DashboardController` has them once People / Extracurricular / TermMark /
  Achievement / Verification models are in)
  — later pages (achievements, child profile, teacher extracurricular): see "When building it" under 3, 5, 6, 10
- ✅ `_bar_chart` (scores / counts) — ☐ real term marks
- ✅ `_donut_chart` (2026-10-19): slices = one circle each with a dash (`stroke-dasharray`) instead of trig arc paths
  (102 → 45 lines); colour names (`.c-series--*`), `unit` word for the tooltip ('students', 'members', 'players'),
  legend = spans pointing at their slice; dead `id`, `centerLabel`, `totalLabel`, `.c-donut-card__total*`, hand-typed
  paths removed; `config/brand.php` deleted. Shared now: `panel.css` (the white card), `chart.css` + `chart.js`
  (colour names + ONE tooltip for bar chart and donuts; bar-chart.js 135 → 88 lines). Checked next to MVC (gap ≈ 5° as MVC).
  ☐ real numbers (with Extracurricular)
- ✅ calendar (`calendar/_calendar`, `_day_card_template`, `_day_card_popup_template`, `_event_editor`) — real data
- ✅ Calendar footer fixed (2026-10-20, owner's screenshots): our "no Add event on past days" rule left staff with a small
  "No events" box + gap, or a card + gap. Now calendar.js picks per DAY (can this person add today?), every role gets the
  "No more events" piece, and the boxes size themselves (`flex: 1 0 4.5rem`: one card tall, then fill what's left —
  the owner's idea); 3 copies of the same 12 lines → one shared rule; the "tall box" modifier deleted. Checked: past /
  future × events / none — always exactly filled. Month name: our calendar dropdown drew it at 14px (MVC 13px) and cut
  with "…" → "Septem…"; now 13px and never cut, like MVC
- ✅ `_upcoming_events` (2026-10-20): 3 inline styles → CSS (they overrode the CSS: title 15px, names 13px — now the CSS says
  so; the dead 16px / 14px values gone); `e()` (also the count); fake fallbacks "JUN" / "Academic" / "sand" removed; own
  variable names cleared after (was `$title`…); dead `$eventsTitle`; "No upcoming events" message = a class; tokens.
  Measured next to MVC (student): card 545×287, title, names, date block — identical
- ✅ Upcoming Events = REAL (2026-10-20, owner's call): `CalendarFeed::upcoming($user)` — the next 3 events this person may
  see from today (`CalendarModel::between`, the calendar's own rules), so the card and the calendar always agree; tag
  colour per category (`config/calendar.php` → `tag_colors`, `upcoming_count`); date block = `<time datetime>`; the mock
  `$upcomingEvents` deleted from all 5 roles (it showed Sep 27–30 as "upcoming"). Checked per role: staff see the
  staff-only day, parents the parent meeting, the student her class's visit
- ✅ Demo data: 7 upcoming events added to `events` / `event_scopes` (Oct 23 – Nov 19: whole school, Grade 10, class 10-A,
  Chess Club, Drama Society; General / Academic / Extracurricular)
- ✅ Sign-up page: 4 inline styles → classes (`.field-label-note`, `.field-input--code-large`, `.auth-resend`,
  `.auth-resend__btn`); landing page's `$role` link → `e()`. `bin/check.sh`: 0 errors
- ✅ (2026-10-28) Formatting (warnings only): the old sign-in backend (AuthController, UserModel, Mailer, routes/web.php) still has
  `{` on the same line in ~45 places → fix with the auth review
  — Note: MVC's Academic page also builds upcoming-events data its view never shows → don't carry it over (4. Academic)
- ✅ `_unread_notices` (2026-10-20): bug I'd made fixed — the pop-up's "No unread notices" reused the card's
  `.c-notices-card__empty` class, so its 2nd rule took the side padding off the card's dashed "No more Notices" box →
  own class `.c-notices-modal__empty`; `e()`; own variable names (`$noticeList`, `$noticeCount`, `$noticePill`) cleared
  after (was `$title`, `$list`… — `$title` is the page header's); dead `$noticesTitle`, `$noticesPill`, `j-notices-view-all`
  removed; pop-up title `<h2>`; tokens without fallbacks. Checked with 2, 1 and 0 notices (temporary mock change, undone):
  "No more" box padding 16px = MVC; pop-up message 24px 0 = MVC; open / × close. Still mock data (see 2. Notice Board)
  — ✅ its pop-up's own classes `c-notices-modal__*` → `c-notices-pop-up__*`, id `j-notices-pop-up` (one name; checked)

**2. Notice Board** — student, teacher, parent, management, admin
- ✅ pages (2026-10-21): student/notice, parent/notice-board, teacher/notice, management/notice, admin/notice —
  5 thin role views + one shared `_notice_board.php` (header + "Post Notice" link for posters, the message after an
  action, search · tabs · filters, the cards in `l-notice-grid`, "nothing found" + Clear filters); the form for
  `?post` / `?edit=5`. `notice-board.css` rewritten (no `!important`, MVC's measured look), `notice-filter.js`
  191 → 84 lines (same filters; cards carry data-category / -audience / -mine / -for-me from the server)
- ✅ pieces: `_datepicker`, `_notice_card` (+ TODAY / YESTERDAY like MVC, data-* for the filters), `_add_notice_page`
- ✅ backend: `NoticeController` (board, save, pin, delete), `NoticeModel` + `Membership` (Clean/MyTry; CalendarModel
  uses Membership now: one answer to "which classes / grades / clubs is this person in"), `config/notices.php`
  (addresses + words per role, categories + audience per poster, `needs_approval` = [] (owner: a yes / no list for
  later), max 5 pinned), `permissions.notices` (post, see_all), routes for 5 roles
- Owner's rules (2026-10-21): A a class / grade / club notice reaches only that group's people in its audience ·
  B expired notices are gone for everyone; scheduled ones show to admin / management / their author only ·
  C teachers post Academic / Extracurricular to their own classes / clubs, for their students and those students'
  parents · D delete = really delete; the activity log keeps the history
- Fixed from MVC: class / grade / club targets were ignored (everyone saw them) · scheduled notices were hidden even
  from their author · the server only protected admin notices (a teacher could edit management's by hand) ·
  unknown categories silently became General · model escaped text (the "&amp;" titles)
- ✅ Dashboards: the student / parent "Unread Notices" are real (`NoticeModel::unread`); the mock notices are deleted;
  opening the board marks its notices read
- ✅ Found + fixed on the way: MySQL's clock was UTC while PHP's is the school's → `Database.php` sets the session
  time zone (NOW() and PHP agree); `Validator` 'min' rule; `Controller::queryText()`; `Model::idList / ids / pairs`
  shared (CalendarModel had them privately); page header's button can be a link (`href`)
- Checked: admin board measured next to MVC (header 57, Post 138.7×40.3, bar at 141, search 260×42, filters 152.5 /
  155.6, grid at 231, gap 20 — identical); admin publish → error keeps typing → edit → pin → delete (asks first),
  each with MVC's message; teacher tabs, form (own 3 classes / 2 clubs only), post to 10-A; who sees what for all 5
  roles; 5-pin limit ("Maximum of 5 …"); unread before / after opening the board; activity log rows; curl attacks:
  another class, whole school, General, management's / admin's notice (edit, delete, pin), no token, `title[]=x`,
  audience Management (teacher, management), student posting (404) / `?post` (no form) → all refused, 0 saved
- ✅ Approval (2026-10-27, owner: approve where it lives; the Verify page is removed): teachers' notices wait
  (`notices.needs_approval` = ['teacher'], white-label switch `feature('approvals')`, approver `permissions.notices.approve`
  = admin). Admin's board: tabs "All Notices" / "Awaiting Approval (n)"; a waiting card is greyed out (`--waiting`)
  with Reject (asks for a reason: the question pop-up's extra) / Approve → `POST {board}/review` → "Notice approved." /
  "Notice rejected.", logged. A teacher's new notice AND every edit waits again (note cleared); an admin / management
  edit keeps the state. Only approved notices can be pinned (pin hidden, refused, a waiting one is unpinned, the
  form hides "Pin the notice"). A rejected notice is gone for everyone but its author ("Not approved: reason").
  Removed: sidebar link, `admin-verify.css/.js`, `reject-modal.css`, `user_accounts.review*` (update 025: teacher
  accounts need no approval — PENDING until they sign up), PeopleModel's read of them.
  Checked (curl): no token, signed out, teacher / management / teacher-on-admin-URL, `decision[]=x`, reject without a
  reason, `note[]=x`, unknown id, already decided, pin a waiting one → all refused; reject → admin + management no
  longer see it, teacher sees the reason; teacher edit → pending + unpinned; approve → a 9-B student sees it (model);
  admin edit keeps approved. Fixed after the owner's look (2026-10-27): the admin page didn't load `tabs.css` (plain
  browser buttons) → the same tabs as teacher / management (39.6px); Reject = `c-btn--maroon` (solid, white text, as
  MVC's approval card), Approve = `c-btn--dark`. Screenshot checked next to MVC's approval card.
  Reason box checked in the browser: button off until typed; sent from the page → "Notice rejected." + the reason saved.
  Bug found + fixed there: `form.id` was the form's hidden field named "id" → `form.getAttribute('id')`
- ☐ still open: the attachment (later, owner); the filters' JS-vs-server talk (later, owner); MVC's filters and tabs
  measured on the teacher board (only admin measured)
- When building it:
  - page header INSIDE the board view (it hides when the post form opens); "+ Post Notice" = `$headerButton`
    (admin, teacher, management); student / parent pass none
  - `notice-filter.js` uses `c-select…` → the dropdown component (audience = multi-select)
  - search / filters: **owner (2026-10-21): keep MVC's JS filtering for now; DISCUSS later** (server `<form method="get">`
    + SQL was offered: less JS, its own address, but a reload per choice)
  - after save / pin / delete: the board reloads and PHP prints the message at its top (owner: no pop-up, no extra
    component; reuse a message look we already have)
  - file uploads: **later** (owner). Until then the server doesn't accept an attachment
  - ✅ teacher notices waiting for admin approval: on the admin's board (2026-10-27, above)
  - header 1.75rem + page stack 1.5rem below = MVC's look → measure
  - the dashboards' Unread Notices become real: bring `NoticeModel` (`unread()`, `markRead()`) + `Membership` (which
    classes / grades / clubs a person is in) from Clean/MyTry; the tables `notices` + `notice_reads` already exist (5 + 6 rows);
    opening the board marks notices read; then the student / parent dashboards pass `unread()` instead of the mock
  - a shared card header (`_card_head.php`: title, icon, count pill, View all) was offered on 2026-10-20 — owner chose to
    keep separate cards for now; reconsider if the Notice Board needs the same header

**3. Profile** — all 5 roles (+ parent/child-profile)
- ✅ My Profile, 5 roles (2026-10-22): `admin|management|teacher|parent|student/profile.php` (thin) →
  `_profile_page` → `_profile_hero` + `_info_card` (new, shared). CSS: `profile-page.css` (rewritten: no `!important`,
  no inline styles, dead date-picker / dropdown / toast-JS rules gone), `profile-hero.css`, `info-card.css` (card +
  its grid + `--wide` / `--on-tint` / `--editable`), row `layout/profile-grid.css` (MVC's extra 24px under the header;
  padding, because a margin merges with the header's). JS: `profile-page.js` 246 → 45 lines (edit on / off, password
  form open / close; Cancel = the form's reset). 7 colour tokens for the hero gradients (`--pine` … `--mint`).
- ✅ Backend: `ProfileController` (show / save / password), `PeopleModel` (new: `find`, `teaching`, `activities`; People
  adds to it), `config/profile.php` (layout per role, editable columns, rules, subtitles), `app/Mock/profiles.php`
  (MVC's example values for details nobody stores), `permissions.profile.password`, routes inside `feature('profile')`,
  Validator `name` + `phone` rules (Clean), `UserModel::updateProfile()` only accepts a fixed list of columns.
  Activity log: PROFILE_UPDATED, PASSWORD_CHANGED, PASSWORD_CHANGE_FAILED.
- Owner's choices (2026-10-22): people change only their contact details (phone, address, emergency contact name +
  phone); names, birthday, ID numbers, email = the school office (People). Students: read only (no Edit button, no
  password, like MVC's password). No photo upload yet; no "password changed" email yet.
- Checked: all 5 roles next to MVC (every measured size / colour / position the same, the hero colour per role, the
  same sections and cards); edit mode (boxes 34px sky, pressed button sky, Save row), refused save (stays in edit mode
  with the reason), Cancel, good save (MVC's toast), password: wrong current / mismatch / weak / good (+ changed back);
  curl: no / wrong token, `phone[]=x`, `password[]=x`, `first_name=` sent by hand (ignored), wrong role, student POST
  (404), signed out (sign-in / JSON), `<script>` (shown as text); `profile` switched off → sidebar plain box + 404.
- Looks different from MVC on purpose: Save / Cancel / Update Password are our buttons (MVC's showed the browser's
  plain grey: its page never loaded button.css); the student page has no Edit Profile; real data instead of MVC's
  examples (MVC's student eyebrow said "Grade 6-6-A", its "Parent Email" showed the student ID).
- ✅ Password eye (2026-10-22, owner; MVC's script had a dead handler for it): shared `_password_field.php` +
  `password-field.css` + `password-field.js` (one click listener, also works in pop-ups). Used by sign-in, sign-up,
  forgot password and My Profile's 3 boxes; the copies in `auth.css` / `auth.js` deleted. Checked: sign-in + sign-up
  eye measured before / after (same place, size, colour, box padding); show / hide on sign-in, sign-up, profile; the
  labels still point at their boxes. Not clicked live: the forgot pop-up's reset step (needs an emailed code; same piece)
- ✅ Photo upload (2026-10-27, People stage E): while editing, a round button on the hero's photo picks a new one and
  sends it (`ProfileController::photo()`, `core/Upload.php`: PNG / JPG / WebP, looked inside, random name, 1 MB
  `profile.photo_kb`); the old file is deleted and the session's photo updated. Students have none (they change nothing).
  No "remove photo" yet (a new one replaces it). Tested: no token, a .php renamed .png, nothing chosen → refused
- ☐ Later (owner): the "your password was changed" email (Clean has the template), counting wrong current passwords
  like sign-in does
- ✅ Names editable on People: the office never edits its own account there (admin not listed, management can't open
  management), so `$_SESSION['user']['first_name']` never goes stale — nothing to do
- ☐ Team: the example cards that need real columns: Nationality, Religion, House, Prefect, Attendance, zone / district /
  province, Qualifications, office / desk / extension, PTA, Academic Year (see `app/Mock/profiles.php`)
- ✅ the hero's name is an `<h2>` (the page header has the one `<h1>`) · sidebar person button = a plain box while
  `feature('profile')` is off · header spacing measured (+24px, `profile-grid.css`) · "compact" dropdowns gone with
  the dropdowns themselves (no dropdowns / date pickers in the cards now)
- When building the rest (People, child profile):
  - `_profile_information_tab` + `_profile_activities_tab` → with People (5); cards = `_info_card` (`info-card.css`),
    then delete the second copy of the card rules in `profile-modal.css`
  - `_person_profile_modal` is a hand-made frame (`c-modal-overlay`) → `_pop_up.php`
  - `profile-modal.js` uses `c-select…` → the dropdown component
  - child profile: metric cards with `l-metric-cards-grid--tight` (1.5rem below); its 4 achievement cards = one
    `AchievementModel` method shared with student/achievements; its hero = `_profile_hero` (`$heroEditing = null`)

**4. Academic** — student/academic, management/academic, admin/academic, teacher/term_marks
(teacher/students → 5. People; `_unassigned_candidate_row` → Extracurricular, where MVC uses it)
- ✅ Stage A, student/academic (2026-10-22) — MVC's example marks (`app/Mock/academic.php`, MVC's own formula) until the
  marks backend is built. `AcademicController::student` / `studentPrint`, routes behind `feature('term_marks')` (the
  sidebar's flag). Pieces, each PHP + CSS + JS:
  - `_export_pdf_button` (only MVC's sky look was ever used → no tones) + `export-pdf.js` (738 → 40 lines): loads a
    printable page the SERVER draws (`print/record_book.php` + `css/print.css`: letterhead from `school()` — logo,
    name, motto — instead of MVC's typed "L'École International", "L'É" crest, 'en-GB' date and random "Document Ref")
    into an out-of-sight frame and prints it
  - `_digital_record_book` + `record-book.css` (moved out of profile-modal.css; MVC's ~12 inline styles → classes;
    `c-font-display` → letter-spacing on the title) + `record-book.js`: PHP draws every grade × term (table body +
    feedback with `hidden`), the script only swaps them (MVC built the HTML twice, in PHP and JS). Grade dropdown lists
    the grades that have marks (MVC listed 6–13: 12 and 13 did nothing). Dropdowns = `default`
  - `_academic_section` + `academic-section.css/js` + `layout/academic-grid.css` (the two columns): total marks card and
    Performance Trend — PHP draws each grade's SVG (MVC built it in JS); ‹ › / dots / card click / tooltip in JS
  - `ordinal()` helper (Clean/MyTry); 11 colour tokens (total card greens, trend bars / axis); `check.sh` knows print.css
  - Words: Grade / Term / Class / Teacher / Student via `word()` (labels, options, headings, the print page)
  - Measured at 1440 next to MVC: every box the same position, size, font and colour (record book 611×641, total card
    489×227, trend 489×366…); grade 8 term 2 shows the same numbers as MVC. Phone width: stacks, no sideways scroll.
    curl: signed out → sign-in · teacher → own dashboard · `grade=99`, `grade[]=6`, `<script>` → back with "That record
    does not exist." (JSON when asked)
  - Look differences (on purpose): the record book's book icon is a maroon outline (MVC drew it as a black block —
    no `c-icon` class); the section has no 16px margin under it (MVC's record book had one; nothing visible); the
    trend's current card is `--chalk` (#FAF8F5, MVC #FAF9F6)
  - Deleted the MVC copies it replaces: `student-academic.css`, `student-academic.js`
- ✅ Stage A fixes after the one-by-one review (2026-10-22): Export printed the SIGN-IN page when the session had
  ended → prints only a page with `body.c-print`, otherwise reloads (tested: signed out, then Export → sign-in page);
  the record book no longer changes the caller's `$exportUrl`; the side cards find the book by `data-book` (not by
  the layout); each trend card's button label reads its averages to screen readers (no `<title>`: it would add the
  browser's own tooltip next to MVC's)
- ✅ Stage B, teacher/term_marks (2026-10-22) — MVC's example students + marks (`app/Mock/term_marks.php`); Submit
  saves nothing (like MVC); the Term filter only shows the term (like MVC). `TermMarkController::index`,
  `GET /teacher/marks` (the sidebar's link) behind `feature('term_marks')`. Each piece reviewed on its own first:
  - `_teacher_achievement_card` → **`_student_card.php` + `student-card.css`** (it is the student card of two
    features; its CSS was already `c-student-card`): new 'marks' variant (Edit Marks = the View look, 20px sides;
    `$studentCardTarget` = its panel), `--editing` (sky ring, lifted); 'grid' alone reads 'from' / 'issue'
  - `_term_marks_panel.php` + `term-marks-panel.css` + `.js`: one panel per student drawn by PHP, hidden (owner's
    option A); the script only shows one, closes it (×, Cancel, Esc — not while a menu is open), filters the subject
    rows (one row → one column, CSS `:has`), and on Submit keeps what was typed (`defaultValue`) + MVC's green note.
    Tint from `c-marks-panel--{tone}` (12 tokens); MVC's inline `onsubmit`, `name="marks[English]"`, `display:none`
    ×2, the never-shown default tint and ~45 `!important` not carried over
  - dropdown `small` → **`tick`**: measured, MVC shows the white pill (128×42) with small options (12px, 6/12) and a
    tick — its "compact" trigger rules never applied, so `small` (30px) was wrong
  - search `c-search--short` (40px; Achievements stays 42) · `student-cards.js` = one search for both pages
    (replaces `teacher-achievements.js`) · `layout/student-cards-grid.css` `--marks` (filter ≥ 9.5rem, 16px to the
    cards, 4 columns from 1100px; the row-wide dropdown min-width removed — MVC's filters are as wide as their text)
    · `layout/term-marks-grid.css`: header + bar pinned, only the cards scroll, open = 3 : 2 with 2 card columns
    (from 640px), stacks below 960px
  - Measured at 1440 next to MVC, closed and open (Maya): every box the same (cards 259×165, Edit Marks 126×34,
    workspace 1120×705, panel 435×669 at 973,211, banner, filter 128×42, rows 195×46, inputs 56×31, feedback 397×219,
    Cancel 89×36, Submit 113×34, the ring), the subject menu (128 wide, options 29.5 high, tick). Behaviour: filter,
    Submit → note + kept, Cancel → closed, reopen → submitted marks, switch student, Esc, search by name and ID.
    Achievements re-checked (search, empty box, filter, Record). curl: signed out → sign-in · student / parent →
    their dashboard · POST → 404. Page never scrolls at 1440 (only the cards)
  - Deleted the MVC copies: `teacher-term-marks.css`, `teacher-term-marks.js`, and `teacher-achievements.js`
- ✅ Stage C, management/academic + admin/academic (backend 2026-10-23, page 2026-10-26) — real data (grades, classes,
  class / subject teachers, curriculum stages); the chart is MVC's example numbers (`app/Mock/class_performance.php`).
  `AcademicController::overview` + 6 POSTs (`addGrade`, `deleteGrade`, `saveClass`, `saveSubjectTeacher`, `saveStage`,
  `deleteStage`), routes for `permissions.academic.edit`; `StageModel` (new), `ClassModel`; update 023 (grade / stage
  names worked out with `word()`, not stored); helpers `grade_name`, `grade_range`, `class_name`, `class_prefix`,
  `class_section`. Pieces, each PHP + CSS (+ JS where it has one):
  - page: `_academic_overview.php` (both roles' files only set their CSS / JS and require it) + `academic.js`
    (~230 lines; replaces MVC's `grade-card.js` 2,359 + `teacher-hover.js` 263): moves the page's ONE class editor and
    ONE stage editor into place, re-opens them after a refused save, subject chips, range label, the workload card
  - `_section_box` (MVC drew the two section headers twice), `_grade_card`, `_grade_class_row` (`<details>`),
    `_grade_class_editor`, `_teacher_workload` (MVC's `_grade_teacher_hover`), `_curriculum_card` ('card' / 'editor';
    curriculum-card.css 565 → 190 lines), `_add_grade_pop_up` (shared `_pop_up.php`; picks a free grade NUMBER — MVC
    typed a name)
  - shared pieces changed: `_bar_chart` 'filters' (a GET form; bar-chart.js sends it on change; the header wraps on
    narrow screens) · `layout/grade-grid.css` (`l-grade-sections`, `l-grade-grid` columns, `l-stage-grid`) ·
    `l-chart-calendar-grid--tight` · `pop-up.css` `c-pop-up__body` / `__footer` · `form-field.css` `c-field__file-hint`
    → `c-field__hint` (any field) · `core/Controller.php` `wantsJson()`: a wrong-role background request now gets JSON
    (403), not a redirect · dropdown 'teacher' variant, question pop-up `blocked` / `extra` (2026-10-23)
  - Words: new `subject` word (`wording.php`: Module / Learning Area); every "Subject" on the Academic pages, term
    marks, the record book and its print page now uses it. Class badge tint by place in the grade (1st sky, 2nd
    sunshine…), not by letter, so any section names work
  - Measured at 1440 next to MVC: chart 733×535 at 288,129 · structure box at 676, 1110 wide · grade cards 524 wide,
    rows 66 high, badge 40×34 · curriculum cards 524×160 · filters 42 high white pills · class editor fields at MVC's
    x / widths. Behaviour (browser, real saves then undone): filters reload, workload card, ✎ / Add Class / Cancel,
    refused class save re-opens with what was typed + red banner, stage editor (add / Enter / duplicate skipped / ×,
    "To" follows "From"), refused stage save re-opens, Add Grade → Grade 12 with 12-A, Add Class 12-B, stage for
    Grade 12, subject teacher in the background, delete stage, delete grade (pills, button off until one is ticked).
    curl: no token / signed out / teacher on all 6 POSTs → refused · `number[]`, `grade_id[]`, `id[]`, `classes=1`,
    `subjects[][]`, `<script>`, out-of-range, other grade's class, grade with students, overlap, from > to, unknown
    ids → their message · subject-teacher JSON on every path (422 / 404 / 419 / 403)
  - Look differences (on purpose): section titles 16px (card title; MVC 18) · Add Grade / Add Class 12px (MVC 11) ·
    "6 SUBJECTS" the round c-tag 10px (MVC 9px) · subject chips c-chip 12px / 600 midnight (MVC 13px / 700 black),
    MVC's 10px corners kept · class editor hint 10px (MVC 9) · Add Grade pop-up: the shared header (umber icon, MVC
    cherry), footer band, Add Grade = c-btn--solid (MVC maroon) · MVC's filters looked white, not cream: its
    `.c-select--cream` rules never applied (the same as term marks) → `default`
  - Deleted the MVC copies: `grade-card.js`, `teacher-hover.js`, `academic.css` (and the old grade-card.css rules)
- ✅ Academic rules (owner 2026-10-26), tested in the browser + curl:
  - Rename a subject: type in its chip; the chip sends the subject's id (`subject_ids[]` beside `subjects[]`), so it
    keeps its teachers and marks and the new name shows everywhere. Only this stage's ids are kept (another stage's id
    → a new subject); lists of different lengths → "Please try again."; the model swaps names through a stand-in so
    two subjects can trade names. A subject with marks can be renamed, never removed
  - Add Grade with no curriculum covering it: the message says "Add a curriculum that covers it…"
  - A subject teacher who doesn't have that subject in their listed subjects: allowed, the message says so
  - A curriculum save that would remove subject teachers (range made smaller, subject taken out) asks first
    ("Save Anyway"); the page knows each stage's choices (`StageModel::assignments()`)
  - Classes: deleting them one by one may leave a grade empty; a class with students is never deleted (owner's pick)
  - Demo data note: testing re-made 6–9's Mathematics (its id is now 51, was 2); everything else as before
- ☐ Curriculum editor: subjects can't be reordered (a new one goes last) → drag or ↑↓ if the school needs an order
- ✅ Combining pass (owner 2026-10-26; the 5 dashboards stay separate files on purpose), each re-measured in the browser:
  - `c-card` (panel.css): the white 14px card that 6 components had each typed (profile section, record book, timeline
    panel, trend, grade card, section box) — the notice card keeps its own (coloured edge + hover lift)
  - `c-avatar` (+ --light / --medium / --large, avatar.css, on every signed-in page) + `initials()`: 7 circles and 6
    copies of the initials code → one. Same sizes; differences: the dropdown / workload initials 10 → 12px, the Record
    pop-up's 13 → 12px, the notice's and student card's initials 800 → 700; a student card's photo shows a midnight
    circle while it loads (it was see-through)
  - `c-pop-up__form` / `__body` / `__footer`: Add Grade, Record Achievement, its evidence preview and the calendar's Add
    event share them (fields scroll, footer stays: checked in a 560px-high window); the event footer is 2px lower (16px)
  - `c-panel__head`: Upcoming Events and Unread Notices had the same header rule
  - Found while doing it: the notice author's name was 800 → 700 (title rule); student-card.css had
    `var(--card-tone, var(--midnight))` (a fallback) → `var(--card-tone)`; dead `box-sizing` lines (global.css sets it)
  - So it doesn't happen again: `bin/check.sh` section 7 also warns on the same 4+ lines under two names
    (`bin/same-rules.php`); its "no fallback" rule now catches `var(--a, var(--b))` too; it reads what every page loads
    from `page_top` / `page_bottom` instead of its own copy of the list. `next-feature` skill: search for the same LINES
    before writing a rule, and the list of shared looks to reach for first
  - Left as is (owner): the coloured dot (chart / donut) and the two identical page rows — check.sh still shows them
- ✅ (2026-10-28, owner) `css/print.css`: the letterhead uses weight 800 (twice) → 700, like every other title
- Note: menu tabs whose page isn't built yet (Extracurricular, People…) open the 404 page until their feature is built
- ✅ (2026-10-28) `routes/web.php`: `{` on the same line all through the file (15 warnings) → one formatting pass
- ✅ (2026-10-28, owner chose B) All pages, phone width: with the full sidebar a 500px window leaves ~180px for the page
  → under 768px sidebar.js shows the collapsed (icons-only) menu, not saved in the cookie; the » button still opens it
  (a parent's "Viewing" switch is in the opened menu). Checked at 500px: the page gets 420px, nothing scrolls sideways.
  Catch: on a phone the menu draws open, then folds (300ms) — the server can't know the screen's width
- For later pages:
  - People pop-up + child profile (5): `_academic_section` with real data; the record book's `edit` look (MVC: add mark,
    feedback box — only `profile-modal.js` used it); then delete the record-book rules still in `profile-modal.css`
  - Certificates (9): MVC's `paginateA4Document` lived in export-pdf.js → build it with the certificate page; its
    print page can use `css/print.css`'s letterhead
  - Team: real marks (tables `marks`, `term_comments` exist) → a marks model replaces `app/Mock/academic.php`, and
    the Academic chart's example numbers (`app/Mock/class_performance.php`)
  - Extracurricular (5): the teacher-in-charge picker shows `_teacher_workload` too (the 'teacher' dropdown; move the
    hover code from academic.js into its own file then)
  - People (5): moving students between classes (a class with students can't be deleted until they are moved)
  - People (5), owner 2026-10-26: switching a teacher off needs a handover first — their class (class teacher) and
    every subject they teach go to someone else, or back to "Assignment pending" (MVC's `_teacher_handover_modal`).
    Today a switched-off teacher drops out of the pickers but keeps what they had
- When building it:
  - ✅ Grade / Subject / Term filters → `default` (measured: MVC's cream rules never applied; white 42px pills)
  - ✅ MVC's delete pop-up has an extra grade / class pill section → `askQuestion()` `extra` slot
  - ✅ `grade-card.js` → `academic.js` + `askQuestion()`; PHP draws the dropdowns
  - ✅ term marks: the hand-written subject filter → the `tick` dropdown; `teacher-term-marks.js` tick code gone
  - ✅ `_add_grade_modal` → `_add_grade_pop_up` in `_pop_up.php`
  - ✅ `_digital_record_book` "compact" dropdowns → `default`
  - ✅ `academic.css`'s `.c-primary-grid` → the `l-chart-calendar-grid--tight` row; academic.css deleted
  - ✅ MVC's `AcademicCrudTrait` builds `upcomingEvents` its academic views never show → not carried over

**5. Extracurricular** — student, teacher, management, admin
- ✅ (2026-10-27) Owner: **MVC's example data, no backend connected** (`app/Mock/extracurricular.php`: MVC's six
  programmes; every form saves nothing and says so — `data-mock-message`). `ExtracurricularController::index($role)`:
  the list (GET search + All / Sports / Clubs tabs, the server picks the cards), one programme (`?id=`; the teacher's
  page always shows the one they run), one achievement (`?award=`, `&edit=1`, `award=new`); `config/extracurricular.php`
  (types, categories, age groups); `permissions.extracurricular` (manage: admin + management; approve: admin).
  - The list: `_extracurricular_card` (Enrolled / Interest / Requested for the student; Change teacher in charge for the
    office = `_pick_menu` — a `<details>`, no script; a programme waiting for approval greyed with the admin's Reject
    (reason) / Accept, everyone else "Pending Approval"; waiting ones hidden from students) + the Create card →
    `_programme_pop_up` ('create').
  - A programme (staff = `_programme_view`, student its own page): `_extracurricular_hero` (+ Edit Programme pop-up),
    `_extracurricular_awards_panel`, the teacher's `_enrolment_panel` (metric cards, `_join_request_card`s scrolling
    sideways, Reject asks a reason), `_extracurricular_schedule_panel` (the shared calendar + `_staff_card`s; each Edit →
    `_staff_pop_up`), `_club_notice_board` (the shared `_notice_card`, display only), `_teams_panel` (age filter = GET
    form; unassigned students + Assign; `_team_card`s; Add / Edit Team pop-ups with member rows), the admin's
    `_danger_zone`. The student: staff pair, `_squad_roster` beside the calendar (same height by CSS — MVC measured it
    in JS), notices, Details & Status.
  - `_award_page`: one achievement, viewing or editing (the server draws the mode — MVC's 775-line script swapped a
    hidden view); participants / photos added and removed by `repeat-list.js`, pictures previewed by `image-picker.js`.
- New shared pieces: `_reason_template` (the reason box — the notice board and Parent → Approvals moved onto it) ·
  `_pick_menu` · `_image_picker` · `_staff_fields` · `_danger_zone` · `c-field-section` (form-field.css) ·
  `c-empty-box--tinted` · `l-filter-bar--close` · `programme-panel.css` (the panel frame MVC typed inline 9 times).
  question-pop-up.js + feedback-banner.js: a form that asks first can also be example data (`requestSubmit()`, marked
  as answered). check.sh: reads `$styles` lists over several lines (perl) and `can('x.…')` as reading a permission.
- Removed: MVC's extracurricular-detail.css, teacher-join-request-card.css, extracurricular-card.js,
  extracurricular-achievement.js, teacher-extracurricular.js, image-uploader.js, form-card.js (9,713 MVC lines →
  ~3,800 ours, with 316 lines of example data).
- MVC bugs not copied: `?id=` opened the wrong programme (JS redraw) · the teacher saw a programme still waiting
  (database number ≠ example data) · students saw waiting programmes · every coach had the same email · the delete
  pop-up's leftover "Grade option / Class options" · a broken football photo link (replaced).
- Checked: 17 page states (4 roles, list / programme / achievement / edit / new / filters) load with no PHP or JS
  errors and no unstyled classes; every pop-up, menu and example form clicked (banner shown, pop-up / menu closed,
  reason required before Reject, + / × rows, Enter adds a participant); a real form through the question pop-up
  (Sign Out) still works. curl: wrong role → own dashboard, signed out → sign-in, `id[]`, `award[]`, `type[]`, `q[]`,
  `age[]` → no crash, unknown / waiting programme or award → back with "… does not exist." (shown once), a teacher's
  `?id=` is ignored, `<script>` in the search printed safe, POST → 404. Measured next to MVC: list (search, grid, cards,
  buttons) the same ±1px; the programme pages match screenshot for screenshot.
- Different from MVC (decided / our looks): our tabs (D2), titles 16px / 600 (card-title rule), 10px tags / labels,
  pop-ups in our frame · the teacher in charge is picked from a dropdown (MVC: pills + a text box) · the staff
  cards' contact lines in normal letters (MVC: capitals beside the calendar only) · Created Date as an icon list
  (MVC's empty icon box dropped) · a whole card isn't a link, its button is · the Danger Zone of an achievement only
  while viewing (a form can't sit inside the edit form) · Save on an achievement stays on the edit page (example data)
- ✅ (2026-10-27, owner's look) The calendar has the dashboards' shape on every programme page (`l-calendar-box`: 360 ×
  528; the student's roster takes the rest of the row) · Save on an achievement goes back to it ("Achievement saved.",
  `data-mock-go` + `?saved=1`) · leaving an edit page with changes asks first (below)
- ☐ backend later (owner): programmes, members (activities / activity_members already exist; Clean/MyTry has a tested
  controller + model — without its parent email), approvals, join requests (Parent → Approvals is real already),
  teams, achievements and photos (no tables yet); the calendar's events for a programme + "Add event" (off: example
  programmes have no real events); the dashboards' donuts and "Sports & Clubs Active"
- ✅ (2026-10-28, owner: keep) the 10 four-line "same lines" warnings are small flex rows — each component keeps its own
  row; `bin/same-rules.php` now skips rules that only arrange (display, gap, align…). Still reported: 5 repeated LOOKS
  (3 title texts, the chart colour dot, the programme photo fill) — for a later look at c-panel__title & co.
- Notes kept from planning (for the backend): approval of new clubs / sports (owner 2026-10-27, replaces MVC's Verify page tab): one management adds →
    `activities.review` = pending (the column + demo Photography Club exist, update 012). Admin's Extracurricular
    page shows it greyed out with Reject / Approve, exactly like a waiting notice: `extracurricular.needs_approval`
    (+ `feature('approvals')`), `permissions.extracurricular.approve`, a model `review()` like `NoticeModel::review()`
    (`WHERE review = 'pending'`), Reject asks for a reason (the question pop-up's extra `<template>` with a `required`
    field, as `_notice_board.php`), a count on a tab like "Awaiting Approval (n)". Waiting / rejected clubs stay
    hidden from students, join requests and pickers (CalendarModel already shows only approved ones to non-teachers).
    If that makes the admin's waiting work spread out, discuss a small count on the sidebar items then
  - `ExtracurricularModel` exists (Parent → Approvals: `forParent()`, `answer()`) → add the rest of Clean's methods
    there. A student's own join request waits for the parent first (`status = 'parent'`), then the teacher in charge
    (`'teacher'`): the teacher's join-request cards show the parent's answer; their Reject = the question pop-up + a
    reason `<template>`; their Accept / Reject buttons = `c-btn--decision`. The parent dashboard's "Sports & Clubs
    Active" (mock) becomes real here

**6. Achievements** — student/achievements, teacher/achievements (+ honours board)
- ✅ Honours board + landing highlights read the database: `AchievementModel::honoursBoard($limit)` (rows with
  `on_honours_board = 1`, newest first), tag colours in `config/achievements.php`, update 022 (`photo_alt` +
  two broken dashes repaired); `LandingController::achievementList()` (hard-coded) deleted; `_achievement_row.php`
  uses the table's column names. Visible change vs MVC: newest first (landing shows Athletics, Music, Debate) and the
  athletics participant is the linked student "Nethmi Perera" (MVC typed "Nethmi Fernando")
- ✅ Front end, both pages (2026-10-22). **Owner: no backend MVC didn't have** → MVC's example data moved to
  `app/Mock/achievements.php` (timeline, 6 students, the reported issue); `AchievementController` only draws the pages;
  Record saves nothing (like MVC: it closes and says "Achievement recorded successfully!"). Routes: GET only.
  - `_achievements_timeline` + `achievements-timeline.css` + `.js` (30 lines): `$timelineVariant` 'panel' (student) /
    'plain' (inside the student card); filtering hides items, CSS hides empty years and the last line (`:has`,
    `:nth-last-child(1 of …)`), no inline styles from JS. Groups (button word, Record type, tag colour) in
    `config/achievements.php` 'groups'; month labels from `country('month_format')` (new, 'M Y')
  - `_teacher_achievement_card` + its CSS: `$studentCardVariant` 'grid' (coloured card: View = a link to
    `?student=…`, Record) / 'banner' (top of the student's timeline: FINALISED, View Issue, Record Achievement;
    `$studentCardContent` under it). View is now a PHP page (MVC swapped the screen with JS)
  - `_record_achievement_pop_up` (on `_pop_up.php`) + `record-achievement.css` + `.js`: `$recordVariant` 'record'
    (record-achievement.js puts the clicked student in) / 'review' (the issue, filled in by PHP, + evidence preview);
    MVC's 412-line `teacher-achievements.js` → `record-achievement.js` (~75) + `teacher-achievements.js` (~45, search +
    filter)
  - shared pieces grown: form-field `.c-fields--sand`, tag sizes `--md` `--round` + tones ink/rose/fern/violet/oat/
    seaglass, button `--sunshine` `--link`, search `--rounded`, empty box `--titled`, metric row `--tight`, new row
    `layout/student-cards-grid.css`; 27 colour tokens (MVC's exact colours, achievements block in global.css)
  - `_feedback_banner.php` + `feedback-banner.css` + `.js` (new shared piece; MVC's feedback-banners.js built HTML
    strings → PHP `<template>`); `showFeedbackBanner(message, type)`. MVC's `.c-card-overlay` / `.c-form-bottom-toast`
    rules (approvals) dropped from that file → rebuild with Approvals
  - deleted (replaced): `achievements.css`, `achievements.js`, `teacher-achievements.js` (MVC 412 lines),
    `feedback-banners.js`; `bin/check.sh` allows any `$…Content` slot (also cleared the old `$formCardContent` warning)
  - Checked next to MVC (1440px): student page (cards, panel, buttons, years, dots, lines, tags, text — all same
    place / size / colour); teacher grid (search 448px, filter, cards, photos, buttons), student view (Back, banner,
    FINALISED, View Issue, Record, timeline), Record pop-up (608×869, every field at MVC's position, footer, button),
    the banner (480×54 top centre). Behaviour: filters, search + empty box, Record for 2 students, file name + size,
    drop a file, send → banner (gone after 3.5s), review → evidence preview → approve. curl: signed out, wrong role,
    `?student[]=x`, `?student=<script>` (never printed; "That student is not in your list."), POST → 404, no PHP warnings
  - Looks different from MVC on purpose: Cancel / × are our styled buttons (MVC's showed browser grey: button.css not
    loaded); the date picker in the pop-up has the sand colours (decided earlier); the "Reported Issue" box is
    terracotta like every error (MVC red); the pop-up frame's border / shadow are the shared frame's
- ☐ Backend (later, owner): real achievements per student / teacher's real students (`Membership`), Record + Review
  saving, uploads (with notice attachments), "report an issue" for students — Clean/MyTry has a tested model
- ✅ (2026-10-28: no `showToast` left) People's script calls MVC's old `showToast()` → `showFeedbackBanner()` when People is built (grade-card.js is gone:
  Academic uses the feedback banner)
- ✅ Term marks: the card (now `_student_card`) got its 'marks' look (Edit Marks; MVC's `#icon-pencil` is a copy of
  `edit`), reuses `l-student-cards-grid` + `c-search--rounded` (Academic, Stage B)
- ✅ Child profile: reuses `_achievements_timeline` ('panel') + metric cards `--tight` (2026-10-28)

**7. Character certificates** — student/certificates, management/character_certificate
- ✅ (2026-10-27) Owner: **keep MVC's example data** (`app/Mock/certificates.php`: 18 certificates, their requests and
  missing records), the forms save nothing (`data-mock-message`); the PDF is made by **Dompdf 3.1.6** (Composer).
  - `CertificateController`: `management()` (list: GET search + status / pathway tabs, counts, two groups; `?student=`
    = one certificate) · `student()` · `pdf($role)` (the same sheet → `CertificatePdf::make()` → inline PDF; a student
    only their own) · `prepare()` works out the card colour (MVC's crc32 of ID + name), what still waits, "N. Perera",
    the class and period. `config/certificates.php` (statuses, pathways, sections + icons, tones, purposes, deliveries,
    max copies) · `word('principal')` (white-label: Principal / Vice-Chancellor).
  - `_certificate_sheet.php`: ONE template for the screen and the PDF (`$sheetFor`), Dompdf-safe CSS (tables, opacity,
    solid tokens; no flex / grid / color-mix). On screen the text flows into A4 pages by itself (CSS columns, one
    column = one page) and certificate-sheet.js (~70 lines) counts them: "Page 1 of 2", Previous / Next, pages come
    and go while editing; Edit Wording = `contentEditable` on the three lists. The signature is a blank line (signed
    by hand after printing).
  - `icon_image()` (helpers.php): an icon as an `<img>` for Dompdf (it can't read `<use>` or colour from CSS); its
    SVG gets 4/3 of the picture's size, else Dompdf draws it 4/3 too big over the words (measured).
  - pdf.css: A4 page, Poppins (`public/assets/fonts/poppins/`, OFL), `line-height: 1.1` (Dompdf adds Poppins' tall
    line box: 1.1 there = MVC's spacing on screen) → one page with the signature, like MVC's print.
  - New shared pieces: `c-btn--compact` (12px, 36px: toolbar buttons) · `c-tabs__count` · `_evidence_preview_pop_up`
    (+ evidence-preview.css, moved out of record-achievement.css).
  - Removed: MVC's character-certificate.css / .js, student-certificates.css / .js (3,922 MVC lines → ~2,080 ours,
    with the 144-line example data); MVC's download button on an evidence row (it did nothing).
- Checked: list filters / tabs / search (`q[]=x`, `status[]=y`, `pathway=zzz` → the full list; `<script>` printed safe);
  `?student=nope` and the PDF without / with a wrong ID → back to the list (404 message); student on the management
  page or PDF → own dashboard; signed out → sign-in; both PDFs = 1 A4 page. Browser: 2 pages for 2021/0456 (MVC too),
  25 typed points → 3 pages, Next slides, deleting them → 2; Edit Wording ↔ Done Editing; Student Requests pop-up,
  Approve → "Request approved.", Finalise → "Certificate issued."; both student pop-ups open; no JS errors;
  no unstyled classes (scan.js).
- Measured next to MVC (1440px): header buttons 36px / 12px, sheet 780×1011, the particulars on one line each
  (label 17.5% · value 30%), pager 36px, side card, cards and groups — same.
- Different from MVC (decided / our looks): everything sits 24px lower (the standard header gap) · status / pathway
  tabs are our `c-tabs` · "Name with initials" = "N. Perera" (MVC printed "NP") · the motto comes from `school()` ·
  Title Case buttons ("Print Certificate", "Edit Wording") · pop-ups use our frame and `c-label` section titles ·
  the student page's pager is 16px under the sheet like management's (MVC 36 there, 16 on management).
- ☐ backend later (owner): real certificates, requests and missing records (who asks, who approves, the teacher the
  missing record goes to — MVC names the class teacher), saving the edited wording, Finalise → issued
- ✅ (2026-10-28) the Request Character Certificate dropdowns (and the missing record's type) start empty like MVC:
  `_dropdown.php` `$required = 'Please select a purpose.'` → the red line (`c-field__error`) under it; dropdown.js stops
  the send while it's empty, focuses it, hides the line on a choice. Also fixed for every form: a reset (after sending,
  Discard Changes) left the old choice showing (a hidden input ignores reset) → dropdown.js puts back the first value
- ✅ (2026-10-28: the 4 lines done; the PDF still made — today's certificate fits one page, so the move itself shows
  only with a longer one) later (owner, 2026-10-27): keep a section whole across pages (MVC's rule). Tested: Dompdf reads only
  `page-break-inside` / `page-break-after` (it ignores `break-inside`), Chrome's columns read both → in
  certificate-sheet.css: `page-break-inside: avoid` on `.c-certificate__section`, and the heading / point / footer
  rules switched to the `page-break-…` names (today the PDF ignores them: a point can split mid-line)
- ☐ `.c-certificate-panel__actions` = the same 4 lines as `.c-audit__filters`, `.c-bar-chart-card__extra`,
  `.c-parent-row__top` (a wrapping row of buttons) → one shared class in a later clean-up

**8. People directory** — management/people, admin/people
- ✅ `_people_directory_panel`, `_people_directory_row`, `_add_person_page`, `_admission_guardian`, `_parent_picker`,
  `_deactivate_parent_dialog` (A–E below; the deactivate dialog became the Sibling-Guard refusal message)
- When building it:
  - wrap page header + role tabs + list in `<div id="j-directory-view">` (people-directory.js hides it as one)
  - the role tabs sit on the header's right → add `$headerActions` (a component name) to `_page_header`
  - dropdowns: add-person fields = `tinted` + `$tone`; row status = `status` + `active` / `deactivated` (JS swaps the tone);
    toolbar filters = `toolbar` + `$prefix`; phone-code width stays in people-directory.css
  - `people-directory.js` builds the status dropdown as HTML → use the component's markup
  - default dropdowns inside a form card: MVC 14px / 500 vs ours 13px / 600 → decide here
  - `_deactivate_parent_dialog` → `askQuestion()` if it's a yes / no
- ✅ Stage A, base (2026-10-26):
  - Update 024: student religion / nationality / address area-district-region; parent passport, home / office
    phone, office address; staff office address; `staff_qualifications` (degree, institution, year). Fresh install
    (schema + seed + 24 updates) = the live tables, checked
  - `country.php` (white-label): passport pattern, default nationality, religions, `address_parts` (each country names
    its three parts and their choices: Sri Lanka = MVC's 7 zones, all 25 districts — MVC listed 8 — and 9 provinces)
  - `config/people.php`: each role's colour, rows per page, relationships, blood groups, the ages MVC checked
    (parent 15+, teacher 21–65, management 21–70), join date at most 3 months ahead
  - `permissions.php` 'people': directory (admin, management) · management tab (admin) · own students (teacher)
  - `Validator`: `email`, `national_id` (country pattern), `passport` · `ClassModel::hasRoom()`
  - `PeopleModel`: Clean's tested directory (search, status, class, paging) + a teacher's own students, counts,
    parent choices, add (all or nothing; full class refused), add student + new parent, update (+ qualifications),
    switch on / off (a parent with no other children at school follows), the "taken" checks; MVC's checks Clean
    lacked: `activeChildren()` (Sibling-Guard), `personalEmailTaken()`; `find()` reads the new columns
  - Tested on a copy of the database (31 checks, then the copy deleted): every role found, directory totals = counts,
    search with SQL text, status / teacher / paging filters, codes + school emails (test_teacher2@…), qualifications
    in order, full class leaves nothing half-saved, student off → parent off and back, update replaces qualifications
    and a parent's sign-in email, the three Validator rules. All 27 built pages still open with no warnings
  - Waiting for B–E (check.sh warns): `countByRole`, `parentChoices`, the four "taken" checks,
    `addStudentWithNewParent`, `setActive`, `tones`, `per_page`; the country lists are read by the forms (E)
- ✅ Stage B, directory (2026-10-26): admin/people, management/people, teacher/students
  - `PeopleController` (index, students, status) + routes behind `feature('people')` from `permissions.people`;
    `PeopleModel`: filters grade / class / activity / own classes / subject / relationship, status on / off, the row
    lists (`rowLists`: activities; subjects + classes, class teacher of, in charge of; children + class), `duties()`,
    filter choices. Students open on the first grade that HAS students (MVC: the first grade)
  - Pieces: `_people_directory.php` ('office' / 'teacher') + people-directory.css / .js · `_people_table.php` (4 role
    layouts + the teacher's view-only one, parent name + phone) + people-table.css · `layout/people-grid.css`
  - Shared looks added: `_page_header` `$headerContent` (the right side) · tabs `--roles` (+ each role's chosen
    colour) and `--grey`; tabs may be links · search `--small` · buttons `--terracotta`, `--maroon` · avatar `--row`
    + role colours · tag `--soft` (10px) · info card `--compact` and an optional icon · background-save.js sends
    'background:answer' (People recolours the status pill)
  - Filters are a GET form (dropdown change → reload; grade change forgets the class); role tabs, class chips,
    status tabs and page links are links that keep the other filters; 25 rows a page (`people.per_page`)
  - Status: background save; refused with the reason: a parent with a child still at school (Sibling-Guard), a
    teacher with duties ("Hand over Alex Benjamin's duties first: 10-A, Mathematics (9-B, 10-A, 10-B), Football…"
    — until the handover pop-up in D); a student off takes a parent with no other children with them
  - Measured at 1440 next to MVC: role tabs 458×45 (tab 92×32), toolbar 18 / 24 padding + each role's tint and line,
    grade filter 143×34, chips 42×31, title 18 / 700, search 240×34, status tabs 211, class boxes 531×51, "found"
    38, header row 55, rows 71, avatar 32, status pill 144×42; card 52px under the header (teacher page 28, no count)
  - Tested: browser (filters, chips, status on / off + colour, the three refusals); curl: wrong role / signed out /
    student → refused, junk in the address (tab, grade, class, page, status, arrays, `<script>`, SQL text) → safe
    defaults, status route: no token 419, teacher 403, unknown / admin / management-by-management id → the same 404,
    bad status 422, JSON on every path; account counts unchanged after the attacks
  - Differences (on purpose / small): Add button 34 high (MVC 37) · tags `c-tag` 10 / 700 caps with 10px corners
    (MVC .06em spacing) · a parent's child is a `c-chip` (12 / 600; MVC 10 / 700) · activity tags coloured by kind
    (club sky, sport terracotta; MVC by order) · the teacher table scrolls sideways inside the card (MVC too)
  - Waiting: View / Edit (C, D) and Add (E) are shown but off · `countByRole`, `parentChoices`, the "taken"
    checks, `addStudentWithNewParent` → E
  - MVC bug also seen: the teacher's sidebar showed another user's name ("Havindu" for Alex)
- ✅ Stage C, profile pop-up (2026-10-26): View on every row (admin, management, the teacher's own students)
  - `PeopleController::profile()` / `studentProfile()` (GET …/people/profile?id=, …/students/profile?id=) answer JSON
    { ok, html }: the server draws the profile (`_person_profile.php`), so no one's details wait in the page and a
    refusal (signed out, wrong role) is JSON too, never a sign-in page inside the pop-up. A teacher only their own
    students (`PeopleModel::teaches()`); an unknown / not-yours id → the same 404
  - Information = `config/people.php` 'profile' (MVC's sections per role: cards or a list — subjects, qualifications,
    children, parents), values from the new `App\Services\PersonDetails` — moved out of ProfileController, which
    My Profile now uses too (one value builder, + the 024 columns). Student tabs (tabs `--sub`): Information ·
    Academics (`_academic_section` reused, the record book's marks are still the example data, now
    `App\Services\RecordBook` — moved out of AcademicController) · Extracurriculars (real memberships, position +
    status) · Achievements (real, `AchievementModel::ofStudent()`)
  - Shared changes: `_pop_up` size 'large' (56rem) · record-book.js and academic-section.js start inside content
    added later ('content:added') · `$exportUrl` null = no Export PDF (the print page is the student's own) · info
    card `--rounded` and grid `--two` · avatar `--sky` · tag `--moss` (an active account)
  - Measured next to MVC: pop-up 896 wide, header tint per role 20 / 28 padding, tabs 828×48 (tab 102×34), section
    titles 12 / 700 caps + terracotta 16px icon, card grids 2 × 408 / 4 × 198, cards 14px corners 12 / 14 padding,
    achievement / club cards 413 wide. Tested: tabs, record book grade switch + trend inside the pop-up, no Export;
    curl: signed out 401, wrong role 403, unknown / admin / management-by-management / not-their-student / a parent's
    id on the teacher's route → the same 404, all JSON. All 27 earlier pages still open (My Profile, Academic)
  - Differences: name 20px (pop-up title; MVC 24) · avatar 44 (MVC 48) · labels 10px (MVC 9) · the × in the corner
    (MVC's sat by the name) · the parent section "Enrollment details" is called "Work & Home" (what it holds)
  - The 024 details (nationality, religion, address parts, passport…) show "—" until the Add / Edit forms fill them
- ✅ Stage D, edit + handover (2026-10-26): admin and management (not the teacher's view)
  - Edit Profile in the pop-up: each card in `config/people.php` 'profile' names the field it edits (4th item);
    `'fields'` is the ONE list of boxes + rules (also for E's add forms). Boxes: text, date (the date picker),
    choice (the dropdown, options = the rule's own `in:` list, "Not given" when optional), class (full ones greyed
    out). `_info_card` draws them (`'box'`, `'options'`, two boxes for a name); boxes show under `data-editing`
  - Save = background POST …/people/update → `PeopleController::update()`: `editable()` (fields of that role's cards
    only, so nothing else can be sent in), Validator, then `problems()` (MVC's rules: ages, dates in order, ID / birth
    certificate / email not someone else's, staff need an ID, parent ID or passport, staff email not the school's,
    emergency number not their own, experience ≤ age − 18, join date ≤ 3 months ahead and after 18). Saved → the
    server redraws the pop-up; refused → banner, the boxes keep what was typed; closing after a save reloads the page
  - A student's parent: in edit mode the main parent's Name is a choice of parents (not switched off);
    `PeopleModel::update(…, $parentId)` replaces the main link in the same transaction
  - Handover: switching off a teacher with duties answers 422 + `handover` (an address); people-directory.js opens
    the handover pop-up (`_teacher_handover.php` + teacher-handover.css, `_pop_up` 'wide'): one card per duty
    (their class, each subject by name with all its classes, each club / sport) with Academic's teacher picker.
    Reassign & Switch Off / Clear Roles & Switch Off → `handover()` (a normal form): same checks as Academic (a class
    teacher has one class, max subjects per teacher), then `PeopleModel::handOver()` passes everything on and
    switches them off in one transaction; the page reloads with "… switched off; duties handed over."
  - Shared changes: Academic's teacher options moved to `App\Services\TeacherChoices` (Academic + handover use it) ·
    dropdown `--small` (MVC's `--compact`: 34px light-sky box in a card) · info card `.c-info-card__box` wraps every
    edit box (My Profile too, checked) · `PeopleModel::duties()` now gives ids (status + handover use it) ·
    `find()` gives one `office_address` (parent or staff) · `PeopleModel::PROFILE_COLUMNS`
  - Tested in the browser: edit, refusal keeps the typing, save redraws, handover pop-up opens from the status pill;
    curl: no token / signed out 419, teacher 403, unknown / management-by-management / `id[]` → same 404 JSON, every
    bad value 422 (gender, class, ages, dates, religion, parent = a teacher, `<b>` in a name, `first_name[]`), role /
    identifier sent in the form ignored, all parent / staff rules; handover: a student's id 404, to themselves / a
    parent / a busy class teacher refused, clear and reassign both work (Mathematics' 3 classes moved together).
    DB restored after the tests. All 27 earlier pages still open, My Profile's edit boxes unchanged (34px)
  - Differences: the name is two boxes in its card (MVC: one box in the header) · status is changed in the table only
    (MVC also had a dropdown in the pop-up's header) · handover header = our pop-up header (MVC terracotta tint), the
    warning = our banner `--warning` · "Assignment pending" (MVC "Leave Unassigned (Mark pending)") · one picker per
    subject (MVC: one for all subjects, which could break the max-subjects rule)
  - Bug found and fixed while testing: `form.id` returns the form's `<input name="id">`, not its id → use `matches()`
  - Left for E: qualifications editing (list), photo, `countByRole`, `addStudentWithNewParent` (all done in E)
- ✅ Stage E, add people (2026-10-27, owner chose: welcome email, parent search, + My Profile photo)
  - Pages `/{role}/people/add?tab=…` (`admin/add_person.php`, `management/add_person.php` → `_add_person_page.php`
    in `_form_card` with the role's colour, MVC's titles): Add Student (sky), Add Teacher (sunshine), Add Staff (maroon,
    admin only), parents tab = MVC's policy card ("parents are added with their child" — MVC's rule, not a bug: the
    old note "Add Parent opens the student form" was wrong) → Add Student. Directory's Add button links there; a
    row's Edit opens the pop-up already editing
  - Boxes from `config/people.php` 'add' (+ 'guardian') using the same 'fields' list as Edit; ONE box drawer
    `_field_box.php` (text / area / phone with the country's code / date / choice / class / photo / parent /
    qualifications) used by the info card ('card') and the Add page ('form', via `_field_grid.php`)
  - Rules in ONE place: `App\Services\PersonFields` (rules, dropdown options, ages, problems(), qualifications) —
    moved out of PeopleController, used by Edit and Add. `PeopleAddController` (extends PeopleController for
    guard() / tabs()): form(), add() (a normal form; refused → the form again with everything typed, incl. the parent
    part and qualification rows), parents() (the search, JSON html), welcome() (Mailer 'welcome': sign-in ID + the
    sign-up link; a student's to their parent; `app.url` = APP_URL, now http://localhost:8080 in .env)
  - Student's parent: radio cards (CSS :has, no script) — existing (`_parent_picker.php`: the first 20 at once like
    MVC, typing searches name / code / email / phone / ID / a child's name, `PeopleModel::searchParents()`), or new
    (ID card or passport, address = the student's when empty) → `addStudentWithNewParent()` (both or neither).
    The same picker in the pop-up's Edit Profile (an edit-only card; `parentChoices()` removed)
  - Teacher's qualifications: `_qualification_rows.php` + qualification-rows.js on Add and in the pop-up (replaces the list)
  - Student photo: `core/Upload.php` (Clean's, images only), saved only after every check passes; removed again if
    the save fails. `photo_kb` once, in config/profile.php
  - Shared changes: form card tones sunshine / terracotta / maroon and an optional subtitle · dropdown `--tinted` = a
    10px box like the text boxes (it had no user; MVC's shape) · form fields: `.c-fields--people`, the * in the
    role's colour, the phone prefix, the photo bar, `c-field__file-input` renamed `c-field__hidden-input` (file
    boxes and choice-card radios) · info card `--edit-only` · `countByRole()` removed (dead)
  - Measured next to MVC (1440): card 768 / 14px corners, header 20/24 padding, body 24, two columns 24 apart, labels
    10/700 70%, boxes 43 high 10px corners alabaster 14/500, sky-tinted dropdowns, guardian cards 14px corners
    terracotta when chosen, parent list 250 high, rows 10px corners, footer band. Differences: title 20 (MVC 18: our
    form card), no "Full Name (with initials)" box (no column; MVC's script only split it), one class list (MVC:
    grade then class), no "Unlock range" (the age range is a server rule), no drafts (owner), the phone code is a
    label (MVC: a dropdown with one choice), "Siblings:" line aqua
  - Tested: browser — add student (existing parent), add teacher (refused school email kept every box + rows, then
    saved with a school email and 2 qualifications), pop-up: edit qualifications (3 rows saved in order), change a
    student's parent through the search; curl — pages per role (management can't open / post management, teacher
    404, signed out → sign-in), no token, `tab[]`, `first_name[]`, every bad value / taken number / taken email /
    a teacher as parent / no passport number / own phone as emergency → the form with the reason, a .php named .png
    refused (nothing saved, no file left), student + new guardian + real photo saved; search: signed out 401, teacher
    403, `q[]`, `%`, `<script>` safe; My Profile photo: no token, fake, nothing chosen refused, real saved. DB restored
    and test photos deleted. check.sh 0 errors; 27 pages + all People pages without warnings
  - Mail (2026-10-27): MVC's Gmail sender (`lecoletesting@gmail.com`; MVC types its app password in
    config/mail.php — don't copy that) is disconnected (BadCredentials). Now sent through Resend (owner's choice:
    no Gmail sender): `.env` SMTP_HOST / PORT / USER=resend / PASS=the API key, MAIL_FROM=onboarding@resend.dev
    (Resend's test sender: it only delivers to the inbox the account was made with = `mail.redirect_to`).
    New `mail.from_address` (MAIL_FROM, or the login for Gmail); Mailer bug fixed: the server's "250 accepted"
    after the message was never read, so QUIT failed and a sent email counted as failed; + a Date header.
    Test welcome email: sent. The add message says when the welcome email could not be sent. The welcome email
    gives everything sign-up asks except the password (the full name as registered + the sign-in ID)
  - Review after E (2026-10-27), fixed: `PersonFields::rules()` made "required|" (ltrim on the wrong side → the
    Validator would crash) · editing a teacher without the qualification rows in the request wiped them (now only
    with `qualifications_sent`) · Mailer: \r\n line ends and a line starting with "." doubled · the parent search no
    longer fetches inside every opened profile (only when its search shows / on Change Parent). All tested
  - Review A–E (2026-10-27), fixed: ONE welcome email per addition like MVC (new student + new parent = one email
    with both accounts' boxes; a sibling's says "your own sign-in stays the same"; staff their own) · student
    photos in the People table and pop-up header (initials when none) · after Add Student the list opens the new
    student's grade and class (it showed the first grade) · the teacher subject filter finds what they teach too
    (it only read their "qualified" text, while the row shows what they teach) · the directory search escapes % and
    _ like the parent search (one `like()` helper) · `Mailer.php` in the house brace style · the test inbox is
    `.env` MAIL_REDIRECT_TO (config default '' = real delivery when live). Tested; DB restored, test photos deleted
  - ☐ Owner: the Resend key was pasted in chat — make a new one on resend.com some day (and put it in `.env`)
  - Owner 2026-10-27: row actions are icons only (`c-btn--icon`, the words in title + aria-label; MVC had words);
    names and contact lines stay on one line. The teacher table shows only "Class Teacher Of" (what they teach and
    the clubs they run are in their profile) instead of MVC's Subject & Classes + Roles, so all four tabs fit at 1440
    with no sideways scroll (teachers 1118 in 1118; MVC's scrolled: 1298 in 1108). `rowLists()` lost its unused
    'in_charge' list. (The pinned Actions column tried before was removed.)
  - Owner 2026-10-27: a student's pop-up — Academics = the record book only (`_digital_record_book` on its own, no
    total card / trend chart); Achievements = the same timeline as their own page (`_achievements_timeline`
    'plain', real achievements: `App\Services\AchievementTimeline` — `byYear()` moved out of AchievementController,
    `items()` turns a category into its group: config/achievements.php 'categories' now [tone, group]).
    achievements-timeline.js listens on the page (works in the pop-up); academic-section.js lost its pop-up start
    (nothing uses it now); the People pages no longer load academic-section / the academic page row
  - Owner 2026-10-27: the handover pop-up follows the house pop-up look (like Add Event / Add Grade): plain white
    body, one field per duty (capital label + tag, the standard dropdown), the footer band with Cancel (ghost),
    Clear Roles (outline), the main button dark (solid). `c-banner--warning` is now a soft terracotta fade for
    every warning (the calendar's clash box too; MVC's was tan)
- MVC's backend rules to keep (read 2026-10-26, StudentActions / ParentActions / TeacherActions / ManagementActions):
  - every name: letters, spaces, hyphens, dots, no numbers (= our `name` rule) · emails lower-cased
  - student: born before admission; age in `student_age` (older: confirm box); birth certificate unique, 3–25
    letters / numbers; address 6+; class belongs to the grade; a free place; an existing parent must not be switched
    off; a parent chosen OR typed in (new: email not used — "choose them from the list instead")
  - parent: relationship Father / Mother / Guardian; national ID OR passport; age 15+; emergency contact not
    themselves (name and phone); can't be switched off while a child is still at school (Sibling-Guard)
  - teacher / management: personal email not the school's domain and unique; national ID unique; age (above);
    experience ≤ age − 18; join date ≤ 3 months ahead and after their 18th birthday; emergency contact not
    themselves; a school email made from the name (+2, +3 when taken); only the admin adds management
  - a new account starts PENDING (ours too); MVC also emailed the parent at once (the student's index number + how to
    sign in). Ours: the person activates on the sign-up page, which emails the code then; owner 2026-10-27: yes, a
    welcome email too (built in E)
  - not carried over: MVC's Sri Lanka NIC day-code check (one country's ID rule in code — the pattern is in
    country.php), its idempotency key table (a normal form + redirect does the same), saved drafts (owner)
- MVC bugs seen (don't copy): class card "10 students" while 7 are listed · a parent's status shows "Select option" ·
  the profile pop-up's × sits by the name · the handover pop-up is in the page
  but nothing opens it · a teacher sees every student (class "6-A" typed in) · 54 `innerHTML` with database text ·
  every person's details sent to every page · drafts with personal data in `lecole_…` browser storage · class
  choices typed 6-A…6-D (no 6-D) · districts 8 of 25 · MVC's own `.c-avatar` (clashes with ours: never load its CSS)

**9. Approvals** — parent/approvals (admin/verify removed 2026-10-27: see Notice Board / Extracurricular)
- ✅ (2026-10-27) REAL, not MVC's example data (owner accepted the reason column): `ApprovalController` (index, answer)
  → `ExtracurricularModel` (new, from Clean: only `forParent()` / `answer()`; feature 10 adds the rest) +
  `PeopleModel::childFirstNames()` → `parent/approvals.php` + `_approval_card.php` (one component; MVC typed the card
  by hand). Update 026 `activity_members.parent_note` (the optional reason for no). Approve: a member (the school
  asked) or on to the teacher in charge (the student asked: "… now waits for the {teacher} in charge to accept.");
  Decline: declined + the reason, shown on the card ("Your reason for declining", a `c-banner--error`).
- The 3 number cards = `_metric_card` with `href` / `chosen` (`c-metric-card--tab`: white, filled when chosen; 24px
  number) → links `?status=pending|approved|declined`, the server filters: **0 JS** (MVC ~240 lines). MVC's chosen
  card was meant to grow 2% but its fade-in held the transform, so it never did — not copied.
- New shared button size `c-btn--decision` (12px, 31px, two equal halves) for every Reject / Decline + Approve pair —
  the notice card's review row uses it too (was `--small`, 27px; its Approve `--dark` → `--solid`, MVC's midnight).
- Words: subtitle from the data ("…for Amaya and Sasha"; MVC typed "Nethmi Perera"); "TIC" → `{Teacher} in charge`;
  "Declined" everywhere (MVC's badge said Rejected); "Request approved." / "Request declined."
- Measured next to MVC (parent, 1440px): cards row y141, number cards 362.7×143, chosen in sunshine, card padding 20,
  buttons 248.5×31 maroon / midnight — same; summary 10px (MVC 12, the label size) so the cards sit 2px higher.
  Browser: Decline → pop-up with the optional reason → "Request declined." + the reason on the card, saved.
  curl: no token, signed out, a teacher (GET / POST), another parent, `answer[]=x`, an unknown request, one already
  answered, approve twice → refused, nothing changed ("That request no longer needs your answer." for every
  not-yours / not-waiting case, so nothing is revealed); `note[]=x` → declined without a reason; `?status=<script>` /
  `status[]=x` → Pending. Log: PARENT_APPROVED / PARENT_DECLINED. Demo request reset to waiting afterwards.
- Deleted: `parent-approvals.css`, `parent-approvals.js` (MVC copies)
- Not carried over: MVC's field trips / "Saturday sessions" (example data; no table — a field-trip feature would need one)
- Team: the management dashboard's "Pending Verifications 12" card is mock; management approves nothing now → relabel
  or make it real when the dashboards get real numbers
- ☐ waiting for the owner's OK: the safeguards from the report — check.sh section 10 (every class a page uses has
  its CSS loaded), a `form.id` / `.action` / `.name` rule, a warning for own classes resizing c-tag / c-chip / c-label,
  `bin/compare.js` (headless MVC-vs-ours screenshots + sizes), and adding them to the next-feature skill

**10. Feedback & complaints** — teacher/feedback, parent/feedback, parent/complaints, management/complaints
- ✅ (2026-10-27) Owner: **keep MVC's example data** (`app/Mock/feedback.php`, `app/Mock/complaints.php`), the forms
  save nothing (like MVC); **no emails** (only sign-in / sign-up send any); MVC's categories (`config/complaints.php`).
  - Feedback: `FeedbackController` (teacher, parent) → `_feedback_board` (filter bar, cards, "nothing found", the
    detail pop-up) + `_feedback_card` (one component; `--full` = the pop-up's copy: whole text, "Official Feedback
    Record") + `_new_feedback_pop_up` (teacher) · `config/feedback.php` (types: name + icon) · feedback.css,
    layout/feedback-grid.css (1 / 2 / 3 columns) · feedback-card.js (~25 lines: a click or Enter copies the card into
    the pop-up). New `icon-starFilled` (MVC's Positive star is filled; the outline one stays for Extracurricular).
  - Complaints: `ComplaintController` (parent: parent #1's like MVC, management: all) → `_complaints_board` +
    `_complaint_card` (management: + Parent, "Mark as Resolved" → the resolve form under the card) +
    `_new_inquiry_pop_up` (parent) · `config/complaints.php` (categories → colour, statuses → name + icon) ·
    complaints.css (rewritten: 549 → ~190 lines), layout/complaints-grid.css · complaints.js (336 → ~30 lines: open /
    close the resolve form).
  - Filters = GET forms (search + dropdowns, `data-submit-on-change`): the server picks the cards (MVC's feedback type
    filter never worked: it listened for `dropdown-change`, the dropdown sends `dropdown:change`).
- New shared pieces: `layout/filter-bar-grid.css` (`l-filter-bar`: search left, dropdowns right; `--page` = first thing
  under the header; the Notice Board moved onto it — measured the same) · `<form data-mock-message="…">`
  (feedback-banner.js: an example-data form closes its pop-up and shows the message — Record Achievement moved onto
  it, its own copy removed) · `$headerButton['popUp']` (the unused `'hook'` setting replaced) · `$popUpIcon` optional
  (MVC's New Feedback header has none) · `c-field-pair` (two fields side by side: Record, New Feedback, New Inquiry;
  `c-record-form__pair` removed) · `c-tag--flame` (In Progress) · `c-btn--moss` (Resolve) · Submit Feedback =
  `--terracotta --pill --large` (the same shape as Publish Notice).
- Measured next to MVC (1440px): filter bar, search 448, dropdown, 3-column grid 360 wide — same; cards ±4px (badge
  10px tag); detail pop-up (Back 90×37 moss + card 512) and New Feedback pop-up (576 wide) match; complaints card,
  badges, tinted answer, resolve bar (sky tint for Academic, Resolve faded until typed) match.
  Browser: Mark as Resolved → form, Resolve → "Inquiry resolved.", × closes; filters reload with the choice kept;
  empty search → the "nothing found" box; New Inquiry empty → stays open (required), filled → "Inquiry submitted.";
  New Feedback → "Feedback sent."; Enter on a card opens it in full, Back closes; Record Achievement still says
  "Achievement recorded."; no JS errors on any of the pages.
- Different from MVC (decided / our looks): Complaints sits 24px lower (the same as every other page; MVC's bar was at
  117 here only) · its filters "All Categories" / "All Statuses" at their own width (MVC: "All" / "All", stretched) ·
  "+ New Inquiry" / "+ New Feedback" = the sky header pill (MVC: maroon / small sky) · resolved green → moss · 17px
  title → 18 · 12.4px → 12 · the inquiry pop-up's labels 12 → 10px (labels) · "Mark as Resolved" = our outline
  button (alabaster; MVC white) · management search hint drops "reference" (no reference number is shown)
- ☐ backend later (owner): real feedback / complaints (Clean/MyTry has a tested version, but its tables differ from
  MVC's screens: praise/concern without a title, 3 statuses + a message thread — reshape them then)
- ☐ the "nothing found" box for complaints is white (MVC tinted it alabaster) — `c-empty-box--tinted` exists now
  (Extracurricular, 2026-10-27): use it here if wanted

**11. Audit log** — admin/audit
- ✅ (2026-10-27) `AuditController::index()` → `ActivityLog::recent($filters, 350)` / `actions()` (no second log model) →
  `admin/audit.php` + `_audit_card.php`. Filters = GET form, the server searches the WHOLE log (MVC only the newest 350
  in the browser): activity, actor role (from the account — MVC guessed it from the ID's text), words (ID, name, details,
  address; `%` `_` typed are letters). Short action names in `config/audit.php` ('names', the rest = the code's words),
  role colours ('tones'). Dropdowns send the form via the new shared `<form data-submit-on-change>` (dropdown.js; People
  uses it too, its own submit line removed). Card = `c-card` + 6px tone edge, `c-avatar`, `c-chip`, `c-label`,
  `c-tag --round --sand`, `c-empty-box`, `c-search`; audit.css 364 → ~200 lines (no `!important`, hexes, dead footer /
  ip / linked rules, its own `.c-tag`); `audit-card.js` deleted. Sizes 9 → 10px, 11 → 12px (the scale); the action tag
  shows the short name (was the raw code). Checked (curl): `action[]=x`, `role=hacker`, `action=NOPE` → all events;
  `q=%'"` → empty box; LOGIN_FAILED → 190 of 190; system + failed → 46; teacher / signed out → refused.
  Fixed after the owner's look: the page didn't load `panel.css`, so `c-card` (white frame, corners, border) was missing;
  the ID was a `c-chip` (line height 1.2 + "…" cut the email) → its own box with MVC's look. Measured next to MVC:
  card 1120 wide, actor column 302.7, avatar 40, ID 250.7×29, tag 140 wide — same; card 83 vs 80px high (the agreed
  9 → 10px label and tag sizes)

## Missing-CSS scan (2026-10-27)
Both bugs above were a shared look (`c-card`, `c-tabs`) used on a page whose `$styles` didn't list its file. A scan of
every role's sidebar pages (+ the post / add forms) for c- / l- classes that no loaded CSS mentions found, besides those
two: `c-pop-up--person` (a `$popUpVariant` nobody styled → removed) and only harmless names: `c-notice-card--academic`
and `c-tabs__tab--sky` (the default look), `c-grade-card`, `c-section-box`, `c-notices-card`, `c-timeline`,
`c-add-person` (a block name whose look is `c-card` / `c-panel` + its parts). Re-run it after each new page
(a headless Playwright script; ask Claude for it)

## Unsaved changes + other "logical needs" (owner, 2026-10-27)
- ✅ Leaving an edit page with changes asks first ("Leave Without Saving?" — Keep Editing / Leave Without Saving; the
  question pop-up), closing the tab or reloading gets the browser's warning: `unsaved-changes.js` (every page,
  page_bottom) + `data-warn-unsaved` on Add Notice / Edit Notice, Add Person, My Profile, the Academic class and
  stage editors, an achievement's edit page. An editor closed with its own Cancel no longer counts. Checked in the
  browser on each.
- ✅ (2026-10-28) A pop-up with something typed / chosen / added asks "Discard Changes?" before ×, Cancel, Esc or the
  backdrop closes it (Keep Editing / Discard Changes) — every pop-up form, automatically (pop-up.js sends a cancelable
  'pop-up:close'; unsaved-changes.js answers). Term Marks (a panel in the page) warns on leaving the page.
  A dropdown choice counts when it is sent with the form (has a name) and the form isn't a filter.
- ✅ (2026-10-28) Sending once: a form really being sent turns its buttons off (not the example-data, question or
  background ones, which don't leave the page). Checked: Publish Notice was off as the page left. The sign-in pages
  don't load these scripts (no question pop-up there) — their sends are already rate-limited on the server
- ✅ (2026-10-28) The idle sign-out: a minute before (config/app.php 'session_timeout', `Controller::sessionTimeout()`
  read once for the server and the page) "Still There?" → Stay Signed In asks for the page again (= activity);
  no answer → the page reloads and the server shows the sign-in page with its message (session-timeout.js).
- ☐ After saving on a real page the browser's Back can show the form again: the pages already send "no-store"; check
  each real form ends with a redirect (Post → Redirect → Get) — they do in the code read so far, test each once
- ☐ Example-data pages: what was "saved" doesn't stay (Interest stays "Interest", a deleted team comes back) — expected
  until the backend; the banner says what would happen
- ☐ (only if wanted later) keep a form's typed text across an idle sign-out — complex and security-sensitive; the
  warning above covers it (owner, 2026-10-28)
- ☐ Certificates' Edit Wording (typed straight into the sheet, not a form): leaving loses it too — mark it when its
  save is built

## White-label review (2026-10-21)
My reviews never asked "is anything school-specific typed in?", so parts ticked above still had gaps. Fixed:
- ✅ Timezone was never set (country.php was never read) → PHP used UTC, so "today" (calendar past days, Upcoming
  Events, the save check) was a day behind between 00:00 and 05:30. `country()` helper + `bootstrap.php` sets it
- ✅ Dates: `country('date_format')` (honours board, the calendar's "events can be added until…" message);
  `country('language')` (new, 'en-US') → `<html lang>` on every page → `calendar.js` writes dates in it (was 'en-US')
- ✅ Emails: subject used "L'École" → `school('name')`; layout's typed backups ("L'École", admin@lecole.edu) removed;
  the footer link's inline style → `.mail-link`; `mail.php` `from_name` → `school('name')`
- ⚠ `mail.php` had a real Gmail address + app password as defaults → removed (they belong in `.env` only).
  That password has been in the code (also in MVC / Clean) → **change it in Google** if this code was ever shared
- ✅ Words: sidebar "Term Marks" → `word('term')`; calendar pill "All School" → `word('institution')`; "Add event"
  pop-up grade(s) / class(es) labels, placeholders and the details hint → `word()` / `plural()`
- ✅ "L'ÉCOLE —" in the header comment of the 30 rebuilt CSS / JS files and the check script → removed;
  `sidebar.js` history name `lecolePortalGuard` → `portalGuard`
- ✅ `bin/check.sh` section 8 (white-label): the school's name, web name and phone code typed anywhere in rebuilt
  code; fixed date formats (PHP `date('M d, Y')`, JS `'en-US'`); a settings file nothing reads.
  `next-feature` skill: a white-label step in Pass 2
- Not checked: switching `school.type` to 'university' (only that `word()` is now used; the words stay the same here)
- ✅ `academic.php`: the Academic page reads `class_size`, `grade_numbers`, `class_name`, `first_section`,
  `max_subjects_per_teacher`, `term_starts` (2026-10-26)
- ☐ `academic.php` still unread: `grade_letters` (term marks / record book, when real marks come), `student_age`
  (sign-up / People), `id_formats` only partly
- ✅ `subject` word (2026-10-26): school Subject · university Module · nursery Learning Area; class badge tints by
  place, not by section letter (a school with "1, 2" or named sections kept grey badges)
- ⏸ (owner, 2026-10-28: skip for now) `country.language` is 'en-US' but the words are UK spelling → 'en-GB'? It is
  not a language picker (nothing on screen): only `<html lang>` for screen readers / spell-check, and the JS dates
- ✅ (2026-10-28) `country.php`: `time_format` (unused; the calendar is 24-hour) deleted
- ☐ `currency`, `phone`, `national_id` → the sign-up / People pages
- ✅ (2026-10-28: none left in `public/assets`) Unbuilt MVC copies (fix when their page is built; section 8 flags them once a page loads them): `@lecole.edu`,
  `+94…` numbers and `lecole_` storage keys in `people-directory.js` / `extracurricular-card.js`; `'en-GB'` / `'en-US'`
  dates in 6 JS files; "L'ÉCOLE —" headers in the other CSS / JS files

## Backend + security review (2026-10-21)
Why missed: my reviews read the views closely and the backend only where a feature touched it, and only by reading
(never sending bad data); `check.sh` matched dead functions by name only and never looked at files, tokens or settings.
Now: the `next-feature` skill has a "Backend under it" step (input, leaks, abuse, transactions, JSON paths, curl tests);
`check.sh` sections 6 (PDO's own methods don't count as uses) and 9 (unloaded files, unused tokens, unread settings).
- ✅ Bad form data (`identifier[]=x`) crashed sign-in with file paths shown → every auth value via `postText()` /
  new `postPassword()` (passwords are not trimmed). Tested with curl
- ✅ Session cookie: `HttpOnly`, `SameSite=Lax`, strict mode (were off; Clean/MyTry had them). Tested: Set-Cookie header
- ✅ Error page: `errors/error.php` for 404 (Router) and 500 (bootstrap's handler on the live site: details to the log,
  a friendly page to visitors); `404.php` merged into it
- ✅ CSRF: ONE gate in `core/Router.php` (`Controller::checkCsrf()` before every POST) instead of 9 hand-written checks;
  the calendar's delete looked the event up before checking. `refuse()`: JSON for background requests, red message +
  back to the form for pages. Tested: no token → back to the form / 419 JSON; another site's Referer → stays on ours
- ✅ Signed out while the "Add event" pop-up is open → JSON 401 "Please sign in to continue." (was the HTML sign-in page)
- ✅ Forgot password: same message and same next step for every ID (an unknown one always gets "Incorrect code");
  one code a minute at most (inbox flooding). Tested: unknown vs real ID, 2nd request inside a minute → no new code
- ✅ Dead: `app.js` (copy of auth.js), empty `css/app.css`, Model's 4 PDO wrappers → `transaction()` (from Clean/MyTry,
  used by `CalendarModel::save`), `$useIt`, `emailCode()`'s `$back`, `maintenance_mode`, `url`, 11 colour tokens,
  the `/auth/{role}Signup` routes
- ✅ Duplicates: one `fail()` in AuthController (was failSignin / failStep / setFlash + redirect ×5); "admin + management
  see all" → `permissions.calendar.see_all` (was typed twice); `CalendarFeed`'s role list → from config/calendar.php;
  `Database.php` → `config('database')`; `csrfField()` → `e()`
- ✅ White-label: student ID prefix from `academic.php` (`normalise()` had "STU" typed); calendar messages use `word()`
- ✅ Sign-in no longer reveals which IDs exist: an unknown ID, a not-yet-activated one (no password) and a wrong password
  get the SAME answer "Incorrect ID or password." + link "New here? Activate your account"; "locked by admin" /
  "deactivated" only after the right password. Differs from MVC: no "N tries left" (it only appeared for real IDs) and
  no separate "not activated yet" message. Tested: unknown / pending / real ID → identical; right password → signs in
- Note (left as is): after 5 wrong passwords a real ID says "Too many wrong tries… locked"; an unknown ID never locks.
  Closing that would mean counting wrong tries for unknown IDs too (e.g. from activity_logs)
- ☐ Hand-in: switch off `demo_quickfill` (it prints the demo password into the sign-in page); `APP_DEBUG=false`
- ✅ `UserModel::updateProfile()` only accepts the columns in its fixed list `PROFILE_COLUMNS` (2026-10-22, Profile)
- ☐ Team: `resetPassword()`'s result is ignored (says "reset" even if the account was locked meanwhile)
- ✅ (2026-10-28) AuthController / UserModel / VerificationCode / Mailer / routes still have `{` on the same line (warnings)
- ✅ (2026-10-28) bug: the sign-in / sign-up pages read `$_GET['identifier']` / `['name']` directly — `?identifier[]=x`
  printed a PHP warning (with the file path) inside the ID box → `queryText()`
- ✅ (2026-10-28) `routes/web.php` typed the Extracurricular roles → `config/permissions.php` 'extracurricular.page'
- ✅ (2026-10-28) `.env.example`: DB_PORT 3307 (MVC's) → 3308; dead `APP_NAME` (typed "MyTry") / `APP_ENV` removed
  (nothing reads them) — ☐ owner: delete the same two lines from your own `.env`
- ✅ (2026-10-28) sign-up step 1 sent a new code every time it was sent again (after "restart") — no wait → a code
  under `resend_seconds` old still works: no new email. Tested: a planted fresh code + 2 sends → no new code
- ✅ (2026-10-28) the sign-in / sign-up pages had no "send once" (a double click sent twice) → it moved out of
  unsaved-changes.js into its own `send-once.js` (the auth pages can't load unsaved-changes.js: it needs the question
  pop-up); page_bottom + signin + signup load it. Tested from inside the page: the button is off 20ms after the send
  (the old guards.js check looked after the page had left, so it always said "false")
- Note: the database clock is UTC, PHP's is the school's (Asia/Colombo). Fine today (each compares with itself);
  never compare a PHP date with MySQL `NOW()`

## Shared UI pieces (2026-10-21)
Inventory: every MVC page (38, 5 roles) measured live, incl. closed pop-ups → 373 distinct looks. Owner's
decisions on near-twins: merge labels card / grade-form, error line + error banner (ONE red: terracotta),
empty boxes, search pill (44px text start), tags (one shared tag); keep both: base / calendar label, base /
calendar box, notice / people tabs.
- ✅ Built (only the looks used today; the rest below, added when their first page comes — no unused CSS):
  `form-field.css` (label + required *, box, message box, checkbox, radio, file box, error line; looks
  `.c-fields--sky` notice form, `.c-fields--calendar` "Add event") · `banner.css` (info · warning · error) ·
  `_form_card.php` + `form-card.css` (page form: Back + card + header, tone sky) · `search-field.css` (pill) ·
  `tabs.css` (sky) · `empty-box.css` · `tag.css` (xs · sm · pill × linen, sky, sunshine, terracotta, maroon,
  sand, cherry, grey)
- ✅ Switched: Post / Edit Notice form (add-notice.css: layout only), notice board (notice-board.css: layout only),
  notice card tags, calendar "Add event" (event-editor.css lost its field / banner copies), Upcoming Events tags
  + pill, Unread Notices pill. Audience tag colours: `config/notices.php` 'audience_tones'
- Checked: snapshot of every element's computed style + box on 7 pages before / after (2,570 elements): notice
  boards 0 changes; forms same boxes (+ title box weight 500 = MVC; ours had 400); intended only: calendar error
  banner / field errors maroon → terracotta, event "General" tag sky 0.15 → 0.22; `check.sh` 0 errors
- Note: `form-card.css` was an untouched 816-line MVC copy (no page loaded it) — overwritten by the new form card;
  MVC/ keeps the original for People, Feedback, Certificates
- **Measured MVC looks to add later (values = what the screen showed):**
  - fields base (Complaints, Reject, Verify, Extracurricular pop-ups): label 12/700 CAPS 0.08em 65%; box white,
    border alabaster, r10, p10/12, 14/500; focus sky border + 3px ring sky 25%
  - fields card (Add Person, Guardian, Certificates, Feedback): label 10/700 CAPS 0.06em 70%; box = base
  - fields filled (People forms): box bg alabaster 55%, border alabaster, r10, p10/12, 14/500
  - fields large (Record Achievement): label 13/700 CAPS 0.08em midnight; box h44 r12 p0/16 14/400; message p12/16
  - fields small: 13/500 r8 p7.2/12 (club edits); 13/400 r8 p8.8/13.6 (complaint reply)
  - choice buttons (People ID type): 13/600 r8 p8/14 border alabaster; chosen: terracotta text, bg 8%, border terracotta
  - file boxes: Record Achievement (bg alabaster, 1.5px dashed rgba(168,107,50,.35), r10 p20/16, icon rgb(140,90,36),
    file name pill sand 35%); club cover (bg sand 12%, 2px dashed midnight 20%, r10)
  - hints: 12/400 60% · People helper 10/400 55% · note banner (People: bg cream, border sand, r14 p12/16, 12/400 70%)
  - form card headers (Add Person): sky (midnight text), sunshine / terracotta / maroon (white text); title 18/700
    -0.01em; p20/24 (notice: 20/800, p24/28)
  - role buttons: terracotta 13/700 r8 p8/16 · maroon 13/700 r10 p8/20 · sunshine 13/700 r10 p8/20 · sky 14/700 r10 p10/16
  - search: small (People filters) h34 r10 border alabaster 12/500, text from 36px, icon 14 · rounded (Teacher marks,
    achievements) h40–42 r10 14/500, text from 40px
  - tabs: dark (Extracurricular: group white r-full p4; tab 13/600 p6.4/18; chosen midnight + white text) ·
    white-on-grey (People status: group midnight 5% + border 12%, p2; tab 12/600 p5.6/12; chosen white 12/700) ·
    People roles (12/600 p7.2/18.4; chosen sky 12/700) · subtabs (group alabaster 35% r14 p6; tab 12/700 r10 p8/14
    60%; chosen white) · Certificates (13/700 r-full p0/18; chosen midnight) · Child profile (14/700 r14 p10/16; chosen midnight)
  - empty boxes tinted: Achievements (bg cream 50%, 2px dashed 15%, r14 p40/16) · Complaints (bg alabaster 35%,
    dashed alabaster, r14 p56/24, title 16/700) · club (2px dashed alabaster, r14 p32) · Guardian (white 70%, r10 p16)
  - tags: People (r10 10/700 0.06em p4/8: sky 30%, moss-grey (164,171,152,.3) + moss text, terracotta 15%, sand 50%) ·
    ID / status pills (r6 9/700 p4/10: sky 20%; active moss 15% + moss; unknown alabaster) · Achievement badges (pill
    11/700 p2.8/8.8, own palette: academic (43,78,99)/(223,233,238), sports maroon/(244,220,219), clubs
    (75,107,46)/(228,236,216), leadership (106,63,150)/(237,224,245)) · status badges (pending sand 45% r4 p4/10;
    resolved pill (14,159,110)/(211,246,231)) · club pills (pill 11/700 p4/10: sport maroon + white; pending sunshine
    25% + (196,104,0); club (142,183,181))
  - MVC bugs seen: `c-marks-input`, `c-grade-form__input`, `c-form-textarea` (Student Academic, Child Profile) showed
    the browser's raw look (their CSS never loaded) → use the shared fields there

## Frame + checks (2026-10-21)
- ✅ The page frame loads the sidebar's CSS + JS itself (page_top / page_bottom), like buttons and pop-ups: every
  page used to list 'sidebar' in `$styles` / `$scripts`, and forgetting it broke the layout (it happened on a test
  page). Removed from the 5 dashboards. Checked: teacher dashboard, sidebar fixed 256px, collapse, each file once
- ✅ `check.sh`: "class used in a view but no loaded CSS" is one line per component now (was one per class: 104 → 50
  warnings), so a real warning isn't lost among components no page loads yet
- Note: our app and MVC share the `PHPSESSID` cookie, so using one signs you out of the other — fine (owner): MVC goes away

## Consistency pass (owner, 2026-10-22) — the same kind of thing looks the same everywhere
Discussed one item at a time; applied together in one pass (then every built page re-measured), before Academic C.
Measured on the 58 CSS files our pages load: 29 font sizes, 9 label letter-spacings, 5 card-title looks.
- ✅ **1. Fonts — done 2026-10-22** (decision below). What changed:
  - Sizes: 29 → the 11 of the set, all in rem; `check.sh` section 2 now FAILS any other size (print.css and the
    trend chart's SVG text, marked `svg units`, are left out). Sizes given by job, not only by rounding: the Export
    button and the tabs are controls (→ 13), "Add event" is a control (15 → 13), the Unread Notices pop-up title is a
    pop-up title (17 → 20 / 800), the two 6-digit code boxes are one look (`field-input--code-large` deleted)
  - New shared `label.css` (`.c-label`, `--light`), loaded by the page frame + the sign-in and landing pages: ~40
    places now use it (form / filter labels in all 3 forms and on sign-in, weekdays, dates, event times, eyebrows,
    sidebar "MAIN MENU" + portal name, metric card labels, total marks label, chart axis titles, "VIEW ALL" buttons).
    Each place keeps only its colour (orange, terracotta, moss, white…) or its spacing
  - Card titles: every card uses `c-panel__title` (16 · 600): dashboard panels, donuts, Upcoming Events, Unread
    Notices, profile sections, record book, Performance Trend, Full Timeline; `--small` and 4 own title rules deleted;
    `panel.css` added to the profile, achievements and academic pages
  - Tags: 5 size variants (`--xs --sm --md --pill --round`) → one tag + `--round`; the profile hero's ID / ACTIVE,
    the calendar's event category and the landing honours tags were tags under other names → now `c-tag` + only their
    own colours (`.ach-tag` deleted); `tag.css` added to the profile and landing pages
  - Also removed while there: 4 `font-family` repeats in bar-chart.css
  - Checked: `php -l` (75 files), `check.sh` 0 errors (it caught a broken class from one of my edits — fixed), every
    page of every role loads with no PHP warning; looked at in the browser: sign-in, student dashboard / academic /
    profile, teacher Record pop-up / Add event pop-up / notice form / term marks, honours board
- ✅ **Found while doing 1 — done 2026-10-23** (with the leftovers of 2 and 4, below). What happened to each:
  - Already done by 3 / 4: the sign-in fields (3), `font-family: inherit` (3), weekday names (4), capitals typed in
    PHP (4), the two words (4). Pop-ups' focus ring on ×: measured in Chrome — opened with the mouse no ring, with
    the keyboard the ring shows (3's `:focus-visible` rule); nothing to change
  - Chip: one `.c-chip` (+ `--round`) in tag.css (a short name in normal letters, 12px · 600; a long one ends with …):
    the multi-select's chosen options and the calendar's audience pill ("All School") — dropdown.css keeps only its
    sky + the ×, day-card.css only the sunshine + 130px; the badge's 10px, `text-transform: none` and "prevent
    leaks" comment deleted
  - Not a problem: tag colours (`tone-maroon` = a solid tile, `c-tag--maroon` = a soft tag: two looks since 2);
    `.c-calendar__add-event-btn` (one shared rule for the 3 dashed boxes + its own look, not a repeat)
  - Donut tooltip: filled on mouseenter, then only `chartTooltip.move` (like the bar chart); tested: the same element
    after a move
  - Favicon: `<link rel="icon">` = `school('logo')` on every page head (frame, sign-in, sign-up, chooser, landing,
    honours board, error page) — white-label, no file to swap; also fixed "Sign In —L'École" (no space) in 2 titles
    and "Honours board" → "Honours Board" in its tab
  - `_achievement_row.php` → 2 spaces like the other components (+ Title Case fact labels)
  - Multi-select placeholder: the empty chip row no longer takes the space (`:not(:has(.c-chip))`) → on the left
  - Event form: the 7 `<label>`s that pointed at nothing (custom dropdowns, the time group) → `<span>` like the notice
    form (Event Title / Details keep `for`); "Start Time" / "End Time" a lighter shade (`--text-hint`) under
    "Scheduled Time Window"; the grade / class words via `words()` (2 variables deleted)
  - Titles, one weight per job: card titles 600, every other title 700 (was 800: pop-up frame, question pop-up,
    Unread Notices pop-up, evidence preview, form card, notice card, student card name). One pop-up title look:
    `.c-pop-up__title` (20px · 700 · −0.01em) also on the question, Unread Notices and evidence titles (their own size
    / weight rules deleted); the Forgot Password and evidence titles 18 → 20px
  - Checked: `check.sh` 0 errors (56 warnings, as before); curl every role, 21 pages, no PHP warning; headless Chrome
    (own profile): every title measured (20px 700 −0.2px / 16px 700 / 18px 700), chips 12px, placeholder 19px from
    the left, only the 2 tied labels left, tabs, honours facts, footer links; no console error, no 404
  - The list as it was:
  - Same thing, two ways: the sign-in pages draw their own form fields (`auth.css` `.field-*`) apart from
    `form-field.css` · tag colours exist twice (`c-tag--maroon` in tag.css and `tone-maroon` in global.css) ·
    weekday names typed in English in the calendar (`'Mo', 'Tu'…`) but made by `date()` in the date picker · a sentence-
    case pill (calendar audience "Grade 10 · 10-A") is neither a tag nor a chip — define a "chip"
  - Unwanted code: `.c-calendar__add-event-btn` written twice in day-card.css · `font-family: inherit` repeated on
    buttons (the reset already does it) · day-card badge's `text-transform: none` + "prevent leaks" comment · words typed
    in capitals in PHP ('NOTICE', 'EVENTS', 'MARKS') when the CSS makes them capitals anyway
  - Technical: the donut tooltip builds new elements on every mouse move (build on enter, only move after) ·
    `favicon.ico` missing (a 404 on every page) · `_achievement_row.php` uses 4-space indents unlike every other view
  - Clarity / use: pop-ups open with the browser's blue focus ring on × · the multi-select's placeholder ("Select
    classes…") sits on the right · a group label and a field label now look the same (calendar "Scheduled time window"
    over "Start time") · the calendar form's `<label>`s are not tied to their fields (`for`) · title weights differ
    (cards 600; notice title, form card, pop-ups 800) and pop-up titles −0.01em vs the question pop-up none
  - Words (item 4): "will appear here" subtitle; "central school portal" typed on the notice form
  - 1a: 11 sizes, one per job — 10 small capital labels / dates · 12 helper text, captions, chips · 13 controls
    (buttons, dropdowns, search, nav, card body) · 14 body, inputs · 16 card titles · 18 names / big headings ·
    20 pop-up titles · 24 page title · 30 / 36 big numbers (48 only the landing hero). Merged: 8, 8.5, 9 → 10;
    11, 11.5, 12.5, 12.8 → 12; 13.5 → 13; 15, 16.8, 17 → 16; 18.4 → 18; 22.4, 25.6, 26 → 24; 32 → 30. One spelling
    (rem). ~55 elements move, nearly all by 1px. Not included: print.css (A4 paper) and SVG chart text (stretched
    with the chart). `check.sh` rejects any other size
  - 1b: one `.c-label` (`label.css`): 10px · 700 · uppercase · 0.08em · midnight 60%; `--light` on dark backgrounds.
    Used by every small capital text (~33 places: form / filter labels, weekdays, dates, eyebrows — the landing and
    sign-in eyebrows get tighter). ~−110 CSS lines
  - 1c: one card title `c-panel__title` 16px · 600 for every card (dashboard panels 18 → 16, donut / events /
    notices 15 → 16, record book + trend 15/700 → 16/600, timeline heading); `--small` removed. ~−20 lines.
    Names (student card 18/800), pop-up titles (20) and the page title (24) stay
- ✅ **2. Colours — done 2026-10-22** (owner: fix all of 2a–2g)
  - Text / lines / hover: 29 shades of midnight typed as rgba → 9 tokens by job, made from `--midnight` with
    `color-mix` (`--text-soft 70 · --text-muted 60 · --text-faint 50 · --text-hint 40`, `--line-faint 8 · --line 15 ·
    --line-strong 20`, `--wash 6 · --wash-strong 10`); other midnight tints `color-mix(var(--midnight) N%)`
  - Palette tints (sky ×55, terracotta, sunshine, maroon, sand, alabaster, cream, flame, deepsea, bronze, clay)
    → `color-mix(in srgb, var(--token) N%, transparent)`: another school's colours now reach every tint
    (before, changing `--skyblue` left 55 old-blue tints — a white-label bug)
  - One red: `--danger` = maroon (`--danger-soft` its tint) for delete, error banners (were terracotta), required *,
    the issue ring, × hover, the sign-in error box; success = moss (the term marks note; was Google green); the admin
    hero's green ACTIVE badge now looks like every role's. `--online` (the sidebar's presence dot) kept: moss
    would not show on the dark sidebar
  - The academic trend chart uses the dashboard chart's maroon / sky (was kelp / pink)
  - Tags: the honours board tags use the tag tones (`config/achievements.php` categories renamed: sky, sand, grey,
    default linen) — soft tints like every other tag (were solid tiles); `tone-*` is now only for solid tiles
    (landing pathway cards, feature icons, sign-in role icons); `tone-midnight`, `tone-neutral` deleted
  - Tokens 121 → 108: removed `--color-surface` (= white), `--color-border` (= alabaster), `--color-text`,
    `--color-text-muted`; merged near-copies chalk→ivory, wicker / oat / linen→alabaster, ink-soft→frost,
    putty→stone, driftwood→khaki; removed kelp, blush, rosewood, honeydew, clover, basil, pomegranate, alarm, mint
  - Leftovers: every `var(--x, fallback)` removed (several were wrong: `--color-border, #E2E8F0`, `--radius-sm,
    0.25rem`, `--radius-md, 0.625rem`); auth.css's typed `#B91C1C` / `#FEF2F2` → `--danger` / `--danger-soft`
  - Checked: `check.sh` 0 errors (was 301 colour errors when the rules were added), no unused token; trend chart
    measured in the browser (maroon rgb(127,3,3) / sky rgb(127,199,204)); `color-mix` renders
- ✅ **Why 1 and 2 were not caught while exporting from MVC (and the fix)**: every review compared a new piece with
  MVC only, never with the pieces already built, and `check.sh` had no rule for values — so MVC's per-page sizes
  and colours were copied faithfully ("MVC's look, exactly"). Fixed so it cannot be forgotten:
  - `bin/check.sh` section 2 now FAILS: a font size outside the set; a palette colour typed as `rgba()`; a hex
    outside global.css; a `var(--x, typed fallback)` (shadows wait for item 3)
  - `.claude/skills/next-feature/SKILL.md` Pass 2 has a "Consistency with what we already built" step: map MVC's
    sizes / colours to ours; reuse `c-label`, `c-panel__title`, `c-tag`, the colour tokens; list MVC's differences
    in the report instead of copying them
- ✅ **3. Shapes and buttons — done 2026-10-23** (owner: all of 3a–3m; shapes may NOT change: a pill stays a pill, a
  circle a circle, a corner ≥4px different stays; ≤2px differences join the nearest token; sizes may join)
  - Corners: every corner is a token (new `--radius-xs` 4px for tags, chips, checkboxes); joined (≤2px): notice card
    16→14, sand input 12→10, marks input 6→8, cream / toolbar dropdown 10→8, back button 8→10, trend tooltip 8→10,
    record-book icons 7/8→8, chip × and trend key 2→4. Kept: panels / student cards / pop-ups 20, cards 14, the
    "Forgot password?" pop-up's square ×
  - Shadows: 73 typed → `--shadow-sm` (resting) `-md` (lifted, hover, active tab) `-lg` (menus, date picker, tooltips,
    feedback banner) `-xl` (pop-ups, sidebar), all midnight; `--ring` = the one focus look. Role glows kept as one rule:
    the profile hero (`--hero-glow`), the student card being edited (sky)
  - Focus: `body :focus-visible { box-shadow: var(--ring) }` in global.css; 12 `outline: none` + 18 own glows deleted
    (the search box and the role cards had almost no visible focus before). Disabled: one rule (0.4, not-allowed)
  - Buttons: one `c-btn` + looks by job: new `--outline` (Cancel, Change Password), `--accent` (orange: sign in,
    landing), `--dark` / `--glass` (sign-in back, landing pills, honours back, hero Edit Profile), `--close` (×).
    Deleted: `auth-submit`, `l-btn` (+ `--small`, `--pill`), `back-btn` (+ `--dark`, `--glass`) in global.css,
    `c-question__cancel` / `__confirm` looks (only the tone colour stays), `c-marks-panel__submit / __cancel`,
    `c-profile-section__button`, `c-profile-hero__edit-btn` look, `c-record-form__view-file` look. Every button lifts
    1px on hover (Back slides, × grows, links don't)
  - ×: 5 looks → `c-btn--close` (32px light grey circle, red on hover) in the pop-up frame, Unread Notices, the marks
    panel, the feedback banner, the evidence preview; their 4 own rules deleted
  - Fields: 10 looks → `c-field__input` (42px, form-field.css) and `--small` (34px: profile details + password, marks);
    the sign-in pages now use `c-field` (auth.css `.field / .field-label / .field-input / --code` deleted;
    `c-field__input--code` in form-field.css); date picker trigger, search, pill dropdowns all 42px (`c-search--short`
    deleted); a form look (sky / calendar / sand) only changes colours now. `c-field__error` terracotta → `--danger`
  - Messages: `c-banner--success` (moss) added; `.auth-alert` → `c-banner--error` (banners take an icon + a link);
    the notice board's "published / deleted" was sky info → success; the marks note → success banner; the profile's
    dark toast → the feedback banner (`$feedbackMessage`, shown on load); feedback banner success midnight → moss
  - Cards: one border (`--line-faint`), padding 1.25rem 1.5rem, `--shadow-sm` (panel, record book, trend, timeline,
    profile sections, form card, notice card, metric cards); clickable cards lift 2px + `--shadow-md` (student card
    4→2). The calendar keeps its midnight border: on its midnight background it was never visible
  - Layers: 15 numbers → `--z-raised 10 · --z-sidebar 30 · --z-menu 40 · --z-toast 60` (0–2 only inside a component).
    The marks dropdown menu was at 1050 (above the sidebar) · Hover speed: `--speed` 150ms everywhere (open / close
    animations keep their own) · Avatars: 32 / 44 / 72 / 84px · white tints `rgba(255,255,255,…)` → color-mix
  - Also: `font-family: inherit` repeats deleted (reset does it), banner.css's wrong "errors are terracotta" comment
  - `check.sh` section 2 now FAILS: any `rgba(`, a typed corner, a typed shadow (rings, inset and marked "glow"
    allowed), a typed z-index above 2, `outline: none` or `font-family: inherit` outside global.css; WARNS a typed
    100–220ms transition. next-feature skill: shapes / fields / buttons / messages added to its consistency step
  - Checked: `php -l`, `node --check`, `check.sh` 0 errors (56 warnings, all from before: brace style in 3 PHP
    files, `setPhoto()` unused, the pop-up's own animation); in the browser: teacher sign-in (field 42px, Sign in,
    back), teacher dashboard, Add event pop-up (× , fields), term marks (panel, inputs, Submit → success banner),
    My Profile (Save → floating banner), landing (buttons), Record Achievement pop-up, log-out question; Tab →
    3px sky ring. Not looked at yet: admin / management / parent / student pages one by one, the honours board,
    sign-up and "Forgot password?" steps
  - Visible changes to know: landing's big pills 52 → 40px tall and "Find your access" 12 → 14px text; form inputs a
    few px taller; the sign-in error box a lighter red; × buttons all grey circles
- ✅ **4. Words — done 2026-10-23** (owner: all of 4a–4i)
  - Capitals: Title Case for what names a thing (buttons, titles, tabs, menu / footer links, field labels), sentence
    case for sentences. Changed: Sign In, Forgot Password?, Send 6-Digit Code, Set Password & Continue, Resend Code,
    Back to Sign In, Reset Your Password, View All, Clear Filters, Add Event / Edit Event / Save Event / Save Changes,
    All Day, landing (About Us, Honours Board, Find Your Access, Join the Community, See All Achievements, the 3 feature
    titles, "… Portal", Our System), "… Home", event form labels, portal field labels and sign-up titles
  - Sign In / Sign Out everywhere people read it: sidebar "Sign Out", the question pop-up "Sign Out & Exit", the
    profile's "Last Sign-In" (code names `logout`, `LOGOUT` stay)
  - One name: "notice" (5 board subtitles, the form's placeholder / subtitle, Unread Notices, the management error);
    the notice tabs "For Me" / "Posted by Me" (were My Notices / My Posts); honours (not honors); extracurricular (not
    co-curricular); Sport Programme
  - School words: new `words()` helper (core/helpers.php): `words('{Class} {Teacher}')` → each {key} = word('key'),
    {Class} capital, {class} small. Used in the profile labels + section titles (config/profile.php, ProfileController),
    the notice subtitles (config/notices.php), the portal texts (config/portals.php), the landing cards, the Record
    pop-up (3 "Student"), the teacher Achievements page (subtitle, search, filter, empty box), the dashboards' tab
    titles, the management error, the notice title example, "(Class Teacher)", the calendar's aria-label, sign-up's
    "(as registered with the school)"
  - The role photos + alt texts were typed twice (portals.php AND LandingController) → once, in
    `config/white_label/school.php` 'photos' (another school changes its pictures there)
  - Messages: "<Thing> <done>." — Notice published / updated / pinned / deleted, Event saved, Changes saved, Marks saved,
    Achievement recorded, "Your password has been reset. Please sign in."; the two "wrong code" messages are one text
  - Empty: "No upcoming events.", "No unread notices.", "No more notices" + "You have read every notice.", the board
    "No notices yet." (none at all) / "No notices match your search or filters." (the per-role `empty` setting deleted)
  - "…" one character (9 placeholders); capitals typed in PHP (NOTICES, EVENTS, OCT, MARKS, the term axis) → normal
    words, the CSS shows capitals (`text-transform` added to `.c-trend__axis-title`); calendar weekdays made by `date()`
    like the date picker (were typed 'Mo', 'Tu'…)
  - `check.sh` section 8 now FAILS: a typed Student / Teacher / Parent / Grade / Class / Term / School in views,
    controllers, services, core, loaded JS and config (not white_label, not app/Mock; `'value' => '…'` data allowed);
    `...`; "successfully"; Logout / Log Out / Login. Tested on sample bad lines (all caught). next-feature skill:
    a "words" line in its consistency step
  - Checked: `check.sh` 0 errors (56 warnings, the same as before); curl as every role: all 21 pages 200 with no PHP
    warning, and the new words are on them (e.g. "School Email", "Current Class", "Last Portal Sign-In", "For Me");
    `words()` tested with the university words ("Course Lecturer · your courses · University-wide").
    NOT looked at in a browser (Playwright was in use by another session): the tag / month / axis capitals rely on
    `tag.css` / `label.css` / the new axis rule — look once
- ✅ Found while doing 4 (2026-10-23): the role lists → the notice tabs read `config('notices.tabs')` (new setting),
  the landing footer `config('app.roles')` (same order, nothing visible changed). Left as notes: app/Mock's
  `'from' => 'Class'` tag and the mock chart titles ("Extra-curricular Activity Engagement", "Grade wise engagement
  of cricket") go with the real data · unbuilt JS files hold typed words, `alert()`, "successfully", "..." —
  `check.sh` catches them once a page loads them · unbuilt `extracurricular-detail.css` has its own `.c-chip`:
  merge into tag.css's when Extracurricular is built · UK spelling is not white-label (no translation system)
- ✅ Found while doing 2: `--online` vs moss — kept on purpose in 2 (moss does not show on the dark sidebar)

## Findings while building the dashboards + calendar (2026-10-19)
- ☐ "View all" pop-up: MVC staggered each row's fade-in (`style.animationDelay` from JS) — dropped; do it in CSS
  (`:nth-child`, like the metric cards) if wanted
- ✅ The event pop-up loads its own `<link>` / `<script>` in the middle of the page → moved to the page's lists
- Note: `c-dropdown--compact` never changed anything in MVC (its rules lost to the `:not(...)` chains) → removed from the time pickers
- ✅ Faded icon colours on the role chooser and the landing page (role cards, feature icons, honours tags): `global.css`
  defined `.tone-*` twice, the second (pale, not from MVC) overrode the first → duplicate removed, all solid like MVC
- ✅ Role chooser vs MVC: order, "Management panel", graduation-cap badge (2026-10-20, with the sidebar)
- Note: the clash warning comes back as HTTP 422, so the browser console shows a red line for it (expected, not a bug)
- Note: MVC (8040) and ours (8080) share browser cookies on `localhost` — signing into one signs you out of the other
- Note: `global.css` has `body [hidden] { display: none; }` so `hidden` always wins over a component's `display: flex`
  (an `<svg>` has no `.hidden` property in JS — use `toggleAttribute('hidden', …)`)

## Rule breaks found by the edit hook (2026-10-18)
57 files have `!important` or inline `style=""` (most came over from MVC). Fix each one when its page is built.
- ✅ (2026-10-28: `grep -rlE '!important|style="' app public | grep -v Views/emails` finds nothing) Views: `auth/` (signin, signup, forgot_modal), components (`_metric_card`, `_donut_chart`,
  `_bar_chart`, `_upcoming_events`, `_delete_modal`, `_dropdown`, `_unread_notices`, `_icon_logos`, `icons`)
  (done: the dashboards and the calendar templates)
- ✅ (2026-10-28: 0 left) CSS: `!important` in ~30 files in `public/assets/css/components/`
- ✅ (2026-10-28) JS: inline styles written by ~20 files → 6 left, each a worked-out position no class can hold
  (tooltips at the mouse: chart, academic, academic-section; datepicker and dropdown placed by the window's room;
  the certificate pager's slide). dropdown.js: its fixed values → classes `--end` / `--up`; its "menu inside a table"
  code (placeOverTable, `[data-fixed-dropdown]`, the `.c-table-scroll` lookup) was dead — no page has one — removed
- List them: `grep -rlE '!important|style="' app public | grep -v Views/emails`
