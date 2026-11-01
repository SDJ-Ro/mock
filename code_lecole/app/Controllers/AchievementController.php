<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AchievementTimeline;
use Core\Controller;

// The Achievements pages: the student's (number cards + timeline) and the teacher's (student cards → one student's
// timeline, Record pop-up). The data is still example data (app/Mock/achievements.php) and Record saves
// nothing yet; only the pages are drawn here.
class AchievementController extends Controller
{
    // GET /student/achievements
    public function student(): void
    {
        $user = $this->requireRole('student');
        $mock = $this->mock();

        $this->page('student/achievements', $user, [
            'metrics'  => AchievementTimeline::metrics($mock['timeline']),
            'timeline' => AchievementTimeline::byYear($mock['timeline']),
        ]);
    }

    // GET /teacher/achievements — the student cards; ?student=2021/0203 shows that student's timeline instead
    public function teacher(): void
    {
        $user     = $this->requireRole('teacher');
        $mock     = $this->mock();
        $students = $mock['students'];
        $code     = $this->queryText('student');
        $chosen   = null;

        usort($students, fn (array $a, array $b): int => $b['issue'] <=> $a['issue']);   // a reported issue first

        if ($code !== '')
        {
            $chosen = array_values(array_filter($students, fn (array $student): bool => $student['code'] === $code))[0]
                ?? $this->refuse('That student is not in your list.', '/teacher/achievements', 404);
        }

        $this->page('teacher/achievements', $user, [
            'students' => $students,
            'student'  => $chosen,
            'timeline' => $chosen === null ? [] : AchievementTimeline::byYear($mock['timeline']),
            'issue'    => ($chosen['issue'] ?? false) ? $mock['issue'] : null,
        ]);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function mock(): array
    {
        return require dirname(__DIR__) . '/Mock/achievements.php';
    }
}
