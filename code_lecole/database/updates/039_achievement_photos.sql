-- A programme's achievements (B-2 step 2e, 2026-11-01).
-- An achievement of a club or sport is an `achievements` row with its extracurricular_id (the same table B-3 uses):
-- its students in achievement_students (each sees it on their timeline), other names (not in the portal) in `others`,
-- the team it represented in `team` (empty = an individual one). Its event gallery: the photos below, in order.
SET NAMES utf8mb4;

CREATE TABLE achievement_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    achievement_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,                 -- an upload (public/uploads/extracurricular/) or a built-in photo
    sort_order INT NOT NULL DEFAULT 0,
    INDEX idx_achievement_photos (achievement_id, sort_order),
    CONSTRAINT fk_photo_achievement FOREIGN KEY (achievement_id) REFERENCES achievements (id) ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
