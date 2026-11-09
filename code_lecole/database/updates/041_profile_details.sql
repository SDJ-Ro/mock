-- B-11 Profile details (2026-11-07): the details My Profile showed as example values and nobody stored.
-- A student's house and prefect role; a staff member's office phone (a teacher's extension, management's office line)
-- and employment status. Everything else the page showed already had a column (religion, nationality, address parts,
-- office address, home phone, qualifications).
SET NAMES utf8mb4;

ALTER TABLE student_details
    ADD COLUMN house VARCHAR(50) NULL AFTER address_region,
    ADD COLUMN prefect_role VARCHAR(100) NULL AFTER house;

ALTER TABLE staff_details
    ADD COLUMN office_phone VARCHAR(30) NULL AFTER office_address,
    ADD COLUMN employment_status VARCHAR(50) NULL AFTER office_phone;
