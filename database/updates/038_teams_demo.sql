-- DEMO: Football has one team, "Under 15 Squad", with Dinuk (Under 15) as Captain since February. Chess (a club) has no
-- teams, so its empty box shows.
SET NAMES utf8mb4;

INSERT INTO teams (id, extracurricular_id, name, age_group, cover_url) VALUES
    (1, 1, 'Under 15 Squad', 'Under 15', '/assets/images/examples/football-match.jpg');

INSERT INTO team_members (team_id, student_id, position, since) VALUES
    (1, 10, 'Captain', CONCAT(YEAR(CURDATE()), '-02-15'));
