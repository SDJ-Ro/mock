-- Verifications: some new things wait for an admin's OK before anyone else sees / uses them (rules: config/reviews.php).
-- The same 4 columns on each table that can be reviewed. Everything that already exists counts as approved.
ALTER TABLE user_accounts
    ADD COLUMN review ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved',
    ADD COLUMN review_note VARCHAR(255) NULL,
    ADD COLUMN reviewed_by INT NULL,
    ADD COLUMN reviewed_at DATETIME NULL;

ALTER TABLE activities
    ADD COLUMN review ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved',
    ADD COLUMN review_note VARCHAR(255) NULL,
    ADD COLUMN reviewed_by INT NULL,
    ADD COLUMN reviewed_at DATETIME NULL,
    ADD COLUMN created_by INT NULL;

ALTER TABLE notices
    ADD COLUMN review ENUM('approved', 'pending', 'rejected') NOT NULL DEFAULT 'approved',
    ADD COLUMN review_note VARCHAR(255) NULL,
    ADD COLUMN reviewed_by INT NULL,
    ADD COLUMN reviewed_at DATETIME NULL;

-- One of each waiting, for the demo: a teacher's notice, a club management added, a teacher management added
INSERT INTO notices (title, body, category, audience, class_id, author_id, author_role, publish_at, review) VALUES
    ('9-B reading list', 'The new reading list for this term is in the library. Please borrow at least two books.',
        'Academic', 'student,parent', 8, 12, 'teacher', NOW(), 'pending');

INSERT INTO activities (name, type, description, teacher_id, schedule, venue, is_open, review, created_by) VALUES
    ('Photography Club', 'club', 'Digital and film photography, with a term-end exhibition.', 3, 'Wednesdays, 2:30 – 4:00 PM', 'Art room', 1, 'pending', 2);

INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, review) VALUES
    (13, 'priya_mendis@lecole.edu', 'teacher', NULL, 'PENDING', 'pending');

INSERT INTO profiles (account_id, code, first_name, last_name, date_of_birth, gender, national_id, personal_email, phone, address, emergency_name, emergency_phone) VALUES
    (13, 'TEA-2026-0013', 'Priya', 'Mendis', '1991-04-18', 'Female', '199110912345', 'priya.mendis@example.com', '+94 77 456 1234',
        '8 Temple Road, Galle', 'Ravi Mendis', '+94 77 456 9999');

INSERT INTO staff_details (account_id, job_title, subjects, join_date, experience_years) VALUES
    (13, 'Art teacher', 'Art, Design', CURDATE(), 5);
