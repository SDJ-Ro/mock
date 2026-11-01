<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Curriculum stages: a range of grade numbers that share one subject list (stages + stage_subjects tables).
// A grade's stage is found from its number, so changing a range never needs re-linking. Ranges never overlap.
// A subject that has marks is never deleted or renamed: taken out, it is retired (retired_on) — gone from new terms,
// still on old reports under its old name.
class StageModel extends Model
{
    // Every stage, lowest grades first, with its subjects ([id => name], in order) and its retired ones ('retired')
    public function all(): array
    {
        $stages = array_column($this->db->query('SELECT * FROM stages ORDER BY first_grade')->fetchAll(), null, 'id');

        foreach ($stages as &$stage)
        {
            $stage['subjects'] = [];
            $stage['retired']  = [];
        }
        unset($stage);

        foreach ($this->db->query('SELECT id, stage_id, name, retired_on FROM stage_subjects ORDER BY sort_order, name')->fetchAll() as $subject)
        {
            $stages[$subject['stage_id']][$subject['retired_on'] === null ? 'subjects' : 'retired'][$subject['id']] = $subject['name'];
        }

        return array_values($stages);
    }

    public function find(int $id): ?array
    {
        foreach ($this->all() as $stage)
        {
            if ($stage['id'] === $id)
            {
                return $stage;
            }
        }

        return null;
    }

    // The range of a stage that overlaps first–last (other than $exceptId), as "Grades 6–9", or null
    public function overlapping(int $first, int $last, ?int $exceptId = null): ?string
    {
        $statement = $this->db->prepare('SELECT first_grade, last_grade FROM stages WHERE first_grade <= :last AND last_grade >= :first AND id <> :except LIMIT 1');
        $statement->execute(['first' => $first, 'last' => $last, 'except' => $exceptId ?? 0]);
        $other = $statement->fetch();

        return $other ? grade_range($other['first_grade'], $other['last_grade']) : null;
    }

    // How many grades exist with a number from first to last (a stage needs at least one)
    public function gradesIn(int $first, int $last): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM grades WHERE sort_order BETWEEN :first AND :last');
        $statement->execute(['first' => $first, 'last' => $last]);

        return (int) $statement->fetchColumn();
    }

    // How many marks each subject has: [subject id => count] (only subjects with marks). A subject with marks is never removed.
    public function marks(): array
    {
        return $this->pairs('SELECT subject_id, COUNT(*) FROM marks GROUP BY subject_id');
    }

    // This year's subject teachers for a stage's subjects, in all classes: [[grade number, subject id], …]
    // (the editor uses them to ask before a save removes some)
    public function assignments(int $stageId): array
    {
        $statement = $this->db->prepare(
            'SELECT g.sort_order, st.subject_id FROM current_subject_teachers st
             JOIN stage_subjects ss ON ss.id = st.subject_id
             JOIN classes c ON c.id = st.class_id
             JOIN grades g ON g.id = c.grade_id
             WHERE ss.stage_id = :id'
        );
        $statement->execute(['id' => $stageId]);

        return $statement->fetchAll(\PDO::FETCH_NUM);
    }

    // Adds ($id = null) or updates a stage. $subjects = [['id' => this stage's subject id or null, 'name',
    // 'replaces' => the id of a subject with marks this one takes over (a rename), or null], …] in order.
    // Returns [its id, how many subject teachers went]. A subject with an id keeps it, so its teachers and marks stay.
    // One left out: retired if it has marks, deleted (with its teachers: CASCADE) if not. A new one with the name of a
    // retired one brings that one back. A replacement takes over this year's teachers of the one it replaces.
    // Classes the new range leaves out lose their subject teachers.
    public function save(?int $id, array $stage, array $subjects): array
    {
        return $this->transaction(function () use ($id, $stage, $subjects): array
        {
            $before = $id === null ? 0 : count($this->assignments($id));
            $marks  = $this->marks();

            if ($id === null)
            {
                $this->db->prepare('INSERT INTO stages (first_grade, last_grade, description) VALUES (:first_grade, :last_grade, :description)')->execute($stage);
                $id = (int) $this->db->lastInsertId();
            }
            else
            {
                $this->db->prepare('UPDATE stages SET first_grade = :first_grade, last_grade = :last_grade, description = :description WHERE id = :id')
                    ->execute($stage + ['id' => $id]);
            }

            // The ones left out: retired now if they have marks, deleted at the end if not (after a replacement has taken
            // over their teachers). Every other one gets a stand-in name for a moment, so two subjects can swap names
            // without bumping into the one-name-per-stage rule
            $leftOut = $this->db->prepare('SELECT id FROM stage_subjects WHERE stage_id = :id AND retired_on IS NULL AND id NOT IN ('
                . self::idList(array_filter(array_column($subjects, 'id'))) . ')');
            $leftOut->execute(['id' => $id]);
            $leftOut = $leftOut->fetchAll(\PDO::FETCH_COLUMN);
            $keep    = array_filter($leftOut, fn (int $subjectId): bool => isset($marks[$subjectId]));

            $this->db->exec('UPDATE stage_subjects SET retired_on = CURDATE() WHERE id IN (' . self::idList($keep) . ')');
            $this->db->prepare("UPDATE stage_subjects SET name = CONCAT('~', id) WHERE stage_id = :id AND retired_on IS NULL")->execute(['id' => $id]);

            $retired = $this->db->prepare('SELECT LOWER(name), id FROM stage_subjects WHERE stage_id = :id AND retired_on IS NOT NULL');
            $retired->execute(['id' => $id]);
            $retired = $retired->fetchAll(\PDO::FETCH_KEY_PAIR);

            $insert = $this->db->prepare('INSERT INTO stage_subjects (stage_id, name, sort_order) VALUES (:stage, :name, :position)');
            $update = $this->db->prepare('UPDATE stage_subjects SET name = :name, sort_order = :position, retired_on = NULL WHERE id = :id AND stage_id = :stage');
            $year     = (new SchoolYear())->current();
            $takeOver = $this->db->prepare('UPDATE subject_teachers SET subject_id = :new WHERE subject_id = :old AND year = :year');

            foreach (array_values($subjects) as $position => $subject)
            {
                $values    = ['stage' => $id, 'name' => $subject['name'], 'position' => $position];
                $subjectId = $subject['id'] ?? $retired[mb_strtolower($subject['name'])] ?? null;   // a retired name comes back

                if ($subjectId === null)
                {
                    $insert->execute($values);
                    $subjectId = (int) $this->db->lastInsertId();
                }
                else
                {
                    $update->execute($values + ['id' => $subjectId]);
                }

                if (($subject['replaces'] ?? null) !== null)
                {
                    $takeOver->execute(['new' => $subjectId, 'old' => $subject['replaces'], 'year' => $year]);
                }
            }

            // A retired subject isn't taught any more this year (earlier years' teachers stay); the rest left out go
            $this->db->prepare('DELETE FROM subject_teachers WHERE year = :year AND subject_id IN (' . self::idList($keep) . ')')->execute(['year' => $year]);
            $this->db->exec('DELETE FROM stage_subjects WHERE id IN (' . self::idList(array_diff($leftOut, $keep)) . ')');
            $this->forgetOutOfRange($year);

            return [$id, $before - count($this->assignments($id))];
        });
    }

    // Deleting a stage removes its subjects and their teachers (CASCADE); the controller checks there are no marks first
    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM stages WHERE id = :id')->execute(['id' => $id]);
    }

    // After a range changes, a class may be outside the stage its subject teachers came from — this year's go
    // (earlier years stay as they were)
    private function forgetOutOfRange(int $year): void
    {
        $this->db->prepare(
            'DELETE st FROM subject_teachers st
             JOIN stage_subjects ss ON ss.id = st.subject_id
             JOIN stages s ON s.id = ss.stage_id
             JOIN classes c ON c.id = st.class_id
             JOIN grades g ON g.id = c.grade_id
             WHERE st.year = :year AND g.sort_order NOT BETWEEN s.first_grade AND s.last_grade'
        )->execute(['year' => $year]);
    }
}
