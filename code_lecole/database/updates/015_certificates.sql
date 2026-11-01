-- Character certificates: a student asks (saying what it's for); management writes and issues it, or declines with a reason.
-- The text is saved when issued, so a printed certificate never changes afterwards.
CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    purpose VARCHAR(150) NOT NULL,           -- "University application"
    status ENUM('requested', 'issued', 'declined') NOT NULL DEFAULT 'requested',
    academic_text VARCHAR(1000) NULL,
    conduct_text VARCHAR(1000) NULL,
    activities_text VARCHAR(500) NULL,
    decline_reason VARCHAR(255) NULL,
    decided_by INT NULL,
    issued_on DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_certificate_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_certificate_decider FOREIGN KEY (decided_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- DEMO: Amaya's issued certificate, and Nethmi's request waiting for management
INSERT INTO certificates (id, student_id, purpose, status, academic_text, conduct_text, activities_text, decided_by, issued_on, created_at) VALUES
    (1, 9, 'Scholarship application', 'issued',
        'Amaya is a Grade 10 student in class 10-A. Amaya achieved an average of 81.8 (grade A) in Term 1 and was placed first in the class.',
        'Amaya is courteous, dependable and respectful towards classmates and staff, and carries every responsibility with maturity.',
        'Chess Club (Team captain); National Debate Champions (2024)', 2, CURDATE() - INTERVAL 10 DAY, NOW() - INTERVAL 14 DAY),
    (2, 5, 'University application', 'requested', NULL, NULL, NULL, NULL, NULL, NOW() - INTERVAL 2 DAY);
