<?php

declare(strict_types=1);

// Achievements. Each category: the colour of its tag (a tag tone, tag.css: c-tag--{tone}) and the timeline group it
// belongs to ('groups' below: a student's timeline in People's profile pop-up filters by group).
// Read with config('achievements.categories.Football.tone'). A category not listed here gets 'linen' / 'clubs'.
return [
    'categories' => [
        'Academic'   => ['tone' => 'sky',        'group' => 'academic'],
        'Sports'     => ['tone' => 'maroon',     'group' => 'sports'],
        'Football'   => ['tone' => 'maroon',     'group' => 'sports'],      // a result is tagged with the sport itself
        'Athletics'  => ['tone' => 'sunshine',   'group' => 'sports'],
        'Debating'   => ['tone' => 'sky',        'group' => 'clubs'],
        'Music'      => ['tone' => 'terracotta', 'group' => 'clubs'],
        'Arts'       => ['tone' => 'terracotta', 'group' => 'clubs'],
        'Robotics'   => ['tone' => 'sand',       'group' => 'clubs'],
        'Clubs'      => ['tone' => 'sunshine',   'group' => 'clubs'],
        'Leadership' => ['tone' => 'grey',       'group' => 'leadership'],
    ],

    // The timeline's 4 groups (its filter buttons): key => [button word, the Record form's type, tag colour (tag.css)].
    // Each timeline item names its group; the dot's colour follows the group (achievements-timeline.css).
    'groups' => [
        'academic'   => ['label' => 'Academic',   'type' => 'Academic',          'tone' => 'ink'],
        'sports'     => ['label' => 'Sports',     'type' => 'Sports',            'tone' => 'rose'],
        'clubs'      => ['label' => 'Clubs',      'type' => 'Clubs & Societies', 'tone' => 'fern'],
        'leadership' => ['label' => 'Leadership', 'type' => 'Leadership',        'tone' => 'violet'],
    ],
];
