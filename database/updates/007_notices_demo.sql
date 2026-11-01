-- DEMO DATA for the notice board (dates are relative to the day the update runs)

INSERT INTO notices (id, title, body, category, audience, class_id, author_id, author_role, publish_at, expires_at, pinned) VALUES
    (1, 'Welcome to the new term',
        'Classes begin on Monday at 7:30 AM. Please make sure uniforms are complete and timetables are collected from class teachers.',
        'General', 'student,parent,teacher,management', NULL, 1, 'admin', NOW() - INTERVAL 3 DAY, NULL, 1),
    (2, 'Staff meeting on Friday',
        'All teaching staff are asked to attend a short meeting in the main hall after the last period on Friday.',
        'Administrative', 'teacher,management', NULL, 2, 'management', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 7 DAY, 0),
    (3, 'Grade 10-A science project',
        'Project groups for the science fair are now on the class board. First drafts are due in two weeks.',
        'Academic', 'student,parent', 9, 3, 'teacher', NOW() - INTERVAL 2 HOUR, NULL, 0),
    (4, 'Inter-house sports meet',
        'The inter-house sports meet will be held next month. Students who wish to take part should register with their house captains.',
        'Extracurricular', 'student,parent,teacher', NULL, 2, 'management', NOW() + INTERVAL 2 DAY, NULL, 0);
