<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AchievementModel;
use App\Models\Assignments;
use App\Models\ClassModel;
use App\Models\ExtracurricularModel;
use App\Models\PeopleModel;
use App\Models\TeamModel;
use App\Services\AcademicWatch;
use App\Services\CalendarFeed;
use App\Services\Extracurriculars;
use App\Services\JoinRules;
use App\Services\TeacherChoices;
use Core\Controller;
use Core\Upload;
use Core\Validator;

// The Extracurricular pages for the four roles, and saving a programme. The server picks what each page shows:
//   the list (student, management, admin): search + type (+ the admin's Archived tab), the programme cards
//   one programme (?id=…): its panels for that role; the teacher's page: the programme they run (a list if several)
//   one achievement (?id=…&award=…, + &edit=1 to edit, award=new to add one)
// Who may do what: config/permissions.php 'extracurricular' — management and admin create and edit every programme
// (a management one waits for the admin), the admin approves / rejects, archives and restores, a teacher in charge
// edits their own programme's description, cover photo and session details, its teams and its achievements.
// The parent's Child profile shows the same list and programme pages for the child (ChildProfileController).
class ExtracurricularController extends Controller
{
    // GET /{role}/extracurricular[?q=…&type=sport|club|archived | ?id=3[&age=Under 15][&award=19[&edit=1]]]
    public function index(string $role): void
    {
        $user     = $this->requireRole($role);
        $listPath = "/{$role}/extracurricular";
        $id       = (int) $this->queryText('id');

        if ($role === 'teacher')
        {
            $runs = (new ExtracurricularModel())->runBy($user['id']);
            $id   = in_array($id, array_column($runs, 'id'), true) ? $id : (count($runs) === 1 ? $runs[0]['id'] : 0);

            if ($id === 0)
            {
                $this->page("{$role}/extracurricular", $user, ['runs' => $runs] + $this->listPage($user, $role, $listPath, []));
                return;
            }
        }

        $data = $id !== 0 ? $this->programmePage($user, $role, $id, $listPath, []) : $this->listPage($user, $role, $listPath, []);
        $this->page("{$role}/extracurricular", $user, $data);
    }

    // POST /{role}/extracurricular/save — a new programme (no id), or one part of one: 'programme' (the Edit pop-up),
    // 'teacher' (its teacher in charge), 'coach', 'details' (schedule and venue). Back to its page with the banner.
    public function save(string $role): never
    {
        $user   = $this->requireRole($role);
        $model  = new ExtracurricularModel();
        $id     = (int) $this->postText('id', 11) ?: null;
        $row    = $id === null ? null : $model->find($id);
        $part   = $id === null ? 'new' : $this->postText('part', 20);
        $back   = $id === null ? "/{$role}/extracurricular" : $this->programmeUrl($role, $id);
        $manage = can('extracurricular.manage');
        $runs   = $row !== null && $this->runs($row, $user);

        if ($id !== null && $row === null)
        {
            $this->refuse('That programme does not exist.', "/{$role}/extracurricular", 404);
        }

        if ($row !== null && $row['archived_at'] !== null)
        {
            $this->refuse('That programme is archived: restore it first.', $back, 409);
        }

        // What each form may change, and who may send it
        $fields = match (true)
        {
            $part === 'new' && $manage                => ['name', 'type', 'teacher', 'age_group_limit', 'description'],
            $part === 'programme' && $manage          => ['name', 'type', 'category', 'description', 'cover', 'uses_age_groups', 'open_to', 'lowest_grade', 'is_open',
                                                          'age_group_limit', 'teacher', 'coach_name', 'coach_specialty', 'coach_email', 'coach_phone'],
            $part === 'programme' && $runs            => ['description', 'cover'],
            $part === 'teacher' && $manage            => ['teacher'],
            $part === 'coach' && $manage              => ['coach_name', 'coach_specialty', 'coach_email', 'coach_phone'],
            $part === 'details' && ($manage || $runs) => ['schedule', 'venue'],
            default                                   => $this->refuse('You can\'t change that programme.', $back, 403),
        };

        $values  = $this->programmeValues($fields, $row, $back);
        $teacher = in_array('teacher', $fields, true) ? $values['teacher'] : false;   // false: not this form's business
        unset($values['teacher']);

        if ($id === null)
        {
            $values['uses_age_groups'] = $values['type'] === 'sport' ? '1' : '0';   // a new club starts without age groups
            $approved                  = can('extracurricular.approve');
            $id       = $model->add($values, $teacher, $approved, $user['id']);
            $this->log($user, 'PROGRAMME_ADDED', $values['name'] . ($approved ? '' : ' (waits for approval)'));
            $this->setFlash('notice', $approved ? 'Programme created.' : 'Programme created. It waits for the admin\'s approval.');
            $this->redirect($this->programmeUrl($role, $id));
        }

        $this->keepingUploads(fn () => $model->update($id, $values), [$values['cover_url'] ?? null]);

        if (isset($values['cover_url']))
        {
            Upload::forget($row['cover_url']);   // the photo it replaced (never the example photos under /assets/)
        }

        if ($teacher !== false && $teacher !== $row['tic_id'])
        {
            (new Assignments())->setTeacherInCharge($id, $teacher);
        }

        $this->log($user, 'PROGRAMME_UPDATED', ($values['name'] ?? $row['name']) . " ({$part})");
        $this->setFlash('notice', match ($part)
        {
            'teacher' => words('{Teacher} in Charge changed.'),
            'details' => 'Details saved.',
            default   => 'Programme saved.',
        });
        $this->redirect($this->postText('back', 10) === 'list' ? "/{$role}/extracurricular" : $back);
    }

    // POST /{role}/extracurricular/review (id, decision = approve | reject, note = why, needed for reject) — the admin
    // answers a programme waiting for approval. Rejected: removed; the audit log keeps it and why.
    public function review(string $role): never
    {
        $user     = $this->requireRole($role);
        $back     = "/{$role}/extracurricular";
        $model    = new ExtracurricularModel();
        $row      = $model->find((int) $this->postText('id', 11));
        $decision = $this->postText('decision', 10);
        $note     = $this->postText('note', 255);

        if (!can('extracurricular.approve'))
        {
            $this->refuse('Only the admin can approve programmes.', $back, 403);
        }

        if ($row === null || $row['review'] !== 'pending')
        {
            $this->refuse('That programme doesn\'t wait for approval any more.', $back, 409);
        }

        if (!in_array($decision, ['approve', 'reject'], true) || ($decision === 'reject' && $note === ''))
        {
            $this->refuse($decision === 'reject' ? 'Please say why it is rejected.' : 'Please choose Accept or Reject.', $back, 422);
        }

        if ($decision === 'approve')
        {
            $model->approve($row['id'], $user['id']);
            $this->log($user, 'PROGRAMME_APPROVED', $row['name']);
            $this->setFlash('notice', 'Programme approved.');
            $this->redirect($back);
        }

        $this->log($user, 'PROGRAMME_REJECTED', "{$row['name']}: {$note}");
        $model->remove($row['id']);
        $this->setFlash('notice', 'Programme rejected.');
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/archive (id, restore = 1 to bring it back) — the admin. Archived: hidden from the
    // lists and from joining, its history kept; it shows under the admin's Archived tab, with Restore.
    public function archive(string $role): never
    {
        $user    = $this->requireRole($role);
        $model   = new ExtracurricularModel();
        $row     = $model->find((int) $this->postText('id', 11));
        $restore = $this->postText('restore', 1) === '1';

        if (!can('extracurricular.approve'))
        {
            $this->refuse('Only the admin can archive programmes.', "/{$role}/extracurricular", 403);
        }

        if ($row === null || $row['review'] !== 'approved' || ($row['archived_at'] !== null) !== $restore)
        {
            $this->refuse('That programme can\'t be ' . ($restore ? 'restored.' : 'archived.'), "/{$role}/extracurricular", 409);
        }

        if ($restore)
        {
            $model->restore($row['id']);   // its old memberships stay ended: students ask again
        }
        else
        {
            $model->archive($row['id'], $user['id']);
            $model->endMemberships($row['id'], null, 'archived', $user['id']);
        }

        $this->log($user, $restore ? 'PROGRAMME_RESTORED' : 'PROGRAMME_ARCHIVED', $row['name']);
        $this->setFlash('notice', $restore ? "{$row['name']} restored." : "{$row['name']} archived.");
        $this->redirect($restore ? $this->programmeUrl($role, $row['id']) : "/{$role}/extracurricular?type=archived");
    }

    // POST /student/extracurricular/interest (id) — a student asks to join: their parent is asked first (Approvals), then
    // the teacher in charge. Only when JoinRules says they may (the same reasons the card shows).
    public function interest(string $role): never
    {
        $user    = $this->requireRole($role);
        $model   = new ExtracurricularModel();
        $row     = $model->find((int) $this->postText('id', 11));
        $back    = "/{$role}/extracurricular";
        $student = (new PeopleModel())->find($user['id']);

        if (!can('extracurricular.join') || $row === null)
        {
            $this->refuse('You can\'t join that programme.', $back, 403);
        }

        if (($model->statesOf($user['id'])[$row['id']]['status'] ?? 'declined') !== 'declined')
        {
            $this->refuse("You have already asked to join {$row['name']}.", $back, 409);
        }

        $whyNot = JoinRules::whyNot($row, $student, AcademicWatch::of($user['id']), $model->memberBirthdays()[$row['id']] ?? []);

        if ($whyNot !== null)
        {
            $this->refuse("You can't join {$row['name']} now: {$whyNot}.", $back, 422);
        }

        $model->ask($row['id'], $user['id']);
        $this->log($user, 'EXTRACURRICULAR_INTEREST', $row['name']);
        $this->setFlash('notice', words('Interest sent. Your {parent} is asked first.'));
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/request (id, student, decision = accept | reject, note = why, needed for reject) — the
    // teacher in charge (or management / admin) answers a request the parent said yes to. Accept: only if there's room.
    public function decide(string $role): never
    {
        $user     = $this->requireRole($role);
        $model    = new ExtracurricularModel();
        $row      = $this->membersProgramme($role, $user);
        $back     = $this->programmeUrl($role, $row['id']);
        $student  = (new PeopleModel())->find((int) $this->postText('student', 11));
        $decision = $this->postText('decision', 10);
        $note     = $this->postText('note', 255);
        $waiting  = $student !== null && ($model->statesOf($student['id'])[$row['id']]['status'] ?? null) === 'teacher';

        if (!$waiting)
        {
            $this->refuse('That request doesn\'t wait for you any more.', $back, 409);
        }

        if (!in_array($decision, ['accept', 'reject'], true) || ($decision === 'reject' && $note === ''))
        {
            $this->refuse($decision === 'reject' ? 'Please say why it is rejected.' : 'Please choose Accept or Reject.', $back, 422);
        }

        if ($decision === 'accept' && ($full = JoinRules::isFull($row, $student, $model->memberBirthdays()[$row['id']] ?? [])) !== null)
        {
            $this->refuse("{$row['name']} is {$full}.", $back, 422);
        }

        // Accepted, and exactly one team of their age group: they go straight into it (else they wait in "Unassigned")
        $fits = $decision === 'accept' ? (new TeamModel())->forAgeGroup($row['id'], $row['uses_age_groups'] ? JoinRules::ageGroup($student['date_of_birth']) : null) : [];

        if (!$model->decide($row['id'], $student['id'], $decision === 'accept', $decision === 'accept' ? null : $note, count($fits) === 1 ? array_key_first($fits) : null))
        {
            $this->refuse('That request doesn\'t wait for you any more.', $back, 409);   // answered by someone else meanwhile
        }

        $this->log($user, $decision === 'accept' ? 'EXTRACURRICULAR_ACCEPTED' : 'EXTRACURRICULAR_REJECTED',
            "{$student['full_name']} → {$row['name']}" . ($decision === 'reject' ? ": {$note}" : ''));
        $this->setFlash('notice', match (true)
        {
            $decision === 'reject' => 'Join request rejected.',
            count($fits) === 1     => "{$student['full_name']} enrolled and placed in " . reset($fits) . '.',
            default                => "{$student['full_name']} enrolled.",
        });
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/remove (id, student) — the teacher in charge (or management / admin) takes a member
    // out (no reason asked): the membership goes to the history, with today's date and who did it
    public function remove(string $role): never
    {
        $user    = $this->requireRole($role);
        $model   = new ExtracurricularModel();
        $row     = $this->membersProgramme($role, $user);
        $back    = $this->programmeUrl($role, $row['id']);
        $student = (new PeopleModel())->find((int) $this->postText('student', 11));

        if ($student === null || ($model->statesOf($student['id'])[$row['id']]['status'] ?? null) !== 'member')
        {
            $this->refuse('That ' . strtolower(word('student')) . ' is not a member.', $back, 409);
        }

        $model->endMemberships($row['id'], $student['id'], 'removed', $user['id']);
        $this->log($user, 'EXTRACURRICULAR_REMOVED', "{$student['full_name']} ← {$row['name']}");
        $this->setFlash('notice', "{$student['full_name']} removed from {$row['name']}.");
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/team (id, team = its id or empty for a new one, name, age_group, cover = a photo,
    // member[] + member_position[] = the roster rows) — the teacher in charge (or management / admin) adds or changes a
    // team. Only members, each in a team of their own age group; old places go to the history (TeamModel::save()).
    public function saveTeam(string $role): never
    {
        $user   = $this->requireRole($role);
        $row    = $this->membersProgramme($role, $user);
        $back   = $this->programmeUrl($role, $row['id']);
        $teams  = new TeamModel();
        $team   = $this->programmeTeam($teams, $row, $back, true);
        $name   = $this->postText('name', 100);
        $groups = $row['uses_age_groups'] ? array_keys(config('extracurricular.age_groups')) : [];
        $age    = $groups === [] ? null : $this->postText('age_group', 20);

        if ($name === '' || $teams->nameTaken($row['id'], $name, $team['id'] ?? null))
        {
            $this->refuse($name === '' ? 'Please give the team a name.' : "There is already a team called \"{$name}\".", $back, 422);
        }

        if ($age !== null && !in_array($age, $groups, true))
        {
            $this->refuse('Please choose the team\'s age group.', $back, 422);
        }

        // The roster: one row per member (a row left empty is skipped), each once, of the team's age group
        $members   = array_column((new ExtracurricularModel())->people($row['id'], 'member'), null, 'id');
        $positions = $this->postList('member_position');
        $roster    = [];

        foreach ($this->postList('member') as $i => $chosen)
        {
            $student  = $members[(int) $chosen] ?? null;
            $position = trim(mb_substr($positions[$i] ?? '', 0, 50));

            if ($chosen === '')
            {
                continue;
            }

            if ($student === null || array_key_exists($student['id'], $roster))
            {
                $this->refuse($student === null ? "Please choose members of {$row['name']} only." : "{$student['name']} is in the roster twice.", $back, 422);
            }

            if ($age !== null && JoinRules::ageGroup($student['date_of_birth']) !== $age)
            {
                $this->refuse("{$student['name']} is not in the {$age} age group.", $back, 422);
            }

            $roster[$student['id']] = $position === '' ? null : $position;
        }

        [$cover, $problem] = Upload::image($_FILES['cover'] ?? [], 'extracurricular', (int) config('extracurricular.cover_kb'));

        if ($problem !== null)
        {
            $this->refuse($problem, $back, 422);
        }

        $this->keepingUploads(fn () => $teams->save($team['id'] ?? null, $row['id'], ['name' => $name, 'age_group' => $age]
            + ($cover === null ? [] : ['cover_url' => $cover]), $roster), [$cover]);

        if ($cover !== null && $team !== null)
        {
            Upload::forget($team['cover_url']);   // the photo it replaced
        }

        $this->log($user, $team === null ? 'TEAM_ADDED' : 'TEAM_UPDATED', "{$name} ({$row['name']}, " . count($roster) . ' members)');
        $this->setFlash('notice', $team === null ? 'Team created.' : 'Team saved.');
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/assign (id, student, team) — "Assign" on a member not in a team yet: into a team of
    // their own age group, as a player (a position is set in the team's pop-up)
    public function assign(string $role): never
    {
        $user    = $this->requireRole($role);
        $row     = $this->membersProgramme($role, $user);
        $back    = $this->programmeUrl($role, $row['id']);
        $teams   = new TeamModel();
        $team    = $this->programmeTeam($teams, $row, $back, false);
        $student = array_column((new ExtracurricularModel())->people($row['id'], 'member'), null, 'id')[(int) $this->postText('student', 11)] ?? null;

        if ($student === null)
        {
            $this->refuse('That ' . strtolower(word('student')) . ' is not a member.', $back, 409);
        }

        if (!array_key_exists($team['id'], $teams->forAgeGroup($row['id'], $row['uses_age_groups'] ? JoinRules::ageGroup($student['date_of_birth']) : null)))
        {
            $this->refuse("{$student['name']} is not in {$team['name']}'s age group.", $back, 422);
        }

        $teams->assign($team['id'], $student['id']);
        $this->log($user, 'TEAM_ASSIGNED', "{$student['name']} → {$team['name']} ({$row['name']})");
        $this->setFlash('notice', "{$student['name']} assigned to {$team['name']}.");
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/team-delete (id, team) — the team goes; its members are back in "Unassigned", their
    // places kept in the history
    public function deleteTeam(string $role): never
    {
        $user  = $this->requireRole($role);
        $row   = $this->membersProgramme($role, $user);
        $back  = $this->programmeUrl($role, $row['id']);
        $teams = new TeamModel();
        $team  = $this->programmeTeam($teams, $row, $back, false);

        $teams->delete($team['id']);
        Upload::forget($team['cover_url']);
        $this->log($user, 'TEAM_DELETED', "{$team['name']} ({$row['name']})");
        $this->setFlash('notice', 'Team deleted.');
        $this->redirect($back);
    }

    // POST /{role}/extracurricular/award (id, award = its id or empty for a new one, title, date, venue, level,
    // tournament, organiser, place, medal, age_group, representing = a team or 'Individual', summary, others = names not
    // in the portal, participant[] = members, keep_photo[] = the gallery photos kept, in order, cover, gallery[] = new
    // photos) — the teacher in charge (or management / admin) adds or changes an achievement. It is the achievement of
    // every participant too: it shows on their timeline. Back to the achievement's page.
    public function saveAward(string $role): never
    {
        $user   = $this->requireRole($role);
        $row    = $this->membersProgramme($role, $user);
        $back   = $this->programmeUrl($role, $row['id']);
        $awards = new AchievementModel();
        $award  = $this->programmeAward($awards, $row, $back, true);
        $here   = $award === null ? "{$back}&award=new" : "{$back}&award={$award['id']}&edit=1";   // a refusal: back to the form
        $sent   = [];

        foreach (['title' => 150, 'date' => 10, 'venue' => 150, 'level' => 50, 'tournament' => 150, 'organiser' => 150, 'place' => 100,
                  'medal' => 50, 'age_group' => 20, 'representing' => 100, 'summary' => 600, 'others' => 255] as $field => $max)
        {
            $sent[$field] = $this->postText($field, $max);
        }

        $errors = Validator::check($sent, ['title' => 'required', 'date' => 'required|date', 'level' => 'required|in:' . implode(',', country('achievement_levels'))],
            ['title' => 'Achievement title', 'level' => 'Scope / level']);
        $teams  = array_column((new TeamModel())->ofProgramme($row['id']), 'name');
        $groups = $row['uses_age_groups'] ? array_keys(config('extracurricular.age_groups')) : [];

        if ($errors !== [] || $sent['date'] > date('Y-m-d'))
        {
            $this->refuse($errors !== [] ? reset($errors) : 'The date can\'t be in the future.', $here, 422);
        }

        // A team of the programme (its name now, or the one it was saved with) or 'Individual'; an age group of the programme's
        if (!in_array($sent['representing'], [...$teams, $award['team'] ?? 'Individual', 'Individual'], true)
            || ($sent['age_group'] !== '' && !in_array($sent['age_group'], $groups, true)))
        {
            $this->refuse('Please choose who it represents and its age group from the lists.', $here, 422);
        }

        // Its participants: members of the programme, or someone already on it (they may have left since)
        $allowed  = [...array_column((new ExtracurricularModel())->people($row['id'], 'member'), 'id'), ...array_column($award['students'] ?? [], 'id')];
        $students = array_values(array_unique(array_map('intval', $this->postList('participant'))));

        if (array_diff($students, array_map('intval', $allowed)) !== [])
        {
            $this->refuse("Please choose participants from the members of {$row['name']}.", $here, 422);
        }

        // The gallery: photos it already has (kept, in order) + the new ones, at most 'gallery_photos'
        $kept  = array_values(array_unique(array_map('intval', $this->postList('keep_photo'))));
        $files = Upload::each($_FILES['gallery'] ?? []);
        $limit = (int) config('extracurricular.gallery_photos');

        if (array_diff($kept, array_map('intval', array_column($award['photos'] ?? [], 'id'))) !== [] || count($kept) + count($files) > $limit)
        {
            $this->refuse(count($kept) + count($files) > $limit ? "The gallery can hold at most {$limit} photos." : 'That photo is not in this gallery.', $here, 422);
        }

        // Every photo is checked before anything is kept: one refused → the ones already saved go again
        $saved = [];

        foreach (['cover' => [$_FILES['cover'] ?? []], 'gallery' => $files] as $kind => $chosen)
        {
            foreach ($chosen as $file)
            {
                [$url, $problem] = Upload::image($file, 'extracurricular', (int) config('extracurricular.cover_kb'));

                if ($problem !== null)
                {
                    array_map([Upload::class, 'forget'], array_merge([], ...array_values($saved)));
                    $this->refuse($problem, $here, 422);
                }

                if ($url !== null)
                {
                    $saved[$kind][] = $url;
                }
            }
        }

        $empty  = fn (string $value): ?string => $value === '' ? null : $value;
        $others = implode(', ', array_filter(array_map('trim', explode(',', $sent['others']))));   // "A,B" / "A , B" → "A, B" (how it is read)
        $values = [
            'title' => $sent['title'], 'category' => $row['name'], 'level' => $sent['level'], 'achieved_on' => $sent['date'],
            'result' => $empty($sent['place']), 'medal' => $empty($sent['medal']), 'event_name' => $empty($sent['tournament']),
            'organiser' => $empty($sent['organiser']), 'venue' => $empty($sent['venue']), 'age_group' => $empty($sent['age_group']),
            'team' => $sent['representing'] === 'Individual' ? null : $sent['representing'], 'summary' => $sent['summary'],
            'others' => $empty($others),
        ] + (isset($saved['cover']) ? ['photo_url' => $saved['cover'][0]] : []);

        [$awardId, $unused] = $this->keepingUploads(fn () => $awards->save($award['id'] ?? null, $row['id'], $values, $students, $kept,
            $saved['gallery'] ?? [], $user['id']), array_merge([], ...array_values($saved)));
        array_map([Upload::class, 'forget'], $unused);   // replaced or removed photos (never the built-in ones under /assets/)

        $this->log($user, $award === null ? 'ACHIEVEMENT_ADDED' : 'ACHIEVEMENT_UPDATED', "{$sent['title']} ({$row['name']}, " . count($students) . ' participants)');
        $this->setFlash('notice', $award === null ? 'Achievement added.' : 'Achievement saved.');
        $this->redirect("{$back}&award={$awardId}");
    }

    // POST /{role}/extracurricular/award-delete (id, award) — the admin or the teacher in charge deletes an achievement
    // (from every participant's timeline too); its photos are deleted; the Audit Log keeps what it was
    public function deleteAward(string $role): never
    {
        $user   = $this->requireRole($role);
        $row    = $this->membersProgramme($role, $user);
        $back   = $this->programmeUrl($role, $row['id']);
        $awards = new AchievementModel();
        $award  = $this->programmeAward($awards, $row, $back, false);

        if (!$this->mayDeleteAwards($row, $user))
        {
            $this->refuse('Only the admin or the ' . strtolower(words('{teacher} in charge')) . ' can delete an achievement.', "{$back}&award={$award['id']}", 403);
        }

        array_map([Upload::class, 'forget'], $awards->delete($award['id']));
        $this->log($user, 'ACHIEVEMENT_DELETED', "{$award['title']} ({$row['name']}, {$award['achieved_on']})");
        $this->setFlash('notice', 'Achievement deleted.');
        $this->redirect($back);
    }

    // GET /{role}/extracurricular/member?id=…&student=… — a member's club view for the People pop-up (people-profile.js),
    // as JSON { ok, html }: what the teacher in charge needs to look after them (config/people.php 'partial' → 'club').
    // An unknown student and one who isn't a member get the same answer.
    public function member(string $role): never
    {
        $user   = $this->requireRole($role);
        $model  = new ExtracurricularModel();
        $row    = $model->find((int) $this->queryText('id', 11));
        $person = (new PeopleModel())->find((int) $this->queryText('student', 11));
        $theirs = $row !== null && $row['archived_at'] === null && $this->looksAfter($row, $user);

        if (!$theirs || $person === null || ($model->statesOf($person['id'])[$row['id']]['status'] ?? null) !== 'member')
        {
            $this->refuse('That account no longer exists.', "/{$role}/extracurricular", 404);
        }

        (new PeopleController())->sendProfile($person, null, '', 'club');
    }

    // POST /{role}/extracurricular/watch (student, action = lift | lock, reason = needed to lift) — management / admin lift
    // a student's Academic Watch for the term it was worked out from (or lock it again). Back to the page they came from.
    public function watch(string $role): never
    {
        $user    = $this->requireRole($role);
        $back    = $this->previousPage();
        $student = (new PeopleModel())->find((int) $this->postText('student', 11));
        $watch   = $student === null ? null : AcademicWatch::of($student['id']);
        $action  = $this->postText('action', 10);
        $reason  = $this->postText('reason', 255);

        if (!can('extracurricular.watch') || $watch === null)
        {
            $this->refuse('That ' . strtolower(word('student')) . ' is not on Academic Watch.', $back, 409);
        }

        if (!in_array($action, ['lift', 'lock'], true) || ($action === 'lift' && $reason === ''))
        {
            $this->refuse($action === 'lift' ? 'Please say why the lock is lifted.' : 'Please choose Lift Lock or Lock.', $back, 422);
        }

        (new ExtracurricularModel())->setWatchLift($student['id'], $watch['year'], $watch['term'], $action === 'lift' ? $reason : null, $user['id']);
        $this->log($user, $action === 'lift' ? 'ACADEMIC_WATCH_LIFTED' : 'ACADEMIC_WATCH_LOCKED', $student['full_name'] . ($action === 'lift' ? ": {$reason}" : ''));
        $this->setFlash('notice', $action === 'lift' ? "{$student['full_name']} may join clubs and sports again." : "{$student['full_name']} is locked again.");
        $this->redirect($back);
    }

    // The list's data (also the Child profile's Sports & Clubs tab): the filters, the cards, where the cards link to.
    // Those who manage see programmes waiting for approval (greyed); the admin also has the Archived tab.
    protected function listPage(array $user, string $role, string $listPath, array $listKeep): array
    {
        $types   = array_keys(config('extracurricular.types'));
        $wanted  = $this->queryText('type');
        $filters = [
            'q'    => $this->queryText('q'),
            'type' => in_array($wanted, $types, true) || ($wanted === 'archived' && can('extracurricular.approve')) ? $wanted : '',
        ];
        $archived = $filters['type'] === 'archived';
        $model    = new ExtracurricularModel();
        $rows     = $model->all(can('extracurricular.manage') || $role === 'teacher', $archived);

        // A teacher's list: only the programmes they run
        if ($role === 'teacher')
        {
            $theirs = array_column($model->runBy($user['id']), 'id');
            $rows   = array_values(array_filter($rows, fn (array $row): bool => in_array($row['id'], $theirs, true)));
        }

        return [
            'programme'  => null,
            'can'        => $this->listRights(),
            'filters'    => $filters,
            'programmes' => Extracurriculars::cards($rows, $this->studentRow($user, $role), ['q' => $filters['q'], 'type' => $archived ? '' : $filters['type']]),
            'emptyText'  => match (true)
            {
                $role === 'teacher' && $rows === [] => 'You are not in charge of any programme this year.',
                $archived                           => 'No archived programmes.',
                default                             => 'No activities match your search or filters.',
            },
            'teachers'   => $this->teacherOptions(),
            'listPath'   => $listPath,
            'listKeep'   => $listKeep,   // what the list's links keep (the Child profile's ?tab=clubs)
            'runs'       => null,        // the teacher's own programmes (several: the teacher's list)
        ];
    }

    // One programme (or one of its achievements) for this role (also the Child profile's, as a student sees it).
    // A student (and a parent through the child) sees only approved, open programmes; a teacher, the ones they run.
    protected function programmePage(array $user, string $role, int $id, string $listPath, array $listKeep): array
    {
        $listUrl = url_with($listPath, $listKeep);
        $row     = (new ExtracurricularModel())->find($id);
        $visible = $row !== null && match ($role)
        {
            'student' => $row['review'] === 'approved' && $row['archived_at'] === null,
            'teacher' => $this->runs($row, $user) && $row['archived_at'] === null,
            default   => can('extracurricular.manage'),
        };

        if (!$visible)
        {
            $this->refuse('That programme does not exist.', $listUrl, 404);
        }

        $studentId = $this->studentSeen($user, $role);
        $current   = $studentId === null ? null : ((new ExtracurricularModel())->statesOf($studentId)[$id] ?? null);
        $member    = $role !== 'student' || ($current['status'] ?? null) === 'member';   // staff, or a member: the coach's contact, the roster
        $programme = Extracurriculars::page($row, $current, $member, $studentId, $this->queryText('age'), $user);
        $archived  = $row['archived_at'] !== null;

        $can = [
            'edit'        => !$archived && $this->looksAfter($row, $user),      // Edit programme, details, teams, achievements; members:
                                                                                 // accept / reject, remove, their club view
            'editAll'     => !$archived && can('extracurricular.manage'),        // name, type, category, who can join, staff
            'editStaff'   => !$archived && can('extracurricular.manage'),        // the teacher in charge and the coach
            'archive'     => can('extracurricular.approve') && $row['review'] === 'approved',
            'deleteAward' => !$archived && $this->mayDeleteAwards($row, $user),  // an achievement's Danger Zone
        ];

        // Its addresses: the programme, and an award (+ its id or 'new')
        $programmeUrl = url_with($listPath, $listKeep + ['id' => $id]);
        $awardUrl     = $programmeUrl . '&award=';
        $wanted       = $this->queryText('award', 11);
        $award        = Extracurriculars::award($programme, $wanted, $can['edit']);

        if ($wanted !== '' && $award === null)
        {
            $this->refuse('That achievement does not exist.', $programmeUrl, 404);
        }

        return [
            'programme'      => $programme,
            'award'          => $award,
            'editing'        => $award !== null && ($award['id'] === 'new' || $this->queryText('edit') === '1') && $can['edit'],
            'can'            => $can,
            'teachers'       => $this->teacherOptions(),
            'gradeOptions'   => array_map(fn (array $grade): array => ['value' => (string) $grade['number'], 'label' => grade_name($grade['number'])],
                (new ClassModel())->grades()),
            'listUrl'        => $role === 'teacher' && count((new ExtracurricularModel())->runBy($user['id'])) < 2 ? null : $listUrl,
            'listPath'       => $listPath,
            'listKeep'       => $listKeep,
            'programmeUrl'   => $programmeUrl,
            'awardUrl'       => $awardUrl,
            'calendarConfig' => feature('calendar') ? CalendarFeed::config($user, "extracurricular:{$id}") : null,
        ];
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // What the list's buttons may do: Create card, "Change" the teacher in charge (manage), a waiting card's
    // Accept / Reject and the Archived tab (approve), Interest (join; the coach's phone only once enrolled)
    private function listRights(): array
    {
        return ['manage' => can('extracurricular.manage'), 'approve' => can('extracurricular.approve'), 'join' => can('extracurricular.join')];
    }

    // Whose memberships the page shows: the student's own, or (a parent through the Child profile) the child's
    private function studentSeen(array $user, string $role): ?int
    {
        return $role !== 'student' ? null : ($user['role'] === 'parent' ? $user['child'] : $user['id']);
    }

    // That student as JoinRules reads them (gender, grade_number, date_of_birth), or null
    private function studentRow(array $user, string $role): ?array
    {
        $id = $this->studentSeen($user, $role);

        return $id === null ? null : (new PeopleModel())->find($id);
    }

    // Who may do what with one programme (the rules in one place):
    //   runs           its teacher in charge this year
    //   looksAfter     its members, teams and achievements: its teacher in charge, or management / admin
    //   mayDeleteAwards  its achievements' Danger Zone: its teacher in charge, or the admin
    private function runs(array $row, array $user): bool
    {
        return (int) $row['tic_id'] === $user['id'];
    }

    private function looksAfter(array $row, array $user): bool
    {
        return can('extracurricular.manage') || $this->runs($row, $user);
    }

    private function mayDeleteAwards(array $row, array $user): bool
    {
        return can('extracurricular.approve') || $this->runs($row, $user);
    }

    // The programme a members' form is about, if this person looks after it (looksAfter()) and it isn't archived.
    // Anything else → refused (the same words for an unknown one).
    private function membersProgramme(string $role, array $user): array
    {
        $row = (new ExtracurricularModel())->find((int) $this->postText('id', 11));

        if ($row === null || $row['archived_at'] !== null || !$this->looksAfter($row, $user))
        {
            $this->refuse('You can\'t change that programme\'s members.', "/{$role}/extracurricular", 403);
        }

        return $row;
    }

    // The team a form is about ('team'): one of this programme's, or null for a new one ($newAllowed). Anything else →
    // refused.
    private function programmeTeam(TeamModel $teams, array $row, string $back, bool $newAllowed): ?array
    {
        $teamId = (int) $this->postText('team', 11);
        $team   = $teamId === 0 ? null : $teams->find($teamId);

        if (($team === null && !($teamId === 0 && $newAllowed)) || ($team !== null && (int) $team['extracurricular_id'] !== (int) $row['id']))
        {
            $this->refuse('That team does not exist.', $back, 404);
        }

        return $team;
    }

    // The achievement a form is about ('award'): one of this programme's (with its students and photos), or null for a
    // new one ($newAllowed). Anything else, or the achievements module switched off → refused.
    private function programmeAward(AchievementModel $awards, array $row, string $back, bool $newAllowed): ?array
    {
        $awardId = (int) $this->postText('award', 11);
        $award   = $awardId === 0 ? null : $awards->find($awardId);

        if (!feature('achievements') || ($award === null && !($awardId === 0 && $newAllowed))
            || ($award !== null && (int) $award['extracurricular_id'] !== (int) $row['id']))
        {
            $this->refuse('That achievement does not exist.', $back, 404);
        }

        return $award;
    }

    // The teacher pickers' options: "Assignment pending" (nobody) first, then every teacher who isn't switched off
    private function teacherOptions(): array
    {
        return TeacherChoices::options((new ClassModel())->teachers(), false);
    }

    private function programmeUrl(string $role, int $id): string
    {
        return "/{$role}/extracurricular?id={$id}";
    }

    // The sent values of these fields, checked; a problem → back to the page with the first message.
    // 'teacher' → an id or null (nobody yet); 'cover' → the uploaded photo's address (only when one was chosen).
    private function programmeValues(array $fields, ?array $row, string $back): array
    {
        $sent   = [];
        $rules  = [
            'name'            => 'required|max:100',
            'type'            => 'required|in:' . implode(',', array_keys(config('extracurricular.types'))),
            'category'        => 'in:' . implode(',', config('extracurricular.categories')),
            'description'     => 'max:1000',
            'age_group_limit' => 'number|between:1,1000',
            'uses_age_groups' => 'in:0,1',
            'open_to'         => 'in:everyone,boys,girls',
            'lowest_grade'    => 'in:' . implode(',', array_column((new ClassModel())->grades(), 'number')),
            'is_open'         => 'in:0,1',
            'coach_name'      => 'max:150',
            'coach_specialty' => 'max:150',
            'coach_email'     => 'email|max:191',
            'coach_phone'     => 'max:30',
            'schedule'        => 'max:150',
            'venue'           => 'max:150',
        ];

        foreach (array_diff($fields, ['teacher', 'cover']) as $field)
        {
            $sent[$field] = $this->postText($field, 1000);
        }

        $labels = ['age_group_limit' => 'Limit of ' . strtolower(plural('student')) . ' per age group', 'coach_email' => 'Coach email',
                   'lowest_grade' => 'Lowest ' . strtolower(word('grade'))];
        // An empty optional field is fine: only check what was filled in (and the required ones)
        $checked = array_filter($sent, fn (string $value, string $field): bool => $value !== '' || str_contains($rules[$field], 'required'), ARRAY_FILTER_USE_BOTH);
        $errors  = Validator::check($checked, array_intersect_key($rules, $checked), $labels);

        if ($errors !== [])
        {
            $this->refuse(reset($errors), $back, 422);
        }

        if (isset($sent['name']) && (new ExtracurricularModel())->nameTaken($sent['name'], $row['id'] ?? null))
        {
            $this->refuse("There is already a programme called \"{$sent['name']}\".", $back, 422);
        }

        if (in_array('teacher', $fields, true))
        {
            $teacher = $this->postText('teacher', 11);
            $known   = (new ClassModel())->teachers();

            if ($teacher !== '' && !isset($known[(int) $teacher]))
            {
                $this->refuse('Please choose a ' . strtolower(word('teacher')) . ' from the list.', $back, 422);
            }

            $sent['teacher'] = $teacher === '' ? null : (int) $teacher;
        }

        if (in_array('cover', $fields, true))
        {
            [$cover, $problem] = Upload::image($_FILES['cover'] ?? [], 'extracurricular', (int) config('extracurricular.cover_kb'));

            if ($problem !== null)
            {
                $this->refuse($problem, $back, 422);
            }

            if ($cover !== null)
            {
                $sent['cover_url'] = $cover;
            }
        }

        // Empty optional fields are "not set"; a setting that must have a value and came empty stays as it was
        foreach (['age_group_limit', 'lowest_grade', 'category'] as $optional)
        {
            if (array_key_exists($optional, $sent) && $sent[$optional] === '')
            {
                $sent[$optional] = null;
            }
        }

        foreach (['uses_age_groups', 'open_to', 'is_open'] as $setting)
        {
            if (($sent[$setting] ?? null) === '')
            {
                unset($sent[$setting]);
            }
        }

        if (($sent['type'] ?? $row['type'] ?? '') === 'sport' && array_key_exists('uses_age_groups', $sent))
        {
            $sent['uses_age_groups'] = '1';
        }

        return $sent;
    }
}
