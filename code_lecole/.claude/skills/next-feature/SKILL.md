---
name: next-feature
description: Plan (then build, once the lead approves) a task from ../team/CHECKLIST.md "Backends left to connect" (B-3, B-4…) or a smaller fix. Use when someone says "next feature", "plan B-5", "start my task", or asks to analyse or plan a feature. One complete report, no second round.
argument-hint: <task, e.g. B-5 Feedback>
---

# Next feature: $ARGUMENTS

One complete report per task, so nobody has to ask "recheck" or "anything else?". Rules: `CLAUDE.md`.
Make no file changes until the lead approves the plan.

## Pass 1: read everything (no file changes)
1. **The task:** its entry in `../team/CHECKLIST.md` ("Backends left to connect"), every ☐ item that mentions it
   (grep the task number and the feature's name), its rows in the notification list, and the person's
   `../team/handover/{name}.md`.
2. **The pages:** every view that shows this feature (grep `app/Views`), the components they include, their CSS and JS,
   and what data each one is given today. Pages still on example data read `app/Mock/*.php`: that file is the exact
   shape the real data must have.
3. **The backend under it:** route (`routes/web.php`), controller, models, services, config, the tables
   (`database/schema.sql` + `database/updates/`). What exists already, what is example data.
4. **Reuse:** how a finished feature did the same thing (B-1 Marks, B-2 Extracurricular are the examples to copy:
   `TermMarkController`, `ExtracurricularController`, `TeamModel`, `JoinRules`). A helper that already does it
   (`can()`, `rule()`, `word()`, `e()`, `transaction()`, `Upload`, `AuditLog`, `refuse()`, `setFlash()`).

## Pass 2: think it through
- **Rules and logic:** every rule the task needs, written as "who may do what, when" — and where each one lives
  (`config/permissions.php`, `config/white_label/rules.php`, the feature's config). Questions only the lead can
  answer go in the report as questions, with a recommended answer.
- **Input:** every form value through `postText()` / `postPassword()` / `postList()`; what happens with an empty
  value, a too-long one, `field[]=x`, an ID that isn't theirs.
- **What an attacker learns:** do two messages differ for a real vs an unknown ID?
- **Saving:** SQL only in models, prepared statements; several changes together → `transaction()`; an update file
  for each table change; the Audit Log for changes people must be able to trace.
- **Background (JSON) requests:** JSON on every path (signed out, expired token, error).
- **Dead code:** the example data this task replaces (`app/Mock/…`, `data-mock-message`) is deleted when the real
  data works; nothing left that nobody calls.
- **Duplicates:** the same rule typed twice → one place.
- **The look stays the same:** the page must look exactly as before; say so if anything visible changes.
- **White-label:** words via `word()` / `plural()`, dates via `country('date_format')`, nothing school-specific typed.
- **Simpler:** can an existing component, helper or browser feature do it? Fewer settings, less JS, PHP works it out.

## Pass 3: re-run Pass 2 on your own plan
Look for anything new your plan creates (dead settings, a new duplicate, a missing check) and fold it in.

## The report (one message, short, tables)
1. Where it shows (table: page · component · what changes).
2. Rules and logic (table: rule · where it lives · decided or question for the lead).
3. Issues found (table: # · issue · where · why it matters).
4. The plan in small steps, each one commit (table: step · files · commit message `type(scope): …`).
5. Tests: one curl per refusal path, and the pages to look at in the browser.
6. What goes to the checklist for later tasks.
Then ask "Approve?".

## After approval: build in small commits
- One step = one commit; the person reviews each diff before committing.
- Verify: `php -l`, `bash bin/check.sh` = 0 errors, the browser, curl attacks (no token, `field[]=x`, wrong role,
  signed out).
- Update `../team/CHECKLIST.md` (tick, findings, later items).
- Report: what changed (table), what was checked and how, what is still example data or open.
