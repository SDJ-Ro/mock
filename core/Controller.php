<?php

declare(strict_types=1);

namespace Core;

use App\Models\AuditLog;
use App\Models\PeopleModel;
use App\Models\UserModel;
use RuntimeException;

abstract class Controller
{
    // A signed-in parent's children (account id → first name), read once per request (children())
    private ?array $children = null;

    // One line in the activity log: who did what ("NOTICE_DELETED", "#12 Sports Day")
    protected function log(array $user, string $action, string $details): void
    {
        (new AuditLog())->record($action, $user['id'], $user['identifier'], $details);
    }

    protected function view(string $viewName, array $data = []): void
    {
        $viewPath = dirname(__DIR__) . '/app/Views/' . $viewName . '.php';

        if (!is_file($viewPath))
        {
            throw new RuntimeException("View file not found: {$viewName}.php");
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    // A signed-in page: its view plus what every page frame needs (sidebar, forms), so no controller repeats it.
    // $user is what requireRole() returned. The page's own data comes in $data.
    protected function page(string $viewName, array $user, array $data = []): void
    {
        // Never keep a signed-in page in the browser's back / forward cache: after logging out, Back asks the server
        // again (and gets the sign-in page) instead of showing the old page
        header('Cache-Control: no-store');

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        $this->view($viewName, $data + [
            'currentRole' => $user['role'],
            'currentPath' => $path === "/{$user['role']}" ? "/{$user['role']}/dashboard" : $path,   // the role's home = its Dashboard
            'userName'    => $user['first_name'] ?? '',
            'userPhoto'   => $user['photo'] ?? null,
            'csrfField'   => $this->csrfField(),
            'sessionTimeout' => $this->sessionTimeout($user['role']),   // session-timeout.js warns a minute before
            // A parent's children and the one the portal is showing (the sidebar's "Viewing" switch, POST /parent/child)
            'children'    => $user['role'] === 'parent' ? $this->children($user) : [],
            'chosenChild' => $user['child'] ?? null,
            // sidebar.js remembers "collapsed" in a cookie per role, so the page is drawn collapsed from the start (no jump)
            'sidebarCollapsed' => ($_COOKIE["sidebar_{$user['role']}"] ?? '') === 'collapsed',
            // What the last save or refusal left (setFlash 'notice' / 'error') → the sliding banner, drawn by page_bottom.php.
            // A form that shows its error INSIDE the form reads 'error' itself while $data is built (before this line),
            // so it is never shown twice. The rule: docs/HOW-IT-WORKS.md "Telling the person".
            'flashNotice' => $this->getFlash('notice'),
            'flashError'  => $this->getFlash('error'),
        ]);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function setFlash(string $key, mixed $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): mixed
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    protected function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    // The guard: only a signed-in user with this role gets in. Returns that user.
    protected function requireRole(string $role): array
    {
        $user = $this->getUser();

        // Not signed in → to the sign-in page (a background request, e.g. the calendar's pop-up, gets the message as JSON)
        if ($user === null)
        {
            $this->refuse('Please sign in to continue.', "/auth/{$role}", 401, 'notice');
        }

        // Switched off or locked by the office since signing in → signed out now, not when the session runs out
        if (!(new UserModel())->canStaySignedIn((int) $user['id']))
        {
            $this->endSession();
            $this->refuse('This account has been deactivated. Please contact ' . school('support_email') . '.', "/auth/{$role}", 401);
        }

        // Signed in as a different role → to your own dashboard (a background request gets JSON)
        if ($user['role'] !== $role)
        {
            if ($this->wantsJson())
            {
                $this->sendJson(['ok' => false, 'message' => 'That is not part of your account.', 'errors' => []], 403);
            }

            $this->redirect("/{$user['role']}");
        }

        // Idle for too long → sign out (admins sooner than everyone else)
        $timeout = $this->sessionTimeout($role);

        if (time() - ($_SESSION['last_activity'] ?? 0) > $timeout)
        {
            $this->endSession();
            $this->refuse('You were signed out after ' . intdiv($timeout, 60) . ' minutes without activity.', "/auth/{$role}", 401, 'notice');
        }

        $_SESSION['last_activity'] = time();

        // A parent sees one child at a time (the sidebar's "Viewing"): the one they chose, or their first child.
        // It is kept as $user['child'], so every page (and Membership: notices, the calendar) follows that child.
        if ($role === 'parent')
        {
            $children = $this->children($user);

            if (!isset($children[$_SESSION['user']['child'] ?? 0]))
            {
                $_SESSION['user']['child'] = array_key_first($children);   // null: no child linked yet
            }

            $user['child'] = $_SESSION['user']['child'];
        }

        return $user;
    }

    // A parent's children: account id → first name (People links them when a student is added or edited)
    protected function children(array $user): array
    {
        return $this->children ??= (new PeopleModel())->childrenOf($user['id']);
    }

    // How long a role may stay idle before it is signed out, in seconds (config/app.php; the page warns a minute before)
    protected function sessionTimeout(string $role): int
    {
        return config("app.session_timeout.{$role}", config('app.session_timeout.default'));
    }

    // Forget who is signed in, but keep a fresh empty session so flash messages still work
    protected function endSession(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token']))
        {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    protected function csrfField(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e($this->getCsrfToken()) . '">';
    }

    // Called by core/Router.php before EVERY POST action: the form must carry this visitor's token
    // (proves the form came from our own page, not another website). No token / a wrong one → refused.
    public function checkCsrf(): void
    {
        $expected = $_SESSION['_csrf_token'] ?? '';

        // A form bigger than PHP takes (post_max_size) arrives with nothing in it, token included: say why, not "expired"
        if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > ini_bytes('post_max_size'))
        {
            $this->refuse('That file is too big. Please choose a smaller one.', $this->previousPage(), 413);
        }

        if ($expected === '' || !hash_equals($expected, $this->postText('_csrf_token', 100)))
        {
            $this->refuse('Your session expired. Please try again.', $this->previousPage(), 419);
        }
    }

    // Stop here: a background request (it asked for JSON) gets the message as JSON with this status code;
    // a normal page goes to $url with the message ($flash: 'error' = red, 'notice' = the calm blue one)
    protected function refuse(string $message, string $url, int $statusCode, string $flash = 'error'): never
    {
        if ($this->wantsJson())
        {
            $this->sendJson(['ok' => false, 'message' => $message, 'errors' => []], $statusCode);
        }

        $this->setFlash($flash, $message);
        $this->redirect($url);
    }

    // A background request (fetch with Accept: application/json) — it gets JSON back, never a redirect
    protected function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    // The page the form was sent from (only its path + ?query, never another website's address)
    protected function previousPage(): string
    {
        $from = parse_url($_SERVER['HTTP_REFERER'] ?? '/');
        $path = $from['path'] ?? '/';

        // One "/" and then a name, or the home page: a browser reads "//evil.example" or "/\evil.example" as ANOTHER website
        if (!str_starts_with($path, '/') || in_array($path[1] ?? '', ['/', '\\'], true))
        {
            return '/';
        }

        return $path . (isset($from['query']) ? "?{$from['query']}" : '');
    }

    // One text value from the submitted form, cut to $max characters.
    // Anything that isn't plain text (e.g. someone sending "field[]=") becomes '' instead of crashing.
    protected function postText(string $key, int $max = 255): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? trim(mb_substr($value, 0, $max)) : '';
    }

    // One value from the address (?edit=5), like postText(): not plain text → ''
    protected function queryText(string $key, int $max = 100): string
    {
        $value = $_GET[$key] ?? '';

        return is_string($value) ? trim(mb_substr($value, 0, $max)) : '';
    }

    // A password exactly as typed (not trimmed: a space is part of it). Not plain text → ''.
    protected function postPassword(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? mb_substr($value, 0, 255) : '';
    }

    // A multi-choice field (name="grades[]") → its values as a list of texts. Not a list → [].
    protected function postList(string $key): array
    {
        $values = $_POST[$key] ?? [];

        return is_array($values) ? array_values(array_filter($values, 'is_string')) : [];
    }

    protected function sendJson(array $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}