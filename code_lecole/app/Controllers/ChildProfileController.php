<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PeopleModel;
use App\Services\AchievementTimeline;

// The parent's Child profile and the "Viewing" switch: which child the whole parent
// portal shows (Controller::requireRole() keeps it as $user['child']). Every tab reuses what is already built:
//   Information   → People's profile of the child (PeopleController::profileData(), view only — real details)
//   Academics     → the student's Academic section (RecordBook: example marks until the marks backend)
//   Sports & Clubs → the student's Extracurricular list and programme pages (example data; this controller extends
//                   ExtracurricularController for them), at /parent/child-profile?tab=clubs…
//   Achievements  → the student's number cards + timeline (the child's real achievements, like People's pop-up)
class ChildProfileController extends ExtracurricularController
{
    // The tabs: [name, the module it needs (config/white_label/features.php), or null]
    private const TABS = [
        'information'  => ['Information', null],
        'academics'    => ['Academics', 'term_marks'],
        'clubs'        => ['Sports & Clubs', 'extracurricular'],
        'achievements' => ['Achievements', 'achievements'],
    ];

    // GET /parent/child-profile?tab=information|academics|clubs|achievements (clubs: + the list's q / type, or ?id=…[&award=…])
    public function show(): void
    {
        $user  = $this->requireRole('parent');
        $tabs  = array_map(fn (array $tab): string => $tab[0], array_filter(self::TABS, fn (array $tab): bool => $tab[1] === null || feature($tab[1])));
        $tab   = array_key_exists($this->queryText('tab'), $tabs) ? $this->queryText('tab') : 'information';
        $child = $user['child'] === null ? null : (new PeopleModel())->find($user['child']);
        $data  = ['tabs' => $tabs, 'tab' => $tab, 'profile' => null];

        if ($child !== null)
        {
            $data['profile'] = (new PeopleController())->profileData($child, null);
            $data += match ($tab)
            {
                'academics'    => ['exportUrl' => '/parent/child-profile/print'] + $data['profile']['student']['book'],
                'clubs'        => (int) $this->queryText('id') !== 0
                    ? $this->programmePage($user, 'student', (int) $this->queryText('id'), '/parent/child-profile', ['tab' => 'clubs'])
                    : $this->listPage($user, 'student', '/parent/child-profile', ['tab' => 'clubs']),
                'achievements' => $this->achievements($child['id']),
                default        => [],
            };
        }

        $this->page('parent/child_profile', $user, $data);
    }

    // POST /parent/child (choice = a child's account id) — which child the whole parent portal shows; back to the same page
    public function choose(): never
    {
        $user = $this->requireRole('parent');
        $id   = (int) $this->postText('choice', 11);

        // Only one of THEIR children (the same message for any other account, real or not)
        if (!isset($this->children($user)[$id]))
        {
            $this->refuse('That child is not linked to your account.', '/parent/dashboard', 404);
        }

        $_SESSION['user']['child'] = $id;
        $this->redirect($this->previousPage());
    }

    // The Achievements tab: the child's achievements (and the automatic ones from their marks, when term marks are on)
    // as the four number cards and the timeline
    private function achievements(int $childId): array
    {
        $items = AchievementTimeline::forStudent($childId);

        return ['metrics' => AchievementTimeline::metrics($items), 'timeline' => AchievementTimeline::byYear($items)];
    }
}
