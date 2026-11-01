-- DEMO: just enough for each joining screen. Dinuk (10) is a Football member and waits for Ishara (Swimming's teacher
-- in charge) after his parent said yes; Nethmi already waits for Alex (Football); Sasha already waits for her parent (Drama).
SET NAMES utf8mb4;

INSERT INTO extracurricular_members (extracurricular_id, student_id, status, requested_by, joined_on) VALUES
    (1, 10, 'member', 'student', CONCAT(YEAR(CURDATE()), '-02-10'));

INSERT INTO extracurricular_members (extracurricular_id, student_id, status, requested_by, parent_id, parent_answered_at) VALUES
    (4, 10, 'teacher', 'student', 8, NOW() - INTERVAL 2 DAY);
