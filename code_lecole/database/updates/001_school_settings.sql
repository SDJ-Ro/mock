-- What the admin changed on Settings (school details + words). Overrides config/white_label/*.php.
-- Only CHANGED values are stored; deleting a row puts that value back to the config default.
CREATE TABLE school_settings (
    setting_key VARCHAR(100) PRIMARY KEY,   -- e.g. 'school.name', 'wording.teacher'
    value TEXT NOT NULL,                    -- lists (the address) are stored one item per line
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_settings_user FOREIGN KEY (updated_by)
        REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
