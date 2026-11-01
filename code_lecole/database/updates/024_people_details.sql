-- People (2026-10-26): the remaining personal details, so the People pages show and save real data.
-- The address parts are named generally (area / district / region): each country names them in
-- config/white_label/country.php ('address_parts', e.g. Educational Zone / District / Province).
SET NAMES utf8mb4;

ALTER TABLE student_details
    ADD COLUMN religion VARCHAR(50) NULL AFTER blood_group,
    ADD COLUMN nationality VARCHAR(50) NULL AFTER religion,
    ADD COLUMN address_area VARCHAR(100) NULL AFTER nationality,
    ADD COLUMN address_district VARCHAR(100) NULL AFTER address_area,
    ADD COLUMN address_region VARCHAR(100) NULL AFTER address_district;

ALTER TABLE parent_details
    ADD COLUMN passport VARCHAR(20) NULL AFTER employer,            -- when they have no national ID
    ADD COLUMN home_phone VARCHAR(30) NULL AFTER passport,
    ADD COLUMN office_phone VARCHAR(30) NULL AFTER home_phone,
    ADD COLUMN office_address VARCHAR(300) NULL AFTER office_phone;

ALTER TABLE staff_details
    ADD COLUMN office_address VARCHAR(300) NULL AFTER experience_years;   -- a teacher's office, management's office location

-- A teacher's qualifications: as many as they have, in the order typed
CREATE TABLE staff_qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    degree VARCHAR(150) NOT NULL,
    institution VARCHAR(150) NULL,
    year_obtained SMALLINT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_qualification_account FOREIGN KEY (account_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
