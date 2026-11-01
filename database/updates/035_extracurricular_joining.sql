-- Joining a club / sport (B-2 step 2c, 2026-11-01).
-- extracurricular_members = each student's CURRENT row with a programme: a request on its way, a no, or a membership.
-- extracurricular_member_history = every membership that ended (removed, archived programme, left the school): when a
-- membership ends its row moves here, so joining again starts a new current row and nothing is ever overwritten.
SET NAMES utf8mb4;

ALTER TABLE extracurricular_members
    ADD COLUMN joined_on DATE NULL AFTER position,                              -- the day the teacher in charge accepted
    ADD COLUMN declined_by ENUM('parent', 'teacher') NULL AFTER parent_note,    -- who said no ('declined')
    ADD COLUMN teacher_note VARCHAR(255) NULL AFTER declined_by;               -- the teacher in charge's reason for no

UPDATE extracurricular_members SET joined_on = DATE(updated_at) WHERE status = 'member';
UPDATE extracurricular_members SET declined_by = 'parent' WHERE status = 'declined';

CREATE TABLE extracurricular_member_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    extracurricular_id INT NOT NULL,
    student_id INT NOT NULL,
    position VARCHAR(50) NULL,
    joined_on DATE NULL,
    left_on DATE NOT NULL,
    left_why ENUM('removed', 'archived', 'left_school') NOT NULL,
    ended_by INT NULL,                                                          -- who removed them / archived / switched them off
    INDEX idx_member_history_student (student_id),
    CONSTRAINT fk_emh_extracurricular FOREIGN KEY (extracurricular_id) REFERENCES extracurriculars (id) ON DELETE CASCADE,
    CONSTRAINT fk_emh_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_emh_ended_by FOREIGN KEY (ended_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Academic Watch (config/white_label/rules.php) is worked out from marks; management / admin can lift it for one
-- published term (the term it was worked out from): a newer published term decides again
CREATE TABLE academic_watch_lifts (
    student_id INT NOT NULL,
    year SMALLINT NOT NULL,
    term TINYINT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    lifted_by INT NULL,
    lifted_at DATETIME NOT NULL,
    PRIMARY KEY (student_id, year, term),
    CONSTRAINT fk_watch_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_watch_lifted_by FOREIGN KEY (lifted_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
