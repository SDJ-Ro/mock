<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// One inquiry and one answer per complaint. The controller checks roles and configured form choices.
class ComplaintModel extends Model
{
    private const SELECT = 'SELECT c.*, parent.full_name AS parent_name, student.full_name AS student_name,
            cl.name AS class_name, grade.sort_order AS grade_number,
            resolver.full_name AS resolver_name
        FROM complaints c
        JOIN profiles parent ON parent.account_id = c.parent_id
        JOIN profiles student ON student.account_id = c.student_id
        LEFT JOIN current_class_students placement ON placement.student_id = c.student_id
        LEFT JOIN classes cl ON cl.id = placement.class_id
        LEFT JOIN grades grade ON grade.id = cl.grade_id
        LEFT JOIN profiles resolver ON resolver.account_id = c.resolved_by';

    // A parent's inquiries for the currently selected child, newest first.
    public function forParent(int $parentId, int $studentId): array
    {
        $statement = $this->db->prepare(self::SELECT . ' WHERE c.parent_id = :parent AND c.student_id = :student
            ORDER BY c.created_at DESC, c.id DESC');
        $statement->execute(['parent' => $parentId, 'student' => $studentId]);

        return $statement->fetchAll();
    }

    // Management sees every inquiry. Filters are applied by the controller after fetching the board.
    public function forManagement(): array
    {
        return $this->db->query(self::SELECT . ' ORDER BY c.created_at DESC, c.id DESC')->fetchAll();
    }

    // A selected child must belong to the signed-in parent, including at the moment of submission.
    public function isChildOf(int $parentId, int $studentId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM student_parents
            WHERE parent_account_id = :parent AND student_account_id = :student');
        $statement->execute(['parent' => $parentId, 'student' => $studentId]);

        return $statement->fetchColumn() !== false;
    }

    // The controller supplies validated text; management receives every new inquiry on its board.
    public function submit(int $parentId, int $studentId, string $category, string $subject, string $body): int
    {
        $statement = $this->db->prepare('INSERT INTO complaints
            (parent_id, student_id, category, subject, body)
            SELECT :parent, :student, :category, :subject, :body
            FROM student_parents WHERE parent_account_id = :linked_parent AND student_account_id = :linked_student');
        $statement->execute(['parent' => $parentId, 'student' => $studentId,
            'category' => $category, 'subject' => $subject, 'body' => $body,
            'linked_parent' => $parentId, 'linked_student' => $studentId]);

        return $statement->rowCount() === 1 ? (int) $this->db->lastInsertId() : 0;
    }

    // Only the first resolution wins; a second attempt leaves the saved answer unchanged.
    public function resolve(int $id, string $response, int $resolverId): bool
    {
        $statement = $this->db->prepare("UPDATE complaints SET status = 'resolved', response = :response,
            resolved_by = :resolver, resolved_at = NOW() WHERE id = :id AND status = 'in_progress'");
        $statement->execute(['response' => $response, 'resolver' => $resolverId, 'id' => $id]);

        return $statement->rowCount() === 1;
    }
}
