-- Teacher feedback about a student (praise or a concern), optionally for one subject. Parents and the student read it;
-- read_at = when a parent first saw it (the teacher sees that it was read).
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NULL,
    subject_id INT NULL,                     -- stage_subjects.id (NULL = general, e.g. from the class teacher)
    kind ENUM('praise', 'concern') NOT NULL,
    message VARCHAR(1000) NOT NULL,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feedback_student (student_id, created_at),
    CONSTRAINT fk_feedback_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE SET NULL,
    CONSTRAINT fk_feedback_subject FOREIGN KEY (subject_id) REFERENCES stage_subjects (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- DEMO: Alex about Nethmi (Mathematics, read) and Amaya (general, not read yet); Ishara about Nethmi (Science)
INSERT INTO feedback (id, student_id, teacher_id, subject_id, kind, message, read_at, created_at) VALUES
    (1, 5, 3, 8, 'praise', 'Nethmi has improved a lot in algebra this term and helps others during group work.', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 5 DAY),
    (2, 5, 12, 9, 'concern', 'Nethmi needs more care with practical write-ups — the method sections are often incomplete.', NULL, NOW() - INTERVAL 2 DAY),
    (3, 9, 3, NULL, 'praise', 'Amaya leads the class well and is always ready to help a classmate.', NULL, NOW() - INTERVAL 1 DAY);
