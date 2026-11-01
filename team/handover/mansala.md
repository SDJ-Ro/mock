# Mansala — your tasks

Read `team/ONBOARDING.md` first: it has the overview of what the project has so far.
Every code path below is inside `code_lecole/`.

Each task: branch from `dev` → `/next-feature` plan → the lead approves → small commits → pull request into `dev`.
The full decisions are in `team/CHECKLIST.md` → "Backends left to connect".

## 1. B-6 Complaints — first (the simplest)
- **What:** the parent writes (recipient: class teacher / head teacher / principal, category, subject, body, child);
  one-way; management marks it resolved with an answer.
- **Pages now on example data:** `parent/complaints.php`, `management/complaints.php` ← `ComplaintController` reads
  `app/Mock/complaints.php`. Components: `_complaints_board`, `_complaint_card`, `_new_inquiry_pop_up`.
- **Needs:** a `complaints` table (update file; the old one was dropped in update 027 — it was a thread, the proposal
  says one-way), `ComplaintModel`, POST routes (send, resolve), the categories in `config/complaints.php`, who may
  resolve (`config/permissions.php`). The parent's child comes from the child switch (`$user['child']`).
- **Tell people (B-7 list):** complaint sent → its recipient · resolved → the parent.

## 2. B-8 Permission request cards
- **What:** trips / events (name, date, place, cost) → each parent approves or rejects, with the time; the teacher
  in charge can't take a student without a yes.
- **Decided:** a permission request is a **notice of kind "Ask parents for permission"** (date, place, cost,
  reply-by) that creates an Approvals card per parent; replies are counted on the notice.
- **Reuse:** the Notice Board's form and save (`NoticeController`, `_add_notice_page`) and the parent's Approvals page
  (`ApprovalController`, `_approval_card` — it already shows club-Interest cards).
- **Tell people:** a request is sent; its reply-by date is near → the students' parents.

## 3. B-4 Character certificates — after B-3 Achievements
- **What:** built from verified records (clubs, achievements), draft preview (pending items marked), management's
  final verification, PDF download locked until verified, leaving date (stops new entries, warns), certificate
  requests + missing-record requests.
- **Pages now on example data:** `management/character_certificate.php`, `student/certificates.php` ←
  `CertificateController` reads `app/Mock/certificates.php`. The PDF already works (`Services/CertificatePdf`,
  Dompdf, `_certificate_sheet`).
- **Reads:** `achievements` with their `extracurricular_id`, team and result; positions with start / end dates
  (`team_member_history`).
- **Tell people:** a leaving date is set → class teacher, teachers in charge, management.

## Practice (before B-6)

Make one small change on a branch `practice/mansala` (for example: a teacher must be under 30 — `config/people.php`,
`ages.teacher.max`), open a **draft** pull request into `dev`, and ask the lead to review it. It is closed without
merging — it's only to practise the whole Git flow once.

## Ask the lead

Anything outside your tasks · any decision marked ☐ or "discuss" in the checklist · a new table or column ·
a change to how a page looks.
