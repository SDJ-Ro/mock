<?php

declare(strict_types=1);

// Feedback (the teacher's Feedback Channel and the parent's Teacher Feedback pages): the three kinds and how each looks.
// Read with config('feedback.types'). type → [its name, its icon (icons.php)]; the colours: feedback.css
// (.c-feedback-card--{type}: positive moss, constructive sunshine, negative maroon).
return [
    'types' => [
        'positive'     => ['Positive', 'starFilled'],
        'constructive' => ['Constructive', 'trendingUp'],
        'negative'     => ['Negative', 'alertCircle'],
    ],
];
