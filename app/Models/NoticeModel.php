<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// The notice board (the notices + notice_reads tables). Who may SEE a notice is decided in one place: visibility().
// Who may CHANGE one: can_manage() (config/permissions.php 'posts'). The rules (owner, 2026-10-21):
// a notice for a class / grade / club reaches only that group's people in its audience; expired notices are gone for
// everyone; scheduled and waiting notices show only to admin, management and their author; deleting removes it
// (the audit log keeps the history).
class NoticeModel extends Model
{
    // Everything a card needs: the author's name, the class / grade / club it is for, and whether it is live yet
    private const SELECT = "SELECT n.*, a.full_name AS author_name, c.name AS class_name, g.sort_order AS grade_number, x.name AS extracurricular_name,
               IF(n.publish_at > NOW(), 'scheduled', 'live') AS state
        FROM notices n
        LEFT JOIN profiles a ON a.account_id = n.author_id
        LEFT JOIN classes c ON c.id = n.class_id
        LEFT JOIN grades g ON g.id = n.grade_id
        LEFT JOIN extracurriculars x ON x.id = n.extracurricular_id";

    // ---------------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------------

    // Every notice this user may see: pinned live ones first, then the newest
    public function board(array $user): array
    {
        return $this->find($user, '1 = 1', []);
    }

    // The live notices this user hasn't opened the board for yet (the student / parent dashboards' "Unread Notices")
    public function unread(array $user): array
    {
        return $this->find($user, 'n.publish_at <= NOW() AND NOT EXISTS
            (SELECT 1 FROM notice_reads r WHERE r.notice_id = n.id AND r.account_id = :reader)', ['reader' => $user['id']]);
    }

    // A club / sport's live notices this user may see (its page's Notice Board): pinned first, then the newest
    public function forExtracurricular(array $user, int $extracurricularId): array
    {
        return $this->find($user, 'n.extracurricular_id = :extracurricular AND n.publish_at <= NOW()', ['extracurricular' => $extracurricularId]);
    }

    // One notice, only if this user may see it
    public function findFor(array $user, int $id): ?array
    {
        return $this->find($user, 'n.id = :id', ['id' => $id])[0] ?? null;
    }

    // Live, approved pinned notices (the limit counts only these); $exceptId = the notice being changed
    public function countPinned(?int $exceptId = null): int
    {
        $statement = $this->db->prepare("SELECT COUNT(*) FROM notices WHERE pinned = 1 AND id <> :except AND review = 'approved'
            AND publish_at <= NOW() AND (expires_at IS NULL OR expires_at > NOW())");
        $statement->execute(['except' => $exceptId ?? 0]);

        return (int) $statement->fetchColumn();
    }

    // ---------------------------------------------------------------------
    // Changing
    // ---------------------------------------------------------------------

    // Remember that this person has seen these notices (opening the board counts; only the first time is kept)
    public function markRead(array $noticeIds, int $accountId): void
    {
        $insert = $this->db->prepare('INSERT IGNORE INTO notice_reads (notice_id, account_id) VALUES (:notice, :account)');

        foreach ($noticeIds as $noticeId)
        {
            $insert->execute(['notice' => $noticeId, 'account' => $accountId]);
        }
    }

    // Adds ($id = null) or updates a notice → its id. $notice's keys come from NoticeController (a fixed list), never the form.
    public function save(?int $id, array $notice): int
    {
        if ($id === null)
        {
            $columns = implode(', ', array_keys($notice));
            $holders = ':' . implode(', :', array_keys($notice));
            $this->db->prepare("INSERT INTO notices ({$columns}) VALUES ({$holders})")->execute($notice);

            return (int) $this->db->lastInsertId();
        }

        $sets = implode(', ', array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($notice)));
        $this->db->prepare("UPDATE notices SET {$sets} WHERE id = :id")->execute($notice + ['id' => $id]);

        return $id;
    }

    public function setPinned(int $id, bool $pinned): void
    {
        $this->db->prepare('UPDATE notices SET pinned = :pinned WHERE id = :id')->execute(['pinned' => (int) $pinned, 'id' => $id]);
    }

    // Approve or reject a waiting notice → false if it isn't waiting any more (someone decided first)
    public function review(int $id, bool $approve, ?string $note, int $reviewerId): bool
    {
        $statement = $this->db->prepare("UPDATE notices SET review = :review, review_note = :note, reviewed_by = :by, reviewed_at = NOW()
            WHERE id = :id AND review = 'pending'");
        $statement->execute(['review' => $approve ? 'approved' : 'rejected', 'note' => $note, 'by' => $reviewerId, 'id' => $id]);

        return $statement->rowCount() === 1;
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM notices WHERE id = :id')->execute(['id' => $id]);   // its reads go with it (ON DELETE CASCADE)
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // The notices this user may see that also match $where: pinned live ones first, then the newest
    private function find(array $user, string $where, array $values): array
    {
        [$visible, $visibleValues] = $this->visibility($user);

        $statement = $this->db->prepare(self::SELECT . " WHERE {$visible} AND {$where}
            ORDER BY (n.pinned = 1 AND n.publish_at <= NOW()) DESC, n.publish_at DESC, n.id DESC");
        $statement->execute($visibleValues + $values);

        return $statement->fetchAll();
    }

    // The WHERE part that decides who sees what → [sql, values]. An expired notice: nobody. A rejected one: only its author.
    // Admin and management ('see_all'): every other notice, also scheduled and waiting ones.
    // Everyone else: approved, live notices with their role in the audience, for the whole school or for one of their
    // groups (class, grade, club); a poster ('notices.post', a teacher) also sees their own posts in any state.
    // (A model doesn't read the session, so it asks the permission list with the user it was given, not can().)
    private function visibility(array $user): array
    {
        $notExpired = '(n.expires_at IS NULL OR n.expires_at > NOW())';

        if (in_array($user['role'], config('permissions.notices.see_all'), true))
        {
            return ["({$notExpired} AND (n.review <> 'rejected' OR n.author_id = :me))", ['me' => $user['id']]];
        }

        $in   = (new Membership())->idListsOf($user);   // ['class' => '9', 'grade' => '5', 'extracurricular' => '2']
        $live = "n.review = 'approved' AND n.publish_at <= NOW() AND FIND_IN_SET(:role, n.audience)
                 AND (COALESCE(n.class_id, n.grade_id, n.extracurricular_id) IS NULL
                      OR n.class_id IN ({$in['class']}) OR n.grade_id IN ({$in['grade']}) OR n.extracurricular_id IN ({$in['extracurricular']}))";
        $own  = in_array($user['role'], config('permissions.notices.post'), true) ? ' OR n.author_id = ' . (int) $user['id'] : '';

        return ["({$notExpired} AND (({$live}){$own}))", ['role' => $user['role']]];
    }
}
