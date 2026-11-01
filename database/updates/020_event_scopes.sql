-- The calendar: one event can be for several groups at once — the whole school, some grades, some classes, or some
-- clubs / sports — and has one of 3 categories.

-- Who each event is for: one row per group. scope_id is the grade / class / activity id (NULL for the whole school).
CREATE TABLE event_scopes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    scope_type ENUM('school', 'grade', 'class', 'activity') NOT NULL,
    scope_id INT NULL,
    INDEX idx_scope_lookup (scope_type, scope_id),
    CONSTRAINT fk_scope_event FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Move each event's single grade / class into the new table
INSERT INTO event_scopes (event_id, scope_type, scope_id)
SELECT id,
       CASE WHEN class_id IS NOT NULL THEN 'class' WHEN grade_id IS NOT NULL THEN 'grade' ELSE 'school' END,
       COALESCE(class_id, grade_id)
FROM events;

ALTER TABLE events
    DROP FOREIGN KEY fk_event_grade,
    DROP FOREIGN KEY fk_event_class,
    DROP COLUMN grade_id,
    DROP COLUMN class_id;

-- The 3 categories: General (school-wide), Academic, Extracurricular
UPDATE events SET category = 'Academic' WHERE category = 'Exam';
UPDATE events SET category = 'General' WHERE category IN ('Meeting', 'Holiday');

-- A demo event for a club, so the Extracurricular part of the calendar has something to show
INSERT INTO events (title, details, category, audience, event_date, start_time, end_time, author_id, author_role) VALUES
    ('Chess Club friendly match', 'Library hall. Bring your club badge.', 'Extracurricular', 'student,parent',
        CURDATE() + INTERVAL 4 DAY, '15:30', '17:00', 1, 'admin');
INSERT INTO event_scopes (event_id, scope_type, scope_id) VALUES (LAST_INSERT_ID(), 'activity', 2);
