<?php

declare(strict_types=1);

// Example data for the Inquiries & Complaints pages: the parent page shows parent #1's three, management sees all six. Read in ComplaintController.
// Replaced by real data (the complaints table) when the complaints backend is built.
// One inquiry: title, category (config/complaints.php), message, status ('in_progress' | 'resolved'), date
// (YYYY-MM-DD), student, grade, parent, parentId, response (the school's answer, shown once it is resolved).
return [
    'complaints' => [
        ['title' => 'Clarification on Chemistry Term Exam Syllabus', 'category' => 'Academic', 'status' => 'in_progress', 'date' => '2024-10-18',
         'message' => 'We noticed that the upcoming Chemistry term exam syllabus includes organic reactions which were not fully covered during regular classes prior to the revision week. Could the subject teacher please clarify the specific chapters tested?',
         'student' => 'Nethmi Perera', 'grade' => 'Grade 10-A', 'parent' => 'Mr. & Mrs. Perera', 'parentId' => 1,
         'response' => 'We have forwarded your note to the Head of Science Department. Mrs. Fernando will be sharing an updated revision outline during tomorrow morning class.'],
        ['title' => 'School Bus Route #4 Morning Arrival Timings', 'category' => 'Transport', 'status' => 'resolved', 'date' => '2024-10-10',
         'message' => 'Bus #4 has been arriving 20 minutes past the scheduled pickup window at Rajagiriya junction on rain days. This resulted in students almost missing the morning assembly twice last week.',
         'student' => 'Nethmi Perera', 'grade' => 'Grade 10-A', 'parent' => 'Mr. & Mrs. Perera', 'parentId' => 1,
         'response' => 'The driver route schedule has been adjusted with an earlier 6:40 AM departure from the depot on bad weather days. Timings have normalized and traffic monitoring is active.'],
        ['title' => 'Physics Lab Equipment Calibration Request', 'category' => 'Facilities', 'status' => 'in_progress', 'date' => '2024-10-15',
         'message' => 'During the recent practical preparation session, several digital multimeters and optical benches in Physics Lab 2 gave erratic measurements according to the students.',
         'student' => 'Kavindu Silva', 'grade' => 'Grade 11-B', 'parent' => 'Dr. Samantha Silva', 'parentId' => 2,
         'response' => ''],
        ['title' => 'Canteen Healthy Menu Options Query', 'category' => 'Facilities', 'status' => 'resolved', 'date' => '2024-09-28',
         'message' => 'Can we introduce more fresh fruit and unsweetened dairy options in the junior canteen for students with lactose sensitivity and diabetic family histories?',
         'student' => 'Dineth De Silva', 'grade' => 'Grade 8-C', 'parent' => 'Mrs. Nilmini De Silva', 'parentId' => 3,
         'response' => 'Thank you for the constructive suggestion. The catering contractor has introduced fresh seasonal cut fruit cups and low-sugar yogurt cups starting this week.'],
        ['title' => 'Extracurricular Club Clashing Times (Debating & Swimming)', 'category' => 'Extracurricular', 'status' => 'resolved', 'date' => '2024-09-15',
         'message' => 'Senior Debating society practice on Thursdays at 3:30 PM overlaps directly with Swim squad dry-land training. Is it possible for debating team A to practice on Tuesday afternoons instead?',
         'student' => 'Nethmi Perera', 'grade' => 'Grade 10-A', 'parent' => 'Mr. & Mrs. Perera', 'parentId' => 1,
         'response' => 'The Debating society Master-in-Charge has rescheduled the senior division practice slot to Tuesday 3:30 PM to avoid clash with aquatic disciplines.'],
        ['title' => 'Duplicate Fee Receipt Notification for Term 3', 'category' => 'Administration', 'status' => 'resolved', 'date' => '2024-09-10',
         'message' => 'We received two differing invoice reference emails for the third term facility maintenance fee. Please verify that our online bank transfer was credited only once.',
         'student' => 'Tariq Rasheed', 'grade' => 'Grade 9-B', 'parent' => 'Mr. Farhan Rasheed', 'parentId' => 4,
         'response' => 'Payment verified. Single credit confirmed against receipt #REC-88421. The duplicate reminder was generated due to an automated batch retry error and has been cleared.'],
    ],

    'parentId' => 1,   // the parent page shows this parent's

    // The New Inquiry pop-up's "Associated Student" choices
    'children' => ['Nethmi Perera (Grade 10-A)'],
];
