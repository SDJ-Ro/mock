-- DEMO: four years of school life for the demo students, so the record book, positions and awards have real data.
--   Nethmi (5) and Amaya (9): 6-A → 7-A → 8-A → 9-A, now 10-A · Dinuk (10): 6-B → 7-B → 8-B, now 9-B ·
--   Sasha (11): 6-A last year, now 7-A
-- Every past term is published; this year terms 1–2 are published and term 3 is being typed (10-A has started).
-- The marks come from a fixed formula (45–96), one AB (Dinuk, 7-B, term 2, Humanities).
SET NAMES utf8mb4;

INSERT INTO school_years (year) VALUES
    (YEAR(CURDATE()) - 4), (YEAR(CURDATE()) - 3), (YEAR(CURDATE()) - 2), (YEAR(CURDATE()) - 1);

-- Each past year's terms (this year's are in 030)
INSERT INTO school_terms (year, term, starts_on, ends_on)
    SELECT y.year, t.term, CONCAT(y.year, t.starts), CONCAT(y.year, t.ends)
    FROM (SELECT YEAR(CURDATE()) - 4 AS year UNION SELECT YEAR(CURDATE()) - 3 UNION SELECT YEAR(CURDATE()) - 2 UNION SELECT YEAR(CURDATE()) - 1) y
    CROSS JOIN (SELECT 1 AS term, '-01-02' AS starts, '-04-10' AS ends UNION SELECT 2, '-05-02', '-08-07' UNION SELECT 3, '-09-01', '-12-04') t;

-- Where each student was (classes: 1 = 6-A, 2 = 6-B, 3 = 7-A, 4 = 7-B, 5 = 8-A, 6 = 8-B, 7 = 9-A)
INSERT INTO class_students (year, student_id, class_id) VALUES
    (YEAR(CURDATE()) - 4, 5, 1), (YEAR(CURDATE()) - 3, 5, 3), (YEAR(CURDATE()) - 2, 5, 5), (YEAR(CURDATE()) - 1, 5, 7),
    (YEAR(CURDATE()) - 4, 9, 1), (YEAR(CURDATE()) - 3, 9, 3), (YEAR(CURDATE()) - 2, 9, 5), (YEAR(CURDATE()) - 1, 9, 7),
    (YEAR(CURDATE()) - 3, 10, 2), (YEAR(CURDATE()) - 2, 10, 4), (YEAR(CURDATE()) - 1, 10, 6),
    (YEAR(CURDATE()) - 1, 11, 1);

-- Their class teachers then (3 Alex, 6 Nimal, 12 Ishara, 13 Priya), and 7-A's this year
INSERT INTO class_teachers (year, class_id, teacher_id) VALUES
    (YEAR(CURDATE()) - 4, 1, 13),
    (YEAR(CURDATE()) - 3, 3, 6), (YEAR(CURDATE()) - 3, 2, 3),
    (YEAR(CURDATE()) - 2, 5, 12), (YEAR(CURDATE()) - 2, 4, 13),
    (YEAR(CURDATE()) - 1, 7, 3), (YEAR(CURDATE()) - 1, 6, 6), (YEAR(CURDATE()) - 1, 1, 13),
    (YEAR(CURDATE()), 3, 13);

-- Marks: every student × their grade's subjects × every term up to this year's term 2, plus this year's term 3 for 10-A
-- (the subjects come from the stage that covers the class's grade)
INSERT IGNORE INTO marks (student_id, subject_id, year, term, mark, entered_by)
    SELECT cs.student_id, ss.id, cs.year, t.term,
           IF(cs.student_id = 10 AND cs.year = YEAR(CURDATE()) - 2 AND t.term = 2 AND ss.id = 4, NULL,
              45 + MOD(cs.student_id * 37 + ss.id * 13 + cs.year * 7 + t.term * 11, 52)),
           ct.teacher_id
    FROM class_students cs
    JOIN classes c ON c.id = cs.class_id
    JOIN grades g ON g.id = c.grade_id
    JOIN stages s ON g.sort_order BETWEEN s.first_grade AND s.last_grade
    JOIN stage_subjects ss ON ss.stage_id = s.id
    JOIN school_terms t ON t.year = cs.year
    LEFT JOIN class_teachers ct ON ct.year = cs.year AND ct.class_id = cs.class_id
    WHERE cs.year < YEAR(CURDATE()) OR t.term <= 2 OR (t.term = 3 AND cs.class_id = 9 AND cs.student_id = 5);

-- The class teacher's comment for every published term
INSERT IGNORE INTO term_comments (student_id, year, term, comment, written_by)
    SELECT cs.student_id, cs.year, t.term,
           ELT(1 + MOD(cs.student_id + t.term + cs.year, 3),
               'A solid term with steady effort in every subject. Keep revising regularly.',
               'Good progress this term. More practice in the weaker subjects will lift the average further.',
               'Excellent work and a positive attitude in class. Keep up the high standard.'),
           ct.teacher_id
    FROM class_students cs
    JOIN school_terms t ON t.year = cs.year
    LEFT JOIN class_teachers ct ON ct.year = cs.year AND ct.class_id = cs.class_id
    WHERE cs.year < YEAR(CURDATE()) OR t.term <= 2;

-- Published: every past term, and this year's terms 1–2 (a week after each term ended)
INSERT INTO term_publications (year, class_id, term, published_by, published_at)
    SELECT DISTINCT cs.year, cs.class_id, t.term, ct.teacher_id, t.ends_on + INTERVAL 7 DAY
    FROM class_students cs
    JOIN school_terms t ON t.year = cs.year
    LEFT JOIN class_teachers ct ON ct.year = cs.year AND ct.class_id = cs.class_id
    WHERE cs.year < YEAR(CURDATE()) OR t.term <= 2;
