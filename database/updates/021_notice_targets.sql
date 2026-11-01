-- The notice form targets a whole grade or one class (Academic notices), or one club / sport (Extracurricular notices).
-- At most one of class_id / grade_id / activity_id is set; none = everyone in the audience.
ALTER TABLE notices
    ADD COLUMN grade_id INT NULL AFTER class_id,
    ADD COLUMN activity_id INT NULL AFTER grade_id,
    ADD CONSTRAINT fk_notice_grade FOREIGN KEY (grade_id) REFERENCES grades (id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_notice_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE CASCADE;
