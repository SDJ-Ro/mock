-- DEMO (B-3, 2026-11-05):
--   1. Demo row 6 "Top of the Grade — Term 1" (Amaya, 9) goes: the marks already give her that award live
--      (TermResults: top_of_grade, Term 1), so it was shown twice. Its achievement_students row goes with it (CASCADE).
--   2. One pending missing-record request from Amaya (9). Her class teacher this year is Alex (3, class 9), so it shows
--      on Alex's Achievements page. Its evidence file is example data: storage/uploads/ is not in git.
SET NAMES utf8mb4;

DELETE FROM achievements WHERE id = 6 AND title = 'Top of the Grade — Term 1';

INSERT INTO missing_record_requests (student_id, record_type, academic_year, title, details, proof_path, proof_name, created_at)
VALUES (9, 'Sports / Club Enrollment or Position', '2025', 'House Prefect — Teal House',
        'Appointed House Prefect for Teal House, responsible for coordinating inter-house sports and events for the year. It is missing from my records.',
        'proofs/demo-appointment-letter.pdf', 'Appointment_Letter_2025.pdf', NOW() - INTERVAL 2 DAY);
