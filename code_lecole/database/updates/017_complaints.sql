-- Complaints and questions from parents to the school office, as a conversation (complaint_messages).
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    student_id INT NULL,                     -- the child it's about (optional)
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    INDEX idx_complaints_status (status, created_at),
    CONSTRAINT fk_complaint_parent FOREIGN KEY (parent_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_complaint_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE complaint_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    author_id INT NULL,
    message VARCHAR(2000) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cm_complaint FOREIGN KEY (complaint_id) REFERENCES complaints (id) ON DELETE CASCADE,
    CONSTRAINT fk_cm_author FOREIGN KEY (author_id) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- DEMO: Suresh asked about the exam syllabus (answered, in progress); Kamala reported the bus (resolved)
INSERT INTO complaints (id, parent_id, student_id, title, category, status, created_at, resolved_at) VALUES
    (1, 4, 5, 'Science term test syllabus', 'Academic', 'in_progress', NOW() - INTERVAL 4 DAY, NULL),
    (2, 7, 11, 'School bus arriving late', 'Transport', 'resolved', NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 9 DAY);

INSERT INTO complaint_messages (complaint_id, author_id, message, created_at) VALUES
    (1, 4, 'The test syllabus includes chapters that were not covered in class yet. Could the teacher clarify which chapters are tested?', NOW() - INTERVAL 4 DAY),
    (1, 2, 'Thank you — we have asked the Science department, and Ms. Gunasekara will share the chapter list in class this week.', NOW() - INTERVAL 3 DAY),
    (2, 7, 'The bus has been about 20 minutes late on rainy mornings, so Sasha nearly missed assembly twice last week.', NOW() - INTERVAL 12 DAY),
    (2, 2, 'The driver now leaves the depot 15 minutes earlier on rainy days. Please let us know if it happens again.', NOW() - INTERVAL 9 DAY);
