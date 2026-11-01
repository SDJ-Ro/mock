-- Assignments per school year (B-0, 2026-10-29). The school's structure (grades, classes, subjects, clubs / sports)
-- stays as it is; WHO is WHERE now has a year, so last year's classes and teachers are kept, not overwritten:
--   class_students      a student's class          (was student_details.class_id)
--   class_teachers      a class's class teacher     (was classes.class_teacher_id)
--   subject_teachers    who teaches a subject in a class (was class_subject_teachers, no year)
--   teachers_in_charge  a club / sport's teacher in charge (was activities.teacher_id)
-- The current year = the newest row of school_years. Year-end (B-9) adds the next year and carries the teachers over;
-- until then nothing changes on its own when the calendar year turns.
-- Each table has a "current_…" view: the same rows for the current year only. Pages read the views; App\Models\Assignments
-- is the only place that writes the tables.
SET NAMES utf8mb4;

CREATE TABLE school_years (
    year SMALLINT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO school_years (year) VALUES (YEAR(CURDATE()));

-- One class per student per year. A class that has ever had students can't be deleted (RESTRICT): its history stays.
CREATE TABLE class_students (
    year SMALLINT NOT NULL,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    PRIMARY KEY (year, student_id),
    INDEX idx_class_students_class (class_id, year),
    CONSTRAINT fk_cs_year FOREIGN KEY (year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT fk_cs_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE,
    CONSTRAINT fk_cs_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE RESTRICT
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- One class teacher per class per year, and a teacher is class teacher of only one class a year (UNIQUE)
CREATE TABLE class_teachers (
    year SMALLINT NOT NULL,
    class_id INT NOT NULL,
    teacher_id INT NOT NULL,
    PRIMARY KEY (year, class_id),
    UNIQUE KEY uq_class_teacher_once (year, teacher_id),
    CONSTRAINT fk_ct_year FOREIGN KEY (year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT fk_ct_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE,
    CONSTRAINT fk_ct_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- One teacher per subject per class per year. Removing a subject from a stage removes its teachers (CASCADE).
CREATE TABLE subject_teachers (
    year SMALLINT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    PRIMARY KEY (year, class_id, subject_id),
    INDEX idx_subject_teachers_teacher (teacher_id, year),
    CONSTRAINT fk_st_year FOREIGN KEY (year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT fk_st_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE,
    CONSTRAINT fk_st_subject FOREIGN KEY (subject_id) REFERENCES stage_subjects (id) ON DELETE CASCADE,
    CONSTRAINT fk_st_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- One teacher in charge per club / sport per year
CREATE TABLE teachers_in_charge (
    year SMALLINT NOT NULL,
    activity_id INT NOT NULL,
    teacher_id INT NOT NULL,
    PRIMARY KEY (year, activity_id),
    INDEX idx_tic_teacher (teacher_id, year),
    CONSTRAINT fk_tic_year FOREIGN KEY (year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT fk_tic_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE,
    CONSTRAINT fk_tic_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Today's assignments move into the current year
INSERT INTO class_students (year, student_id, class_id)
    SELECT YEAR(CURDATE()), account_id, class_id FROM student_details WHERE class_id IS NOT NULL;
INSERT INTO class_teachers (year, class_id, teacher_id)
    SELECT YEAR(CURDATE()), id, class_teacher_id FROM classes WHERE class_teacher_id IS NOT NULL;
INSERT INTO subject_teachers (year, class_id, subject_id, teacher_id)
    SELECT YEAR(CURDATE()), class_id, subject_id, teacher_id FROM class_subject_teachers;
INSERT INTO teachers_in_charge (year, activity_id, teacher_id)
    SELECT YEAR(CURDATE()), id, teacher_id FROM activities WHERE teacher_id IS NOT NULL;

-- ... and the old places go
ALTER TABLE student_details DROP FOREIGN KEY fk_student_class, DROP COLUMN class_id;
ALTER TABLE classes DROP FOREIGN KEY fk_class_teacher, DROP INDEX class_teacher_id, DROP COLUMN class_teacher_id;
ALTER TABLE activities DROP FOREIGN KEY fk_activity_teacher, DROP COLUMN teacher_id;
DROP TABLE class_subject_teachers;

-- The current year's rows only (what every page shows)
CREATE VIEW current_class_students AS
    SELECT student_id, class_id FROM class_students WHERE year = (SELECT MAX(year) FROM school_years);
CREATE VIEW current_class_teachers AS
    SELECT class_id, teacher_id FROM class_teachers WHERE year = (SELECT MAX(year) FROM school_years);
CREATE VIEW current_subject_teachers AS
    SELECT class_id, subject_id, teacher_id FROM subject_teachers WHERE year = (SELECT MAX(year) FROM school_years);
CREATE VIEW current_teachers_in_charge AS
    SELECT activity_id, teacher_id FROM teachers_in_charge WHERE year = (SELECT MAX(year) FROM school_years);

-- Every class a teacher teaches this year: as its class teacher, or a subject in it (one row per teacher and class)
CREATE VIEW current_teaching AS
    SELECT teacher_id, class_id FROM current_class_teachers
    UNION
    SELECT teacher_id, class_id FROM current_subject_teachers;
