-- Grade and stage names are worked out, not stored (Academic page, 2026-10-23):
-- a grade is shown as word('grade') + its number ("Grade 6", or "Year 6" for another school) — grade_name() in core/helpers.php;
-- a stage is shown as its range ("Grades 6–9"). A stored "Grade 6" would stay English when the school's words change.
ALTER TABLE grades
    DROP INDEX name,
    DROP COLUMN name,
    ADD UNIQUE KEY uq_grade_number (sort_order);

ALTER TABLE stages
    DROP INDEX name,
    DROP COLUMN name;
