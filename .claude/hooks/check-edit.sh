#!/bin/bash
# Runs after every Edit/Write by Claude. Checks the file just changed against the CLAUDE.md rules.
# PHP that doesn't parse: exit 2, the error (on stderr) goes to Claude, which must fix it.
# Rule breaks: printed as a note for Claude (JSON on stdout), not blocking.

file=$(jq -r '.tool_input.file_path // empty')

# Only files inside this project
case "$file" in
    "$CLAUDE_PROJECT_DIR"/*) ;;
    *) exit 0 ;;
esac
[ -f "$file" ] || exit 0

broken=""
problems=""

# 1. PHP must parse
if [[ "$file" == *.php ]]; then
    lint=$(php -l "$file" 2>&1) || broken="php -l failed for $file:\n$lint"
fi

# 2. No !important (CSS, PHP, JS; emails need it, so they are skipped)
if [[ "$file" =~ \.(css|php|js)$ && "$file" != */Views/emails/* ]]; then
    found=$(grep -n '!important' "$file")
    [ -n "$found" ] && problems+="!important is not allowed:\n$found\n"
fi

# 3. No inline style="" in views (emails need inline styles, so they are skipped)
if [[ "$file" == *.php && "$file" != */Views/emails/* ]]; then
    found=$(grep -n 'style="' "$file")
    [ -n "$found" ] && problems+="Inline style=\"\" is not allowed (use a class in the component's CSS):\n$found\n"
fi

# 4. SQL only in Models (database updates are SQL files, not PHP)
if [[ "$file" == *.php && "$file" != */app/Models/* && "$file" != */database/* ]]; then
    found=$(grep -nE '(SELECT .+ FROM|INSERT INTO|DELETE FROM|UPDATE [a-z_]+ SET)' "$file")
    [ -n "$found" ] && problems+="SQL belongs in app/Models only:\n$found\n"
fi

# 5. Form values only through postText() / postPassword() / postList()
if [[ "$file" == *.php && "$file" != */core/Controller.php ]]; then
    found=$(grep -n '\$_POST' "$file")
    [ -n "$found" ] && problems+="Read form values with postText() / postPassword() / postList(), never \$_POST:\n$found\n"
fi

if [ -n "$broken" ]; then
    echo -e "$broken" >&2
    exit 2
fi

if [ -n "$problems" ]; then
    note=$(echo -e "Rule check for $file:\n${problems}Fix them before committing (bash bin/check.sh must end with 0 errors).")
    jq -n --arg note "$note" '{hookSpecificOutput: {hookEventName: "PostToolUse", additionalContext: $note}}'
fi
exit 0
