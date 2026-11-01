# School portal

Vanilla PHP 8 (no framework). Five portals (student, parent, teacher, management, admin) and the public landing page.
How the code fits together: `../team/HOW-IT-WORKS.md`. What is built and what is still example data: `../team/CHECKLIST.md`.

**New to the team? Start at [`team/ONBOARDING.md`](../team/ONBOARDING.md).** How we use Git: `../team/GIT.md`.
Every command below runs inside this folder (`code_lecole/`).

## Run it

```bash
cp .env.example .env              # then fill in the email settings (the rest works as it is)
composer install                  # Dompdf, for the certificate PDF
docker compose up -d              # MySQL on port 3308 (+ phpMyAdmin on http://localhost:8086)
php database/update.php           # the database updates that haven't run yet
php -d upload_max_filesize=6M -d post_max_size=64M -S localhost:8080 -t public   # the site: http://localhost:8080
```

PHP's own upload limits are 2 MB a file and 8 MB a form unless raised (the `-d` settings above): photos may be up
to 5 MB each, and an achievement's gallery up to 12 photos sent together (`config/extracurricular.php`). On a real
server, set the same two values in its `php.ini`.

The first `docker compose up` loads `database/schema.sql` and `database/seed.sql`; `update.php` then adds the
changes in `database/updates/`, with the demo data (every active test account's password: `Password@123`).

## Check the rules

```bash
bash bin/check.sh                 # 0 errors before every hand-in
```

## Another school

Change the files in `config/white_label/` only: name, logo and contacts, the words (Grade, Class, Term…),
which modules are on, the date format.
