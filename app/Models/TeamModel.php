<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// A programme's teams (teams) and who plays in them (team_members, a free-text position each, since a date).
// When someone leaves a team, changes position, or the team is deleted, the old place goes to team_member_history
// (with the team's name), so nothing that happened is lost. Who may be in which team: ExtracurricularController.
class TeamModel extends Model
{
    // A programme's teams by name, each with its members: [['id', 'name', 'age_group', 'cover_url', 'members' =>
    // [['id', 'name', 'photo', 'grade_number', 'date_of_birth', 'position', 'since'], …]], …]
    public function ofProgramme(int $extracurricularId): array
    {
        $statement = $this->db->prepare('SELECT id, name, age_group, cover_url FROM teams WHERE extracurricular_id = :id ORDER BY name');
        $statement->execute(['id' => $extracurricularId]);
        $teams = array_column(array_map(fn (array $team): array => $team + ['members' => []], $statement->fetchAll()), null, 'id');

        $members = $this->db->prepare(
            'SELECT tm.team_id, u.id, p.full_name AS name, p.photo_url AS photo, g.sort_order AS grade_number, p.date_of_birth, tm.position, tm.since
             FROM team_members tm
             JOIN teams t ON t.id = tm.team_id
             JOIN user_accounts u ON u.id = tm.student_id
             JOIN profiles p ON p.account_id = u.id
             LEFT JOIN current_class_students cs ON cs.student_id = u.id
             LEFT JOIN classes c ON c.id = cs.class_id
             LEFT JOIN grades g ON g.id = c.grade_id
             WHERE t.extracurricular_id = :id
             ORDER BY tm.position IS NULL, p.first_name, p.last_name'
        );
        $members->execute(['id' => $extracurricularId]);

        foreach ($members->fetchAll() as $member)
        {
            $teams[$member['team_id']]['members'][] = $member;
        }

        return array_values($teams);
    }

    public function find(int $teamId): ?array
    {
        $statement = $this->db->prepare('SELECT id, extracurricular_id, name, age_group, cover_url FROM teams WHERE id = :id');
        $statement->execute(['id' => $teamId]);

        return $statement->fetch() ?: null;
    }

    // Is this name used by another team of the programme? (any capitals)
    public function nameTaken(int $extracurricularId, string $name, ?int $exceptId = null): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM teams WHERE extracurricular_id = :id AND LOWER(name) = LOWER(:name) AND id <> :except');
        $statement->execute(['id' => $extracurricularId, 'name' => $name, 'except' => $exceptId ?? 0]);

        return (bool) $statement->fetchColumn();
    }

    // The teams of a programme for one age group (or for any age: age_group empty), by name: [id => name]
    public function forAgeGroup(int $extracurricularId, ?string $ageGroup): array
    {
        $statement = $this->db->prepare('SELECT id, name FROM teams WHERE extracurricular_id = :id AND (age_group IS NULL OR age_group = :group) ORDER BY name');
        $statement->execute(['id' => $extracurricularId, 'group' => $ageGroup ?? '']);

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    // Adds ($teamId null) or changes a team and its whole roster ([student id => position or null]). A member who left
    // the roster, or whose position changed, gets a history row (from their 'since' to today). All or nothing. Returns its id.
    public function save(?int $teamId, int $extracurricularId, array $team, array $roster): int
    {
        return $this->transaction(function () use ($teamId, $extracurricularId, $team, $roster): int
        {
            if ($teamId === null)
            {
                $this->db->prepare('INSERT INTO teams (extracurricular_id, name, age_group, cover_url) VALUES (:id, :name, :age, :cover)')
                    ->execute(['id' => $extracurricularId, 'name' => $team['name'], 'age' => $team['age_group'], 'cover' => $team['cover_url'] ?? null]);
                $teamId = (int) $this->db->lastInsertId();
            }
            else
            {
                $sets = 'name = :name, age_group = :age' . (isset($team['cover_url']) ? ', cover_url = :cover' : '');
                $this->db->prepare("UPDATE teams SET {$sets} WHERE id = :team")
                    ->execute(['name' => $team['name'], 'age' => $team['age_group'], 'team' => $teamId] + (isset($team['cover_url']) ? ['cover' => $team['cover_url']] : []));
            }

            $now = $this->db->prepare('SELECT student_id, position FROM team_members WHERE team_id = :team');
            $now->execute(['team' => $teamId]);
            $now = $now->fetchAll(\PDO::FETCH_KEY_PAIR);

            foreach ($now as $studentId => $position)
            {
                if (!array_key_exists($studentId, $roster) || $roster[$studentId] !== $position)
                {
                    $this->leave($teamId, (int) $studentId);
                }
            }

            $insert = $this->db->prepare('INSERT IGNORE INTO team_members (team_id, student_id, position, since) VALUES (:team, :student, :position, CURDATE())');

            foreach ($roster as $studentId => $position)
            {
                $insert->execute(['team' => $teamId, 'student' => $studentId, 'position' => $position]);
            }

            return $teamId;
        });
    }

    // One member into a team, as a player (no position) from today
    public function assign(int $teamId, int $studentId): void
    {
        $this->db->prepare('INSERT IGNORE INTO team_members (team_id, student_id, position, since) VALUES (:team, :student, NULL, CURDATE())')
            ->execute(['team' => $teamId, 'student' => $studentId]);
    }

    // A team goes; its members' places go to the history first (they are back in "Unassigned")
    public function delete(int $teamId): void
    {
        $this->transaction(function () use ($teamId): void
        {
            $members = $this->db->prepare('SELECT student_id FROM team_members WHERE team_id = :team');
            $members->execute(['team' => $teamId]);

            foreach ($members->fetchAll(\PDO::FETCH_COLUMN) as $studentId)
            {
                $this->leave($teamId, (int) $studentId);
            }

            $this->db->prepare('DELETE FROM teams WHERE id = :team')->execute(['team' => $teamId]);
        });
    }

    // A student leaves every team of a programme (they left it), or of every programme ($extracurricularId null)
    public function leaveAll(?int $extracurricularId, int $studentId): void
    {
        $statement = $this->db->prepare(
            'SELECT tm.team_id FROM team_members tm JOIN teams t ON t.id = tm.team_id
             WHERE tm.student_id = :student' . ($extracurricularId === null ? '' : ' AND t.extracurricular_id = :id')
        );
        $statement->execute(['student' => $studentId] + ($extracurricularId === null ? [] : ['id' => $extracurricularId]));

        foreach ($statement->fetchAll(\PDO::FETCH_COLUMN) as $teamId)
        {
            $this->leave((int) $teamId, $studentId);
        }
    }

    // One place ends: into the history (from 'since' to today, the team's name kept), then out of the team
    private function leave(int $teamId, int $studentId): void
    {
        $keys = ['team' => $teamId, 'student' => $studentId];

        $this->db->prepare(
            'INSERT INTO team_member_history (extracurricular_id, team_name, student_id, position, from_on, to_on)
             SELECT t.extracurricular_id, t.name, tm.student_id, tm.position, tm.since, CURDATE()
             FROM team_members tm JOIN teams t ON t.id = tm.team_id WHERE tm.team_id = :team AND tm.student_id = :student'
        )->execute($keys);
        $this->db->prepare('DELETE FROM team_members WHERE team_id = :team AND student_id = :student')->execute($keys);
    }
}
