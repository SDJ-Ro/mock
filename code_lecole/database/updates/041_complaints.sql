-- One-way inquiries from a parent about the selected child, answered by management.
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    body VARCHAR(2000) NOT NULL,
    status ENUM('in_progress', 'resolved') NOT NULL DEFAULT 'in_progress',
    response VARCHAR(2000) NULL,
    resolved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    INDEX idx_complaints_parent (parent_id, student_id, created_at),
    INDEX idx_complaints_status (status, created_at),
    CONSTRAINT fk_complaint_parent FOREIGN KEY (parent_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_complaint_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_complaint_resolver FOREIGN KEY (resolved_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
