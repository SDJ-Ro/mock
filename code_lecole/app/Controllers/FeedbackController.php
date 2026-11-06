<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\FeedbackModel;
use App\Models\TermMarkModel;
use Core\Controller;
use Core\Validator;

// The Feedback pages and their forms. Who may do what: config/permissions.php 'feedback'; kinds, audiences, lengths:
// config/feedback.php.
//   the teacher's Feedback Channel: everything they wrote (who it went to, read / unread) + "New Feedback"
//   the parent's / the student's Teacher Feedback: what was written for them (FeedbackModel decides by the audience)
//   save: the class teacher writes to one student of their class this year (one-way: no replies)
//   read: the student / a parent opened a card (feedback-card.js sends it in the background) → its read mark
// The search box and the type filter are a normal GET form: the cards are picked here.
class FeedbackController extends Controller
{
    // GET /teacher/feedback?q=…&type=positive
    public function teacher(): void
    {
        $user  = $this->requireRole('teacher');
        $model = new FeedbackModel();
        $cards = array_map(fn (array $row): array => $this->card($row) + ['to' => $this->sentTo($row), 'readUrl' => null],
            $model->sentBy((int) $user['id']));

        // New Feedback's choices: the students of their class this year (none → no New Feedback button)
        $students = array_map(fn (array $student): array => [
            'value' => (string) $student['id'],
            'label' => "{$student['name']} ({$student['code']}) - " . word('grade') . " {$student['class_name']}",
        ], can('feedback.write') ? $model->students((int) $user['id']) : []);

        $this->page('teacher/feedback', $user, $this->board($cards) + [
            'students'  => $students,
            'subjects'  => $this->subjects((int) $user['id']),
            'audiences' => config('feedback.audiences'),
        ]);
    }

    // GET /parent/feedback?q=…&type=negative — the child the portal is showing (the sidebar's "Viewing")
    public function parent(): void
    {
        $user  = $this->requireRole('parent');
        $rows  = $user['child'] === null ? [] : (new FeedbackModel())->forParent((int) $user['child']);
        $cards = array_map(fn (array $row): array => $this->card($row) + [
            'to'      => '',
            'readUrl' => $row['parent_read_at'] === null ? '/parent/feedback/read' : null,
        ], $rows);

        $this->page('parent/feedback', $user, $this->board($cards));
    }

    // GET /student/feedback?q=…&type=positive
    public function student(): void
    {
        $user  = $this->requireRole('student');
        $cards = array_map(fn (array $row): array => $this->card($row) + [
            'to'      => '',
            'readUrl' => $row['student_read_at'] === null ? '/student/feedback/read' : null,
        ], (new FeedbackModel())->forStudent((int) $user['id']));

        $this->page('student/feedback', $user, $this->board($cards));
    }

    // POST /teacher/feedback/save (student, audience, type, subject, title, content) — New Feedback. Back to the page.
    public function save(): never
    {
        $user    = $this->requireRole('teacher');
        $back    = $this->previousPage();
        $lengths = config('feedback.lengths');

        if (!can('feedback.write'))
        {
            $this->refuse('You may not write feedback.', $back, 403);   // the class check comes below
        }

        // One longer than allowed is kept, so "too long" is refused with a message instead of being cut quietly
        $sent = [
            'student'  => $this->postText('student', 11),
            'audience' => $this->postText('audience', 10),
            'type'     => $this->postText('type', 20),
            'subject'  => $this->postText('subject', $lengths['subject']),
            'title'    => $this->postText('title', $lengths['title'] + 1),
            'content'  => $this->postText('content', $lengths['message'] + 1),
        ];

        $errors = Validator::check($sent, [
            'student'  => 'required',
            'audience' => 'required|in:' . implode(',', array_keys(config('feedback.audiences'))),
            'type'     => 'required|in:' . implode(',', array_keys(config('feedback.types'))),
            'subject'  => 'required',
            'title'    => "required|max:{$lengths['title']}",
            'content'  => "required|max:{$lengths['message']}",
        ], ['student' => word('student'), 'type' => 'Feedback category', 'title' => 'Feedback title / topic', 'content' => 'Feedback content']);

        if ($errors !== [])
        {
            $this->refuse(reset($errors), $back, 422);
        }

        // Only their own class's students this year, as New Feedback lists them: an unknown id and someone else's
        // student get the same answer
        $model    = new FeedbackModel();
        $students = array_column($model->students((int) $user['id']), 'name', 'id');
        $student  = (int) $sent['student'];

        if (!isset($students[$student]))
        {
            $this->refuse('That ' . strtolower(word('student')) . ' is not in your ' . strtolower(word('class')) . '.', $back, 404);
        }

        if (!in_array($sent['subject'], $this->subjects((int) $user['id']), true))
        {
            $this->refuse(word('subject') . ' must be one of the choices.', $back, 422);
        }

        $id = $model->add([
            'student_id' => $student,
            'teacher_id' => (int) $user['id'],
            'kind'       => $sent['type'],
            'audience'   => $sent['audience'],
            'subject'    => $sent['subject'],
            'title'      => $sent['title'],
            'message'    => $sent['content'],
        ]);

        $this->log($user, 'FEEDBACK_SENT', "#{$id} {$sent['title']} ({$sent['type']}, to {$sent['audience']}, " . strtolower(word('student')) . " #{$student})");
        $this->setFlash('notice', "Feedback sent to {$students[$student]}.");
        $this->redirect($back);
    }

    // POST /student/feedback/read, /parent/feedback/read (id) — in the background, when a card is opened.
    // Only feedback this person may read (FeedbackModel::find() decides): someone else's, a parent with a
    // student-only one, a student with a parent-only one and an unknown id all get the same answer.
    public function read(string $role): never
    {
        $user       = $this->requireRole($role);
        $model      = new FeedbackModel();
        $studentIds = $role === 'parent' ? array_keys($this->children($user)) : [(int) $user['id']];
        $feedback   = can('feedback.read') ? $model->find((int) $this->postText('id', 11), $role, $studentIds) : null;

        if ($feedback === null)
        {
            $this->refuse('That feedback does not exist.', "/{$role}/feedback", 404);
        }

        $model->markRead((int) $feedback['id'], $role);
        $this->sendJson(['ok' => true, 'message' => '']);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // One feedback row → what _feedback_card.php shows (+ 'to' and 'readUrl', added by each page)
    private function card(array $row): array
    {
        return [
            'id'      => (int) $row['id'],
            'type'    => $row['kind'],
            'title'   => $row['title'],
            'subject' => $row['subject'],
            'teacher' => $row['teacher_name'],
            'student' => $row['student_name'],
            'date'    => substr($row['created_at'], 0, 10),
            'text'    => $row['message'],
        ];
    }

    // The teacher's line on their own card: who it went to, and read / unread for each reader
    // ("To Nethmi Perera · Student: Read · Parent: Unread")
    private function sentTo(array $row): string
    {
        $read  = fn (?string $at): string => $at === null ? 'Unread' : 'Read';
        $parts = [$row['student_name']];

        if ($row['audience'] !== 'parent')
        {
            $parts[] = word('student') . ': ' . $read($row['student_read_at']);
        }

        if ($row['audience'] !== 'student')
        {
            $parts[] = word('parent') . ': ' . $read($row['parent_read_at']);
        }

        return 'To ' . implode(' · ', $parts);
    }

    // New Feedback's Subject choices: the subjects of the teacher's class's curriculum now, then the general one
    private function subjects(int $teacherId): array
    {
        $marks = new TermMarkModel();
        $class = $marks->classOf($teacherId);

        return [...($class === null ? [] : array_values($marks->subjectsFor((int) $class['grade_number']))), config('feedback.general')];
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
            && ($search === '' || str_contains(mb_strtolower("{$card['title']} {$card['subject']} {$card['teacher']} {$card['student']} {$card['text']}"), $search)));

        return ['filters' => $filters, 'types' => $types, 'feedback' => array_values($cards)];
    }
}
