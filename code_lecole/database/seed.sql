INSERT INTO user_accounts (identifier, role, password_hash, activation_status, activated_at) VALUES
    ('david_silva_admin@lecole.edu', 'admin',      '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW()),
    ('sarah_vance@lecole.edu',       'management', '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW()),
    ('alex_benjamin@lecole.edu',     'teacher',    '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW()),
    ('suresh_perera@gmail.com',      'parent',     '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW()),
    ('STU-2026-0001',                'student',    '$2y$10$XPpdU8haziMIscD0MJsWjuShRbVgZ/XvYqhKIUVvbATLD6dDT4uDm', 'ACTIVE', NOW());

INSERT INTO user_accounts (identifier, role, password_hash, activation_status) VALUES
    ('nimal_fernando@lecole.edu',    'teacher',    NULL, 'PENDING');

-- Profiles (account ids 1–6 follow the order of the accounts above)
INSERT INTO profiles (account_id, first_name, last_name, personal_email, phone) VALUES
    (1, 'David',  'Silva',    'david.silva@example.com',   '+94 77 100 0001'),
    (2, 'Sarah',  'Vance',    'sarah.vance@example.com',   '+94 77 000 0001'),
    (3, 'Alex',   'Benjamin', 'alex.benjamin@example.com', '+94 70 456 7890'),
    (4, 'Suresh', 'Perera',   'suresh_perera@gmail.com',   '+94 77 345 6678'),
    (5, 'Nethmi', 'Perera',   NULL,                        NULL),
    (6, 'Nimal',  'Fernando', 'nimal.fernando@example.com', '+94 71 222 3344');

-- Nethmi (student) is Suresh's (parent) child, so her emails go to him
INSERT INTO student_parents (student_account_id, parent_account_id, is_primary) VALUES
    (5, 4, 1);
