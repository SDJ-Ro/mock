<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

// Handles one-time 6-digit codes in the verification_codes table
class VerificationCode extends Model
{
    public const ACTIVATE = 'activate';
    public const RESET    = 'reset';

    // Generates a 6-digit code, hashes it, stores it in DB, and returns the raw 6-digit number to email to the user
    public function create(int $accountId, string $purpose): string
    {
        $code = (string) random_int(100000, 999999);

        // Delete any existing codes for this user and purpose
        $this->db->prepare('DELETE FROM verification_codes WHERE account_id = :account AND purpose = :purpose')
            ->execute(['account' => $accountId, 'purpose' => $purpose]);

        // Insert new hashed code
        $this->db->prepare(
            'INSERT INTO verification_codes (account_id, purpose, code_hash, attempts_left, expires_at)
             VALUES (:account, :purpose, :hash, :attempts, NOW() + INTERVAL :minutes MINUTE)'
        )->execute([
            'account'  => $accountId,
            'purpose'  => $purpose,
            'hash'     => password_hash($code, PASSWORD_DEFAULT),   // stored safely hashed
            'attempts' => config('app.codes.max_attempts'),
            'minutes'  => config('app.codes.expire_minutes'),
        ]);

        return $code;
    }

    // Check a typed code -> 'ok' (the code is used up), 'wrong' (one try fewer), or 'expired'
    public function check(int $accountId, string $purpose, string $typed): string
    {
        $statement = $this->db->prepare(
            'SELECT id, code_hash, attempts_left FROM verification_codes
             WHERE account_id = :account AND purpose = :purpose AND used_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1'
        );
        $statement->execute(['account' => $accountId, 'purpose' => $purpose]);
        $row = $statement->fetch();

        if ($row === false || $row['attempts_left'] <= 0) 
        {
            return 'expired';
        }

        if (!password_verify(trim($typed), $row['code_hash'])) 
        {
            $this->db->prepare('UPDATE verification_codes SET attempts_left = attempts_left - 1 WHERE id = :id')
                ->execute(['id' => $row['id']]);

            return 'wrong';
        }

        $this->db->prepare('UPDATE verification_codes SET used_at = NOW() WHERE id = :id')
            ->execute(['id' => $row['id']]);

        return 'ok';
    }

    // Seconds left before the user is permitted to request another code (cooldown)
    public function secondsUntilResend(int $accountId, string $purpose): int
    {
        $statement = $this->db->prepare(
            'SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS age
             FROM verification_codes
             WHERE account_id = :account AND purpose = :purpose
             ORDER BY id DESC LIMIT 1'
        );
        $statement->execute(['account' => $accountId, 'purpose' => $purpose]);
        $age = $statement->fetchColumn();

        if ($age === false)
        {
            return 0;
        }

        return max(0, config('app.codes.resend_seconds') - (int) $age);
    }
}
