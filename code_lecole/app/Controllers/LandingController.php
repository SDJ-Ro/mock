<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AchievementModel;
use Core\Controller;

class LandingController extends Controller
{
    public function index(): void
    {
        $this->view('landing_page/landing', [
            'pathways'   => $this->pathways(),
            'highlights' => (new AchievementModel())->honoursBoard(3),   // the 3 newest on the honours board
        ]);
    }

    // The public honours board: every achievement the office marked for it (achievements.on_honours_board)
    public function achievements(): void
    {
        $this->view('landing_page/achievements', [
            'achievements' => (new AchievementModel())->honoursBoard(),
        ]);
    }

    // The 4 "community" cards: each role's photo (config/white_label/school.php 'photos') and its words
    private function pathways(): array
    {
        return [
            [
                'title' => word('students'),
                'tone'  => 'tan',
                'photo' => school('photos.student.src'),
                'alt'   => school('photos.student.alt'),
                'text'  => words('Your timetable, {grades}, and report cards sit beside the clubs, teams, and creative work you take part in. Track achievements and never miss a notice or deadline.'),
            ],
            [
                'title' => word('teachers'),
                'tone'  => 'maroon',
                'photo' => school('photos.teacher.src'),
                'alt'   => school('photos.teacher.alt'),
                'text'  => words('Plan lessons, record {grades}, and run the clubs and teams you lead from one place. Log {student} progress and character notes, and post notices straight to your {classes}.'),
            ],
            [
                'title' => word('parents'),
                'tone'  => 'lightblue',
                'photo' => school('photos.parent.src'),
                'alt'   => school('photos.parent.alt'),
                'text'  => words('See academic progress as it happens and follow the activities, teams, and achievements shaping your child. Approve {institution} forms and stay in touch with {teachers}.'),
            ],
            [
                'title' => word('management'),
                'tone'  => 'terracotta',
                'photo' => school('photos.management.src'),
                'alt'   => school('photos.management.alt'),
                'text'  => words('Review enrolment and approval queues, oversee staffing, {classes}, and curriculum, and read {institution}-wide performance at a glance — then publish notices to everyone.'),
            ],
        ];
    }
}
