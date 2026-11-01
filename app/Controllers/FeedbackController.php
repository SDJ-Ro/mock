<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;

// The Feedback pages: the teacher's Feedback Channel (cards + "New Feedback") and the parent's Teacher Feedback.
// The data is still example data (app/Mock/feedback.php) and "New Feedback" saves nothing yet; only the
// pages are drawn here. The search box and the type filter are a normal GET form: the cards are picked here.
class FeedbackController extends Controller
{
    // GET /teacher/feedback?q=…&type=positive
    public function teacher(): void
    {
        $user = $this->requireRole('teacher');
        $mock = $this->mock();

        $this->page('teacher/feedback', $user, $this->board($mock['teacher']) + [
            'students' => $mock['students'],
            'subjects' => $mock['subjects'],
        ]);
    }

    // GET /parent/feedback?q=…&type=negative
    public function parent(): void
    {
        $user = $this->requireRole('parent');

        $this->page('parent/feedback', $user, $this->board($this->mock()['parent']));
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function mock(): array
    {
        return require dirname(__DIR__) . '/Mock/feedback.php';
    }

    // The filters (only values from the lists are kept) and the cards that match them
    private function board(array $feedback): array
    {
        $types   = config('feedback.types');
        $filters = [
            'q'    => $this->queryText('q'),
            'type' => array_key_exists($this->queryText('type'), $types) ? $this->queryText('type') : '',
        ];
        $search  = mb_strtolower($filters['q']);

        $cards = array_filter($feedback, fn (array $card): bool =>
            ($filters['type'] === '' || $card['type'] === $filters['type'])
            && ($search === '' || str_contains(mb_strtolower("{$card['subject']} {$card['teacher']} {$card['student']} {$card['text']}"), $search)));

        return ['filters' => $filters, 'types' => $types, 'feedback' => array_values($cards)];
    }
}
