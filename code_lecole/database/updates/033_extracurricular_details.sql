-- What a programme's pages show and who may join it (B-2 step 2b, 2026-11-01). Its teacher in charge is in
-- teachers_in_charge (per year, update 028); members are B-2 step 2c.
SET NAMES utf8mb4;

ALTER TABLE extracurriculars
    ADD COLUMN category VARCHAR(50) NULL AFTER type,                          -- config/extracurricular.php 'categories'
    ADD COLUMN cover_url VARCHAR(255) NULL AFTER description,                 -- an upload; empty = the default cover
    ADD COLUMN coach_specialty VARCHAR(150) NULL AFTER coach_name,            -- the coach is an outside person, not an account
    ADD COLUMN coach_email VARCHAR(191) NULL AFTER coach_specialty,
    ADD COLUMN coach_phone VARCHAR(30) NULL AFTER coach_email,
    ADD COLUMN age_group_limit SMALLINT UNSIGNED NULL AFTER venue,            -- students per age group; empty = rules.php's default
    ADD COLUMN team_limit TINYINT UNSIGNED NULL AFTER age_group_limit,        -- students per team; empty = no limit
    ADD COLUMN uses_age_groups TINYINT(1) NOT NULL DEFAULT 1 AFTER team_limit, -- a sport always does; a club may not
    ADD COLUMN open_to ENUM('everyone', 'boys', 'girls') NOT NULL DEFAULT 'everyone' AFTER uses_age_groups,
    ADD COLUMN lowest_grade TINYINT UNSIGNED NULL AFTER open_to,              -- the lowest grade number that may join; empty = any
    ADD COLUMN archived_at DATETIME NULL AFTER reviewed_at,                   -- archived: hidden from lists and joining, history kept
    ADD COLUMN archived_by INT NULL AFTER archived_at,
    ADD CONSTRAINT fk_extracurricular_archived_by FOREIGN KEY (archived_by) REFERENCES user_accounts (id) ON DELETE SET NULL;
