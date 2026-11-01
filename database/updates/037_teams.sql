-- Teams and positions (B-2 step 2d, 2026-11-01).
-- A programme's teams (an age group each, or none for a club without age groups); its members are placed in them by the
-- teacher in charge (or automatically, when exactly one team of their age group exists). A position (free text) belongs
-- to a team place and has a start date; a change of position, leaving a team, or a deleted team writes the old place to
-- team_member_history (the team's name kept), so a certificate can say "Captain, Under 15 Squad, Feb–Oct 2026".
-- Positions now live only here: the membership rows lose theirs; there is no "students per team" limit (owner).
SET NAMES utf8mb4;

ALTER TABLE extracurriculars DROP COLUMN team_limit;
ALTER TABLE extracurricular_members DROP COLUMN position;
ALTER TABLE extracurricular_member_history DROP COLUMN position;

CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    extracurricular_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    age_group VARCHAR(20) NULL,                -- config/extracurricular.php 'age_groups'; empty = any age (a club)
    cover_url VARCHAR(255) NULL,               -- an upload; empty = the programme's cover
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_team_name (extracurricular_id, name),
    CONSTRAINT fk_team_extracurricular FOREIGN KEY (extracurricular_id) REFERENCES extracurriculars (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE team_members (
    team_id INT NOT NULL,
    student_id INT NOT NULL,
    position VARCHAR(50) NULL,                 -- "Captain", "Goalkeeper"…; empty = a player
    since DATE NOT NULL,                       -- in this team (with this position) since
    PRIMARY KEY (team_id, student_id),
    INDEX idx_team_members_student (student_id),
    CONSTRAINT fk_tm_team FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE,
    CONSTRAINT fk_tm_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE team_member_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    extracurricular_id INT NOT NULL,
    team_name VARCHAR(100) NOT NULL,           -- kept: the team may be renamed or deleted later
    student_id INT NOT NULL,
    position VARCHAR(50) NULL,
    from_on DATE NOT NULL,
    to_on DATE NOT NULL,
    INDEX idx_team_history_student (student_id),
    CONSTRAINT fk_tmh_extracurricular FOREIGN KEY (extracurricular_id) REFERENCES extracurriculars (id) ON DELETE CASCADE,
    CONSTRAINT fk_tmh_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
