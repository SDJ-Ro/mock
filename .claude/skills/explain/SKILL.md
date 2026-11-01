---
name: explain
description: Explain a file, function or page of this project so its owner can defend it in the viva and change it in the code check. Use when someone asks "explain", "how does this work", "what does this file do", or "/explain <file>".
argument-hint: <file, function or page>
---

# Explain: $ARGUMENTS

The person asking must be able to explain this code themselves, and change it live.

1. Read the file (and the files it calls or is called by) before explaining.
2. Start with one line: what this file is for.
3. Show the path of a request, as a short chain, for example:
   `routes/web.php` → `NoticeController::save()` → `NoticeModel::save($values)` → redirect → `app/Views/teacher/notice.php`
   Say what is passed at each arrow.
4. Then go through the code in small blocks: show the block, then one short line per important line.
   No long paragraphs.
5. Point out the rules it follows (CSRF, `e()`, prepared statements, `$variant`, `can()`, `rule()`…) and why each matters.
6. End with:
   - 3 likely viva questions about this code, with a one-line answer each;
   - 2 likely code-check changes ("add a field", "limit X to 30") and exactly which files and lines would change.

Do not change any file while explaining.
