-- Academic structure: curriculum stages + their subjects, class teachers, subject teachers.

-- A stage = a range of grades that share one subject list ("Lower secondary", grades 6–9).
-- Which stage a grade is in is worked out from its number (grades.sort_order), so nothing needs re-linking
-- when a stage's range changes. Ranges must not overlap (checked by AcademicModel).
CREATE TABLE stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    first_grade INT NOT NULL,
    last_grade INT NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stage_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stage_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_stage_subject (stage_id, name),
    CONSTRAINT fk_subject_stage FOREIGN KEY (stage_id) REFERENCES stages (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- One class teacher per class, and UNIQUE = a teacher can be class teacher of only one class (the database enforces it)
ALTER TABLE classes
    ADD COLUMN class_teacher_id INT NULL UNIQUE AFTER capacity,
    ADD CONSTRAINT fk_class_teacher FOREIGN KEY (class_teacher_id) REFERENCES user_accounts (id) ON DELETE SET NULL;

-- Who teaches which subject in which class. Removing a subject from a stage removes its assignments (CASCADE).
CREATE TABLE class_subject_teachers (
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    PRIMARY KEY (class_id, subject_id),
    CONSTRAINT fk_cst_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE CASCADE,
    CONSTRAINT fk_cst_subject FOREIGN KEY (subject_id) REFERENCES stage_subjects (id) ON DELETE CASCADE,
    CONSTRAINT fk_cst_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
