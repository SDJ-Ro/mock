-- Demo data for B-11: the profile details the demo accounts had only as example text (2026-11-07).
SET NAMES utf8mb4;

UPDATE student_details SET house = 'Emerald House', prefect_role = 'Junior Prefect', nationality = 'Sri Lankan', religion = 'Buddhism',
    address_area = 'Colombo', address_district = 'Colombo', address_region = 'Western'
WHERE account_id = (SELECT id FROM user_accounts WHERE identifier = 'STU-2026-0001');

UPDATE staff_details SET office_phone = '+94 11 234 5678', employment_status = 'Permanent Full-Time', office_address = 'Senior Wing, Floor 2, Desk 14'
WHERE account_id = (SELECT id FROM user_accounts WHERE identifier = 'alex_benjamin@lecole.edu');

UPDATE staff_details SET office_phone = '+94 11 555 1200', office_address = 'Main Admin Building, Suite 102'
WHERE account_id = (SELECT id FROM user_accounts WHERE identifier = 'sarah_vance@lecole.edu');

UPDATE parent_details SET home_phone = '+94 11 456 7890'
WHERE account_id = (SELECT id FROM user_accounts WHERE identifier = 'suresh_perera@gmail.com');

INSERT INTO staff_qualifications (account_id, degree, institution, year_obtained, sort_order)
SELECT id, 'B.Sc. Education (Hons)', 'University of Colombo', 2010, 1 FROM user_accounts WHERE identifier = 'alex_benjamin@lecole.edu'
AND NOT EXISTS (SELECT 1 FROM staff_qualifications q WHERE q.account_id = user_accounts.id);
