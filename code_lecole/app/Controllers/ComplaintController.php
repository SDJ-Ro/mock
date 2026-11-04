<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ComplaintModel;
use Core\Controller;

// Parent inquiries and management answers. The model owns SQL; the router checks CSRF on every POST.
class ComplaintController extends Controller
{
    public function parent(): void
    {
        $user = $this->requireRole('parent');
        $rows = $user['child'] === null ? [] : (new ComplaintModel())->forParent((int) $user['id'], (int) $user['child']);
        $children = $this->children($user);
        $selectedChild = $children[$user['child']] ?? '';

        $this->page('parent/complaints', $user, $this->board($rows) + ['children' => [$selectedChild]]);
    }

    public function management(): void
    {
        $user = $this->requireRole('management');

        $this->page('management/complaints', $user, $this->board((new ComplaintModel())->forManagement()));
    }

    // The child comes from the session, never from a form field.
    public function send(): never
    {
        $user = $this->requireRole('parent');
        $url  = '/parent/complaints';

        if (!can('complaints.send'))
        {
            $this->refuse('That is not part of your account.', $url, 403);
        }

        $model   = new ComplaintModel();
        $childId = (int) ($user['child'] ?? 0);

        if ($childId === 0 || !$model->isChildOf((int) $user['id'], $childId))
        {
            $this->refuse('That is not part of your account.', $url, 403);
        }

        $subjectMax = config('complaints.limits.subject');
        $bodyMax    = config('complaints.limits.body');
        $category   = $this->postText('category');
        $subject    = $this->postText('subject', $subjectMax + 1);
        $body       = $this->postText('message', $bodyMax + 1);
        if (!array_key_exists($category, config('complaints.categories'))
            || $subject === '' || mb_strlen($subject) > $subjectMax || $body === '' || mb_strlen($body) > $bodyMax)
        {
            $this->refuse('Please check the inquiry fields.', $url, 422);
        }

        $id = $model->submit((int) $user['id'], $childId, $category, $subject, $body);

        if ($id === 0)
        {
            $this->refuse('That is not part of your account.', $url, 403);
        }

        $this->log($user, 'COMPLAINT_SENT', "#{$id}");
        $this->done($url, 'Inquiry submitted.');
    }

    public function resolve(): never
    {
        $user = $this->requireRole('management');
        $url  = '/management/complaints';

        if (!can('complaints.resolve'))
        {
            $this->refuse('That is not part of your account.', $url, 403);
        }

        $responseMax = config('complaints.limits.response');
        $id          = $this->postText('id');
        $response    = $this->postText('response', $responseMax + 1);

        if (!ctype_digit($id) || (int) $id < 1 || $response === '' || mb_strlen($response) > $responseMax)
        {
            $this->refuse('Please check the response.', $url, 422);
        }

        if (!(new ComplaintModel())->resolve((int) $id, $response, (int) $user['id']))
        {
            $this->refuse('This inquiry is unavailable.', $url, 422);
        }

        $this->log($user, 'COMPLAINT_RESOLVED', "#{$id}");
        $this->done($url, 'Inquiry resolved.');
    }

    // Give the existing cards their expected fields and apply the search and filters.
    private function board(array $rows): array
    {
        $categories = config('complaints.categories');
        $statuses   = config('complaints.statuses');
        $filters    = [
            'q'        => $this->queryText('q'),
            'category' => $this->queryText('category'),
            'status'   => $this->queryText('status'),
        ];
        $filters['category'] = array_key_exists($filters['category'], $categories) ? $filters['category'] : '';
        $filters['status']   = array_key_exists($filters['status'], $statuses) ? $filters['status'] : '';
        $search = mb_strtolower($filters['q']);
        $cards  = array_map(fn (array $row): array => $this->card($row), $rows);
        $cards  = array_values(array_filter($cards, fn (array $card): bool =>
            ($filters['category'] === '' || $card['category'] === $filters['category'])
            && ($filters['status'] === '' || $card['status'] === $filters['status'])
            && ($search === '' || str_contains(mb_strtolower(implode(' ', [$card['title'], $card['message'],
                $card['category'], $card['student'], $card['parent']])), $search))));

        return ['filters' => $filters, 'categories' => $categories, 'statuses' => $statuses, 'complaints' => $cards];
    }

    private function card(array $row): array
    {
        return [
            'id'       => (int) $row['id'],
            'title'    => $row['subject'],
            'category' => $row['category'],
            'message'  => $row['body'],
            'status'   => $row['status'],
            'date'     => $row['created_at'],
            'student'  => $row['student_name'],
            'grade'    => $row['class_name'] === null ? '' : word('grade') . ' ' . $row['class_name'],
            'parent'   => $row['parent_name'],
            'response' => $row['response'] ?? '',
        ];
    }

    private function done(string $url, string $message): never
    {
        if ($this->wantsJson())
        {
            $this->sendJson(['ok' => true, 'message' => $message]);
        }

        $this->setFlash('notice', $message);
        $this->redirect($url);
    }
}
