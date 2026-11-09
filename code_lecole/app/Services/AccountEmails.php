<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;
use Core\Mailer;

// Emails about an account that are not a code or a welcome. Where a person's emails go: UserModel::emailFor()
// (a student's go to the parent). A failed send never stops what the person was doing (Mailer::send() only returns false).
final class AccountEmails
{
    // "Your password was changed" — sent after a password changed, so that someone who did not do it hears about it.
    // $account: id, role, identifier. $how: where it was changed, in a few words ("in My Profile", "with a reset code").
    // Never contains the password.
    public static function passwordChanged(array $account, string $how): bool
    {
        $users = new UserModel();
        $email = $users->emailFor($account);

        if ($email === null)
        {
            return false;
        }

        $profile   = $users->findProfile((int) $account['id']);
        $isStudent = $account['role'] === 'student';

        return Mailer::send($email, school('name') . ' — Your password was changed', 'password_changed', [
            'name'    => $isStudent ? '' : ($profile['first_name'] ?? ''),
            'whose'   => $isStudent ? trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')) . "'s" : 'your',
            'when'    => date(country('full_date_format') . ' \a\t H:i'),
            'how'     => $how,
            'support' => school('support_email'),
        ]);
    }
}
