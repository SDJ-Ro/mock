-- Who has opened which notice — for the "Unread Notices" card on the student and parent dashboards.
-- A row is added the first time someone opens a notice; it never changes after that.
CREATE TABLE notice_reads (
    notice_id INT NOT NULL,
    account_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notice_id, account_id),
    CONSTRAINT fk_read_notice FOREIGN KEY (notice_id) REFERENCES notices (id) ON DELETE CASCADE,
    CONSTRAINT fk_read_account FOREIGN KEY (account_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
