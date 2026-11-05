<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CertificateModel;
use App\Models\GraduationBatchModel;
use App\Models\PeopleModel;
use App\Models\SchoolYear;
use App\Services\CertificatePdf;
use Core\Controller;

// Graduating certificates: a live draft for the student, a due batch for management, and an immutable issued PDF.
class CertificateController extends Controller
{
    public function management(): void
    {
        $user = $this->requireRole('management');
        $due = (new GraduationBatchModel())->dueStudents();
        $all = array_map([$this, 'card'], $due);
        $wants = $this->queryText('student');

        if ($wants !== '')
        {
            $chosen = array_values(array_filter($due, fn (array $c): bool => (string) $c['student_id'] === $wants))[0]
                ?? $this->refuse('That certificate does not exist.', '/management/character-certificate', 404);
            $this->page('management/character_certificate', $user,
                ['certificate' => $this->prepare(new CertificateModel(), (int) $chosen['student_id'])]);
            return;
        }

        $filters = [
            'q' => $this->queryText('q'),
            'status' => array_key_exists($this->queryText('status'), config('certificates.statuses')) ? $this->queryText('status') : '',
            'pathway' => 'graduating',
        ];
        $search = mb_strtolower($filters['q']);
        $shown = array_filter($all, fn (array $c): bool =>
            ($filters['status'] === '' || $c['status'] === $filters['status'])
            && ($search === '' || str_contains(mb_strtolower("{$c['name']} {$c['id']} {$c['cohort']}"), $search)));
        $counts = ['' => count($all)];
        $groups = [];

        foreach (array_keys(config('certificates.statuses')) as $status)
        {
            $counts[$status] = count(array_filter($all, fn (array $c): bool => $c['status'] === $status));
            $groups[$status] = array_values(array_filter($shown, fn (array $c): bool => $c['status'] === $status));
        }

        $this->page('management/character_certificate', $user, compact('filters', 'counts', 'groups') + ['certificate' => null]);
    }

    public function student(): void
    {
        $user = $this->requireRole('student');
        $model = new CertificateModel();

        $this->page('student/certificates', $user, [
            'certificate' => $this->prepare($model, (int) $user['id']),
            'classTeacher' => (new PeopleModel())->classTeacherOfStudent((int) $user['id'])['name'] ?? null,
            'rejectedRequests' => array_values(array_filter($model->requestsOf((int) $user['id']),
                fn (array $r): bool => $r['status'] === 'rejected')),
        ]);
    }

    public function saveDraft(): never
    {
        $user = $this->requireRole('management');

        if (!can('certificates.finalise'))
        {
            $this->refuse('You cannot change certificates.', '/management/character-certificate', 403);
        }

        $id = (int) $this->postText('student');
        $back = '/management/character-certificate?student=' . $id;
        $model = new CertificateModel();
        $batch = $model->batchOf($id) ?? $this->refuse('That certificate does not exist.', '/management/character-certificate', 404);

        if (!$model->saveDraft($id, (int) $batch['school_year'], $this->wording()))
        {
            $this->refuse('That draft can no longer be changed.', $back, 409);
        }

        $this->log($user, 'CERTIFICATE_DRAFT_SAVED', (string) $id);
        $this->setFlash('notice', 'Draft wording saved.');
        $this->redirect($back);
    }

    public function finalise(): never
    {
        $user = $this->requireRole('management');

        if (!can('certificates.finalise'))
        {
            $this->refuse('You cannot finalise certificates.', '/management/character-certificate', 403);
        }

        $id = (int) $this->postText('student');
        $back = '/management/character-certificate?student=' . $id;
        $model = new CertificateModel();
        $batch = $model->batchOf($id) ?? $this->refuse('That certificate does not exist.', '/management/character-certificate', 404);
        $person = $model->person($id, (int) $batch['school_year']) ?? $this->refuse('That certificate does not exist.', '/management/character-certificate', 404);
        $name = $person['full_name'];
        $parts = explode(' ', $name);
        $last = array_pop($parts);
        $initials = implode('', array_map(fn (string $part): string => mb_substr($part, 0, 1) . '. ', $parts)) . $last;
        $cohort = $this->cohort($person, (int) $batch['school_year']);
        $period = $this->period($person, (int) $batch['school_year']);

        if (!$model->finalise($id, (int) $batch['school_year'], (int) $user['id'], $this->wording(), [
            'name' => $name, 'initials' => $initials, 'cohort' => $cohort, 'period' => $period,
            'school' => school('name'), 'motto' => school('motto'),
        ]))
        {
            $this->refuse('Resolve pending record requests before finalising, or this certificate was already issued.', $back, 409);
        }

        $this->log($user, 'CERTIFICATE_ISSUED', (string) $id);
        $this->setFlash('notice', 'Certificate issued. The student can now download and print it.');
        $this->redirect($back);
    }

    public function pdf(string $role): never
    {
        $user = $this->requireRole($role);

        if (!can('certificates.download'))
        {
            $this->refuse('You cannot download certificates.', "/{$role}", 403);
        }

        $id = $role === 'student' ? (int) $user['id'] : (int) $this->queryText('student');
        $model = new CertificateModel();
        $issued = $model->latestIssued($id);

        if ($issued === null)
        {
            $this->refuse('This certificate is not ready to print.', $role === 'student' ? '/student/certificates' : '/management/character-certificate', 403);
        }

        $certificate = $this->prepare($model, $id, $issued);
        ob_start();
        $this->view('print/certificate', ['certificate' => $certificate]);
        header('Cache-Control: no-store');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="character-certificate-' . preg_replace('/[^A-Za-z0-9-]/', '-', $certificate['id']) . '.pdf"');
        echo CertificatePdf::make(ob_get_clean());
        exit;
    }

    private function wording(): array
    {
        $text = [];

        foreach (array_keys(config('certificates.sections')) as $section)
        {
            $text[$section] = $this->postText($section, (int) config('certificates.max_section_chars'));

            if ($text[$section] === '')
            {
                $this->refuse('Add wording to every certificate section before saving.', $this->previousPage(), 422);
            }
        }

        return ['academic' => $text['academic'], 'activities' => $text['activities'], 'conduct' => $text['conduct']];
    }

    private function prepare(CertificateModel $model, int $studentId, ?array $issuedCopy = null): array
    {
        $batch = $model->batchOf($studentId);
        $year = (int) ($issuedCopy['school_year'] ?? $batch['school_year'] ?? (new SchoolYear())->current());
        $person = $model->person($studentId, $year) ?? $this->refuse('That ' . strtolower(word('student')) . ' does not exist.', '/management/character-certificate', 404);
        $saved = $issuedCopy ?? $model->saved($studentId, $year) ?? $model->latestIssued($studentId);
        $issued = $saved !== null && $saved['status'] === 'issued';
        $points = $model->recordsOf($studentId);
        $requests = $model->requestsOf($studentId);
        $records = [];
        $waitingPoints = ['academic' => [], 'activities' => [], 'conduct' => []];

        foreach ($requests as $request)
        {
            $section = $request['record_type'] === 'Academic Achievement' ? 'academic' : 'activities';

            if (!$issued && $request['status'] === 'pending')
            {
                $waitingPoints[$section][] = $request['title'];
            }

            $records[] = [
                'id' => 'REQ-' . $request['id'], 'submitted' => $request['created_at'],
                'status' => ucfirst($request['status']), 'section' => $section,
                'title' => $request['title'], 'description' => $request['details'],
                'teacher' => $request['teacher_name'] ?? words('Current {Class} {Teacher}'),
                'teacherStatus' => ucfirst($request['status']), 'note' => $request['reason'] ?? '',
                'files' => [], 'activity' => [],
            ];
        }

        foreach (array_keys(config('certificates.sections')) as $section)
        {
            $column = $section . '_text';

            if ($saved !== null && $saved[$column] !== null)
            {
                $points[$section] = array_values(array_filter(array_map('trim', preg_split('/\R/u', $saved[$column]) ?: []),
                    fn (string $line): bool => $line !== ''));
            }
        }

        $name = $issued ? $saved['student_name'] : $person['full_name'];
        $parts = explode(' ', $name);
        $last = array_pop($parts);
        $initials = $issued ? $saved['name_with_initials'] : implode('', array_map(fn (string $part): string => mb_substr($part, 0, 1) . '. ', $parts)) . $last;
        $cohort = $issued ? $saved['cohort_text'] : $this->cohort($person, $year);
        $period = $issued ? $saved['study_period'] : $this->period($person, $year);
        $activity = [];

        if ($batch !== null)
        {
            $activity[] = [$batch['graduation_date'] . ' 00:00:00', 'Graduating certificate ready for review', 'System'];
        }

        if ($issued)
        {
            $activity[] = [$saved['finalised_at'], 'Certificate finalised', 'Management'];
        }

        $tones = config('certificates.tones');

        return [
            'studentId' => $studentId, 'id' => $person['code'], 'name' => $name,
            'cohort' => $cohort, 'class' => $person['class_name'] ?? '', 'period' => $period,
            'withInitials' => $initials, 'pathway' => 'graduating',
            'status' => $issued ? 'issued' : 'pending', 'requested' => $issued ? $saved['issued_on'] : date('Y-m-d'),
            'academic' => $points['academic'], 'activities' => $points['activities'], 'conduct' => $points['conduct'],
            'records' => $records, 'requests' => [], 'activity' => $activity,
            'waitingPoints' => $waitingPoints,
            'waitingCount' => count($waitingPoints['academic']) + count($waitingPoints['activities']) + count($waitingPoints['conduct']),
            'hasWaiting' => !$issued || $waitingPoints['academic'] !== [] || $waitingPoints['activities'] !== [] || $waitingPoints['conduct'] !== [],
            'tone' => $tones[crc32((string) $studentId) % count($tones)],
            'schoolName' => $issued ? $saved['school_name'] : school('name'),
            'schoolMotto' => $issued ? $saved['school_motto'] : school('motto'),
        ];
    }

    private function cohort(array $person, int $year): string
    {
        return ($person['grade_number'] === null ? '' : word('grade') . ' ')
            . ($person['class_name'] ?? '') . ' · ' . words('{Class} of ') . $year;
    }

    private function card(array $student): array
    {
        $tones = config('certificates.tones');

        return [
            'studentId' => (int) $student['student_id'], 'id' => $student['code'],
            'name' => $student['full_name'], 'class' => $student['class_name'],
            'cohort' => word('grade') . ' ' . $student['class_name'] . ' · ' . words('{Class} of ') . $student['school_year'],
            'status' => $student['status'],
            'hasWaiting' => $student['status'] === 'pending' || (int) $student['pending_count'] > 0,
            'tone' => $tones[crc32((string) $student['student_id']) % count($tones)],
        ];
    }

    private function period(array $person, int $year): string
    {
        return ($person['admission_date'] === null ? '' : substr($person['admission_date'], 0, 4) . '–') . $year;
    }
}
