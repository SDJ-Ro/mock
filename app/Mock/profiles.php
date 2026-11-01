<?php

declare(strict_types=1);

// Example values on My Profile for the details nobody stores yet, shown read-only. Each one is a detail still to build: once it has a column,
// its card in config/profile.php points at the real value and its line here is deleted.
// Read in ProfileController::show(): [role][card label] => value ('eyebrow' = the line above the name).
return [
    'admin' => [
        'eyebrow'               => 'System Administrator & IT Director',
        'Central IT Office'     => '+94 11 234 5678',
        'Emergency NOC Line'    => '+94 11 999 8877',
        'Nationality'           => 'Sri Lankan',
        'Qualifications'        => 'B.Sc in Computer Science, CISA Certified',
        'Central Station'       => 'Central Server Facility · Server Room A',
        'Access Level'          => 'Full Superadmin Privileges · Tier 4 Root',
        'Department'            => 'Central IT & Platform Operations',
        'Infrastructure Node'   => 'Colombo Regional Cloud Node · AWS ap-southeast-1',
        'Security Audit Log'    => 'Level 4 Immutable Audit Log Active',
        'Reports To'            => 'Principal & Board of Trustees',
        'Session Security'      => 'Mandatory 2FA · IP Whitelist Enforced',
        'Modules Managed'       => 'User Directory, RBAC Roles, Cloud Infrastructure, Security Audits',
        'Compliance Standard'   => 'FERPA & GDPR Educational Data Compliance Verified',
        'Maintenance Window'    => 'Weekly Scheduled Window: Sundays 02:00 – 04:00 UTC',
        'Disaster Recovery RPO' => 'RPO: 15 mins · RTO: 1 hour (Certified Q1 2026)',
        'Portal Role'           => 'System Administrator',
    ],
    'management' => [
        'eyebrow'         => 'Senior Executive Management',
        'Office Line'     => '+94 11 555 1200',
        'Office Location' => 'Main Admin Building · Suite 102',
    ],
    'teacher' => [
        'Department Extension' => '+94 11 234 5678 ext. 204',
        'Qualifications'       => 'B.Sc. Education (Hons), Dip. Secondary Teaching',
        'Staff Room Desk'      => 'Senior Wing · Floor 2 · Desk 14',
        'Employment Status'    => 'Permanent Full-Time Faculty',
    ],
    'parent' => [
        'Alternative Phone' => '+94 11 456 7890 (Home)',
        'PTA Member'        => 'Active Member · Executive Committee 2025/26',
        'Communications'    => 'SMS & Email Alerts Enabled',
        'Emergency Pick-Up' => 'Authorized Primary Collector',
    ],
    'student' => [
        'Emergency Line'   => '+94 11 456 7890',
        'Nationality'      => 'Sri Lankan',
        'Religion'         => 'Buddhism',
        'House'            => 'Emerald House',
        'Prefect Role'     => 'Junior Prefect (2025/2026)',
        'Attendance Rate'  => '96.4% this term',
        'Educational Zone' => 'Colombo Zone 3',
        'District'         => 'Colombo',
        'Province'         => 'Western',
        'Academic Year'    => '2025 / 2026',
    ],
];
