<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Term marks (marks), the class teacher's comment per term (term_comments) and which class terms are published
// (term_publications). A mark is 0–100, or null = AB (absent). Only SQL here: totals, averages, positions and
// awards are worked out by App\Services\TermResults.
class TermMarkModel extends Model
{
    // ---------------------------------------------------------------------
    // The class teacher's Term Marks page
    // ---------------------------------------------------------------------

    // The class this teacher is class teacher of this year → ['id', 'name', 'grade_number'], or null
    public function classOf(int $teacherId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT c.id, c.name, g.sort_order AS grade_number
             FROM current_class_teachers ct JOIN classes c ON c.id = ct.class_id JOIN grades g ON g.id = c.grade_id
             WHERE ct.teacher_id = :me'
        );
        $statement->execute(['me' => $teacherId]);

        return $statement->fetch() ?: null;
    }

    // The subjects a grade is marked in now (its curriculum's, not retired), in order: [subject id => name]
    public function subjectsFor(int $gradeNumber): array
    {
        $statement = $this->db->prepare(
            'SELECT ss.id, ss.name FROM stage_subjects ss JOIN stages s ON s.id = ss.stage_id
             WHERE :grade BETWEEN s.first_grade AND s.last_grade AND ss.retired_on IS NULL
             ORDER BY ss.sort_order, ss.name'
        );
        $statement->execute(['grade' => $gradeNumber]);

        return array_column($statement->fetchAll(), 'name', 'id');
    }

    // A class's students for one term: everyone in it that year who is still at school, or who has marks that term
    // (a student who left after sitting the term still counts), by first name:
    // [id => ['id', 'name', 'code', 'photo', 'admission_date', 'marks' => [subject id => mark or null (AB)], 'comment']]
    public function sheet(int $classId, int $year, int $term): array
    {
        $statement = $this->db->prepare(
            "SELECT u.id, p.full_name AS name, p.code, p.photo_url AS photo, sd.admission_date
             FROM class_students cs
             JOIN user_accounts u ON u.id = cs.student_id
             JOIN profiles p ON p.account_id = u.id
             JOIN student_details sd ON sd.account_id = u.id
             WHERE cs.class_id = :class AND cs.year = :year
               AND (u.activation_status <> 'INACTIVE'
                    OR EXISTS (SELECT 1 FROM marks m WHERE m.student_id = u.id AND m.year = :year2 AND m.term = :term))
             ORDER BY p.first_name, p.last_name"
        );
        $statement->execute(['class' => $classId, 'year' => $year, 'year2' => $year, 'term' => $term]);
        $students = [];

        foreach ($statement->fetchAll() as $student)
        {
            $students[$student['id']] = $student + ['marks' => [], 'comment' => ''];
        }

        $in = self::idList(array_keys($students));

        $marks = $this->db->prepare("SELECT student_id, subject_id, mark FROM marks WHERE student_id IN ({$in}) AND year = :year AND term = :term");
        $marks->execute(['year' => $year, 'term' => $term]);

        foreach ($marks->fetchAll() as $row)
        {
            $students[$row['student_id']]['marks'][$row['subject_id']] = $row['mark'];
        }

        $comments = $this->db->prepare("SELECT student_id, comment FROM term_comments WHERE student_id IN ({$in}) AND year = :year AND term = :term");
        $comments->execute(['year' => $year, 'term' => $term]);

        foreach ($comments->fetchAll() as $row)
        {
            $students[$row['student_id']]['comment'] = $row['comment'];
        }

        return $students;
    }

    // Saves one student's term: [subject id => 0–100, null (AB), or '' (not entered: removed)] and the comment ('' =
    // none). All or nothing. Returns what changed: [subject id => [before, after]] (before / after: a number, 'AB' or '').
    public function save(int $studentId, int $year, int $term, array $marks, string $comment, int $by): array
    {
        return $this->transaction(function () use ($studentId, $year, $term, $marks, $comment, $by): array
        {
            $keys   = ['student' => $studentId, 'year' => $year, 'term' => $term];
            $before = $this->db->prepare('SELECT subject_id, mark FROM marks WHERE student_id = :student AND year = :year AND term = :term');
            $before->execute($keys);
            $before = array_column($before->fetchAll(), 'mark', 'subject_id');

            $upsert = $this->db->prepare(
                'INSERT INTO marks (student_id, subject_id, year, term, mark, entered_by) VALUES (:student, :subject, :year, :term, :mark, :by)
                 ON DUPLICATE KEY UPDATE mark = VALUES(mark), entered_by = VALUES(entered_by)'
            );
            $remove = $this->db->prepare('DELETE FROM marks WHERE student_id = :student AND subject_id = :subject AND year = :year AND term = :term');
            $shown  = fn (array $list, int $subject): string => !array_key_exists($subject, $list) || $list[$subject] === '' ? '' : (string) ($list[$subject] ?? 'AB');
            $changes = [];

            foreach ($marks as $subjectId => $mark)
            {
                $mark === '' ? $remove->execute($keys + ['subject' => $subjectId]) : $upsert->execute($keys + ['subject' => $subjectId, 'mark' => $mark, 'by' => $by]);

                if ($shown($before, $subjectId) !== $shown($marks, $subjectId))
                {
                    $changes[$subjectId] = [$shown($before, $subjectId), $shown($marks, $subjectId)];
                }
            }

            if ($comment === '')
            {
                $this->db->prepare('DELETE FROM term_comments WHERE student_id = :student AND year = :year AND term = :term')->execute($keys);
            }
            else
            {
                $this->db->prepare(
                    'INSERT INTO term_comments (student_id, year, term, comment, written_by) VALUES (:student, :year, :term, :comment, :by)
                     ON DUPLICATE KEY UPDATE comment = VALUES(comment), written_by = VALUES(written_by)'
                )->execute($keys + ['comment' => $comment, 'by' => $by]);
            }

            return $changes;
        });
    }

    // ---------------------------------------------------------------------
    // Publishing
    // ---------------------------------------------------------------------

    // When a class's term was published → ['published_at', 'published_by'], or null (still a draft)
    public function publication(int $classId, int $year, int $term): ?array
    {
        $statement = $this->db->prepare('SELECT published_at, published_by FROM term_publications WHERE class_id = :class AND year = :year AND term = :term');
        $statement->execute(['class' => $classId, 'year' => $year, 'term' => $term]);

        return $statement->fetch() ?: null;
    }

    // Has any term of this year been published for this class? (then its students stay in it for the rest of the year)
    public function anyPublished(int $classId, int $year): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM term_publications WHERE class_id = :class AND year = :year LIMIT 1');
        $statement->execute(['class' => $classId, 'year' => $year]);

        return (bool) $statement->fetchColumn();
    }

    // Publish a class's term (once; never undone)
    public function publish(int $classId, int $year, int $term, int $by): void
    {
        $this->db->prepare('INSERT IGNORE INTO term_publications (year, class_id, term, published_by, published_at) VALUES (:year, :class, :term, :by, NOW())')
            ->execute(['year' => $year, 'class' => $classId, 'term' => $term, 'by' => $by]);
    }

    // ---------------------------------------------------------------------
    // Results (published terms only)
    // ---------------------------------------------------------------------

    // Every published mark of one grade in one term (all its classes), for TermResults:
    // [['student_id', 'class_id', 'subject_id', 'subject', 'mark' (null = AB)], …] in subject order
    public function gradeTerm(int $year, int $term, int $gradeNumber): array
    {
        $statement = $this->db->prepare(
            'SELECT cs.student_id, cs.class_id, m.subject_id, ss.name AS subject, m.mark
             FROM class_students cs
             JOIN classes c ON c.id = cs.class_id
             JOIN grades g ON g.id = c.grade_id
             JOIN term_publications tp ON tp.year = cs.year AND tp.class_id = cs.class_id AND tp.term = :term
             JOIN marks m ON m.student_id = cs.student_id AND m.year = cs.year AND m.term = :term2
             JOIN stage_subjects ss ON ss.id = m.subject_id
             WHERE cs.year = :year AND g.sort_order = :grade
             ORDER BY ss.sort_order, ss.name'
        );
        $statement->execute(['term' => $term, 'term2' => $term, 'year' => $year, 'grade' => $gradeNumber]);

        return $statement->fetchAll();
    }

    // Has every class of the grade that has students that year published this term? (Top of Grade waits for it)
    public function gradePublished(int $year, int $term, int $gradeNumber): bool
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(DISTINCT cs.class_id) = COUNT(DISTINCT tp.class_id)
             FROM class_students cs
             JOIN classes c ON c.id = cs.class_id
             JOIN grades g ON g.id = c.grade_id
             LEFT JOIN term_publications tp ON tp.year = cs.year AND tp.class_id = cs.class_id AND tp.term = :term
             WHERE cs.year = :year AND g.sort_order = :grade'
        );
        $statement->execute(['term' => $term, 'year' => $year, 'grade' => $gradeNumber]);

        return (bool) $statement->fetchColumn();
    }

    // A student's school years, oldest first: [['year', 'class_id', 'class_name', 'grade_number'], …]
    public function yearsOf(int $studentId): array
    {
        $statement = $this->db->prepare(
            'SELECT cs.year, cs.class_id, c.name AS class_name, g.sort_order AS grade_number
             FROM class_students cs JOIN classes c ON c.id = cs.class_id JOIN grades g ON g.id = c.grade_id
             WHERE cs.student_id = :id ORDER BY cs.year'
        );
        $statement->execute(['id' => $studentId]);

        return $statement->fetchAll();
    }

    // A class's published terms in one year: [term number => ['published_at']]
    public function publishedTerms(int $classId, int $year): array
    {
        $statement = $this->db->prepare('SELECT term, published_at FROM term_publications WHERE class_id = :class AND year = :year ORDER BY term');
        $statement->execute(['class' => $classId, 'year' => $year]);

        return array_column($statement->fetchAll(), null, 'term');
    }

    // A student's comment for a term with its writer → ['comment', 'teacher'], or null
    public function comment(int $studentId, int $year, int $term): ?array
    {
        $statement = $this->db->prepare(
            'SELECT tc.comment, p.full_name AS teacher FROM term_comments tc LEFT JOIN profiles p ON p.account_id = tc.written_by
             WHERE tc.student_id = :id AND tc.year = :year AND tc.term = :term'
        );
        $statement->execute(['id' => $studentId, 'year' => $year, 'term' => $term]);

        return $statement->fetch() ?: null;
    }
}
