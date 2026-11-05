<?php

declare(strict_types=1);

use App\Controllers\AcademicController;
use App\Controllers\AchievementController;
use App\Controllers\ApprovalController;
use App\Controllers\AuditController;
use App\Controllers\LandingController;
use App\Controllers\AuthController;
use App\Controllers\CalendarController;
use App\Controllers\CertificateController;
use App\Controllers\ChildProfileController;
use App\Controllers\ComplaintController;
use App\Controllers\DashboardController;
use App\Controllers\ExtracurricularController;
use App\Controllers\FeedbackController;
use App\Controllers\NoticeController;
use App\Controllers\PeopleAddController;
use App\Controllers\PeopleController;
use App\Controllers\ProfileController;
use App\Controllers\TermMarkController;
use Core\Router;

return static function (Router $router): void
{
    // Landing pages
    $router->get('/', [LandingController::class, 'index']);
    $router->get('/landing', [LandingController::class, 'index']);

    if (feature('honours_board'))
    {
        $router->get('/landing/achievements', [LandingController::class, 'achievements']);
    }

    // Auth Chooser & Logout
    $router->get('/auth', [AuthController::class, 'chooser']);
    $router->post('/logout', [AuthController::class, 'logout']);

    // Per-role Auth & Dashboard routes
    foreach (config('app.roles') as $role)
    {
        // Sign-in
        $router->get("/auth/{$role}", [AuthController::class, 'signin', $role]);
        $router->post("/auth/{$role}", [AuthController::class, 'handleSignin', $role]);

        // Sign-up
        $router->get("/auth/{$role}/signup", [AuthController::class, 'signup', $role]);
        $router->post("/auth/{$role}/signup", [AuthController::class, 'handleSignup', $role]);
        $router->post("/auth/{$role}/verify", [AuthController::class, 'handleActivate', $role]);
        $router->post("/auth/{$role}/resend", [AuthController::class, 'resendActivation', $role]);

        // Forgot password & reset
        $router->post("/auth/{$role}/forgot", [AuthController::class, 'handleForgot', $role]);
        $router->post("/auth/{$role}/reset", [AuthController::class, 'handleReset', $role]);

        // Portal Dashboard home after sign-in
        $router->get("/{$role}", [DashboardController::class, 'show', $role]);
        $router->get("/{$role}/dashboard", [DashboardController::class, 'show', $role]);
    }

    // The calendar's "Add event" pop-up (only the roles that may add events)
    if (feature('calendar'))
    {
        foreach (config('permissions.calendar.create') as $role)
        {
            $router->post("/{$role}/calendar/save", [CalendarController::class, 'save', $role]);
            $router->post("/{$role}/calendar/delete", [CalendarController::class, 'delete', $role]);
        }
    }

    // The notice board (every role; addresses in config/notices.php). Posting, pinning, deleting: the roles that may post.
    if (feature('notices'))
    {
        foreach (config('notices.board') as $role => $board)
        {
            $router->get($board['url'], [NoticeController::class, 'board', $role]);

            if (in_array($role, config('permissions.notices.post'), true))
            {
                $router->post("{$board['url']}/save", [NoticeController::class, 'save', $role]);
                $router->post("{$board['url']}/pin", [NoticeController::class, 'pin', $role]);
                $router->post("{$board['url']}/delete", [NoticeController::class, 'delete', $role]);
            }

            if (feature('approvals') && in_array($role, config('permissions.notices.approve'), true))
            {
                $router->post("{$board['url']}/review", [NoticeController::class, 'review', $role]);
            }
        }
    }

    // The parent's Child profile, and the "Viewing" switch: which child the whole parent portal shows
    $router->get('/parent/child-profile', [ChildProfileController::class, 'show']);
    $router->post('/parent/child', [ChildProfileController::class, 'choose']);

    // Approvals: a parent's yes / no before their child joins a club or sport (only parents: their own children)
    if (feature('parent_consent'))
    {
        $router->get('/parent/approvals', [ApprovalController::class, 'index']);
        $router->post('/parent/approvals/answer', [ApprovalController::class, 'answer']);
    }

    // Feedback (the teacher's and the parent's page; example data until the feedback backend is built)
    if (feature('feedback'))
    {
        $router->get('/teacher/feedback', [FeedbackController::class, 'teacher']);
        $router->get('/parent/feedback', [FeedbackController::class, 'parent']);
    }

    // Complaints (the parent's and management's page; example data until the complaints backend is built)
    if (feature('complaints'))
    {
        $router->get('/parent/complaints', [ComplaintController::class, 'parent']);
        $router->get('/management/complaints', [ComplaintController::class, 'management']);
    }

    // Character certificates (the student's and management's page; example data until the certificates backend is built)
    // + the PDF (Dompdf)
    if (feature('certificates'))
    {
        $router->get('/student/certificates', [CertificateController::class, 'student']);
        $router->get('/student/certificates/pdf', [CertificateController::class, 'pdf', 'student']);
        $router->get('/management/character-certificate', [CertificateController::class, 'management']);
        $router->get('/management/character-certificate/pdf', [CertificateController::class, 'pdf', 'management']);
    }

    // Extracurricular (four roles' pages): the list, one programme (?id=), one achievement (?award=); the teacher's
    // page shows the programmes they run. Saving: who may do what is checked in the controller.
    if (feature('extracurricular'))
    {
        foreach (config('permissions.extracurricular.page') as $role)
        {
            $router->get("/{$role}/extracurricular", [ExtracurricularController::class, 'index', $role]);
            $router->post("/{$role}/extracurricular/save", [ExtracurricularController::class, 'save', $role]);
            $router->post("/{$role}/extracurricular/review", [ExtracurricularController::class, 'review', $role]);
            $router->post("/{$role}/extracurricular/archive", [ExtracurricularController::class, 'archive', $role]);
            $router->post("/{$role}/extracurricular/interest", [ExtracurricularController::class, 'interest', $role]);
            $router->post("/{$role}/extracurricular/request", [ExtracurricularController::class, 'decide', $role]);
            $router->post("/{$role}/extracurricular/remove", [ExtracurricularController::class, 'remove', $role]);
            $router->post("/{$role}/extracurricular/team", [ExtracurricularController::class, 'saveTeam', $role]);
            $router->post("/{$role}/extracurricular/assign", [ExtracurricularController::class, 'assign', $role]);
            $router->post("/{$role}/extracurricular/team-delete", [ExtracurricularController::class, 'deleteTeam', $role]);
            $router->get("/{$role}/extracurricular/member", [ExtracurricularController::class, 'member', $role]);

            // A programme's achievements (they are achievements too: they go with that module)
            if (feature('achievements'))
            {
                $router->post("/{$role}/extracurricular/award", [ExtracurricularController::class, 'saveAward', $role]);
                $router->post("/{$role}/extracurricular/award-delete", [ExtracurricularController::class, 'deleteAward', $role]);
            }
        }

        // Lift / lock a student's Academic Watch: from People's profile pop-up (who: permissions 'extracurricular.watch')
        foreach (config('permissions.extracurricular.watch') as $role)
        {
            $router->post("/{$role}/extracurricular/watch", [ExtracurricularController::class, 'watch', $role]);
        }
    }

    // The Audit Log (read only; who: config/permissions.php 'audit.view')
    if (feature('audit_log'))
    {
        foreach (config('permissions.audit.view') as $role)
        {
            $router->get("/{$role}/audit", [AuditController::class, 'index', $role]);
        }
    }

    // Achievements: the student's and the teacher's page; the class teacher records one; the student's missing-record
    // request (from the Character Certificate page) and the class teacher's answer; a request's evidence file
    if (feature('achievements'))
    {
        $router->get('/student/achievements', [AchievementController::class, 'student']);
        $router->post('/student/achievements/request', [AchievementController::class, 'sendRequest']);
        $router->get('/teacher/achievements', [AchievementController::class, 'teacher']);
        $router->post('/teacher/achievements/record', [AchievementController::class, 'record']);
        $router->post('/teacher/achievements/approve', [AchievementController::class, 'approve']);
        $router->post('/teacher/achievements/reject', [AchievementController::class, 'reject']);
        $router->get('/teacher/achievements/file', [AchievementController::class, 'viewFile']);
    }

    // The student's Academic Records + its printable page, and the class teacher's Term Marks (save, publish)
    if (feature('term_marks'))
    {
        $router->get('/student/academic', [AcademicController::class, 'student']);
        $router->get('/student/academic/print', [AcademicController::class, 'recordPrint', 'student']);
        $router->get('/parent/child-profile/print', [AcademicController::class, 'recordPrint', 'parent']);
        $router->get('/teacher/marks', [TermMarkController::class, 'index']);
        $router->post('/teacher/marks/save', [TermMarkController::class, 'save']);
        $router->post('/teacher/marks/publish', [TermMarkController::class, 'publish']);
    }

    // The school's structure (the Academic Overview): grades, classes, teachers, curriculum stages — the roles that may edit it
    if (feature('academic'))
    {
        foreach (config('permissions.academic.edit') as $role)
        {
            $router->get("/{$role}/academic", [AcademicController::class, 'overview', $role]);
            $router->post("/{$role}/academic/grades/add", [AcademicController::class, 'addGrade', $role]);
            $router->post("/{$role}/academic/grades/delete", [AcademicController::class, 'deleteGrade', $role]);
            $router->post("/{$role}/academic/classes/save", [AcademicController::class, 'saveClass', $role]);
            $router->post("/{$role}/academic/subject-teacher", [AcademicController::class, 'saveSubjectTeacher', $role]);
            $router->post("/{$role}/academic/stages/save", [AcademicController::class, 'saveStage', $role]);
            $router->post("/{$role}/academic/stages/delete", [AcademicController::class, 'deleteStage', $role]);
        }
    }

    // People (the users directory) for the roles that may use it, and the teacher's own students (config/permissions.php)
    if (feature('people'))
    {
        foreach (config('permissions.people.directory') as $role)
        {
            $router->get("/{$role}/people", [PeopleController::class, 'index', $role]);
            $router->get("/{$role}/people/profile", [PeopleController::class, 'profile', $role]);
            $router->post("/{$role}/people/status", [PeopleController::class, 'status', $role]);
            $router->post("/{$role}/people/update", [PeopleController::class, 'update', $role]);
            $router->get("/{$role}/people/handover", [PeopleController::class, 'handoverForm', $role]);
            $router->post("/{$role}/people/handover", [PeopleController::class, 'handover', $role]);
            $router->get("/{$role}/people/add", [PeopleAddController::class, 'form', $role]);
            $router->post("/{$role}/people/add", [PeopleAddController::class, 'add', $role]);
            $router->get("/{$role}/people/parents", [PeopleAddController::class, 'parents', $role]);
        }
        foreach (config('permissions.people.own_students') as $role)
        {
            $router->get("/{$role}/students", [PeopleController::class, 'students', $role]);
            $router->get("/{$role}/students/profile", [PeopleController::class, 'studentProfile', $role]);
        }
    }

    // My Profile (every role). Saving details: the roles with details they may change (config/profile.php);
    // changing the password: config/permissions.php 'profile.password'.
    if (feature('profile'))
    {
        foreach (config('app.roles') as $role)
        {
            $router->get("/{$role}/profile", [ProfileController::class, 'show', $role]);

            if (config("profile.editable.{$role}") !== [])
            {
                $router->post("/{$role}/profile/save", [ProfileController::class, 'save', $role]);
                $router->post("/{$role}/profile/photo", [ProfileController::class, 'photo', $role]);
            }

            if (in_array($role, config('permissions.profile.password'), true))
            {
                $router->post("/{$role}/profile/password", [ProfileController::class, 'password', $role]);
            }
        }
    }
};
