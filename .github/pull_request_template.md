## What this does
<!-- One or two lines. Which task: B-3, B-5… or the checklist item. -->

Task:

## Changes
<!-- One line per file or group of files: what changed and why. -->

-

## How I tested it
- [ ] `bash bin/check.sh` ends with **0 errors**
- [ ] `php -l` on every PHP file I changed
- [ ] Opened every page I touched in the browser (each role that sees it) — it looks the same as before
- [ ] curl attacks on every new POST: no CSRF token · `field[]=x` · the wrong role · signed out → all refused, data unchanged
- [ ] A new table change is a new file in `database/updates/` and `php database/update.php` runs it on a fresh database

## Rules (CLAUDE.md)
- [ ] SQL only in `app/Models/`, prepared statements · several changes together in `transaction()`
- [ ] Form values only through `postText()` / `postPassword()` / `postList()`
- [ ] `e()` on every value a view prints · no inline `style=""` · no `!important`
- [ ] Who may do what from `config/permissions.php` · numbers (limits, marks, ages) in `config/white_label/rules.php` or the feature's config
- [ ] School words via `word()` / `plural()` · no school name typed anywhere
- [ ] The example data this replaces (`app/Mock/…`) is deleted · no dead code left behind
- [ ] `team/CHECKLIST.md` ticked and updated

## I can explain it
- [ ] I can explain every line of this pull request myself, and change it live (code check)

## Still open / not checked
<!-- Be honest: what is still example data, what you could not test. -->
