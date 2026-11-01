-- Term marks for real (B-1, 2026-10-29).
SET NAMES utf8mb4;

-- Each school year's terms, with their dates (the Academic page's School Year section edits them: B-13).
-- A term can be typed in only once it has started; config/white_label/academic.php 'term_starts' = a new year's defaults.
CREATE TABLE school_terms (
    year SMALLINT NOT NULL,
    term TINYINT NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    PRIMARY KEY (year, term),
    CONSTRAINT fk_term_year FOREIGN KEY (year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT chk_term_dates CHECK (ends_on > starts_on)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- A mark is 0–100, or empty = AB (absent: left out of the total and average).
-- A subject with marks can't be deleted (RESTRICT): old reports keep showing it.
ALTER TABLE marks
    MODIFY mark TINYINT UNSIGNED NULL,
    ADD CONSTRAINT chk_mark_range CHECK (mark <= 100),
    DROP FOREIGN KEY fk_mark_subject;
ALTER TABLE marks
    ADD CONSTRAINT fk_mark_subject FOREIGN KEY (subject_id) REFERENCES stage_subjects (id) ON DELETE RESTRICT;

-- A subject taken out of a curriculum after it has marks is retired, not deleted: gone from new terms, still on old
-- reports under its old name (renaming one with marks = a new subject + the old one retired)
ALTER TABLE stage_subjects
    ADD COLUMN retired_on DATE NULL AFTER sort_order;

-- A class's term is visible to its students and parents once the class teacher publishes it (never unpublished)
CREATE TABLE term_publications (
    year SMALLINT NOT NULL,
    class_id INT NOT NULL,
    term TINYINT NOT NULL,
    published_by INT NULL,
    published_at DATETIME NOT NULL,
    PRIMARY KEY (year, class_id, term),
    CONSTRAINT fk_pub_year FOREIGN KEY (year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT fk_pub_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE RESTRICT,
    CONSTRAINT fk_pub_teacher FOREIGN KEY (published_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- This year's terms (the defaults)
INSERT INTO school_terms (year, term, starts_on, ends_on) VALUES
    (YEAR(CURDATE()), 1, CONCAT(YEAR(CURDATE()), '-01-02'), CONCAT(YEAR(CURDATE()), '-04-10')),
    (YEAR(CURDATE()), 2, CONCAT(YEAR(CURDATE()), '-05-02'), CONCAT(YEAR(CURDATE()), '-08-07')),
    (YEAR(CURDATE()), 3, CONCAT(YEAR(CURDATE()), '-09-01'), CONCAT(YEAR(CURDATE()), '-12-04'));
