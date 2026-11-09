<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CalendarModel;
use App\Models\NoticeModel;
use Core\Controller;
use Core\Upload;
use Core\Validator;

// The notice board, for every role (addresses: config/notices.php). Everyone reads; admin, management and teachers
// also post, pin, edit and delete (config/permissions.php 'notices.post', 'posts' for where and whose). Teachers' notices
// wait for the admin's Approve / Reject on the admin's board ('Awaiting Approval' tab; permissions 'notices.wait_for_approval').
// Every action is a normal form: the server checks it, saves it and shows the board again with a message at its top.
// The post / edit form is the same page with ?post or ?edit=5 (components/_add_notice_page.php).
// A notice's attachment (PDF, Word, JPG, PNG) is saved in storage/uploads/notices — not in public/, so it is opened only
// through attachment(), which asks the same "may this person see the notice?" as the board (Core\Upload::document()).
class NoticeController extends Controller
{
    private const RULES = [
        'title'      => 'required|min:3|max:200',
        'body'       => 'required|min:10|max:5000',
        'category'   => 'required|max:30',
        'publish_at' => 'date',
        'expires_at' => 'date',
    ];

    // ---------------------------------------------------------------------
    // The board (and the form)
    // ---------------------------------------------------------------------

    // GET the board. Posters get the form instead with ?post (new) or ?edit=5. Opening the board marks its notices read.
    public function board(string $role): void
    {
        $user      = $this->requireRole($role);
        $board     = config("notices.board.{$role}");
        $canPost   = can('notices.post');
        $wantsForm = $canPost && (isset($_GET['post']) || $this->queryText('edit') !== '');   // ?post has no value: only "is it there?"
        $editing   = $wantsForm && $this->queryText('edit') !== '' ? $this->manageable($user, (int) $this->queryText('edit')) : null;
        $notices   = $wantsForm ? [] : array_map(fn (array $notice): array => $this->card($notice, $user), (new NoticeModel())->board($user));

        if (!$wantsForm)
        {
            (new NoticeModel())->markRead(array_column(array_filter($notices, fn (array $n): bool => $n['state'] === 'live'), 'id'), $user['id']);
        }

        $this->page("{$role}/notice", $user, [
            'boardUrl'       => $board['url'],
            'subtitle'       => words($board['subtitle']),
            'searchText'     => $board['search'],
            'notices'        => $notices,
            'noticeForm'     => $wantsForm ? $this->form($user, $role, $editing) : null,
            'canPost'        => $canPost,
            'tabs'           => in_array($role, config('notices.tabs'), true),   // For Me / Posted by Me
            'waitingCount'   => $this->approves() ? count(array_filter($notices, fn (array $n): bool => $n['review'] === 'pending')) : null,
            'audienceFilter' => $canPost ? config("notices.audience.{$role}") : null,
            'categories'     => config('notices.categories.admin'),               // the filter lists every category
        ]);
    }

    // ---------------------------------------------------------------------
    // Saving, pinning, deleting (POST, posters only: routes/web.php)
    // ---------------------------------------------------------------------

    // POST {board}/save (no id = a new notice)
    public function save(string $role): never
    {
        $user     = $this->requireRole($role);
        $id       = (int) $this->postText('id') ?: null;
        $existing = $id === null ? null : $this->manageable($user, $id);
        $values   = $this->typedValues($existing);
        $notice   = $this->checked($user, $role, $values, $existing);   // back to the form with the reason, or the row to save

        // A waiting role's new notice or edit waits (again); anyone else's edit keeps the notice's state. Only approved ones stay pinned.
        if ($this->waits())
        {
            $notice += ['review' => 'pending', 'review_note' => null, 'reviewed_by' => null, 'reviewed_at' => null];
        }
        elseif ($existing === null)
        {
            $notice += ['review' => 'approved'];
        }

        if (($notice['review'] ?? $existing['review']) !== 'approved')
        {
            $notice['pinned'] = 0;
        }

        if ($existing === null)
        {
            $notice += ['author_id' => $user['id'], 'author_role' => $role];
        }

        // The attachment: a new file replaces the old one (saved only now, when everything else is right)
        [$file, $fileName, $fileError] = Upload::document($_FILES['attachment'] ?? [], 'notices', (int) (config('notices.max_file_mb') * 1024));

        if ($fileError !== null)
        {
            $this->setFlash('notice_form', $values);
            $this->refuse($fileError, config("notices.board.{$role}.url") . ($existing === null ? '?post' : "?edit={$existing['id']}"), 422);
        }

        if ($file !== null)
        {
            $notice += ['attachment_file' => $file, 'attachment_name' => $fileName];
        }

        $id = (new NoticeModel())->save($id, $notice);

        if ($file !== null && $existing !== null)
        {
            Upload::forgetDocument($existing['attachment_file'], 'notices');
        }

        $this->log($user, $existing === null ? 'NOTICE_POSTED' : 'NOTICE_UPDATED', "#{$id} {$notice['title']}");

        $this->done($role, $existing === null ? 'Notice published.' : 'Notice updated.');
    }

    // POST {board}/pin — pins or unpins (at most config('notices.max_pinned') live pinned notices)
    public function pin(string $role): never
    {
        $user   = $this->requireRole($role);
        $notice = $this->manageable($user, (int) $this->postText('id'));
        $pin    = !$notice['pinned'];

        if ($notice['review'] !== 'approved')
        {
            $this->refuse('Only approved notices can be pinned.', config("notices.board.{$role}.url"), 422);
        }

        if ($pin && (new NoticeModel())->countPinned((int) $notice['id']) >= config('notices.max_pinned'))
        {
            $this->refuse('Maximum of ' . config('notices.max_pinned') . ' notices can be pinned at a time. Please unpin an older notice first.',
                config("notices.board.{$role}.url"), 422);
        }

        (new NoticeModel())->setPinned((int) $notice['id'], $pin);
        $this->log($user, $pin ? 'NOTICE_PINNED' : 'NOTICE_UNPINNED', "#{$notice['id']} {$notice['title']}");

        $this->done($role, $pin ? 'Notice pinned.' : 'Notice unpinned.');
    }

    // POST {board}/delete — removes it for good (the activity log keeps who deleted what, and when)
    public function delete(string $role): never
    {
        $user   = $this->requireRole($role);
        $notice = $this->manageable($user, (int) $this->postText('id'));

        (new NoticeModel())->delete((int) $notice['id']);
        Upload::forgetDocument($notice['attachment_file'], 'notices');
        $this->log($user, 'NOTICE_DELETED', "#{$notice['id']} {$notice['title']}");

        $this->done($role, 'Notice deleted.');
    }

    // GET {board}/attachment?id=5 — downloads the notice's file, only for someone who may see the notice (a waiting or
    // scheduled notice's file: only those who see the notice itself). Always a download, never shown by the browser.
    public function attachment(string $role): void
    {
        $user   = $this->requireRole($role);
        $notice = (new NoticeModel())->findFor($user, (int) $this->queryText('id'));
        $path   = $notice === null ? null : Upload::documentPath($notice['attachment_file'], 'notices');

        if ($path === null)
        {
            $this->refuse('That file is no longer available.', config("notices.board.{$role}.url"), 404);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $shown     = str_replace(['"', "\r", "\n"], '', (string) $notice['attachment_name']);

        header('Content-Type: ' . Upload::DOCUMENT_DOWNLOAD_TYPES[$extension]);
        header('Content-Disposition: attachment; filename="' . addcslashes(preg_replace('/[^\x20-\x7E]/', '_', $shown), '\\') . '"; filename*=UTF-8\'\'' . rawurlencode($shown));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($path);

        exit;
    }

    // POST {board}/review (approvers only: routes/web.php) — Approve / Reject a waiting notice; a reason is needed to reject
    // (its author sees it on their card)
    public function review(string $role): never
    {
        $user  = $this->requireRole($role);
        $board = config("notices.board.{$role}.url");

        $notice  = (new NoticeModel())->findFor($user, (int) $this->postText('id'));
        $decision = $this->postText('decision', 10);
        $note     = $this->postText('note', 255);

        if ($notice === null)
        {
            $this->refuse('That notice no longer exists.', $board, 404);
        }

        if (!in_array($decision, ['approve', 'reject'], true))
        {
            $this->refuse('Please choose Approve or Reject.', $board, 422);
        }

        if ($decision === 'reject' && $note === '')
        {
            $this->refuse('Please write a short reason, so the author knows what to change.', $board, 422);
        }

        $approve = $decision === 'approve';

        if (!(new NoticeModel())->review((int) $notice['id'], $approve, $approve ? null : $note, $user['id']))
        {
            $this->refuse('That notice was already decided.', $board, 409);
        }

        $this->log($user, $approve ? 'NOTICE_APPROVED' : 'NOTICE_REJECTED', "#{$notice['id']} {$notice['title']}" . ($approve ? '' : ": {$note}"));

        $this->done($role, $approve ? 'Notice approved.' : 'Notice rejected.');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // Does this person's notice wait for approval? / May this person approve? (both only while feature('approvals') is on)
    private function waits(): bool
    {
        return feature('approvals') && can('notices.wait_for_approval');
    }

    private function approves(): bool
    {
        return feature('approvals') && can('notices.approve');
    }

    // A notice this person may change: can't see it → "no longer exists", may see but not change it → refused
    private function manageable(array $user, int $id): array
    {
        $board  = config("notices.board.{$user['role']}.url");
        $notice = (new NoticeModel())->findFor($user, $id);

        if ($notice === null)
        {
            $this->refuse('That notice no longer exists.', $board, 404);
        }

        if (!can_manage($notice))
        {
            $this->refuse('You can only change your own notices.', $board, 403);
        }

        return $notice;
    }

    // One board row → what a card needs besides the row: may I change it / approve it, did I write it, is it for my role
    private function card(array $notice, array $user): array
    {
        return $notice + [
            'canManage' => can_manage($notice),
            'canReview' => $notice['review'] === 'pending' && $this->approves(),
            'isMine'    => (int) $notice['author_id'] === (int) $user['id'],
            'isForMe'   => in_array($user['role'], explode(',', $notice['audience']), true),
        ];
    }

    // What the form shows: what was just typed (after an error), the notice being edited, or a new notice's defaults
    private function form(array $user, string $role, ?array $editing): array
    {
        $typed    = $this->getFlash('notice_form');
        $anyGroup = can('posts.any_group');   // else only their own classes and clubs: nothing is chosen for them

        return [
            'id'        => $editing['id'] ?? null,
            'values'    => $typed ?? ($editing !== null ? $this->savedValues($editing, $role) : [
                'title' => '', 'body' => '', 'category' => config("notices.categories.{$role}")[0], 'audience' => ['all'],
                'target' => $anyGroup ? 'all' : '', 'extracurricular' => $anyGroup ? 'all' : '',
                'publish_at' => '', 'expires_at' => '', 'pinned' => false, 'attachment_name' => null,
            ]),
            'choices'   => [
                'categories' => config("notices.categories.{$role}"),
                'audience'   => config("notices.audience.{$role}"),
                'schoolWide' => $anyGroup,
            ] + (new CalendarModel())->choicesFor($user),   // grades / classes / extracurriculars this person may post to
            'banner'    => $this->waits()
                ? 'Your notice appears on the central board once an administrator approves it.'
                : 'Notices appear immediately on the central board for the selected audience.',
            'canPin'    => !$this->waits() && ($editing['review'] ?? 'approved') === 'approved',
            'error'     => $typed !== null ? $this->getFlash('error') : null,
            'maxFileMb' => config('notices.max_file_mb'),
        ];
    }

    // A saved notice → the form's values ('all' when it is for every role this poster may write to)
    private function savedValues(array $notice, string $role): array
    {
        $everyone = array_values(array_diff(config("notices.audience.{$role}"), ['all']));
        $audience = explode(',', $notice['audience']);

        return [
            'title'           => $notice['title'],
            'body'            => $notice['body'],
            'category'        => $notice['category'],
            'audience'        => array_diff($everyone, $audience) === [] ? ['all'] : $audience,
            'target'          => $notice['class_id'] !== null ? "class:{$notice['class_id']}" : ($notice['grade_id'] !== null ? "grade:{$notice['grade_id']}" : 'all'),
            'extracurricular' => $notice['extracurricular_id'] !== null ? (string) $notice['extracurricular_id'] : 'all',
            'publish_at'      => substr($notice['publish_at'], 0, 10),
            'expires_at'      => $notice['expires_at'] !== null ? substr($notice['expires_at'], 0, 10) : '',
            'pinned'          => (bool) $notice['pinned'],
            'attachment_name' => $notice['attachment_name'],
        ];
    }

    // The form as it was sent, in the form's own shape (shown again if something is wrong)
    private function typedValues(?array $existing): array
    {
        return [
            'title'           => $this->postText('title', 200),
            'body'            => $this->postText('body', 5000),
            'category'        => $this->postText('category', 30),
            'audience'        => $this->postList('audience'),
            'target'          => $this->postText('target', 20),
            'extracurricular' => $this->postText('extracurricular', 20),
            'publish_at'      => $this->postText('publish_at', 10),
            'expires_at'      => $this->postText('expires_at', 10),
            'pinned'          => $this->postText('pinned') === '1',
            'attachment_name' => $existing['attachment_name'] ?? null,   // a file can't be put back in the box: show the one it has
        ];
    }

    // Every rule → the row to save. Anything wrong → back to the form with the reason + what was typed.
    private function checked(array $user, string $role, array $values, ?array $existing): array
    {
        $choices = (new CalendarModel())->choicesFor($user);
        $offered = config("notices.audience.{$role}");
        $today   = date('Y-m-d');
        $wasDay  = $existing !== null ? substr($existing['publish_at'], 0, 10) : null;

        $errors   = array_values(Validator::check($values, self::RULES, [
            'title' => 'Notice title', 'body' => 'Notice body', 'publish_at' => 'Publish date', 'expires_at' => 'Expiration date',
        ]));
        $category = $values['category'];

        if (!in_array($category, config("notices.categories.{$role}"), true))
        {
            $errors[] = 'Please choose a category from the list.';
        }

        // Who it is for: 'all' = every role this poster may write to
        $picked   = array_values(array_intersect($values['audience'], $offered));
        $audience = in_array('all', $picked, true) ? array_values(array_diff($offered, ['all'])) : $picked;

        if (in_array('management', $values['audience'], true) && !in_array('management', $offered, true))
        {
            $errors[] = $role === 'management'
                ? words('{Management} cannot post notices to {Management}. Please select {Teachers}, {Parents}, or {Students}.')
                : 'Please choose the audience from the list.';
        }
        elseif ($audience === [])
        {
            $errors[] = 'Please choose who the notice is for.';
        }

        // Which group: Academic → everyone / a grade / a class; Extracurricular → every club / one club; others → everyone.
        // Only what the form offered this person (a teacher: their own classes and clubs, never "everyone").
        [$type, $groupId] = array_pad(explode(':', $category === 'Academic' ? $values['target'] : ($category === 'Extracurricular' ? "extracurricular:{$values['extracurricular']}" : 'all')), 2, null);
        $groupId  = $groupId === 'all' ? null : $groupId;
        $offeredGroups = ['grade' => $choices['grades'], 'class' => $choices['classes'], 'extracurricular' => $choices['extracurriculars']];
        $schoolWide    = $type === 'all' || ($type === 'extracurricular' && $groupId === null);

        if ($schoolWide ? !can('posts.any_group') : !isset($offeredGroups[$type][(int) $groupId]))
        {
            $errors[] = !can('posts.any_group')
                ? 'Please choose one of your ' . strtolower(plural('class')) . ' or clubs.'
                : 'Please choose who the notice is for from the list.';
        }

        // Dates: publishing from today (an edited notice may keep its own day); expiry on or after the publish day
        $publishDay = $values['publish_at'] !== '' ? $values['publish_at'] : ($wasDay ?? $today);

        if ($publishDay < $today && $publishDay !== $wasDay)
        {
            $errors[] = 'The publish date can\'t be in the past.';
        }

        if ($values['expires_at'] !== '' && $values['expires_at'] < max($publishDay, $today))
        {
            $errors[] = 'Notice expiration date must be later than the publish date.';
        }

        if ($values['pinned'] && !($existing['pinned'] ?? false)
            && (new NoticeModel())->countPinned($existing['id'] ?? null) >= config('notices.max_pinned'))
        {
            $errors[] = 'Maximum of ' . config('notices.max_pinned') . ' notices can be pinned at a time. Please unpin an older notice first.';
        }

        if ($errors !== [])
        {
            $this->setFlash('notice_form', $values);
            $this->refuse($errors[0], config("notices.board.{$role}.url") . ($existing === null ? '?post' : "?edit={$existing['id']}"), 422);
        }

        return [
            'title'              => $values['title'],
            'body'               => $values['body'],
            'category'           => $category,
            'audience'           => implode(',', $audience),
            'class_id'           => $type === 'class' ? (int) $groupId : null,
            'grade_id'           => $type === 'grade' ? (int) $groupId : null,
            'extracurricular_id' => $type === 'extracurricular' && $groupId !== null ? (int) $groupId : null,
            'publish_at'         => $publishDay === $wasDay ? $existing['publish_at'] : ($publishDay === $today ? date('Y-m-d H:i:s') : "{$publishDay} 00:00:00"),
            'expires_at'         => $values['expires_at'] !== '' ? "{$values['expires_at']} 23:59:59" : null,
            'pinned'             => (int) $values['pinned'],
        ];
    }

    // Success: back to the board with the message at its top
    private function done(string $role, string $message): never
    {
        $this->setFlash('notice', $message);
        $this->redirect(config("notices.board.{$role}.url"));
    }
}
