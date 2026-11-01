-- The honours board's photos need a description for screen readers (the <img alt="…">); it lives with the
-- achievement. Also repairs two dashes that a non-UTF-8 import turned into "?".
SET NAMES utf8mb4;

ALTER TABLE achievements
    ADD COLUMN photo_alt VARCHAR(255) NULL AFTER photo_url;   -- what the photo shows, read aloud by screen readers

UPDATE achievements SET photo_alt = 'School football squad celebrating with a trophy on a floodlit pitch'   WHERE id = 1;
UPDATE achievements SET photo_alt = 'School athlete crossing the finish line on a running track'            WHERE id = 2;
UPDATE achievements SET photo_alt = 'Two students speaking at a debating podium in a panelled hall'         WHERE id = 3;
UPDATE achievements SET photo_alt = 'School orchestra performing on stage'                                  WHERE id = 4;
UPDATE achievements SET photo_alt = 'Students building a competition robot in a school lab'                 WHERE id = 5;

UPDATE achievements SET team  = 'Robotics Club — Team Circuit' WHERE id = 5;
UPDATE achievements SET title = 'Top of the Grade — Term 1'    WHERE id = 6;
