<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Achievements and the students they belong to (tables: achievements, achievement_students, achievement_photos).
// The public honours board and People's profile pop-up read it; a club's or sport's own achievements (with their
// gallery) are added and changed on its Extracurricular page; the Achievements pages add theirs when they are built.
class AchievementModel extends Model
{
    // Each achievement with its students' names as one text ("Amaya Wijeratne, Nethmi Perera")
    private const SELECT = "SELECT a.*,
               (SELECT GROUP_CONCAT(p.full_name ORDER BY p.first_name SEPARATOR ', ')
                FROM achievement_students s JOIN profiles p ON p.account_id = s.student_id WHERE s.achievement_id = a.id) AS student_names
        FROM achievements a";

    // The columns an achievement form may fill (the SQL is built from these names only)
    private const COLUMNS = ['title', 'category', 'level', 'achieved_on', 'result', 'medal', 'event_name', 'organiser', 'venue',
                             'age_group', 'team', 'summary', 'others', 'photo_url'];

    // The public honours board (landing page): the achievements marked for it, newest first; $limit = only the first few
    public function honoursBoard(?int $limit = null): array
    {
        $statement = $this->db->prepare(self::SELECT . ' WHERE a.on_honours_board = 1 ORDER BY a.achieved_on DESC'
            . ($limit !== null ? ' LIMIT ' . $limit : ''));
        $statement->execute();

        return array_map([$this, 'withParticipants'], $statement->fetchAll());
    }

    // One student's achievements, newest first: [['title', 'category', 'achieved_on', 'summary', 'extracurricular_type'
    // (a club's or sport's: its type, else null)], …] (People's profile pop-up: its timeline, AchievementTimeline::items())
    public function ofStudent(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT a.title, a.category, a.achieved_on, a.summary, x.type AS extracurricular_type FROM achievements a
             JOIN achievement_students s ON s.achievement_id = a.id LEFT JOIN extracurriculars x ON x.id = a.extracurricular_id
             WHERE s.student_id = :id ORDER BY a.achieved_on DESC'
        );
        $statement->execute(['id' => $studentId]);

        return $statement->fetchAll();
    }

    // A club's or sport's achievements, newest first (its "Achievements & Gallery" cards)
    public function ofExtracurricular(int $extracurricularId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, title, level, achieved_on, team, photo_url FROM achievements WHERE extracurricular_id = :id ORDER BY achieved_on DESC, id DESC'
        );
        $statement->execute(['id' => $extracurricularId]);

        return $statement->fetchAll();
    }

    // One achievement with its students ([['id', 'name'], …], ours) and its gallery ([['id', 'url'], …], in order), or null
    public function find(int $achievementId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM achievements WHERE id = :id');
        $statement->execute(['id' => $achievementId]);
        $row = $statement->fetch();

        if ($row === false)
        {
            return null;
        }

        $students = $this->db->prepare(
            'SELECT s.student_id AS id, p.full_name AS name FROM achievement_students s JOIN profiles p ON p.account_id = s.student_id
             WHERE s.achievement_id = :id ORDER BY p.first_name, p.last_name'
        );
        $students->execute(['id' => $achievementId]);
        $photos = $this->db->prepare('SELECT id, url FROM achievement_photos WHERE achievement_id = :id ORDER BY sort_order, id');
        $photos->execute(['id' => $achievementId]);

        return $row + ['students' => $students->fetchAll(), 'photos' => $photos->fetchAll()];
    }

    // Adds ($achievementId null) or changes an achievement, all or nothing: its values (keys from COLUMNS; a missing
    // photo_url keeps the old one), its students (the whole list), its gallery (the kept photos' ids, in order, then the
    // new photos' addresses). Returns [its id, the addresses of the photos it no longer uses].
    public function save(?int $achievementId, int $extracurricularId, array $values, array $studentIds, array $keptPhotoIds, array $newPhotos, int $by): array
    {
        return $this->transaction(function () use ($achievementId, $extracurricularId, $values, $studentIds, $keptPhotoIds, $newPhotos, $by): array
        {
            $values = array_intersect_key($values, array_flip(self::COLUMNS));
            $old    = $achievementId === null ? null : $this->find($achievementId);

            if ($achievementId === null)
            {
                $columns = array_keys($values);
                $this->db->prepare('INSERT INTO achievements (' . implode(', ', $columns) . ', extracurricular_id, added_by)
                                    VALUES (:' . implode(', :', $columns) . ', :extracurricular, :by)')
                    ->execute($values + ['extracurricular' => $extracurricularId, 'by' => $by]);
                $achievementId = (int) $this->db->lastInsertId();
            }
            else
            {
                $sets = implode(', ', array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($values)));
                $this->db->prepare("UPDATE achievements SET {$sets} WHERE id = :id")->execute($values + ['id' => $achievementId]);
            }

            $this->db->prepare('DELETE FROM achievement_students WHERE achievement_id = :id')->execute(['id' => $achievementId]);
            $student = $this->db->prepare('INSERT INTO achievement_students (achievement_id, student_id) VALUES (:id, :student)');

            foreach ($studentIds as $studentId)
            {
                $student->execute(['id' => $achievementId, 'student' => $studentId]);
            }

            // The gallery: kept photos get their new order, the others go, the new ones follow
            $gone = array_values(array_filter($old['photos'] ?? [], fn (array $photo): bool => !in_array((int) $photo['id'], $keptPhotoIds, true)));
            $drop = $this->db->prepare('DELETE FROM achievement_photos WHERE id = :photo AND achievement_id = :id');
            $move = $this->db->prepare('UPDATE achievement_photos SET sort_order = :place WHERE id = :photo AND achievement_id = :id');
            $add  = $this->db->prepare('INSERT INTO achievement_photos (achievement_id, url, sort_order) VALUES (:id, :url, :place)');

            foreach ($gone as $photo)
            {
                $drop->execute(['photo' => $photo['id'], 'id' => $achievementId]);
            }

            foreach (array_values($keptPhotoIds) as $place => $photoId)
            {
                $move->execute(['place' => $place, 'photo' => $photoId, 'id' => $achievementId]);
            }

            foreach (array_values($newPhotos) as $place => $url)
            {
                $add->execute(['id' => $achievementId, 'url' => $url, 'place' => count($keptPhotoIds) + $place]);
            }

            $unused = array_column($gone, 'url');

            if (isset($values['photo_url']) && $old !== null)
            {
                $unused[] = $old['photo_url'];   // the cover it replaced
            }

            return [$achievementId, $unused];
        });
    }

    // An achievement goes (its students and photos with it: ON DELETE CASCADE). Returns the addresses of its photos.
    public function delete(int $achievementId): array
    {
        $row = $this->find($achievementId);
        $this->db->prepare('DELETE FROM achievements WHERE id = :id')->execute(['id' => $achievementId]);

        return $row === null ? [] : [$row['photo_url'], ...array_column($row['photos'], 'url')];
    }

    // Adds 'participants' (our students first, then the other names typed in) and 'tone' (the category's tag colour)
    private function withParticipants(array $row): array
    {
        $names = array_filter(array_merge(explode(', ', (string) $row['student_names']), explode(', ', (string) $row['others'])));

        return $row + [
            'participants' => array_values($names),
            'tone'         => config("achievements.categories.{$row['category']}.tone", 'linen'),
        ];
    }
}
