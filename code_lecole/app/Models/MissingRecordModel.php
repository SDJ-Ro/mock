<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// A student's missing-record requests (table: missing_record_requests, database update 042). A request is sent with
// its evidence (a private file, core/Upload.php) and goes to the student's CURRENT class teacher: who that is, is
// looked up when it is read (current_class_teachers), never stored. That teacher approves it (it becomes an
// achievement: AchievementModel::add()) or rejects it with a reason. A request stays 'pending' until then.
class MissingRecordModel extends Model
{
    // Saves a new request (its values: record_type, academic_year, title, details, proof_path, proof_name). Returns its id.
    public function send(int $studentId, array $values): int
    {
        $this->db->prepare(
            'INSERT INTO missing_record_requests (student_id, record_type, academic_year, title, details, proof_path, proof_name)
             VALUES (:student, :record_type, :academic_year, :title, :details, :proof_path, :proof_name)'
        )->execute(['student' => $studentId] + $values);

        return (int) $this->db->lastInsertId();
    }

    // One student's pending requests, oldest first (each one a full row)
    public function pendingOf(int $studentId): array
    {
        $statement = $this->db->prepare(
            "SELECT * FROM missing_record_requests WHERE student_id = :student AND status = 'pending' ORDER BY created_at, id"
        );
        $statement->execute(['student' => $studentId]);

        return $statement->fetchAll();
    }

    // The students (account ids) with a pending request that goes to this class teacher now
    public function pendingFor(int $teacherId): array
    {
        return $this->ids(
            "SELECT DISTINCT r.student_id FROM missing_record_requests r
             JOIN current_class_students cs ON cs.student_id = r.student_id
             JOIN current_class_teachers ct ON ct.class_id = cs.class_id
             WHERE ct.teacher_id = :me AND r.status = 'pending'",
            $teacherId
        );
    }

    // One request, or null
    public function find(int $requestId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM missing_record_requests WHERE id = :id');
        $statement->execute(['id' => $requestId]);

        return $statement->fetch() ?: null;
    }

    // Approves a pending request, all or nothing: the achievement (its values, for the request's student, with the
    // request's evidence as its proof) + the request marked approved and linked to it. Returns the achievement's id,
    // or null when the request was answered meanwhile (then nothing changes).
    public function approve(int $requestId, array $values, int $by): ?int
    {
        return $this->transaction(function () use ($requestId, $values, $by): ?int
        {
            $request = $this->find($requestId);

            if (!$this->answer($requestId, 'approved', null, $by))
            {
                return null;
            }

            $achievementId = (new AchievementModel())->add(
                $values + ['proof_path' => $request['proof_path'], 'proof_name' => $request['proof_name']],
                [(int) $request['student_id']],
                $by
            );
            $this->db->prepare('UPDATE missing_record_requests SET achievement_id = :achievement WHERE id = :id')
                ->execute(['achievement' => $achievementId, 'id' => $requestId]);

            return $achievementId;
        });
    }

    // Rejects a pending request with a reason. False when it was answered meanwhile (then nothing changes).
    public function reject(int $requestId, string $reason, int $by): bool
    {
        return $this->answer($requestId, 'rejected', $reason, $by);
    }

    // Marks a request answered, only while it is still pending (so it can't be answered twice). True if it was.
    private function answer(int $requestId, string $status, ?string $reason, int $by): bool
    {
        $statement = $this->db->prepare(
            "UPDATE missing_record_requests SET status = :status, reason = :reason, answered_by = :by, answered_at = NOW()
             WHERE id = :id AND status = 'pending'"
        );
        $statement->execute(['status' => $status, 'reason' => $reason, 'by' => $by, 'id' => $requestId]);

        return $statement->rowCount() === 1;
    }
}
