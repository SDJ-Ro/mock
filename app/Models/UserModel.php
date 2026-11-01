<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class UserModel extends Model
{
    // Find an account by email or student index.
    // Also calculates how many minutes of lock are left.
    public function findByIdentifier(string $identifier): ?array
    {
        $statement = $this->db->prepare(
            'SELECT *, COALESCE(CEIL(TIMESTAMPDIFF(SECOND, NOW(), lock_expires_at) / 60), 0) AS minutes_locked
             FROM user_accounts
             WHERE identifier = :identifier
             LIMIT 1'
        );
        $statement->execute(['identifier' => $this->normalise($identifier)]);

        return $statement->fetch() ?: null;
    }

    // Wrong password: count it, lock the account when the limit is reached. Returns tries left.
    public function recordFailedLogin(array $account): int
    {
        $max = config('app.login.max_attempts');

        // An old lock that has run out means they start counting from zero again
        $failed = ($account['lock_expires_at'] === null ? (int) $account['failed_login_count'] : 0) + 1;

        $statement = $this->db->prepare(
            'UPDATE user_accounts
             SET failed_login_count = :failed,
                 lock_expires_at = IF(:reached, NOW() + INTERVAL :minutes MINUTE, NULL)
             WHERE id = :id'
        );
        $statement->execute([
            'failed'  => $failed,
            'reached' => (int) ($failed >= $max),
            'minutes' => config('app.login.lock_minutes'),
            'id'      => $account['id'],
        ]);

        return max(0, $max - $failed);
    }

    // Right password: clear the counter and remember when they signed in
    public function recordSuccessfulLogin(int $accountId): void
    {
        $statement = $this->db->prepare(
            'UPDATE user_accounts
             SET failed_login_count = 0, lock_expires_at = NULL, last_login_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $accountId]);
    }

    // May this signed-in account still use the site? Not when it was switched off or locked by the admin since it signed in.
    // (A lock from wrong passwords doesn't count: someone else's guessing must not sign the real person out.)
    public function canStaySignedIn(int $accountId): bool
    {
        $statement = $this->db->prepare(
            "SELECT 1 FROM user_accounts WHERE id = :id AND activation_status = 'ACTIVE' AND locked_at IS NULL"
        );
        $statement->execute(['id' => $accountId]);

        return $statement->fetchColumn() !== false;
    }

    // The person's name and personal email (from profiles table)
    public function findProfile(int $accountId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT first_name, last_name, full_name, personal_email, photo_url FROM profiles WHERE account_id = :id'
        );
        $statement->execute(['id' => $accountId]);

        return $statement->fetch() ?: null;
    }

    // Does the typed name match the school record? (ignores case and extra spaces)
    public function nameMatches(int $accountId, string $typed): bool
    {
        $profile = $this->findProfile($accountId);
        $tidy    = fn (string $name): string => preg_replace('/\s+/', ' ', mb_strtolower(trim($name)));

        return $profile !== null && $tidy($typed) === $tidy($profile['full_name']);
    }

    // Save the new password and switch a PENDING account to ACTIVE
    public function activate(int $accountId, string $passwordHash): bool
    {
        $statement = $this->db->prepare(
            "UPDATE user_accounts
             SET password_hash = :hash, activation_status = 'ACTIVE', activated_at = NOW(),
                 failed_login_count = 0, lock_expires_at = NULL
             WHERE id = :id AND activation_status = 'PENDING'"
        );
        $statement->execute(['hash' => $passwordHash, 'id' => $accountId]);

        return $statement->rowCount() === 1;
    }

    // Forgot password: save the new password and clear any wrong-try lock
    public function resetPassword(int $accountId, string $passwordHash): bool
    {
        $statement = $this->db->prepare(
            "UPDATE user_accounts
             SET password_hash = :hash, failed_login_count = 0, lock_expires_at = NULL, first_login_required = 0
             WHERE id = :id AND activation_status = 'ACTIVE' AND locked_at IS NULL"
        );
        $statement->execute(['hash' => $passwordHash, 'id' => $accountId]);

        return $statement->rowCount() === 1;
    }

    // Replace the temporary password the user signed in with
    public function changePassword(int $accountId, string $passwordHash): void
    {
        $statement = $this->db->prepare(
            'UPDATE user_accounts SET password_hash = :hash, first_login_required = 0 WHERE id = :id'
        );
        $statement->execute(['hash' => $passwordHash, 'id' => $accountId]);
    }

    // Where this person's emails go:
    // Students -> their primary parent's email
    // Others -> personal email, else their login email
    public function emailFor(array $account): ?string
    {
        if ($account['role'] === 'student')
        {
            $statement = $this->db->prepare(
                'SELECT COALESCE(profile.personal_email, parent.identifier)
                 FROM student_parents AS link
                 JOIN user_accounts AS parent ON parent.id = link.parent_account_id
                 LEFT JOIN profiles AS profile ON profile.account_id = parent.id
                 WHERE link.student_account_id = :id
                 ORDER BY link.is_primary DESC
                 LIMIT 1'
            );
            $statement->execute(['id' => $account['id']]);

            return $statement->fetchColumn() ?: null;
        }

        $profile = $this->findProfile((int) $account['id']);

        return $profile['personal_email'] ?? (filter_var($account['identifier'], FILTER_VALIDATE_EMAIL) ?: null);
    }

    // The profiles columns updateProfile() may change (column names in SQL only ever come from this list)
    private const PROFILE_COLUMNS = ['first_name', 'last_name', 'date_of_birth', 'gender', 'national_id', 'personal_email',
                                     'phone', 'address', 'emergency_name', 'emergency_phone'];

    // Update personal details in profiles table: ['phone' => '…', 'address' => '…']. Any other key is ignored.
    public function updateProfile(int $accountId, array $fields): void
    {
        $fields = array_intersect_key($fields, array_flip(self::PROFILE_COLUMNS));

        if ($fields === [])
        {
            return;
        }

        $sets = implode(', ', array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($fields)));
        $this->db->prepare("UPDATE profiles SET {$sets} WHERE account_id = :id")->execute($fields + ['id' => $accountId]);
    }

    // Set profile photo URL
    public function setPhoto(int $accountId, ?string $url): void
    {
        $this->db->prepare('UPDATE profiles SET photo_url = :url WHERE account_id = :id')->execute(['url' => $url, 'id' => $accountId]);
    }

    // Password strength check (empty array = password passed all rules)
    public static function passwordProblems(string $password): array
    {
        $missing = [];

        foreach (config('app.password_rules') as [$pattern, $needs])
        {
            if (!preg_match($pattern, $password))
            {
                $missing[] = $needs;
            }
        }

        return $missing;
    }

    // Normalizes identifier (e.g. "stu-2026-1" -> "STU-2026-0001", emails -> lowercase).
    // The student ID's first part ("STU") comes from config/white_label/academic.php ('STU-{year}-{number}').
    private function normalise(string $identifier): string
    {
        $identifier = trim($identifier);
        $prefix     = strtok(config('white_label.academic.id_formats.student'), '-');

        if (preg_match('/^' . preg_quote($prefix, '/') . '-?(\d{4})-?(\d+)$/i', $identifier, $match))
        {
            return sprintf('%s-%s-%04d', $prefix, $match[1], (int) $match[2]);
        }

        return strtolower($identifier);
    }
}
