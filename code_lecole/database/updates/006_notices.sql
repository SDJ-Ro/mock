-- The notice board. Who can read a notice = its audience (a SET of roles) + optionally one class.
-- Who can change it = config/notices.php ('can_manage'). Deleting is real (the audit log keeps the history).
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    audience SET('student', 'parent', 'teacher', 'management') NOT NULL,   -- e.g. 'student,parent' (FIND_IN_SET reads it)
    class_id INT NULL,                       -- only this class's students / parents / teachers (NULL = everyone in the audience)
    author_id INT NULL,
    author_role VARCHAR(20) NOT NULL,        -- kept even if the author's account is removed (decides who may edit it)
    attachment_name VARCHAR(255) NULL,       -- the file's original name, shown to readers
    attachment_file VARCHAR(100) NULL,       -- the random name it's saved under in storage/uploads/notices/
    publish_at DATETIME NOT NULL,
    expires_at DATETIME NULL,
    pinned TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_notices_feed (publish_at, expires_at, pinned),
    CONSTRAINT fk_notice_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE,
    CONSTRAINT fk_notice_author FOREIGN KEY (author_id) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
