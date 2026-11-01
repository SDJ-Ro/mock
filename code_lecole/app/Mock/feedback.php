<?php

declare(strict_types=1);

// Example data for the Feedback pages: every parent sees the same six, every teacher the same four. Read in FeedbackController.
// Replaced by real data (the feedback table) when the feedback backend is built.
// One feedback: type (config/feedback.php 'types'), subject (its title), teacher, teacherRole, student, parent (the
// teacher's page only: the parent line), date (YYYY-MM-DD), text (the whole feedback; the card shows the start).
return [
    // Parent → Teacher Feedback: what the teachers wrote about the child
    'parent' => [
        ['type' => 'positive', 'subject' => 'Mathematics', 'teacher' => 'Mr. Fernando', 'teacherRole' => 'Mathematics Teacher',
         'student' => 'Nethmi Perera', 'parent' => '', 'date' => '2024-10-25',
         'text' => 'Nethmi has shown remarkable improvement in algebra this term. She actively participates in class discussions and consistently helps her peers during group exercises. Her dedication to mastering complex concepts is truly commendable.'],
        ['type' => 'constructive', 'subject' => 'English Literature', 'teacher' => 'Ms. Perera', 'teacherRole' => 'English Teacher',
         'student' => 'Nethmi Perera', 'parent' => '', 'date' => '2024-10-18',
         'text' => 'Nethmi possesses great analytical skills when breaking down poetry. However, she needs to focus more on her essay structure and time management during written assessments. I recommend she practices outlining her thoughts before writing.'],
        ['type' => 'positive', 'subject' => 'Science', 'teacher' => 'Mrs. Ratnayake', 'teacherRole' => 'Science Teacher',
         'student' => 'Nethmi Perera', 'parent' => '', 'date' => '2024-09-30',
         'text' => 'Outstanding project presentation on renewable energy! Nethmi went above and beyond the syllabus requirements, demonstrating a deep understanding of the subject matter and excellent presentation skills.'],
        ['type' => 'negative', 'subject' => 'Discipline & Punctuality', 'teacher' => 'Mr. Wickramasinghe', 'teacherRole' => 'Sectional Head',
         'student' => 'Nethmi Perera', 'parent' => '', 'date' => '2024-09-22',
         'text' => 'Nethmi was recorded as arriving late on three consecutive Monday mornings, and her Chemistry lab journal submission was delayed by a full week without prior notice. Please ensure stricter adherence to morning attendance and submission deadlines.'],
        ['type' => 'constructive', 'subject' => 'History', 'teacher' => 'Mr. Silva', 'teacherRole' => 'History Teacher',
         'student' => 'Nethmi Perera', 'parent' => '', 'date' => '2024-09-15',
         'text' => 'Nethmi has a good grasp of historical facts, but she is often hesitant to share her thoughts during class discussions. Encouraging her to speak up will help build her confidence and improve her overall engagement.'],
        ['type' => 'positive', 'subject' => 'Art & Design', 'teacher' => 'Ms. Jayasinghe', 'teacherRole' => 'Art Teacher',
         'student' => 'Nethmi Perera', 'parent' => '', 'date' => '2024-09-05',
         'text' => 'Exceptional creativity in the recent watercolor project! Nethmi has a great eye for color blending and composition. Her artwork was selected to be displayed in the main exhibition hallway.'],
    ],

    // Teacher → Feedback Channel (with the parent line)
    'teacher' => [
        ['type' => 'positive', 'subject' => 'Mathematics Term 1 Performance Inquiry', 'teacher' => 'Mr. Fernando', 'teacherRole' => 'Mathematics Teacher',
         'student' => 'Nethmi Perera', 'parent' => 'Suresh Perera', 'date' => '2026-03-12',
         'text' => 'Dear teacher, I would like to inquire about Nethmi\'s progress in Mathematics Term 1. She has been practicing daily at home and has developed a keen interest in algebra problems. We appreciate the extra worksheets provided.'],
        ['type' => 'negative', 'subject' => 'Regarding Term 1 Exam Schedule & Revision', 'teacher' => 'Mr. Fernando', 'teacherRole' => 'Mathematics Teacher',
         'student' => 'Amara Silva', 'parent' => 'Kavinda Silva', 'date' => '2026-03-10',
         'text' => 'Could you please clarify the severe discrepancy in the revision timetable sent home last Friday? Students were given overlapping revision periods with insufficient lead time before the assessment.'],
        ['type' => 'positive', 'subject' => 'Science Project Materials Submission', 'teacher' => 'Mrs. Ratnayake', 'teacherRole' => 'Science Teacher',
         'student' => 'Maya Kapoor', 'parent' => 'Mrs. Kapoor', 'date' => '2026-03-08',
         'text' => 'Submitting the required documentation and materials for Maya\'s upcoming Science project assessment on renewable solar cells. All safety protocols have been reviewed and documented.'],
        ['type' => 'constructive', 'subject' => 'Extra Curricular Activities Participation', 'teacher' => 'Mr. Perera', 'teacherRole' => 'Physical Education Head',
         'student' => 'John Doe', 'parent' => 'Mark Doe (Guardian)', 'date' => '2026-03-05',
         'text' => 'Inquiring about John\'s schedule for upcoming athletics and swimming club activities. He wants to balance his academic studies alongside athletics meets and requests practical advice.'],
    ],

    // The "New Feedback" pop-up's choices
    'students' => [
        'Nethmi Perera (2021/0456) - Grade 10-A',
        'Amara Silva (2021/0203) - Grade 10-B',
        'Maya Kapoor (2022/0118) - Grade 9-A',
        'John Doe (2021/0892) - Grade 10-A',
        'Dilan Jayasuriya (2021/0501) - Grade 11-C',
    ],
    'subjects' => ['Mathematics', 'Science', 'English Literature', 'History', 'Art & Design', 'Physical Education', 'General Conduct'],
];
