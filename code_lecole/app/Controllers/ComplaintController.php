<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;

// The Inquiries & Complaints pages: the parent's (their inquiries + "New Inquiry") and management's (every inquiry,
// "Mark as Resolved"). The data is still example data (app/Mock/complaints.php) and sending or resolving
// saves nothing yet; only the pages are drawn here. The search box and the two filters are a normal GET form.
class ComplaintController extends Controller
{
    // GET /parent/complaints?q=…&category=Transport&status=resolved
    public function parent(): void
    {
        $user = $this->requireRole('parent');
        $mock = $this->mock();
        $mine = array_filter($mock['complaints'], fn (array $complaint): bool => $complaint['parentId'] === $mock['parentId']);

        $this->page('parent/complaints', $user, $this->board($mine) + ['children' => $mock['children']]);
    }

    // GET /management/complaints?q=…&category=…&status=…
    public function management(): void
    {
        $user = $this->requireRole('management');

        $this->page('management/complaints', $user, $this->board($this->mock()['complaints']));
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function mock(): array
    {
        return require dirname(__DIR__) . '/Mock/complaints.php';
    }

    // The filters (only values from the lists are kept) and the inquiries that match them
    private function board(array $complaints): array
    {
        $categories = config('complaints.categories');
        $statuses   = config('complaints.statuses');
        $filters    = [
            'q'        => $this->queryText('q'),
            'category' => array_key_exists($this->queryText('category'), $categories) ? $this->queryText('category') : '',
            'status'   => array_key_exists($this->queryText('status'), $statuses) ? $this->queryText('status') : '',
        ];
        $search     = mb_strtolower($filters['q']);

        $matching = array_filter($complaints, fn (array $complaint): bool =>
            ($filters['category'] === '' || $complaint['category'] === $filters['category'])
            && ($filters['status'] === '' || $complaint['status'] === $filters['status'])
            && ($search === '' || str_contains(mb_strtolower(implode(' ', [$complaint['title'], $complaint['message'], $complaint['category'],
                $complaint['student'], $complaint['parent']])), $search)));

        return ['filters' => $filters, 'categories' => $categories, 'statuses' => $statuses, 'complaints' => array_values($matching)];
    }
}
