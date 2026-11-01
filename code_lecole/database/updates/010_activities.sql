-- Extracurricular activities: sports and clubs (one table for both), each with a teacher in charge.
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('sport', 'club') NOT NULL,
    description VARCHAR(1000) NULL,
    teacher_id INT NULL,                     -- the teacher in charge (TIC)
    coach_name VARCHAR(150) NULL,            -- an outside coach, if any (not an account)
    schedule VARCHAR(150) NULL,              -- "Mondays & Wednesdays, 3:30–5:30 PM"
    venue VARCHAR(150) NULL,
    is_open TINYINT(1) NOT NULL DEFAULT 1,   -- students may ask to join
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Who is in (or asking to join) an activity. The path to 'member':
--   a student asks      → 'parent' (their parent says yes) → 'teacher' (the teacher in charge says yes) → 'member'
--   the school adds one → 'parent' (their parent says yes) → 'member'
-- A parent saying no → 'declined' (the student may ask again later).
CREATE TABLE activity_members (
    activity_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('parent', 'teacher', 'member', 'declined') NOT NULL,
    requested_by ENUM('student', 'school') NOT NULL,
    position VARCHAR(50) NULL,               -- "Captain", "Goalkeeper"…
    parent_id INT NULL,                      -- the parent who answered
    parent_answered_at DATETIME NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_id, student_id),
    CONSTRAINT fk_am_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE,
    CONSTRAINT fk_am_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_am_parent FOREIGN KEY (parent_id) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
