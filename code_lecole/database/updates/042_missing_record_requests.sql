-- Achievements: the teacher's Record pop-up and the student's missing-record requests (B-3, 2026-11-05).
--   achievements             + issued_by ("Verified / Issued By") and the proof file (optional when a teacher records)
--   missing_record_requests  a student's request for a record that is missing; it goes to the student's CURRENT class
--                            teacher, looked up when it is read (never stored here), who approves it (it becomes an
--                            achievement: achievement_id) or rejects it with a reason
-- A proof file is private: saved in storage/uploads/ (proof_path, relative to it), opened only through a checked route.
-- proof_name is the file's own name, shown on screen ("No file attached" when there is none).
SET NAMES utf8mb4;

ALTER TABLE achievements
    ADD COLUMN issued_by VARCHAR(150) NULL AFTER organiser,      -- "Mr. Silva, Club Patron"
    ADD COLUMN proof_path VARCHAR(255) NULL AFTER photo_alt,     -- empty = no file attached
    ADD COLUMN proof_name VARCHAR(255) NULL AFTER proof_path;

CREATE TABLE missing_record_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    record_type VARCHAR(50) NOT NULL,              -- config/certificates.php 'record_types'
    academic_year VARCHAR(40) NOT NULL,            -- "Academic Year / Grade" as the student typed it: "2025", "Grade 12"
    title VARCHAR(150) NOT NULL,
    details VARCHAR(1000) NOT NULL,
    proof_path VARCHAR(255) NOT NULL,              -- the evidence is compulsory
    proof_name VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reason VARCHAR(300) NULL,                      -- why it was rejected
    answered_by INT NULL,                          -- the class teacher who approved / rejected it
    answered_at DATETIME NULL,
    achievement_id INT NULL,                       -- the achievement an approval made
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_missing_record_student (student_id, status),
    CONSTRAINT fk_mrr_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_mrr_answered_by FOREIGN KEY (answered_by) REFERENCES user_accounts (id) ON DELETE SET NULL,
    CONSTRAINT fk_mrr_achievement FOREIGN KEY (achievement_id) REFERENCES achievements (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
