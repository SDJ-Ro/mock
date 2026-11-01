-- DEMO DATA for Academic (two curriculum stages and their subjects)

INSERT INTO stages (id, name, first_grade, last_grade, description) VALUES
    (1, 'Lower secondary', 6, 9,   'Core foundation curriculum.'),
    (2, 'Upper secondary', 10, 11, 'Exam preparation and specialisation.');

INSERT INTO stage_subjects (id, stage_id, name, sort_order) VALUES
    (1, 1, 'English', 1), (2, 1, 'Mathematics', 2), (3, 1, 'Science', 3), (4, 1, 'Humanities', 4), (5, 1, 'Sinhala / Tamil', 5), (6, 1, 'ICT', 6),
    (7, 2, 'English Language', 1), (8, 2, 'Mathematics', 2), (9, 2, 'Science', 3), (10, 2, 'History', 4), (11, 2, 'Business Studies', 5), (12, 2, 'ICT', 6);

-- Class teachers: Alex Benjamin → 10-A, Ishara Gunasekara → 9-B
UPDATE classes SET class_teacher_id = 3  WHERE id = 9;
UPDATE classes SET class_teacher_id = 12 WHERE id = 8;

-- Subject teachers
INSERT INTO class_subject_teachers (class_id, subject_id, teacher_id) VALUES
    (9, 8, 3),  (9, 9, 12),  (10, 8, 3),    -- 10-A Mathematics + Science, 10-B Mathematics
    (8, 2, 3),  (8, 3, 12),  (7, 3, 12);    -- 9-B Mathematics + Science, 9-A Science
