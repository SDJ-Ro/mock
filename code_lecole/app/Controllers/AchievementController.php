<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AchievementModel;
use App\Models\CertificateModel;
use App\Models\ExtracurricularModel;
use App\Models\MissingRecordModel;
use App\Models\PeopleModel;
use App\Services\AchievementTimeline;
use Core\Controller;
use Core\Upload;
use Core\Validator;

// The Achievements pages and their forms. Who may do what: config/permissions.php 'achievements'.
//   the student's page: the four number cards + their whole timeline (AchievementTimeline::forStudent())
//   the teacher's page: their students as cards — their class this year ("Class": Record opens the Record pop-up)
//   and the members of the clubs / sports they run ("Extracurricular": Record opens that programme's "Add
//   achievement" form, B-2); View shows one student's timeline
//   record: the class teacher records an achievement (proof optional); it counts as verified at once
//   a missing-record request: the student sends it with evidence (required) from the Character Certificate page; it
//   goes to their class teacher this year (looked up now, never stored), who approves it (it becomes an achievement)
//   or rejects it with a reason. The evidence is a private file (storage/uploads/), opened only through viewFile().
class AchievementController extends Controller
{
    // GET /student/achievements
    public function student(): void
    {
        $user  = $this->requireRole('student');
        $items = AchievementTimeline::forStudent((int) $user['id']);

        $this->page('student/achievements', $user, [
            'metrics'  => AchievementTimeline::metrics($items),
            'timeline' => AchievementTimeline::byYear($items),
        ]);
    }

    // GET /teacher/achievements — the student cards; ?student=STU-2026-0009 shows that student's timeline instead
    // (and, for their class teacher, the oldest request they sent: the Review pop-up)
    public function teacher(): void
    {
        $user     = $this->requireRole('teacher');
        $students = $this->students((int) $user['id']);
        $code     = $this->queryText('student');
        $chosen   = null;
        $request  = null;

        if ($code !== '')
        {
            $chosen = array_values(array_filter($students, fn (array $student): bool => $student['code'] === $code))[0]
                ?? $this->refuse('That student is not in your list.', '/teacher/achievements', 404);
            $request = $chosen['issue'] ? ((new MissingRecordModel())->pendingOf($chosen['id'])[0] ?? null) : null;
        }

        $this->page('teacher/achievements', $user, [
            'students' => $students,
            'student'  => $chosen,
            'timeline' => $chosen === null ? [] : AchievementTimeline::byYear(AchievementTimeline::forStudent($chosen['id'])),
            'issue'    => $request === null ? null : $this->issue($request),
        ]);
    }

    // POST /teacher/achievements/record (student, type, title, level, result, date, issuer, description, proof = a
    // file, optional) — the class teacher records an achievement for a student of their class this year. Back to the page.
    public function record(): never
    {
        $user    = $this->requireRole('teacher');
        $back    = $this->previousPage();
        $student = (int) $this->postText('student', 11);

        if (!can('achievements.record'))
        {
            $this->refuse('Only a ' . strtolower(words('{class} {teacher}')) . ' can record an achievement here.', $back, 403);
        }

        // Only their own class's students, as the cards show them (not switched off): an unknown id and someone else's
        // student get the same answer
        if (!in_array($student, array_map('intval', array_column((new PeopleModel())->classStudents((int) $user['id']), 'id')), true))
        {
            $this->refuse('That ' . strtolower(word('student')) . ' is not in your ' . strtolower(word('class')) . '.', $back, 404);
        }

        $values = $this->achievementValues($back);
        $proof  = $this->proofFile('proof', false, $back);
        $id     = $this->keepingUploads(fn () => (new AchievementModel())->add($values + $proof, [$student], (int) $user['id']), [$proof['proof_path'] ?? null]);

        $this->log($user, 'ACHIEVEMENT_ADDED', "#{$id} {$values['title']} ({$values['category']}, " . strtolower(word('student')) . " #{$student}"
            . ($proof === [] ? ', no file attached)' : ', with proof)'));
        $this->setFlash('notice', 'Achievement recorded.');
        $this->redirect($back);
    }

    // POST /student/achievements/request (type, year, title, details, evidence = a file, required) — from the
    // missing-record pop-up on the Character Certificate page. Back to that page.
    public function sendRequest(): never
    {
        $user = $this->requireRole('student');
        $back = $this->previousPage();

        if (!can('achievements.request'))
        {
            $this->refuse('Only a ' . strtolower(word('student')) . ' can send a missing-record request.', $back, 403);
        }

        $certificates = new CertificateModel();
        $batch = $certificates->batchOf((int) $user['id']);

        if ($batch !== null && ($certificates->saved((int) $user['id'], (int) $batch['school_year'])['status'] ?? '') === 'issued')
        {
            $this->refuse('The issued certificate can no longer be changed.', $back, 409);
        }

        $teacher = (new PeopleModel())->classTeacherOfStudent((int) $user['id'])
            ?? $this->refuse(words('Your {class} has no {class} {teacher} yet. Please ask the office.'), $back, 422);
        $sent    = [];

        foreach (['type' => 50, 'year' => 40, 'title' => 150, 'details' => 1000] as $field => $max)
        {
            $sent[$field] = $this->postText($field, $max);
        }

        $errors = Validator::check($sent, [
            'type'    => 'required|in:' . implode(',', config('certificates.record_types')),
            'year'    => 'required',
            'title'   => 'required',
            'details' => 'required',
        ], ['type' => 'Record type', 'year' => words('Academic year / {grade}'), 'title' => 'Title / role', 'details' => 'Details & reason']);

        if ($errors !== [])
        {
            $this->refuse(reset($errors), $back, 422);
        }

        $proof  = $this->proofFile('evidence', true, $back);
        $values = ['record_type' => $sent['type'], 'academic_year' => $sent['year'], 'title' => $sent['title'], 'details' => $sent['details']];
        $id     = $this->keepingUploads(fn () => (new MissingRecordModel())->send((int) $user['id'], $values + $proof), [$proof['proof_path']]);

        $this->log($user, 'MISSING_RECORD_SENT', "#{$id} {$sent['title']} (to {$teacher['name']})");
        $this->setFlash('notice', "Missing record request sent to {$teacher['name']}.");
        $this->redirect($back);
    }

    // POST /teacher/achievements/approve (request + the Record form's fields) — the class teacher completes a pending
    // request: it becomes an achievement (the request's evidence = its proof), all in one transaction. Back to the page.
    public function approve(): never
    {
        $user    = $this->requireRole('teacher');
        $back    = $this->previousPage();
        $request = $this->pendingRequest($user, $back);
        $values  = $this->achievementValues($back);

        $achievementId = (new MissingRecordModel())->approve((int) $request['id'], $values, (int) $user['id'])
            ?? $this->refuse('That request has already been answered.', $back, 409);

        $this->log($user, 'MISSING_RECORD_APPROVED', "#{$request['id']} {$values['title']} → achievement #{$achievementId}");
        $this->setFlash('notice', 'Request approved and recorded.');
        $this->redirect($back);
    }

    // POST /teacher/achievements/reject (request, reason) — the class teacher rejects a pending request with a reason
    public function reject(): never
    {
        $user    = $this->requireRole('teacher');
        $back    = $this->previousPage();
        $request = $this->pendingRequest($user, $back);
        $reason  = $this->postText('reason', 300);

        if ($reason === '')
        {
            $this->refuse('Please give a reason for rejecting it.', $back, 422);
        }

        if (!(new MissingRecordModel())->reject((int) $request['id'], $reason, (int) $user['id']))
        {
            $this->refuse('That request has already been answered.', $back, 409);
        }

        $this->log($user, 'MISSING_RECORD_REJECTED', "#{$request['id']} {$request['title']}: {$reason}");
        $this->setFlash('notice', 'Request rejected.');
        $this->redirect($back);
    }

    // GET /teacher/achievements/file?request=… — a request's evidence (View File, opened in a new tab), only for the
    // class teacher it goes to. Anyone else, an unknown request or a missing file → the same answer.
    public function viewFile(): never
    {
        $user    = $this->requireRole('teacher');
        $request = $this->routedRequest($user, (int) $this->queryText('request', 11), '/teacher/achievements');

        if (!Upload::exists($request['proof_path']))
        {
            $this->refuse('That request does not exist.', '/teacher/achievements', 404);
        }

        Upload::send($request['proof_path'], $request['proof_name']);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // The teacher's student cards, a reported issue first: their class this year ('Class'), then the members of the
    // clubs / sports they run ('Extracurricular', recordUrl = that programme's "Add achievement" form). A student in
    // both shows once, as 'Class'. The card's colour comes from the student's id (the same student, the same colour).
    private function students(int $teacherId): array
    {
        $tones    = config('certificates.tones');   // the student card tones (student-card.css)
        $waiting  = (new MissingRecordModel())->pendingFor($teacherId);
        $students = [];

        foreach ((new PeopleModel())->classStudents($teacherId) as $student)
        {
            $students[$student['id']] = $student + ['from' => word('class'), 'recordUrl' => null];
        }

        $programmes = new ExtracurricularModel();

        foreach (feature('extracurricular') ? $programmes->runBy($teacherId) : [] as $programme)
        {
            foreach ($programmes->people($programme['id'], 'member') as $member)
            {
                $students[$member['id']] ??= $member + ['from' => 'Extracurricular', 'recordUrl' => "/teacher/extracurricular?id={$programme['id']}&award=new"];
            }
        }

        $cards = array_map(fn (array $student): array => [
            'id'        => (int) $student['id'],
            'name'      => $student['name'],
            'code'      => $student['code'],
            'photo'     => $student['photo'] ?? '',
            'tone'      => $tones[crc32((string) $student['id']) % count($tones)],
            'from'      => $student['from'],
            'recordUrl' => $student['recordUrl'],
            'issue'     => in_array((int) $student['id'], $waiting, true),
        ], array_values($students));

        usort($cards, fn (array $a, array $b): int => $b['issue'] <=> $a['issue']);   // a reported issue first

        return $cards;
    }

    // A pending request as the Review pop-up shows it (the form filled in from it; the teacher completes the rest)
    private function issue(array $request): array
    {
        return [
            'id'          => (int) $request['id'],
            'message'     => "{$request['record_type']} · {$request['academic_year']}",
            'title'       => $request['title'],
            'description' => $request['details'],
            'file'        => $request['proof_name'],
            'file_meta'   => 'Sent ' . date(country('date_format'), strtotime($request['created_at'])) . ' • Attached by ' . strtolower(word('student')),
        ];
    }

    // The Record / Approve form's achievement fields, checked (a problem → back with the first message) → the row's
    // values. The form's type (Academic, Sports, Clubs & Societies, Leadership) is saved as its group's category.
    private function achievementValues(string $back): array
    {
        $sent = [];

        foreach (['type' => 50, 'title' => 150, 'level' => 50, 'result' => 100, 'date' => 10, 'issuer' => 150, 'description' => 600] as $field => $max)
        {
            $sent[$field] = $this->postText($field, $max);
        }

        $types  = array_column(config('achievements.groups'), 'label', 'type');
        $errors = Validator::check($sent, [
            'type'        => 'required|in:' . implode(',', array_keys($types)),
            'title'       => 'required',
            'level'       => 'required|in:' . implode(',', country('achievement_levels')),
            'date'        => 'required|date',
            'description' => 'required',
        ], ['type' => 'Achievement type', 'title' => 'Achievement title', 'date' => 'Date it occurred']);

        if ($errors !== [] || $sent['date'] > date('Y-m-d'))
        {
            $this->refuse($errors !== [] ? reset($errors) : 'The date can\'t be in the future.', $back, 422);
        }

        $empty = fn (string $value): ?string => $value === '' ? null : $value;

        return [
            'title' => $sent['title'], 'category' => $types[$sent['type']], 'level' => $sent['level'], 'achieved_on' => $sent['date'],
            'result' => $empty($sent['result']), 'issued_by' => $empty($sent['issuer']), 'summary' => $sent['description'],
        ];
    }

    // A form's proof file (the teacher's Supporting Proof: optional; a request's Evidence: required), checked by its
    // real content and size (config/achievements.php) and saved privately → ['proof_path', 'proof_name'], or [] when
    // none was chosen. A refused or missing (required) file → back with the message, nothing saved.
    private function proofFile(string $field, bool $required, string $back): array
    {
        $file = $_FILES[$field] ?? [];

        [$path, $problem] = Upload::document($file, 'proofs', config('achievements.proof_types'), (int) config('achievements.proof_kb'));

        if ($problem !== null || ($path === null && $required))
        {
            $this->refuse($problem ?? 'Please attach your evidence.', $back, 422);
        }

        return $path === null ? [] : ['proof_path' => $path, 'proof_name' => mb_substr(basename((string) $file['name']), 0, 255)];
    }

    // The request a review form is about ('request'): it must go to this teacher now and still be pending
    private function pendingRequest(array $user, string $back): array
    {
        $request = $this->routedRequest($user, (int) $this->postText('request', 11), $back);

        if ($request['status'] !== 'pending')
        {
            $this->refuse('That request has already been answered.', $back, 409);
        }

        return $request;
    }

    // A request that goes to this teacher now (they are the student's class teacher this year). An unknown id and
    // someone else's request get the same answer.
    private function routedRequest(array $user, int $requestId, string $back): array
    {
        $request = (new MissingRecordModel())->find($requestId);

        if (!can('achievements.review') || $request === null || !(new PeopleModel())->isClassTeacherOf((int) $user['id'], (int) $request['student_id']))
        {
            $this->refuse('That request does not exist.', $back, 404);
        }

        return $request;
    }
}
