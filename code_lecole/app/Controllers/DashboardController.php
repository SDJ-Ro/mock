<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NoticeModel;
use App\Services\CalendarFeed;
use App\Services\ExtracurricularDashboard;
use App\Services\MarksDashboard;
use Core\Controller;

class DashboardController extends Controller
{
    // GET /student, /teacher, ... → the signed-in user's home page: app/Views/{role}/dashboard.php
    // The other cards are still example data (app/Mock/dashboards.php); the calendar, "Upcoming
    // Events" and (students, parents) "Unread Notices" are real, with the same who-sees-what rules as their own pages.
    // A module switched off (config/white_label/features.php) takes its pieces away: each card, chart and donut names
    // its 'feature'; a piece that is off arrives as null (or is left out of its list), and the view draws nothing for it.
    // The marks cards and score chart are real too: MarksDashboard fills them from the published terms; so are the
    // sports and clubs pieces: ExtracurricularDashboard fills them from the memberships.
    public function show(string $role): void
    {
        $user  = $this->requireRole($role);
        $board = (require dirname(__DIR__) . '/Mock/dashboards.php')[$role];
        $board = feature('term_marks') ? MarksDashboard::fill($role, $user, $board) : $board;
        $board = feature('extracurricular') ? ExtracurricularDashboard::fill($role, $user, $board) : $board;
        $on    = fn (?array $piece): bool => $piece !== null && feature($piece['feature']);

        $this->page("{$role}/dashboard", $user, [
            'metrics'        => array_values(array_filter($board['metrics'], $on)),
            'chartConfig'    => $on($board['chartConfig']) ? $board['chartConfig'] : null,
            'donuts'         => array_values(array_filter($board['donuts'] ?? [], $on)),
            'calendarConfig' => feature('calendar') ? CalendarFeed::config($user) : null,
            'upcomingEvents' => feature('calendar') ? CalendarFeed::upcoming($user) : null,
            'notices'        => feature('notices') && in_array($role, config('notices.unread_card'), true) ? (new NoticeModel())->unread($user) : null,
        ]);
    }
}
