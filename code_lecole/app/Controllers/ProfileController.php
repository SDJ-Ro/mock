<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PeopleModel;
use App\Models\UserModel;
use App\Services\PersonDetails;
use Core\Controller;
use Core\Upload;
use Core\Validator;

// My Profile (every role): see your details, change the ones config/profile.php allows, change your password.
// Both changes are normal forms: the server checks them, saves, and shows the page again with a "saved" message.
// The cards each role sees: config/profile.php 'layout'; example values (details nobody stores yet): app/Mock/profiles.php.
class ProfileController extends Controller
{
    // GET /{role}/profile. After a refused save the page opens in edit mode with what was typed.
    public function show(string $role): void
    {
        $user  = $this->requireRole($role);
        $typed = $this->getFlash('profile_form');

        $this->page("{$role}/profile", $user, [
            'subtitle'      => config("profile.subtitles.{$role}"),
            'profile'       => $this->profile((new PeopleModel())->find($user['id']), $typed ?? []),
            'editing'       => $typed !== null,
            'error'         => $this->getFlash('error'),   // a refused save: inside the page ("Changes saved." → the frame's banner)
            'passwordError' => $this->getFlash('password_error'),
        ]);
    }

    // POST /{role}/profile/save — only the details this role may change (config/profile.php 'editable')
    public function save(string $role): never
    {
        $user    = $this->requireRole($role);
        $back    = "/{$role}/profile";
        $columns = config("profile.editable.{$role}");
        $values  = [];

        foreach ($columns as $column)
        {
            $values[$column] = $this->postText($column, 300);
        }

        $errors = Validator::check($values, array_intersect_key(config('profile.rules'), $values), config('profile.labels'));

        if ($errors !== [])
        {
            $this->setFlash('profile_form', $values);
            $this->refuse(reset($errors), $back, 422);
        }

        (new UserModel())->updateProfile($user['id'], $values);
        $this->log($user, 'PROFILE_UPDATED', implode(', ', $columns));

        $this->setFlash('notice', 'Changes saved.');
        $this->redirect($back);
    }

    // POST /{role}/profile/photo — a new photo (PNG / JPG / WebP, at most config/profile.php 'photo_kb'; core/Upload.php
    // looks inside the file). The old one is deleted, and the sidebar shows the new one at once (the session keeps it).
    public function photo(string $role): never
    {
        $user = $this->requireRole($role);
        $back = "/{$role}/profile";

        [$url, $error] = Upload::image($_FILES['photo'] ?? [], 'photos', (int) config('profile.photo_kb'));

        if ($error !== null || $url === null)
        {
            $this->refuse($error ?? 'Please choose a photo first.', $back, 422);
        }

        $users = new UserModel();
        $old   = $users->findProfile($user['id'])['photo_url'] ?? null;

        $users->setPhoto($user['id'], $url);
        Upload::forget($old);
        $_SESSION['user']['photo'] = $url;
        $this->log($user, 'PHOTO_CHANGED', $url);

        $this->setFlash('notice', 'Photo updated.');
        $this->redirect($back);
    }

    // POST /{role}/profile/password — needs the current password; the typed passwords are never kept or shown again
    public function password(string $role): never
    {
        $user     = $this->requireRole($role);
        $back     = "/{$role}/profile";
        $users    = new UserModel();
        $account  = $users->findByIdentifier($user['identifier']);
        $current  = $this->postPassword('current_password');
        $password = $this->postPassword('password');
        $problem  = null;

        if (!password_verify($current, (string) $account['password_hash']))
        {
            $problem = "That isn't your current password.";
            $this->log($user, 'PASSWORD_CHANGE_FAILED', 'wrong current password');
        }
        elseif (($missing = UserModel::passwordProblems($password)) !== [])
        {
            $problem = 'Your new password needs ' . implode(', ', $missing) . '.';
        }
        elseif ($password !== $this->postPassword('password_confirm'))
        {
            $problem = 'Passwords do not match.';
        }
        elseif ($password === $current)
        {
            $problem = 'Choose a password different from your current one.';
        }

        if ($problem !== null)
        {
            $this->refuse($problem, $back, 422, 'password_error');
        }

        $users->changePassword($user['id'], password_hash($password, PASSWORD_DEFAULT));
        session_regenerate_id(true);   // a new session ID, like after signing in
        $this->log($user, 'PASSWORD_CHANGED', 'from My Profile');

        $this->setFlash('notice', 'Password updated.');
        $this->redirect($back);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    // One person → what components/_profile_page.php draws: the hero and the sections of cards (config/profile.php 'layout').
    // $typed = what was just typed into the boxes (after a refused save), shown again instead of the saved value.
    private function profile(array $person, array $typed): array
    {
        $role     = $person['role'];
        $layout   = config("profile.layout.{$role}");
        $editable = config("profile.editable.{$role}");
        $values   = PersonDetails::values($person);
        $mock     = (require dirname(__DIR__) . '/Mock/profiles.php')[$role];

        // [label, icon, where from, 'wide'?] → one card. The label's {Class}, {Student}… become the school's words.
        $card = function (array $field) use ($mock, $values, $editable, $typed, $person): array
        {
            $column = in_array($field[2], $editable, true) ? $field[2] : null;   // the profiles column its box saves to

            return [
                'label' => words($field[0]),
                'icon'  => $field[1],
                'value' => ($field[2] === 'mock' ? $mock[$field[0]] : ($values[$field[2]] ?? '')) ?: '—',
                'edit'  => $column,
                'raw'   => $column === null ? '' : ($typed[$column] ?? (string) $person[$column]),   // what the box holds when editing
                'wide'  => ($field[3] ?? null) === 'wide',
            ];
        };
        $section = fn (string $title, string $icon, array $fields, int $columns = 3, ?string $tint = null): array =>
            ['title' => words($title), 'icon' => $icon, 'columns' => $columns, 'tint' => $tint, 'cards' => array_map($card, $fields), 'password' => false];

        $sections = [$section('Contact Information', 'phone', $layout['contact']), $section('Personal Details', 'user', $layout['personal'])];

        if ($layout['role'] !== null)
        {
            [$title, $icon, $tint, $fields] = $layout['role'];
            $sections[] = $section($title, $icon, $fields, 3, $tint);
        }

        foreach ($layout['extra'] as [$title, $icon, $fields])
        {
            $sections[] = $section($title, $icon, $fields);
        }

        // The last section carries "Change Password" for the roles that may change it here
        $sections[] = ['password' => can('profile.password')] + $section('Account Information', 'lockKeyhole', $layout['account'], 4);

        return [
            'hero'      => [
                'role'    => $role,
                'name'    => $person['full_name'],
                'eyebrow' => $this->eyebrow($person, $values, $mock),
                'sub'     => $values['portal'] . ' · ' . school('name') . ' ' . word('institution') . ' Management',
                'id'      => $values['code'],
                'status'  => ucfirst(strtolower($person['activation_status'])),
                'photo'   => $person['photo_url'] ?: config("portals.{$role}.avatar"),
                'photoTo' => $editable !== [] ? "/{$role}/profile/photo" : null,   // where a new photo goes (students: none)
            ],
            'sections'  => $sections,
            'canEdit'   => $editable !== [],
        ];
    }

    // The line above the name: the role's wording, with the real detail where the school has it
    private function eyebrow(array $person, array $values, array $mock): string
    {
        return match ($person['role'])
        {
            'admin'      => $mock['eyebrow'],
            'management' => $person['job_title'] ?: $mock['eyebrow'],
            'teacher'    => $values['subject'] ? "Senior Faculty · {$values['subject']} Department" : 'Faculty',
            'parent'     => word('parent') . ' / Guardian' . ($person['relationship'] ? " · {$person['relationship']}" : ''),
            'student'    => word('student') . ($person['class_name'] ? " · {$person['class_name']}" : ''),
        };
    }
}
