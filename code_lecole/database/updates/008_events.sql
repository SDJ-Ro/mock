-- The calendar. An event is for the whole school, one grade, or one class (grade_id / class_id both NULL = whole school),
-- and for the roles in its audience. Who may change it = config/posting.php (same rules as notices).
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    details VARCHAR(1000) NULL,
    category VARCHAR(50) NOT NULL,
    audience SET('student', 'parent', 'teacher', 'management') NOT NULL,
    grade_id INT NULL,
    class_id INT NULL,
    event_date DATE NOT NULL,
    start_time TIME NULL,                    -- NULL = all day
    end_time TIME NULL,
    author_id INT NULL,
    author_role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_events_date (event_date),
    CONSTRAINT fk_event_grade FOREIGN KEY (grade_id) REFERENCES grades (id) ON DELETE CASCADE,
    CONSTRAINT fk_event_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE,
    CONSTRAINT fk_event_author FOREIGN KEY (author_id) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
