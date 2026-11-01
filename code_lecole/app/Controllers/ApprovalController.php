<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ExtracurricularModel;
use Core\Controller;

// Parent → Approvals: a parent says yes or no before their child joins a club or sport.
// The requests are real (extracurricular_members, ExtracurricularModel).
// The three number cards are links (?status=pending) and the server shows that status; Approve / Decline are plain forms.
class ApprovalController extends Controller
{
    // The three number cards: status → [label, card colour, icon]
    private const STATUSES = [
        'pending'  => ['Awaiting Your Decision', 'sunshine', 'clock'],
        'approved' => ['Approved by You', 'moss', 'checkCircle2'],
        'declined' => ['Declined by You', 'maroon', 'close'],
    ];

    // GET /parent/approvals?status=approved
    public function index(): void
    {
        $user     = $this->requireRole('parent');
        $status   = array_key_exists($this->queryText('status'), self::STATUSES) ? $this->queryText('status') : 'pending';
        // Only the child the portal is showing (the sidebar's "Viewing")
        $requests = array_map([$this, 'card'], array_filter((new ExtracurricularModel())->forParent($user['id']),
            fn (array $row): bool => (int) $row['student_id'] === $user['child']));
        $tabs     = [];

        foreach (self::STATUSES as $key => [$label, $color, $icon])
        {
            $tabs[] = [
                'label'  => $label,
                'color'  => $color,
                'icon'   => $icon,
                'value'  => count(array_filter($requests, fn (array $request): bool => $request['status'] === $key)),
                'href'   => "/parent/approvals?status={$key}",
                'chosen' => $key === $status,
            ];
        }

        $this->page('parent/approvals', $user, [
            'subtitle' => words('Review consent requests from the {institution} — approve or decline club and sport enrolments for ')
                . ($this->children($user)[$user['child']] ?? words('your {student}')) . '.',
            'tabs'     => $tabs,
            'status'   => $status,
            'requests' => array_values(array_filter($requests, fn (array $request): bool => $request['status'] === $status)),
        ]);
    }

    // POST /parent/approvals/answer (extracurricular, student, answer = approve | decline, note = the optional reason for no)
    public function answer(): never
    {
        $user              = $this->requireRole('parent');
        $back              = '/parent/approvals';
        $extracurricularId = (int) $this->postText('extracurricular');
        $studentId         = (int) $this->postText('student');
        $answer            = $this->postText('answer', 10);

        if (!in_array($answer, ['approve', 'decline'], true))
        {
            $this->refuse('Please choose Approve or Decline.', $back, 422);
        }

        // Only a request for one of THEIR children that still waits (the same message for one that never existed)
        $request = array_values(array_filter((new ExtracurricularModel())->forParent($user['id']),
            fn (array $row): bool => (int) $row['extracurricular_id'] === $extracurricularId && (int) $row['student_id'] === $studentId && $row['status'] === 'parent'))[0] ?? null;
        $yes     = $answer === 'approve';
        $note    = $yes ? null : ($this->postText('note') ?: null);

        if ($request === null || !(new ExtracurricularModel())->answer($extracurricularId, $studentId, $user['id'], $yes, $note))
        {
            $this->refuse('That request no longer needs your answer.', $back, 409);
        }

        $this->log($user, $yes ? 'PARENT_APPROVED' : 'PARENT_DECLINED', "{$request['student_name']} → {$request['extracurricular_name']}" . ($note ? ": {$note}" : ''));

        $this->setFlash('notice', $yes
            ? 'Request approved.' . ($request['requested_by'] === 'student' ? " {$request['student_name']}" . words(' now waits for the {teacher} in charge to accept.') : '')
            : 'Request declined.');
        $this->redirect($back . ($yes ? '?status=approved' : '?status=declined'));
    }

    // One request → what the approval card shows. A no from the teacher in charge stays under "Approved by You" (the
    // parent said yes), with the teacher's reason.
    private function card(array $row): array
    {
        $byTeacher = $row['declined_by'] === 'teacher';
        $status    = match (true)
        {
            $row['status'] === 'parent'                  => 'pending',
            $row['status'] === 'declined' && !$byTeacher => 'declined',
            default                                      => 'approved',   // 'teacher' (waits for the teacher in charge), 'member', or the teacher's no
        };

        return [
            'extracurricular_id' => $row['extracurricular_id'],
            'student_id'         => $row['student_id'],
            'status'             => $status,
            'title'              => $row['extracurricular_name'],
            'meta'               => implode(' · ', array_filter([
                $row['teacher_name'] !== null ? words('{Teacher} in charge: ') . $row['teacher_name'] : null,
                $row['schedule'],
            ])),
            'description'        => ($row['requested_by'] === 'student'
                    ? "{$row['student_name']} asked to join."
                    : words('The {institution} would like ') . "{$row['student_name']} to join.")
                . ($row['description'] ? " {$row['description']}" : ''),
            'from'               => $row['requested_by'] === 'student' ? $row['student_name'] : ($row['teacher_name'] ?? school('name')),
            'date'               => date(country('date_format'), strtotime($row['parent_answered_at'] ?? $row['updated_at'])),
            'note'               => $byTeacher ? $row['teacher_note'] : $row['parent_note'],
            'noteLabel'          => $byTeacher ? words('Not accepted by the {teacher} in charge') : 'Your reason for declining',
            'student'            => $row['student_name'],
        ];
    }
}
