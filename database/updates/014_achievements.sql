-- Achievements: what students won or did (a match, a competition, a top result). Shown to the student and their parents,
-- printed on character certificates, and — when the office ticks "honours board" — on the public landing page.
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,           -- config/achievements.php categories (Sports, Academic…)
    level VARCHAR(50) NOT NULL,              -- School, Zonal, Provincial, National…
    achieved_on DATE NOT NULL,
    result VARCHAR(100) NULL,                -- "Runners Up", "Champion"
    medal VARCHAR(50) NULL,                  -- "Gold Medal", "Trophy"
    event_name VARCHAR(150) NULL,
    organiser VARCHAR(150) NULL,
    venue VARCHAR(150) NULL,
    age_group VARCHAR(50) NULL,
    team VARCHAR(150) NULL,
    summary VARCHAR(600) NOT NULL,
    details VARCHAR(1000) NULL,
    others VARCHAR(255) NULL,                -- people without an account here ("+ 22 members", a team from outside)
    photo_url VARCHAR(255) NULL,
    activity_id INT NULL,
    on_honours_board TINYINT(1) NOT NULL DEFAULT 0,
    added_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_achievements_board (on_honours_board, achieved_on),
    CONSTRAINT fk_achievement_activity FOREIGN KEY (activity_id) REFERENCES activities (id) ON DELETE SET NULL,
    CONSTRAINT fk_achievement_author FOREIGN KEY (added_by) REFERENCES user_accounts (id) ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- The students it belongs to
CREATE TABLE achievement_students (
    achievement_id INT NOT NULL,
    student_id INT NOT NULL,
    PRIMARY KEY (achievement_id, student_id),
    CONSTRAINT fk_as_achievement FOREIGN KEY (achievement_id) REFERENCES achievements (id) ON DELETE CASCADE,
    CONSTRAINT fk_as_student FOREIGN KEY (student_id) REFERENCES user_accounts (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- DEMO: the 5 achievements the landing page used to have typed into LandingController, now real rows
INSERT INTO achievements (id, title, category, level, achieved_on, result, medal, event_name, organiser, venue, age_group, team, summary, details, others, photo_url, activity_id, on_honours_board, added_by) VALUES
    (1, 'Div-1 League Runners Up', 'Sports', 'Provincial', '2023-11-18', 'Runners Up', 'Silver Medal', 'Provincial Div-1 Football League',
        'Western Province Schools Football Association', 'SSC Grounds, Colombo', 'Under 19', 'Team A (Senior)',
        'After an undefeated group stage, the senior squad battled through a tense semi-final on penalties to reach the provincial final. Despite a spirited comeback, the team finished as runners up — the club\'s best league result in over a decade.',
        'A 10-team provincial league contested over the season. The squad finished top of the group stage before falling narrowly in the final, 2–1 after extra time.',
        'Kavindu Perera, Sanjula Silva, Isuru Bandara, Ravindu Alwis, Tharindu Jay', '/assets/images/football.jpg', 1, 1, 1),
    (2, '400m All-Island Gold', 'Sports', 'All-Island', '2024-08-05', 'Champion', 'Gold Medal', 'All-Island Schools Athletics Championship',
        'Schools Athletic Association', 'Sugathadasa Stadium, Colombo', 'Under 17', 'Athletics Squad',
        'Nethmi took the 400m final from lane six, holding the lead through the last bend and crossing in a new personal best. It is the school\'s first individual track gold at all-island level in four seasons.',
        'Qualified through district and provincial heats before winning the final by 0.42 seconds in 55.81s.', NULL, '/assets/images/running.jpg', NULL, 1, 1),
    (3, 'National Debate Champions', 'Debating', 'National', '2024-03-22', 'Champions', 'Gold Medal', 'National Schools Debating Championship',
        'National Debating Council', 'BMICH, Colombo', 'Under 19', 'Senior English Debating Team',
        'Four rounds, one semi-final, and a unanimous decision in the grand final on education policy. The senior team argued opposition and took the title along with the Best Speaker award.',
        'A 32-school knockout format judged on matter, manner, and method by an adjudication panel of five.', 'Dinuk Ratnayake, Sasha Karunaratne', '/assets/images/debate.jpg', 3, 1, 1),
    (4, 'Best Ensemble Performance', 'Music', 'Provincial', '2024-06-09', 'First Place', 'Gold Medal', 'Western Province Schools Music Festival',
        'Provincial Department of Education', 'Lionel Wendt Theatre, Colombo', 'Open', 'School Orchestra',
        'The orchestra closed the festival with an arrangement rehearsed across two terms, earning the highest ensemble score of the evening and a standing ovation from the hall.',
        'Judged on intonation, ensemble balance, interpretation, and stage presence across a 12-minute programme.', 'Senuri Alwis, Rashmi Gunasekara, Yasith Silva + 22 members', '/assets/images/orchestra.jpg', NULL, 1, 1),
    (5, 'Innovation Award', 'Robotics', 'Inter-School', '2023-10-12', 'Innovation Award', 'Trophy', 'Inter-School Robotics Challenge',
        'Institute of Engineering Technology', 'Innovation Hall, Moratuwa', 'Under 19', 'Robotics Club — Team Circuit',
        'The team built a low-cost water quality rover in eight weeks and were recognised for the most original engineering solution across 40 competing schools.',
        'Teams were scored on design originality, build quality, autonomy, and a live judged demonstration.', 'Hasitha Jayasuriya, Minoli Perera, Ashen de Silva, Tharushi Peiris', '/assets/images/class.jpg', NULL, 1, 1),
    (6, 'Top of the Grade — Term 1', 'Academic', 'School', CURDATE() - INTERVAL 60 DAY, 'First Place', NULL, 'Grade 10 term tests',
        NULL, NULL, NULL, NULL, 'Highest average in Grade 10 for the first term.', NULL, NULL, NULL, NULL, 0, 3);

INSERT INTO achievement_students (achievement_id, student_id) VALUES
    (2, 5),     -- Nethmi: 400m gold
    (3, 9),     -- Amaya: debate champions
    (6, 9);     -- Amaya: top of the grade
