<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// The audit log (audit_log): every sign-in and every change — who, what, when, from where.
// Written by Controller::log() and AuthController; read by the admin's Audit Log page (AuditController).
class AuditLog extends Model
{
    // The newest events that match the filters, newest first, with the person's role and name from their account
    // (never guessed from the text of the ID). $filters: action ('' = all), role ('' = all, 'system' = no
    // account), q (words in the ID, name, details or address). The controller only passes values from fixed lists.
    public function recent(array $filters, int $limit): array
    {
        $where  = [];
        $values = [];

        if ($filters['action'] !== '')
        {
            $where[]          = 'l.action = :action';
            $values['action'] = $filters['action'];
        }

        if ($filters['role'] === 'system')
        {
            $where[] = 'u.id IS NULL';
        }
        elseif ($filters['role'] !== '')
        {
            $where[]        = 'u.role = :role';
            $values['role'] = $filters['role'];
        }

        if ($filters['q'] !== '')
        {
            $like    = '%' . addcslashes($filters['q'], '%_\\') . '%';   // the typed % and _ are letters, not wildcards
            $where[] = '(l.identifier LIKE :q1 OR p.full_name LIKE :q2 OR l.details LIKE :q3 OR l.ip_address LIKE :q4)';
            $values += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }

        $statement = $this->db->prepare(
            'SELECT l.id, l.identifier, l.action, l.details, l.created_at, u.role, p.full_name
             FROM audit_log l
             LEFT JOIN user_accounts u ON u.id = l.account_id
             LEFT JOIN profiles p ON p.account_id = l.account_id'
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY l.id DESC LIMIT ' . $limit
        );
        $statement->execute($values);

        return $statement->fetchAll();
    }

    // Every kind of action that has happened (the activity filter's list)
    public function actions(): array
    {
        return $this->db->query('SELECT DISTINCT action FROM audit_log ORDER BY action')->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function record(string $action, ?int $accountId, ?string $identifier, string $details = ''): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO audit_log (account_id, identifier, action, ip_address, user_agent, details)
             VALUES (:account, :identifier, :action, :ip, :agent, :details)'
        );
        $statement->execute([
            'account'    => $accountId,
            'identifier' => $identifier,
            'action'     => $action,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'agent'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'details'    => $details,
        ]);
    }
}
