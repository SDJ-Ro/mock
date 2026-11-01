-- Term marks: one mark (out of 100) per student, subject, year and term — and the class teacher's comment per term.
CREATE TABLE marks (
    student_id INT NOT NULL,
    subject_id INT NOT NULL,                 -- stage_subjects.id (removing a subject from a stage removes its marks)
    year SMALLINT NOT NULL,
    term TINYINT NOT NULL,
    mark TINYINT UNSIGNED NOT NULL,
    entered_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, subject_id, year, term),
    INDEX idx_marks_term (year, term, subject_id),
    CONSTRAINT fk_mark_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_mark_subject FOREIGN KEY (subject_id) REFERENCES stage_subjects (id) ON DELETE CASCADE,
    CONSTRAINT fk_mark_teacher FOREIGN KEY (entered_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE term_comments (
    student_id INT NOT NULL,
    year SMALLINT NOT NULL,
    term TINYINT NOT NULL,
    comment VARCHAR(500) NOT NULL,
    written_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, year, term),
    CONSTRAINT fk_comment_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_teacher FOREIGN KEY (written_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- DEMO: term 1 of this year for the two 10-A students (Upper secondary subjects 7–12), and Alex's comments
INSERT INTO marks (student_id, subject_id, year, term, mark, entered_by) VALUES
    (5, 7, YEAR(CURDATE()), 1, 78, 1), (5, 8, YEAR(CURDATE()), 1, 84, 3), (5, 9, YEAR(CURDATE()), 1, 63, 12),
    (5, 10, YEAR(CURDATE()), 1, 72, 1), (5, 11, YEAR(CURDATE()), 1, 68, 1), (5, 12, YEAR(CURDATE()), 1, 89, 1),
    (9, 7, YEAR(CURDATE()), 1, 88, 1), (9, 8, YEAR(CURDATE()), 1, 71, 3), (9, 9, YEAR(CURDATE()), 1, 82, 12),
    (9, 10, YEAR(CURDATE()), 1, 85, 1), (9, 11, YEAR(CURDATE()), 1, 74, 1), (9, 12, YEAR(CURDATE()), 1, 91, 1);

INSERT INTO term_comments (student_id, year, term, comment, written_by) VALUES
    (5, YEAR(CURDATE()), 1, 'Strong overall. A little more focus on Science practicals.', 3),
    (9, YEAR(CURDATE()), 1, 'Excellent work in languages and ICT. Keep it up.', 3);
