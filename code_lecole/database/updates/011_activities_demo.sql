-- DEMO DATA for extracurricular activities

INSERT INTO activities (id, name, type, description, teacher_id, coach_name, schedule, venue, is_open) VALUES
    (1, 'Football', 'sport', 'League training and inter-house matches for all grades.', 3, 'Coach Dinesh Perera',
        'Mondays & Wednesdays, 3:30 – 5:30 PM', 'Main field', 1),
    (2, 'Chess Club', 'club', 'Weekly practice, puzzles and the inter-school chess league.', 12, NULL,
        'Tuesdays, 2:30 – 4:00 PM', 'Library', 1),
    (3, 'Drama Society', 'club', 'Plays, speech and the annual school concert.', NULL, NULL,
        'Fridays, 2:30 – 4:30 PM', 'Main hall', 1),
    (4, 'Swimming', 'sport', 'Squad training for the inter-school season (try-outs only).', 12, 'Ms. Nadeesha Fernando',
        'Thursdays, 3:00 – 5:00 PM', 'School pool', 0);

-- Amaya is in the Chess Club; Nethmi asked to join Football and her parent said yes (waiting for the teacher);
-- the school invited Sasha to the Drama Society (waiting for her parent, Kamala)
INSERT INTO activity_members (activity_id, student_id, status, requested_by, position, parent_id, parent_answered_at) VALUES
    (2, 9,  'member',  'student', 'Team captain', 7, NOW() - INTERVAL 20 DAY),
    (1, 5,  'teacher', 'student', NULL, 4, NOW() - INTERVAL 1 DAY),
    (3, 11, 'parent',  'school',  NULL, NULL, NULL);
