-- DEMO DATA for the calendar (dates are relative to the day the update runs)

INSERT INTO events (id, title, details, category, audience, grade_id, class_id, event_date, start_time, end_time, author_id, author_role) VALUES
    (1, 'Term test week begins', 'Timetables are on the notice board.', 'Exam', 'student,parent,teacher,management',
        NULL, NULL, CURDATE() + INTERVAL 7 DAY, NULL, NULL, 1, 'admin'),
    (2, 'Grade 10 parent meeting', 'In the main hall. Please bring the progress report.', 'Meeting', 'parent,teacher',
        5, NULL, CURDATE() + INTERVAL 3 DAY, '15:00', '16:30', 2, 'management'),
    (3, '10-A science practical', 'Bring lab coats.', 'Academic', 'student',
        NULL, 9, CURDATE() + INTERVAL 2 DAY, '09:00', '10:30', 3, 'teacher'),
    (4, 'Staff development day', NULL, 'Meeting', 'teacher,management',
        NULL, NULL, CURDATE() + INTERVAL 10 DAY, '08:00', '13:00', 2, 'management');
