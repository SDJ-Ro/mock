<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// The final grade's yearly graduation date, set by admin on Academic. Once it arrives, that year's students enter
// management's certificate queue. Class membership is read for the saved year, so a later school-year change does
// not change who belonged to the batch.
class GraduationBatchModel extends Model
{
    // This year's setting, if the admin has saved one.
    public function current(): ?array
    {
        $statement = $this->db->prepare(
            'SELECT b.school_year, b.grade_id, g.sort_order AS grade_number, b.graduation_date, b.set_by
             FROM graduation_batches b JOIN grades g ON g.id = b.grade_id WHERE b.school_year = :year'
        );
        $statement->execute(['year' => (new SchoolYear())->current()]);

        return $statement->fetch() ?: null;
    }

    // Save this year's date for the configured final grade. Once any certificate is issued, the batch cannot change.
    // False means the final grade has not been created, or that year's certificates have already been issued.
    public function saveCurrent(string $date, int $adminId): bool
    {
        $year = (new SchoolYear())->current();
        $grade = $this->db->prepare('SELECT id FROM grades WHERE sort_order = :number');
        $grade->execute(['number' => config('white_label.academic.grade_numbers.last')]);
        $gradeId = $grade->fetchColumn();

        if ($year === 0 || $gradeId === false)
        {
            return false;
        }

        return $this->transaction(function () use ($year, $gradeId, $date, $adminId): bool
        {
            $batch = $this->db->prepare('SELECT school_year FROM graduation_batches WHERE school_year = :year FOR UPDATE');
            $batch->execute(['year' => $year]);
            $batch->fetch();
            $issued = $this->db->prepare("SELECT COUNT(*) FROM certificates WHERE school_year = :year AND status = 'issued'");
            $issued->execute(['year' => $year]);

            if ((int) $issued->fetchColumn() > 0)
            {
                return false;
            }

            $save = $this->db->prepare(
                'INSERT INTO graduation_batches (school_year, grade_id, graduation_date, set_by)
                 VALUES (:year, :grade, :date, :admin)
                 ON DUPLICATE KEY UPDATE grade_id = VALUES(grade_id), graduation_date = VALUES(graduation_date),
                                         set_by = VALUES(set_by)'
            );
            $save->execute(['year' => $year, 'grade' => $gradeId, 'date' => $date, 'admin' => $adminId]);

            return true;
        });
    }

    // Students in a graduating batch whose date has arrived. Earlier unfinished batches remain visible after year-end.
    // No certificate row is created merely by viewing the queue; CertificateModel handles each student's draft.
    public function dueStudents(): array
    {
        $statement = $this->db->prepare(
            "SELECT s.student_id, p.full_name, COALESCE(p.code, u.identifier) AS code,
                    c.name AS class_name, b.school_year, b.graduation_date,
                    COALESCE(cert.status, 'pending') AS status,
                    (SELECT COUNT(*) FROM missing_record_requests r
                     WHERE r.student_id = s.student_id AND r.status = 'pending') AS pending_count
             FROM graduation_batches b
             JOIN class_students s ON s.year = b.school_year
             JOIN classes c ON c.id = s.class_id AND c.grade_id = b.grade_id
             JOIN profiles p ON p.account_id = s.student_id
             JOIN user_accounts u ON u.id = s.student_id
             LEFT JOIN certificates cert ON cert.school_year = b.school_year AND cert.student_id = s.student_id
             WHERE b.graduation_date <= :today
             ORDER BY b.school_year DESC, c.name, p.full_name, s.student_id"
        );
        $statement->execute(['today' => date('Y-m-d')]);

        return $statement->fetchAll();
    }
}
