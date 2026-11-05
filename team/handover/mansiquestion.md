# Mansala — questions for the team lead

This file records decisions and open questions before B-8 and B-4 implementation. It does not authorise code changes by itself.

## B-8 — trip permission cards (on hold)

The intended case is a **trip or similar event**. A parent decides whether their own child may attend. The previous B-8 implementation was reversed because the actor who creates the request and its place in the interface had not been agreed. Do not restore that implementation until the workflow is settled.

Questions for the lead:

1. Who creates a trip permission request: a teacher in charge, management, admin, or another role?
2. Where does that person create it? Should it be a Notice Board item, as the current handover/checklist says, or a different existing screen?
3. Who selects the students or class invited to the trip, and can a request target a club or sport group?
4. Which details are required: trip name, date, place, cost, reply-by date, and any other information?
5. Can a parent change an Approve/Reject answer before the reply-by date? What happens after it?
6. Which staff may see the answers, and how is the rule "a student cannot go without a yes" enforced?

## B-4 — character certificates

### Agreed graduating workflow

1. B-4 currently covers **graduating students only**: the school's configured final grade. The admin updates that batch's graduation date in school settings each year. On that date, the whole batch automatically enters management's certificate queue. No student or parent certificate-request button is needed. Management reviews and finalises **each student separately**.
2. Students may view a draft built from their real records. The existing B-3 missing-record request remains available. The class teacher approves a missing-record request and adds the achievement, or rejects it with a reason; **the student sees that reason near "Something missing?" on the existing certificate page**.
3. The class teacher resolves missing-record requests only; there is no separate teacher sign-off on the whole certificate. Management sees the requests' statuses, reviews the certificate, and edits its wording. A pending missing-record request must be resolved before management can finalise.
4. There is no separate management Approve/Reject decision for a certificate request. In this workflow there is no certificate request: **Finalise** is management's decision to issue the graduating student's certificate. B-3's class-teacher Approve/Reject actions for missing records remain.
5. When management clicks **Finalise**, show a confirmation popup explaining that clicking **OK** makes the certificate PDF available to download and print for the student. Only after confirmation does the certificate become issued and its final text freeze.
6. Before finalisation, the student's **Download / Print** control is disabled. After finalisation, the student may download or print the issued PDF. Parent certificate access is skipped for this phase by the user's later decision. Keep the existing visual design; connect its controls to real data and actions only.

### Leaving pathway — deferred

The lead is finding a solution for leaving students. Do not implement the leaving pathway or a leaving-date rule yet. The earlier proposal to stop achievements and club/team entries after a leaving date remains parked with this pathway.

Questions for the lead:

1. Settled: use the school's configured final grade. The admin updates its graduation date yearly on the existing Academic page, through a small setting. Do not hardcode a grade or date.
2. Settled: show a rejected missing-record request and its reason near "Something missing?" on the student's existing certificate page. B-3 currently has no student "My Requests" panel, so this is a narrowly approved addition.
3. Settled: management reviews and finalises each student's certificate separately after the configured graduation date puts the batch in its queue.
4. Settled: the parent portal has no Certificate tab or PDF entry point; do not add one in this phase. The earlier parent PDF requirement is superseded by the user's later instruction to skip it.
5. What is the exact leaving-student workflow, including who sets the leaving date, where it is set, and which records are blocked afterwards?
6. The old student form offers "Email as Signed PDF." Since certificate-request buttons are removed for graduating students, is email delivery out of scope for this phase?

## Implementation boundary

Follow `team/AI-GUIDE.md`, `team/CHECKLIST.md`, `team/handover/mansala.md`, and `code_lecole/CLAUDE.md`. Prepare a B-4 plan for lead approval before code. Do not change the finished screen design. B-3 is on `feature/b3-achievements` and B-6 is on `feature/b6-complaints`; check the merged base and next migration number before starting a B-4 branch.

## Confirmed gaps in the current screens (read-only check)

- The student certificate page has a **Download / Print** link and a **Request Character Certificate** button. The latter must be removed for the agreed graduating flow; the former must be disabled until finalisation, with the PDF route enforcing the same rule.
- Management has **Print Certificate** and **Finalise**, but no Finalise confirmation popup yet. The agreed popup is a specific addition to the existing screen, with no redesign.
- There is no parent certificate page or parent PDF route/control. Parent access is outside this phase.
- There is no stored graduation date in the current code. Add a small admin-controlled setting on the existing Academic page.
- B-3 stores a missing-record rejection reason, but its student side has no request-history panel. Show the reason near "Something missing?" on the existing certificate page.

No backend implementation or frontend edit has been made for these gaps.
