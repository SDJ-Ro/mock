<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\UserModel;
use App\Models\VerificationCode;
use App\Services\AccountEmails;
use Core\Controller;
use Core\Mailer;

class AuthController extends Controller
{
    // Every failed 6-digit code (sign-up and reset): wrong, used up or expired — one message, so it never tells anything apart
    private const CODE_FAILED = 'That code is wrong or has expired. Check the newest email, or ask for a new code.';

    // Who's signing in?
    public function chooser(): void
    {
        $this->redirectIfSignedIn();

        $this->view('auth/chooser', [
            'portals' => config('portals'),
            'notice'  => $this->getFlash('notice'),
        ]);
    }

    // Sign-in page per role
    public function signin(string $role): void
    {
        $this->redirectIfSignedIn();

        $portals = config('portals');
        if (!isset($portals[$role]))
        {
            $this->redirect('/auth');
        }

        // ?reset=1 → the forgot-password pop-up is on its 2nd step (code + new password) and its errors show in there
        $isResetStep = isset($_GET['reset']);

        $this->view('auth/signin', [
            'role'        => $role,
            'portal'      => $portals[$role],
            'isResetStep' => $isResetStep,
            'notice'      => $this->getFlash('notice'),
            'error'       => $isResetStep ? null : $this->getFlash('error'),
            'resetError'  => $isResetStep ? $this->getFlash('error') : null,
            'errorLink'   => $this->getFlash('error_link'),
            'identifier'  => $this->getFlash('identifier') ?? $this->queryText('identifier'),
            'csrfField'   => $this->csrfField(),
        ]);
    }

    // POST /auth/{role} → check credentials and sign in
    public function handleSignin(string $role): never
    {
        $identifier = $this->postText('identifier');
        $password   = $this->postPassword('password');
        $back       = "/auth/{$role}";
        $keep       = ['identifier' => $identifier];   // the form shows the typed ID again after an error

        $users   = new UserModel();
        $log     = new AuditLog();
        $account = $users->findByIdentifier($identifier);

        // An unknown ID, an account not activated yet, a wrong password AND a paused account all get this SAME answer, so
        // the page never tells a stranger which IDs exist (only a real one could ever say "locked"). It names the rule and
        // the way out: "Forgot Password?" clears the pause (UserModel::resetPassword()), so someone locking a person out on
        // purpose gains nothing. The link helps a new person who hasn't activated their account yet.
        $wrong = fn (): never => $this->fail($back, 'Incorrect ID or password. After ' . config('app.login.max_attempts')
            . ' wrong tries, sign-in for that ID pauses for ' . config('app.login.lock_minutes')
            . ' minutes — use "Forgot Password?" to get in straight away.', $keep + ['error_link' => [
            'href'  => "/auth/{$role}/signup?identifier=" . urlencode($identifier),
            'label' => 'New here? Activate your account',
        ]]);

        // 1. Unknown account, or not activated yet (it has no password to check)
        if ($account === null || $account['activation_status'] === 'PENDING')
        {
            $log->record('LOGIN_FAILED', $account === null ? null : (int) $account['id'], $identifier,
                ($account === null ? 'Unknown ID' : 'Not activated yet') . " ({$role} portal)");
            $wrong();
        }

        $id = (int) $account['id'];

        // 2. Paused from too many failed attempts (checked BEFORE the password, so nobody can keep guessing)
        if ($account['minutes_locked'] > 0)
        {
            $log->record('LOGIN_FAILED', $id, $account['identifier'], "Paused ({$account['minutes_locked']} min left, {$role} portal)");
            $wrong();
        }

        // 3. Wrong password (the 5th one starts the pause — the same answer still)
        if (!password_verify($password, (string) $account['password_hash']))
        {
            $triesLeft = $users->recordFailedLogin($account);
            $log->record('LOGIN_FAILED', $id, $account['identifier'], "Wrong password ({$triesLeft} tries left)");
            $wrong();
        }

        // From here on the person knew the password, so it is safe to say what is wrong with the account

        // 4. Locked by admin
        if ($account['locked_at'] !== null)
        {
            $this->fail($back, 'This account is locked. Please contact ' . school('support_email') . '.', $keep);
        }

        // 5. Deactivated account
        if ($account['activation_status'] === 'INACTIVE')
        {
            $this->fail($back, 'This account has been deactivated. Please contact ' . school('support_email') . '.', $keep);
        }

        // 6. Role mismatch
        if ($account['role'] !== $role)
        {
            $label = config("portals.{$account['role']}.label");
            $log->record('LOGIN_WRONG_PORTAL', $id, $account['identifier'], "{$account['role']} account tried the {$role} portal");

            $this->fail($back, "This is a {$label} account.", $keep + ['error_link' => [
                'href'  => "/auth/{$account['role']}?identifier=" . urlencode($account['identifier']),
                'label' => "Go to {$label} Sign In",
            ]]);
        }

        // 7. Login success
        $users->recordSuccessfulLogin($id);
        $log->record('LOGIN_SUCCESS', $id, $account['identifier'], "{$role} portal");

        $this->startSession($account);
        $this->redirect("/{$role}");
    }

    // -------------------------------------------------------------------------
    // Sign-up / Account Activation
    // -------------------------------------------------------------------------

    // GET /auth/{role}/signup
    public function signup(string $role): void
    {
        $this->redirectIfSignedIn();

        $portals = config('portals');
        if (!isset($portals[$role]) || empty($portals[$role]['can_signup']))
        {
            $this->redirect('/auth');
        }

        if ($this->queryText('restart') === '1')
        {
            unset($_SESSION['activation']);
        }

        $pending = ($_SESSION['activation']['role'] ?? null) === $role ? $_SESSION['activation'] : null;

        $this->view('auth/signup', [
            'role'       => $role,
            'portal'     => $portals[$role],
            'step'       => $pending ? 'code' : 'details',
            'sentTo'     => $pending['sent_to'] ?? '',
            'identifier' => $this->getFlash('identifier') ?? ($this->queryText('identifier') ?: ($pending['identifier'] ?? '')),
            'fullName'   => $this->getFlash('full_name') ?? $this->queryText('name'),
            'error'      => $this->getFlash('error'),
            'notice'     => $this->getFlash('notice'),
            'csrfField'  => $this->csrfField(),
        ]);
    }

    // POST /auth/{role}/signup → Step 1: validate details, email 6-digit code
    public function handleSignup(string $role): never
    {
        $back       = "/auth/{$role}/signup";
        $fullName   = $this->postText('full_name');
        $identifier = $this->postText('identifier');
        $password   = $this->postPassword('password');

        $this->setFlash('full_name', $fullName);
        $this->setFlash('identifier', $identifier);

        // Validate password rules
        $problems = UserModel::passwordProblems($password);
        if (!empty($problems))
        {
            $this->fail($back, 'Password needs ' . implode(', ', $problems) . '.');
        }

        $users   = new UserModel();
        $account = $users->findByIdentifier($identifier);
        $support = school('support_email');

        // Check if account exists, belongs to this role, and name matches records
        if ($account === null || $account['role'] !== $role || !$users->nameMatches((int) $account['id'], $fullName))
        {
            $this->fail($back, "Those details don't match an account waiting to be activated. Check your name and ID, or contact {$support}.");
        }

        if ($account['activation_status'] === 'ACTIVE')
        {
            $this->fail($back, 'This account is already active. Please sign in instead.');
        }

        if ($account['activation_status'] === 'INACTIVE')
        {
            $this->fail($back, "This account has been deactivated. Please contact {$support}.");
        }

        // Store pending activation data in session (account not changed until code is verified)
        $_SESSION['activation'] = [
            'account_id'    => (int) $account['id'],
            'identifier'    => $account['identifier'],
            'role'          => $role,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ];

        // A code sent less than a minute ago still works: no new email, so sending the form again can't flood the inbox
        $justSent = (new VerificationCode())->secondsUntilResend((int) $account['id'], VerificationCode::ACTIVATE) > 0;
        $email    = $justSent ? $users->emailFor($account) : $this->emailCode($account, VerificationCode::ACTIVATE);
        if ($email === null)
        {
            $this->fail($back, "There's no email address on file for this account. Please contact {$support}.");
        }

        $_SESSION['activation']['sent_to'] = $this->maskEmail($email)
            . ($account['role'] === 'student' ? ' (your ' . strtolower(word('parent')) . "'s email)" : '');

        $this->redirect("/auth/{$role}/signup");
    }

    // POST /auth/{role}/verify → Step 2: verify 6-digit code and activate account
    public function handleActivate(string $role): never
    {
        $back    = "/auth/{$role}/signup";
        $pending = $_SESSION['activation'] ?? null;

        if (!$pending || $pending['role'] !== $role)
        {
            $this->redirect("/auth/{$role}/signup?restart=1");
        }

        $code   = $this->postText('code', 10);
        $result = (new VerificationCode())->check((int) $pending['account_id'], VerificationCode::ACTIVATE, $code);

        // A wrong code and a used-up / expired one: one answer (the same as the reset form's)
        if ($result !== 'ok')
        {
            $this->fail($back, self::CODE_FAILED);
        }

        $users = new UserModel();
        if (!$users->activate((int) $pending['account_id'], $pending['password_hash']))
        {
            unset($_SESSION['activation']);
            $this->fail("/auth/{$role}", 'This account is already active. Please sign in.');
        }

        unset($_SESSION['activation']);

        $account = $users->findByIdentifier($pending['identifier']);
        (new AuditLog())->record('ACCOUNT_ACTIVATED', (int) $account['id'], $account['identifier'], "{$role} portal");

        // Automatically sign them in
        $this->startSession($account);

        $this->setFlash('notice', 'Welcome! Your account has been activated.');
        $this->redirect("/{$role}");
    }

    // POST /auth/{role}/resend → Resend verification code
    public function resendActivation(string $role): never
    {
        $back    = "/auth/{$role}/signup";
        $pending = $_SESSION['activation'] ?? null;

        if (!$pending || $pending['role'] !== $role)
        {
            $this->redirect("/auth/{$role}/signup?restart=1");
        }

        $codes = new VerificationCode();
        $wait  = $codes->secondsUntilResend((int) $pending['account_id'], VerificationCode::ACTIVATE);

        if ($wait > 0)
        {
            $this->fail($back, "Please wait {$wait} seconds before asking for another code.");
        }

        $account = (new UserModel())->findByIdentifier($pending['identifier']);
        $this->emailCode($account, VerificationCode::ACTIVATE);

        $this->setFlash('notice', 'A fresh verification code was sent to your email.');
        $this->redirect("/auth/{$role}/signup");
    }

    // -------------------------------------------------------------------------
    // Forgot Password & Reset
    // -------------------------------------------------------------------------

    // POST /auth/{role}/forgot → Send password reset code
    // Every ID gets the same message and the same next step, so nobody can use this form to find out which IDs exist.
    public function handleForgot(string $role): never
    {
        $identifier = $this->postText('identifier');
        $account    = (new UserModel())->findByIdentifier($identifier);
        $canReset   = $account !== null && $account['role'] === $role && $account['activation_status'] === 'ACTIVE' && $account['locked_at'] === null;

        // A real account gets a code — at most one a minute, so nobody can flood its inbox
        if ($canReset && (new VerificationCode())->secondsUntilResend((int) $account['id'], VerificationCode::RESET) === 0)
        {
            $this->emailCode($account, VerificationCode::RESET);
        }

        $_SESSION['forgot'] = [
            'account_id' => $canReset ? (int) $account['id'] : 0,   // 0 = no such account: every code typed is "incorrect"
            'identifier' => $canReset ? $account['identifier'] : $identifier,
            'role'       => $role,
        ];
        $this->setFlash('notice', "If an active account exists for {$identifier}, a 6-digit reset code was sent to its email.");
        $this->redirect("/auth/{$role}?reset=1");
    }

    // POST /auth/{role}/reset → Verify reset code and set new password
    public function handleReset(string $role): never
    {
        $back     = "/auth/{$role}?reset=1";
        $forgot   = $_SESSION['forgot'] ?? null;
        $code     = $this->postText('code', 10);
        $password = $this->postPassword('password');

        if (!$forgot || $forgot['role'] !== $role)
        {
            $this->redirect("/auth/{$role}");
        }

        $problems = UserModel::passwordProblems($password);
        if (!empty($problems))
        {
            $this->fail($back, 'Password needs ' . implode(', ', $problems) . '.');
        }

        // No such account (0) is never 'ok'. A wrong code and a used-up / expired one get the SAME answer: a made-up
        // account could only ever say "wrong", so "expired" would tell a stranger the account is real.
        $result = $forgot['account_id'] === 0
            ? 'wrong'
            : (new VerificationCode())->check($forgot['account_id'], VerificationCode::RESET, $code);

        if ($result !== 'ok')
        {
            $this->fail($back, self::CODE_FAILED);
        }

        $users = new UserModel();

        if ($users->resetPassword((int) $forgot['account_id'], password_hash($password, PASSWORD_DEFAULT)))
        {
            AccountEmails::passwordChanged(['id' => $forgot['account_id'], 'role' => $role, 'identifier' => $forgot['identifier']], 'with a reset code');
        }

        (new AuditLog())->record('PASSWORD_RESET', (int) $forgot['account_id'], $forgot['identifier'], "{$role} portal");
        unset($_SESSION['forgot']);

        $this->setFlash('notice', 'Your password has been reset. Please sign in.');
        $this->redirect("/auth/{$role}?identifier=" . urlencode($forgot['identifier']));
    }

    // POST /logout → sign out
    public function logout(): never
    {
        $user = $this->getUser();

        if ($user !== null)
        {
            (new AuditLog())->record('LOGOUT', $user['id'], $user['identifier']);
            $this->endSession();
            $this->setFlash('notice', "You've been signed out.");
            $this->redirect("/auth/{$user['role']}");
        }

        $this->redirect('/auth');
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function redirectIfSignedIn(): void
    {
        $user = $this->getUser();
        if ($user !== null)
        {
            $this->redirect("/{$user['role']}");
        }
    }

    // Back to the form with a red message. $keep = other values the form shows again ('identifier' = the typed ID,
    // 'error_link' = a help link under the message)
    private function fail(string $back, string $error, array $keep = []): never
    {
        $this->setFlash('error', $error);

        foreach ($keep as $key => $value)
        {
            $this->setFlash($key, $value);
        }

        $this->redirect($back);
    }

    private function emailCode(array $account, string $purpose): ?string
    {
        $users = new UserModel();
        $email = $users->emailFor($account);

        if ($email === null)
        {
            return null;
        }

        $codes   = new VerificationCode();
        $code    = $codes->create((int) $account['id'], $purpose);
        $profile = $users->findProfile((int) $account['id']);
        $name    = $profile['first_name'] ?? $account['identifier'];

        $isReset = $purpose === VerificationCode::RESET;
        $subject = school('name') . ($isReset ? " — Password Reset Code: {$code}" : " — Account Activation Code: {$code}");
        $reason  = $isReset
            ? 'We received a request to reset your password. Use the code below to complete the reset.'
            : 'Thank you for activating your account. Enter this 6-digit verification code to complete activation.';

        Mailer::send($email, $subject, 'code', [
            'name'    => $name,
            'reason'  => $reason,
            'code'    => $code,
            'minutes' => config('app.codes.expire_minutes', 15),
        ]);

        return $email;
    }

    // Remember who signed in. Their first name and photo are kept too, so every page's sidebar has them
    // without asking the database again (the Profile page must update them when they change).
    private function startSession(array $account): void
    {
        $profile = (new UserModel())->findProfile((int) $account['id']);

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => (int) $account['id'],
            'identifier' => $account['identifier'],
            'role'       => $account['role'],
            'first_name' => $profile['first_name'] ?? '',
            'photo'      => $profile['photo_url'] ?? null,
        ];
        $_SESSION['last_activity'] = time();
    }

    private function maskEmail(string $email): string
    {
        [$user, $domain] = explode('@', $email, 2);
        $visible = mb_substr($user, 0, min(2, mb_strlen($user)));
        return $visible . '***@' . $domain;
    }
}
