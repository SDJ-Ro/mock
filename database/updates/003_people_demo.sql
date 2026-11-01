-- DEMO DATA for People + classes (test data only). Password for every ACTIVE account: Password@123

-- Grades 6–11, two classes each
INSERT INTO grades (id, name, sort_order) VALUES
    (1, 'Grade 6', 6), (2, 'Grade 7', 7), (3, 'Grade 8', 8), (4, 'Grade 9', 9), (5, 'Grade 10', 10), (6, 'Grade 11', 11);

INSERT INTO classes (grade_id, name, capacity) VALUES
    (1, '6-A', 40), (1, '6-B', 40), (2, '7-A', 40), (2, '7-B', 40), (3, '8-A', 40), (3, '8-B', 40),
    (4, '9-A', 40), (4, '9-B', 40), (5, '10-A', 40), (5, '10-B', 40), (6, '11-A', 40), (6, '11-B', 40);

-- More demo people (accounts 7–12)
INSERT INTO user_accounts (id, identifier, role, password_hash, activation_status, activated_at) VALUES
    (7,  'kamala.wijeratne@gmail.com',   'parent',  '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW()),
    (8,  'rohan.silva@gmail.com',        'parent',  NULL, 'PENDING', NULL),
    (9,  'STU-2026-0009',                'student', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW()),
    (10, 'STU-2026-0010',                'student', NULL, 'PENDING', NULL),
    (11, 'STU-2026-0011',                'student', NULL, 'PENDING', NULL),
    (12, 'ishara_gunasekara@lecole.edu', 'teacher', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW());

INSERT INTO profiles (account_id, first_name, last_name, personal_email, phone) VALUES
    (7,  'Kamala', 'Wijeratne',  'kamala.wijeratne@gmail.com', '+94 77 222 3344'),
    (8,  'Rohan',  'Silva',      'rohan.silva@gmail.com',      '+94 71 555 6677'),
    (9,  'Amaya',  'Wijeratne',  NULL, NULL),
    (10, 'Dinuk',  'Silva',      NULL, NULL),
    (11, 'Sasha',  'Wijeratne',  NULL, NULL),
    (12, 'Ishara', 'Gunasekara', 'ishara.g@example.com',       '+94 76 123 9876');

-- Codes, birthdays, IDs and addresses for everyone
UPDATE profiles SET code = 'ADM-2026-0001', date_of_birth = '1985-03-12', gender = 'Male',   national_id = '198507212345', address = '12 Flower Road, Colombo 07', emergency_name = 'Anne Silva',      emergency_phone = '+94 77 100 0002' WHERE account_id = 1;
UPDATE profiles SET code = 'MAN-2026-0002', date_of_birth = '1982-07-01', gender = 'Female', national_id = '198268312345', address = '5 Lake Drive, Colombo 05',   emergency_name = 'Mark Vance',      emergency_phone = '+94 77 000 0009' WHERE account_id = 2;
UPDATE profiles SET code = 'TEA-2026-0003', date_of_birth = '1988-06-20', gender = 'Male',   national_id = '198817212345', address = '44 Hill Street, Kandy',      emergency_name = 'Helen Benjamin',  emergency_phone = '+94 70 456 7899' WHERE account_id = 3;
UPDATE profiles SET code = 'PAR-2026-0004', date_of_birth = '1979-11-02', gender = 'Male',   national_id = '197930612345', address = '45 Galle Road, Colombo 06',  emergency_name = 'Nirmala Perera',  emergency_phone = '+94 77 345 0000' WHERE account_id = 4;
UPDATE profiles SET code = 'STU-2026-0001', date_of_birth = '2010-04-15', gender = 'Female',                               address = '45 Galle Road, Colombo 06'                                                       WHERE account_id = 5;
UPDATE profiles SET code = 'TEA-2026-0006', date_of_birth = '1990-01-25', gender = 'Male',   national_id = '199002512345', address = '8 Temple Lane, Galle',       emergency_name = 'Ruwan Fernando',  emergency_phone = '+94 71 222 0000' WHERE account_id = 6;
UPDATE profiles SET code = 'PAR-2026-0007', date_of_birth = '1983-09-09', gender = 'Female', national_id = '198375312345', address = '3 Palm Avenue, Colombo 04', emergency_name = 'Sunil Wijeratne', emergency_phone = '+94 77 999 1111' WHERE account_id = 7;
UPDATE profiles SET code = 'PAR-2026-0008', date_of_birth = '1980-02-14', gender = 'Male',   national_id = '198004512345', address = '19 Station Road, Negombo',   emergency_name = 'Dilani Silva',    emergency_phone = '+94 71 888 2222' WHERE account_id = 8;
UPDATE profiles SET code = 'STU-2026-0009', date_of_birth = '2010-08-30', gender = 'Female', address = '3 Palm Avenue, Colombo 04' WHERE account_id = 9;
UPDATE profiles SET code = 'STU-2026-0010', date_of_birth = '2011-12-05', gender = 'Male',   address = '19 Station Road, Negombo'  WHERE account_id = 10;
UPDATE profiles SET code = 'STU-2026-0011', date_of_birth = '2013-03-21', gender = 'Female', address = '3 Palm Avenue, Colombo 04' WHERE account_id = 11;
UPDATE profiles SET code = 'TEA-2026-0012', date_of_birth = '1991-05-17', gender = 'Female', national_id = '199163712345', address = '27 Rose Garden, Colombo 03', emergency_name = 'Kumar Gunasekara', emergency_phone = '+94 76 000 3333' WHERE account_id = 12;

INSERT INTO student_details (account_id, class_id, birth_certificate, admission_date, blood_group) VALUES
    (5,  (SELECT id FROM classes WHERE name = '10-A'), 'BC-2010-04411', '2021-01-10', 'O+'),
    (9,  (SELECT id FROM classes WHERE name = '10-A'), 'BC-2010-08390', '2021-01-10', 'A+'),
    (10, (SELECT id FROM classes WHERE name = '9-B'),  'BC-2011-12005', '2022-01-12', NULL),
    (11, (SELECT id FROM classes WHERE name = '7-A'),  'BC-2013-03210', '2024-01-08', 'B+');

INSERT INTO parent_details (account_id, relationship, occupation, employer) VALUES
    (4, 'Father', 'Engineer',   'Lanka Power Ltd'),
    (7, 'Mother', 'Accountant', 'City Bank'),
    (8, 'Father', 'Doctor',     'General Hospital');

INSERT INTO student_parents (student_account_id, parent_account_id, is_primary) VALUES
    (9, 7, 1), (10, 8, 1), (11, 7, 1);

INSERT INTO staff_details (account_id, job_title, subjects, join_date, experience_years) VALUES
    (1,  'System Administrator', NULL,                '2019-01-07', 7),
    (2,  'Academic Principal',   NULL,                '2020-01-15', 15),
    (3,  'Science teacher',      'Science, Physics',  '2021-03-01', 6),
    (6,  'Mathematics teacher',  'Mathematics',       '2026-09-01', 3),
    (12, 'Mathematics teacher',  'Mathematics, ICT',  '2022-05-02', 4);
