-- B-4: one graduating batch per school year, then one certificate per student in that batch.
-- The admin sets the date on Academic; reaching that date makes the final grade's students ready for review.
-- A certificate stays pending until management confirms Finalise. Its issued text and particulars are then frozen.
-- Leaving certificates and parent PDF access are outside this update.
SET NAMES utf8mb4;

CREATE TABLE graduation_batches (
    school_year SMALLINT PRIMARY KEY,
    grade_id INT NOT NULL,
    graduation_date DATE NOT NULL,
    set_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_gb_year FOREIGN KEY (school_year) REFERENCES school_years (year) ON DELETE RESTRICT,
    CONSTRAINT fk_gb_grade FOREIGN KEY (grade_id) REFERENCES grades (id) ON DELETE RESTRICT,
    CONSTRAINT fk_gb_set_by FOREIGN KEY (set_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year SMALLINT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('pending', 'issued') NOT NULL DEFAULT 'pending',
    academic_text TEXT NULL,
    activities_text TEXT NULL,
    conduct_text TEXT NULL,
    student_name VARCHAR(150) NULL,
    name_with_initials VARCHAR(150) NULL,
    cohort_text VARCHAR(150) NULL,
    study_period VARCHAR(100) NULL,
    school_name VARCHAR(150) NULL,
    school_motto VARCHAR(255) NULL,
    issued_on DATE NULL,
    finalised_by INT NULL,
    finalised_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_certificate_batch_student (school_year, student_id),
    INDEX idx_certificate_status (status, school_year),
    CONSTRAINT fk_certificate_batch FOREIGN KEY (school_year) REFERENCES graduation_batches (school_year) ON DELETE RESTRICT,
    CONSTRAINT fk_certificate_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE RESTRICT,
    CONSTRAINT fk_certificate_finalised_by FOREIGN KEY (finalised_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
