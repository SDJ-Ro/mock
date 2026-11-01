-- DEMO: just enough for each programme page to show something true. Drama Society keeps no teacher in charge and no
-- coach (so "not assigned yet" shows); the clubs use no age groups.
SET NAMES utf8mb4;

UPDATE extracurriculars SET category = 'Team Sport', cover_url = '/assets/images/examples/football-cover.jpg',
    coach_specialty = 'Football coach', coach_email = 'dinesh.coach@gmail.com', coach_phone = '+94 77 555 0101',
    team_limit = 18, lowest_grade = 6
    WHERE id = 1;

UPDATE extracurriculars SET category = 'Academic Club', cover_url = '/assets/images/examples/debate-students.jpg',
    uses_age_groups = 0
    WHERE id = 2;

UPDATE extracurriculars SET category = 'Performing Arts', uses_age_groups = 0
    WHERE id = 3;

UPDATE extracurriculars SET category = 'Aquatics', coach_specialty = 'Swimming coach',
    coach_email = 'nadeesha.swim@gmail.com', coach_phone = '+94 71 555 0144', lowest_grade = 7
    WHERE id = 4;

UPDATE extracurriculars SET category = 'Creative Arts', cover_url = '/assets/images/examples/digital-arts-cover.jpg',
    uses_age_groups = 0
    WHERE id = 80;
