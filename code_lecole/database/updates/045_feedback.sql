-- Feedback (B-5, 2026-11-06): the class teacher writes to one student of their class. One-way (no replies).
-- The old feedback table (016) was dropped in 027 before anything read it, so it is made again here, in the shape
-- the Feedback pages need:
--   kind      the card's type: config/feedback.php 'types' (Positive / Constructive / Negative)
--   audience  who reads it: config/feedback.php 'audiences' — 'student' is never shown to a parent, 'parent' never
--             to the student, 'both' to both
--   subject   the subject's name when it was written (the class's subjects, or config/feedback.php 'general'); kept
--             as text, so a subject renamed or taken out of the curriculum later doesn't change old feedback
--   student_read_at / parent_read_at  when the student / a parent first opened it (the teacher sees read / unread)
-- The lengths here are the ones in config/feedback.php 'lengths' (the form and the check read them from there).
SET NAMES utf8mb4;

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,                       -- the class teacher who wrote it
    kind ENUM('positive', 'constructive', 'negative') NOT NULL,
    audience ENUM('student', 'parent', 'both') NOT NULL,
    subject VARCHAR(100) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(2000) NOT NULL,
    student_read_at DATETIME NULL,
    parent_read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feedback_student (student_id, created_at),
    INDEX idx_feedback_teacher (teacher_id, created_at),
    CONSTRAINT fk_feedback_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE RESTRICT,
    CONSTRAINT fk_feedback_teacher FOREIGN KEY (teacher_id) REFERENCES user_accounts (id) ON DELETE RESTRICT
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- DEMO: Alex Benjamin (3, class teacher of 10-A) to Nethmi Perera (5) and Amaya Wijeratne (9), the example data's
-- texts. Nethmi's 4 is for her parents only, her 6 and Amaya's 8 for the student only (a parent never sees those).
INSERT INTO feedback (id, student_id, teacher_id, kind, audience, subject, title, message, student_read_at, parent_read_at, created_at) VALUES
    (1, 5, 3, 'positive', 'both', 'Mathematics', 'Algebra Progress This Term',
     'Nethmi has shown remarkable improvement in algebra this term. She actively participates in class discussions and consistently helps her peers during group exercises. Her dedication to mastering complex concepts is truly commendable.',
     NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 10 DAY),
    (2, 5, 3, 'constructive', 'both', 'English Language', 'Essay Structure and Timing',
     'Nethmi possesses great analytical skills when breaking down poetry. However, she needs to focus more on her essay structure and time management during written assessments. I recommend she practices outlining her thoughts before writing.',
     NOW() - INTERVAL 15 DAY, NULL, NOW() - INTERVAL 17 DAY),
    (3, 5, 3, 'positive', 'both', 'Science', 'Renewable Energy Project',
     'Outstanding project presentation on renewable energy! Nethmi went above and beyond the syllabus requirements, demonstrating a deep understanding of the subject matter and excellent presentation skills.',
     NULL, NULL, NOW() - INTERVAL 3 DAY),
    (4, 5, 3, 'negative', 'parent', 'General Conduct', 'Punctuality and Late Submissions',
     'Nethmi was recorded as arriving late on three consecutive Monday mornings, and her Science lab journal submission was delayed by a full week without prior notice. Please ensure stricter adherence to morning attendance and submission deadlines.',
     NULL, NOW() - INTERVAL 24 DAY, NOW() - INTERVAL 25 DAY),
    (5, 5, 3, 'constructive', 'both', 'History', 'Speaking Up in Class Discussions',
     'Nethmi has a good grasp of historical facts, but she is often hesitant to share her thoughts during class discussions. Encouraging her to speak up will help build her confidence and improve her overall engagement.',
     NULL, NULL, NOW() - INTERVAL 32 DAY),
    (6, 5, 3, 'positive', 'student', 'ICT', 'Website Design Project',
     'Exceptional creativity in the recent website design project! Nethmi has a great eye for colour and layout. Her work was selected to be shown at the ICT exhibition.',
     NULL, NULL, NOW() - INTERVAL 1 DAY),
    (7, 9, 3, 'positive', 'both', 'General Conduct', 'Leading the Class',
     'Amaya leads the class well and is always ready to help a classmate. Her calm and organised way of working sets a good example for everyone.',
     NULL, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 4 DAY),
    (8, 9, 3, 'constructive', 'student', 'Mathematics', 'Showing Your Working',
     'Amaya gets the right answers but often skips the steps. Writing out the working will earn more marks and make mistakes easier to find.',
     NULL, NULL, NOW() - INTERVAL 2 DAY);
