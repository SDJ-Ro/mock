<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PeopleModel;
use App\Services\CertificatePdf;
use Core\Controller;

// The Character Certificate pages: management's (every certificate as a capsule card → one certificate with its
// requests) and the student's (their certificate + two request pop-ups). The data is still example data
// (app/Mock/certificates.php) and nothing is saved yet (the forms only say so). The certificate itself is one
// template (_certificate_sheet.php) for the screen and for the PDF that Dompdf makes (pdf(), CertificatePdf).
class CertificateController extends Controller
{
    // GET /management/character-certificate?q=…&status=pending&pathway=leaving · ?student=2021/0456 = one certificate
    public function management(): void
    {
        $user  = $this->requireRole('management');
        $all   = array_map([$this, 'prepare'], $this->mock()['certificates']);
        $wants = $this->queryText('student');

        if ($wants !== '')
        {
            $this->page('management/character_certificate', $user, ['certificate' => $this->find($all, $wants)]);
            return;
        }

        $filters = [
            'q'       => $this->queryText('q'),
            'status'  => array_key_exists($this->queryText('status'), config('certificates.statuses')) ? $this->queryText('status') : '',
            'pathway' => array_key_exists($this->queryText('pathway'), config('certificates.pathways')) ? $this->queryText('pathway') : '',
        ];
        $search  = mb_strtolower($filters['q']);
        $shown   = array_filter($all, fn (array $c): bool =>
            ($filters['status'] === '' || $c['status'] === $filters['status'])
            && ($filters['pathway'] === '' || $c['pathway'] === $filters['pathway'])
            && ($search === '' || str_contains(mb_strtolower("{$c['name']} {$c['id']} {$c['cohort']}"), $search)));

        // The tabs' numbers (of every certificate, not only the ones shown) and the two groups of cards (of the ones shown)
        $counts = ['' => count($all)];
        $groups = [];

        foreach (array_keys(config('certificates.statuses')) as $status)
        {
            $counts[$status] = count(array_filter($all, fn (array $c): bool => $c['status'] === $status));
            $groups[$status] = array_values(array_filter($shown, fn (array $c): bool => $c['status'] === $status));
        }

        $this->page('management/character_certificate', $user, [
            'certificate' => null,
            'filters'     => $filters,
            'counts'      => $counts,
            'groups'      => $groups,
        ]);
    }

    // GET /student/certificates — the example certificate (the same for every student until the backend)
    public function student(): void
    {
        $user = $this->requireRole('student');
        $mock = $this->mock();

        $this->page('student/certificates', $user, [
            'certificate'  => $this->find(array_map([$this, 'prepare'], $mock['certificates']), $mock['student']),
            'classTeacher' => (new PeopleModel())->classTeacherOfStudent((int) $user['id'])['name'] ?? null,   // the missing-record pop-up (B-3)
        ]);
    }

    // GET /{role}/…/pdf[?student=…] — the certificate as a PDF file (Dompdf), opened in the browser's PDF viewer,
    // which prints it or saves it. A student gets their own; management any one.
    public function pdf(string $role): never
    {
        $this->requireRole($role);
        $mock        = $this->mock();
        $certificate = $this->find(array_map([$this, 'prepare'], $mock['certificates']), $role === 'student' ? $mock['student'] : $this->queryText('student'));

        ob_start();
        $this->view('print/certificate', ['certificate' => $certificate]);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="character-certificate-' . str_replace('/', '-', $certificate['id']) . '.pdf"');
        echo CertificatePdf::make(ob_get_clean());
        exit;
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function mock(): array
    {
        return require dirname(__DIR__) . '/Mock/certificates.php';
    }

    // One certificate by its index no., or back to the list
    private function find(array $certificates, string $id): array
    {
        return array_values(array_filter($certificates, fn (array $c): bool => $c['id'] === $id))[0]
            ?? $this->refuse('That certificate does not exist.', '/management/character-certificate', 404);
    }

    // What every view needs besides the example data: the card's colour, what still waits, and the particulars
    // worked out from the name and class ("N. Perera", "Grade 13 completion")
    private function prepare(array $c): array
    {
        $tones   = config('certificates.tones');
        $waiting = array_filter([...$c['requests'], ...$c['records']], fn (array $request): bool => $request['status'] === 'Pending');
        $parts   = explode(' ', $c['name']);
        $last    = array_pop($parts);
        $class   = explode(' · ', $c['cohort'])[0];

        // The missing-record requests that still wait, per section (the sheet shows them as "Pending Review")
        $waitingPoints = [];

        foreach (array_keys(config('certificates.sections')) as $section)
        {
            $waitingPoints[$section] = array_column(array_filter($c['records'],
                fn (array $r): bool => $r['status'] === 'Pending' && $r['section'] === $section), 'title');
        }

        return $c + [
            'tone'         => $tones[crc32($c['id'] . $c['name']) % count($tones)],   // the same student always gets the same colour
            'waitingCount' => count($waiting),
            'hasWaiting'   => $c['status'] === 'pending' || $waiting !== [],
            'withInitials' => implode('', array_map(fn (string $part): string => mb_substr($part, 0, 1) . '. ', $parts)) . $last,
            'class'        => $class,
            'period'       => $c['pathway'] === 'graduating' ? preg_replace('/-[A-Z]$/', '', $class) . ' completion' : "{$class} record",
            'waitingPoints' => $waitingPoints,
        ];
    }
}
