<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;
use DomainException;
use PDO;

// Everyone in the school: user_accounts + profiles + their role's details (student_details, parent_details,
// staff_details, staff_qualifications), and the checks around them (a parent's active children, a staff member's
// personal email, a teacher's own students).
// Used by My Profile (find, teaching, extracurriculars) and People (the rest).
class PeopleModel extends Model
{
    // Each role's own details table
    private const DETAILS_TABLE = [
        'student'    => 'student_details',
        'parent'     => 'parent_details',
        'teacher'    => 'staff_details',
        'management' => 'staff_details',
    ];

    // The fields kept in profiles (every role); any other field of config/people.php 'fields' is in DETAILS_TABLE
    public const PROFILE_COLUMNS = ['first_name', 'last_name', 'date_of_birth', 'gender', 'national_id', 'personal_email',
                                    'phone', 'address', 'emergency_name', 'emergency_phone'];

    // ---------------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------------

    // The account, the profile and the role's own details in one row, plus their parents / children (and a
    // teacher's qualifications). Null when there is no such person.
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.identifier, u.role, u.activation_status, u.last_login_at, u.created_at,
                    p.*, cs.class_id, sd.birth_certificate, sd.admission_date, sd.previous_school, sd.blood_group,
                    sd.medical_notes, sd.religion, sd.nationality, sd.address_area, sd.address_district, sd.address_region,
                    c.name AS class_name, g.sort_order AS grade_number, ct.full_name AS class_teacher,
                    pd.relationship, pd.occupation, pd.employer, pd.passport, pd.home_phone, pd.office_phone,
                    st.job_title, st.subjects, st.join_date, st.experience_years,
                    COALESCE(pd.office_address, st.office_address) AS office_address
             FROM user_accounts u
             JOIN profiles p ON p.account_id = u.id
             LEFT JOIN student_details sd ON sd.account_id = u.id
             LEFT JOIN current_class_students cs ON cs.student_id = u.id
             LEFT JOIN classes c ON c.id = cs.class_id
             LEFT JOIN grades g ON g.id = c.grade_id
             LEFT JOIN current_class_teachers cct ON cct.class_id = c.id
             LEFT JOIN profiles ct ON ct.account_id = cct.teacher_id
             LEFT JOIN parent_details pd ON pd.account_id = u.id
             LEFT JOIN staff_details st ON st.account_id = u.id
             WHERE u.id = :id'
        );
        $statement->execute(['id' => $id]);
        $person = $statement->fetch();

        if ($person === false)
        {
            return null;
        }

        $person['parents']        = $this->linked('student_account_id', 'parent_account_id', $id);
        $person['children']       = $this->linked('parent_account_id', 'student_account_id', $id);
        $person['qualifications'] = $person['role'] === 'teacher' ? $this->qualifications($id) : [];

        return $person;
    }

    // One page of the directory for one role. Returns [rows, total]. $filters (each optional):
    //   q (search), status ('' all · 'on' · 'off'),
    //   students: grade_id, class_id, extracurricular_id, teacher_id (only the classes this teacher teaches)
    //   teachers: subject · parents: relationship
    public function directory(string $role, array $filters, int $page, int $perPage): array
    {
        $where  = ['u.role = :role'];
        $params = ['role' => $role];

        if (($filters['q'] ?? '') !== '')
        {
            $where[] = '(p.full_name LIKE :q OR p.code LIKE :q2 OR u.identifier LIKE :q3)';
            $params += ['q' => self::like($filters['q']), 'q2' => self::like($filters['q']), 'q3' => self::like($filters['q'])];
        }

        // 'on' = not switched off (active, or added and not activated yet) · 'off' = switched off
        if (($filters['status'] ?? '') !== '')
        {
            $where[] = $filters['status'] === 'off' ? "u.activation_status = 'INACTIVE'" : "u.activation_status <> 'INACTIVE'";
        }

        if ($role === 'student' && ($filters['class_id'] ?? null) !== null)
        {
            $where[]            = 'cs.class_id = :class_id';
            $params['class_id'] = $filters['class_id'];
        }

        if ($role === 'student' && ($filters['grade_id'] ?? null) !== null)
        {
            $where[]            = 'c.grade_id = :grade_id';
            $params['grade_id'] = $filters['grade_id'];
        }

        if ($role === 'student' && ($filters['extracurricular_id'] ?? null) !== null)
        {
            $where[]                   = "u.id IN (SELECT student_id FROM extracurricular_members WHERE extracurricular_id = :extracurricular AND status = 'member')";
            $params['extracurricular'] = $filters['extracurricular_id'];
        }

        if ($role === 'student' && ($filters['teacher_id'] ?? null) !== null)
        {
            $where[]           = 'cs.class_id IN (SELECT class_id FROM current_teaching WHERE teacher_id = :teacher)';
            $params['teacher'] = $filters['teacher_id'];
        }

        // A subject: one they teach (what their row shows), or one they are qualified to teach ("Science, Physics")
        if ($role === 'teacher' && ($filters['subject'] ?? '') !== '')
        {
            $where[]            = '(u.id IN (SELECT cst.teacher_id FROM current_subject_teachers cst JOIN stage_subjects ss ON ss.id = cst.subject_id
                                             WHERE ss.name = :subject) OR st.subjects LIKE :subject2)';
            $params['subject']  = $filters['subject'];
            $params['subject2'] = self::like($filters['subject']);
        }

        if ($role === 'parent' && ($filters['relationship'] ?? '') !== '')
        {
            $where[]                = 'pd.relationship = :relationship';
            $params['relationship'] = $filters['relationship'];
        }

        $from = 'FROM user_accounts u
                 JOIN profiles p ON p.account_id = u.id
                 LEFT JOIN current_class_students cs ON cs.student_id = u.id
                 LEFT JOIN classes c ON c.id = cs.class_id
                 LEFT JOIN parent_details pd ON pd.account_id = u.id
                 LEFT JOIN staff_details st ON st.account_id = u.id
                 WHERE ' . implode(' AND ', $where);

        $count = $this->db->prepare("SELECT COUNT(*) {$from}");
        $count->execute($params);

        // A student's row shows their primary parent (the lists of a row: rowLists())
        $parent = 'SELECT pp.%s FROM student_parents sp JOIN profiles pp ON pp.account_id = sp.parent_account_id
                   WHERE sp.student_account_id = u.id ORDER BY sp.is_primary DESC LIMIT 1';
        $rows   = $this->db->prepare(
            "SELECT u.id, u.identifier, u.activation_status, p.code, p.full_name, p.first_name, p.last_name,
                    p.personal_email, p.phone, p.photo_url, c.name AS class_name, pd.relationship, st.job_title, st.subjects,
                    (" . sprintf($parent, 'personal_email') . ") AS parent_email,
                    (" . sprintf($parent, 'full_name') . ") AS parent_name,
                    (" . sprintf($parent, 'phone') . ") AS parent_phone
             {$from}
             ORDER BY p.first_name, p.last_name
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value)
        {
            $rows->bindValue($key, $value);
        }

        $rows->bindValue('limit', $perPage, PDO::PARAM_INT);              // LIMIT needs real numbers
        $rows->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $rows->execute();

        return [$rows->fetchAll(), (int) $count->fetchColumn()];
    }

    // The lists one page of directory rows shows, for those people's ids: [id => [...]]
    //   student: 'extracurriculars' => [['name', 'type'], …]
    //   teacher: 'subjects' => [subject => [class names]] (their profile), 'homeroom' => class name (their row)
    //   parent:  'children' => [['name', 'class_name'], …]
    public function rowLists(string $role, array $ids): array
    {
        $lists = array_fill_keys($ids, []);
        $in    = self::idList($ids);   // whole numbers only (Model::idList)

        if ($role === 'student')
        {
            $rows = $this->db->query(
                "SELECT em.student_id AS id, x.name, x.type FROM extracurricular_members em JOIN extracurriculars x ON x.id = em.extracurricular_id
                 WHERE em.status = 'member' AND em.student_id IN ({$in}) ORDER BY x.name"
            );

            foreach ($rows->fetchAll() as $row)
            {
                $lists[$row['id']]['extracurriculars'][] = $row;
            }
        }

        if ($role === 'teacher')
        {
            $rows = $this->db->query(
                "SELECT cst.teacher_id AS id, ss.name AS subject, c.name AS class_name FROM current_subject_teachers cst
                 JOIN stage_subjects ss ON ss.id = cst.subject_id JOIN classes c ON c.id = cst.class_id
                 WHERE cst.teacher_id IN ({$in}) ORDER BY ss.name, LENGTH(c.name), c.name"
            );

            foreach ($rows->fetchAll() as $row)
            {
                $lists[$row['id']]['subjects'][$row['subject']][] = $row['class_name'];
            }

            $homerooms = $this->db->query(
                "SELECT ct.teacher_id AS id, c.name FROM current_class_teachers ct JOIN classes c ON c.id = ct.class_id WHERE ct.teacher_id IN ({$in})"
            );

            foreach ($homerooms->fetchAll() as $row)
            {
                $lists[$row['id']]['homeroom'] = $row['name'];
            }
        }

        if ($role === 'parent')
        {
            $rows = $this->db->query(
                "SELECT sp.parent_account_id AS id, p.full_name AS name, c.name AS class_name FROM student_parents sp
                 JOIN profiles p ON p.account_id = sp.student_account_id
                 LEFT JOIN current_class_students cs ON cs.student_id = p.account_id LEFT JOIN classes c ON c.id = cs.class_id
                 WHERE sp.parent_account_id IN ({$in}) ORDER BY p.first_name"
            );

            foreach ($rows->fetchAll() as $row)
            {
                $lists[$row['id']]['children'][] = $row;
            }
        }

        return $lists;
    }

    // What a teacher looks after this year, with the ids the handover needs (all three empty = nothing to hand over):
    //   'homeroom'   ['id', 'name'] of the class they are class teacher of, or null
    //   'subjects'   [['class_id', 'subject_id', 'class_name', 'subject'], …] the subjects they teach, per class
    //   'extracurriculars' [['id', 'name', 'type'], …] the clubs / sports they are in charge of
    public function duties(int $teacherId): array
    {
        $find = function (string $sql) use ($teacherId): array
        {
            $statement = $this->db->prepare($sql);
            $statement->execute(['me' => $teacherId]);

            return $statement->fetchAll();
        };

        return [
            'homeroom'         => $find('SELECT c.id, c.name FROM current_class_teachers ct JOIN classes c ON c.id = ct.class_id WHERE ct.teacher_id = :me')[0] ?? null,
            'subjects'         => $find(
                'SELECT cst.class_id, cst.subject_id, c.name AS class_name, ss.name AS subject
                 FROM current_subject_teachers cst
                 JOIN classes c ON c.id = cst.class_id
                 JOIN stage_subjects ss ON ss.id = cst.subject_id
                 WHERE cst.teacher_id = :me
                 ORDER BY ss.name, LENGTH(c.name), c.name'
            ),
            'extracurriculars' => $find(
                'SELECT x.id, x.name, x.type FROM current_teachers_in_charge tic JOIN extracurriculars x ON x.id = tic.extracurricular_id
                 WHERE tic.teacher_id = :me ORDER BY x.name'
            ),
        ];
    }

    // The directory's filter choices: clubs / sports [id => name], subjects (every curriculum's, once each)
    public function extracurricularChoices(): array
    {
        return $this->pairs('SELECT id, name FROM extracurriculars ORDER BY name');
    }

    public function subjectChoices(): array
    {
        return $this->db->query('SELECT DISTINCT name FROM stage_subjects ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
    }

    // Parents a new student can be linked to (not switched off) whose name, code, email, phone, ID card number or a
    // child's name contains $search. At most 20, by name, each with their children's names ("Nethmi Perera (6-A)").
    public function searchParents(string $search): array
    {
        $like = self::like($search);

        return $this->parentRows(
            '(p.full_name LIKE :a OR p.code LIKE :b OR u.identifier LIKE :c OR p.phone LIKE :d OR p.national_id LIKE :e
              OR EXISTS (SELECT 1 FROM student_parents sp2 JOIN profiles cp2 ON cp2.account_id = sp2.student_account_id
                         WHERE sp2.parent_account_id = u.id AND cp2.full_name LIKE :f))',
            ['a' => $like, 'b' => $like, 'c' => $like, 'd' => $like, 'e' => $like, 'f' => $like]
        );
    }

    // One parent the way searchParents() shows them (the picker's chosen parent), or null: unknown or switched off
    public function parentRow(int $id): ?array
    {
        return $this->parentRows('u.id = :id', ['id' => $id])[0] ?? null;
    }

    // How many of a parent's children are still at the school (not switched off). A parent with any can't be
    // switched off (every student keeps an active parent).
    public function activeChildren(int $parentId): int
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM student_parents sp JOIN user_accounts u ON u.id = sp.student_account_id
             WHERE sp.parent_account_id = :parent AND u.activation_status <> 'INACTIVE'"
        );
        $statement->execute(['parent' => $parentId]);

        return (int) $statement->fetchColumn();
    }

    // A parent's children, by name: account id → first name (the sidebar's "Viewing" switch, Parent → Approvals)
    public function childrenOf(int $parentId): array
    {
        return $this->pairs('SELECT p.account_id, p.first_name FROM student_parents sp
            JOIN profiles p ON p.account_id = sp.student_account_id WHERE sp.parent_account_id = :me ORDER BY p.first_name', $parentId);
    }

    // Is this sign-in ID / email already used by any account?
    public function identifierTaken(string $identifier, ?int $exceptId = null): bool
    {
        return $this->exists('SELECT 1 FROM user_accounts WHERE identifier = :value AND id <> :except', $identifier, $exceptId);
    }

    // Is this personal email already someone else's? (staff: where their mail goes, so it must be unique)
    public function personalEmailTaken(string $email, ?int $exceptId = null): bool
    {
        return $this->exists('SELECT 1 FROM profiles WHERE personal_email = :value AND account_id <> :except', $email, $exceptId);
    }

    // Is this birth certificate number already registered to another student?
    public function birthCertificateTaken(string $number, ?int $exceptId = null): bool
    {
        return $this->exists('SELECT 1 FROM student_details WHERE birth_certificate = :value AND account_id <> :except', $number, $exceptId);
    }

    // Is this national ID number already registered to another person?
    public function nationalIdTaken(string $number, ?int $exceptId = null): bool
    {
        return $this->exists('SELECT 1 FROM profiles WHERE national_id = :value AND account_id <> :except', $number, $exceptId);
    }

    // A teacher's classes this year: the one they are class teacher of, and every class they teach (names, e.g. "10-A")
    public function teaching(int $teacherId): array
    {
        $statement = $this->db->prepare(
            'SELECT c.name, ct.class_id IS NOT NULL AS is_mine
             FROM current_teaching t
             JOIN classes c ON c.id = t.class_id
             LEFT JOIN current_class_teachers ct ON ct.class_id = t.class_id AND ct.teacher_id = t.teacher_id
             WHERE t.teacher_id = :me
             ORDER BY c.name'
        );
        $statement->execute(['me' => $teacherId]);
        $classes = $statement->fetchAll();

        return [
            'homeroom' => array_column(array_filter($classes, fn (array $class): bool => (bool) $class['is_mine']), 'name')[0] ?? null,
            'classes'  => array_values(array_unique(array_column($classes, 'name'))),
        ];
    }

    // Clubs / sports by name: the ones a teacher runs this year, or the ones a student is a member of → ['sport' => [...], 'club' => [...]]
    public function extracurriculars(array $person): array
    {
        $sql = $person['role'] === 'teacher'
            ? 'SELECT x.type, x.name FROM current_teachers_in_charge tic JOIN extracurriculars x ON x.id = tic.extracurricular_id WHERE tic.teacher_id = :me ORDER BY x.name'
            : "SELECT x.type, x.name FROM extracurriculars x JOIN extracurricular_members em ON em.extracurricular_id = x.id
               WHERE em.student_id = :me AND em.status = 'member' ORDER BY x.name";
        $statement = $this->db->prepare($sql);
        $statement->execute(['me' => $person['id']]);
        $names = ['sport' => [], 'club' => []];

        foreach ($statement->fetchAll() as $extracurricular)
        {
            $names[$extracurricular['type']][] = $extracurricular['name'];
        }

        return $names;
    }

    // A student's clubs and sports with where they stand: [['name', 'type', 'position', 'status'], …]
    // (status: 'member' · 'parent' / 'teacher' = waiting for their yes · 'declined'; position: their teams and positions
    // there, "Under 15 Squad · Captain", or null)
    public function memberships(int $studentId): array
    {
        $statement = $this->db->prepare(
            "SELECT x.name, x.type, em.status,
                    (SELECT GROUP_CONCAT(CONCAT(t.name, IFNULL(CONCAT(' · ', tm.position), '')) ORDER BY t.name SEPARATOR ', ')
                     FROM team_members tm JOIN teams t ON t.id = tm.team_id
                     WHERE t.extracurricular_id = x.id AND tm.student_id = em.student_id) AS position
             FROM extracurricular_members em JOIN extracurriculars x ON x.id = em.extracurricular_id
             WHERE em.student_id = :id ORDER BY x.name"
        );
        $statement->execute(['id' => $studentId]);

        return $statement->fetchAll();
    }

    // Does this teacher teach this student this year (class teacher or a subject in their class)?
    public function teaches(int $teacherId, int $studentId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM current_class_students cs JOIN current_teaching t ON t.class_id = cs.class_id
             WHERE cs.student_id = :student AND t.teacher_id = :teacher'
        );
        $statement->execute(['student' => $studentId, 'teacher' => $teacherId]);

        return $statement->fetchColumn() !== false;
    }

    // Is this teacher the student's class teacher this year?
    public function isClassTeacherOf(int $teacherId, int $studentId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM current_class_students cs JOIN current_class_teachers ct ON ct.class_id = cs.class_id
             WHERE cs.student_id = :student AND ct.teacher_id = :teacher'
        );
        $statement->execute(['student' => $studentId, 'teacher' => $teacherId]);

        return $statement->fetchColumn() !== false;
    }

    // A teacher's qualifications, in the order typed: [['degree', 'institution', 'year_obtained'], …]
    public function qualifications(int $teacherId): array
    {
        $statement = $this->db->prepare('SELECT degree, institution, year_obtained FROM staff_qualifications WHERE account_id = :id ORDER BY sort_order, id');
        $statement->execute(['id' => $teacherId]);

        return $statement->fetchAll();
    }

    // ---------------------------------------------------------------------
    // Adding
    // ---------------------------------------------------------------------

    // Add a person: a PENDING account with no password (they activate it on the sign-up page) + profile + details
    // (+ a teacher's qualifications). A student also gets a class place this year ($details['class_id']) and a parent
    // link. All or nothing. Returns the new account id.
    public function add(string $role, array $profile, array $details, ?int $parentId = null, array $qualifications = []): int
    {
        return $this->transaction(function () use ($role, $profile, $details, $parentId, $qualifications): int
        {
            $assignments = new Assignments();
            $classId     = $role === 'student' ? (int) $details['class_id'] : null;
            unset($details['class_id']);   // a place, not a detail: class_students

            if ($classId !== null && !$assignments->hasRoom($classId))
            {
                throw new DomainException('This ' . strtolower(word('class')) . ' is full. Please choose another one.');
            }

            // Students sign in with their code; staff with a school email; parents with their own email
            $identifier = match ($role)
            {
                'student' => 'new-' . bin2hex(random_bytes(6)),   // replaced by the code below, once the id is known
                'parent'  => strtolower($profile['personal_email']),
                default   => $this->schoolEmail($profile['first_name'], $profile['last_name']),
            };

            $this->db->prepare("INSERT INTO user_accounts (identifier, role, activation_status) VALUES (:identifier, :role, 'PENDING')")
                ->execute(['identifier' => $identifier, 'role' => $role]);

            $id   = (int) $this->db->lastInsertId();
            $code = $this->code($role, $id);

            if ($role === 'student')
            {
                $this->db->prepare('UPDATE user_accounts SET identifier = :code WHERE id = :id')->execute(['code' => $code, 'id' => $id]);
            }

            $this->insert('profiles', ['account_id' => $id, 'code' => $code] + $profile);
            $this->insert(self::DETAILS_TABLE[$role], ['account_id' => $id] + $details);
            $this->saveQualifications($id, $qualifications);

            if ($classId !== null)
            {
                $assignments->placeStudent($id, $classId);
                $this->db->prepare('INSERT INTO student_parents (student_account_id, parent_account_id, is_primary) VALUES (:student, :parent, 1)')
                    ->execute(['student' => $id, 'parent' => $parentId]);
            }

            return $id;
        });
    }

    // A new student AND their new parent together: both are saved, or neither is. Returns [student id, parent id].
    public function addStudentWithNewParent(array $student, array $studentDetails, array $parent, array $parentDetails): array
    {
        return $this->transaction(function () use ($student, $studentDetails, $parent, $parentDetails): array
        {
            $parentId  = $this->add('parent', $parent, $parentDetails);
            $studentId = $this->add('student', $student, $studentDetails, $parentId);

            return [$studentId, $parentId];
        });
    }

    // ---------------------------------------------------------------------
    // Changing
    // ---------------------------------------------------------------------

    // Save edited details (and a teacher's qualifications, replaced as a whole). A student moving class needs a
    // free place in the new class. $parentId: a student's new main parent (their old main link is replaced).
    public function update(int $id, string $role, array $profile, array $details, ?int $parentId = null, ?array $qualifications = null): void
    {
        $this->transaction(function () use ($id, $role, $profile, $details, $parentId, $qualifications): void
        {
            $assignments = new Assignments();
            $classId     = $role === 'student' && isset($details['class_id']) ? (int) $details['class_id'] : null;
            unset($details['class_id']);   // a place, not a detail: class_students

            $nowIn = (int) $this->find($id)['class_id'];

            if ($classId !== null && $classId !== $nowIn)
            {
                // Once a term of this year is published, the class's positions stand: no moving until next year
                if ($nowIn !== 0 && (new TermMarkModel())->anyPublished($nowIn, (new SchoolYear())->current()))
                {
                    throw new DomainException(words('This {student}\'s {class} has published {term} marks this year, so they stay in it until next year.'));
                }

                if (!$assignments->hasRoom($classId))
                {
                    throw new DomainException('That ' . strtolower(word('class')) . ' is full. Please choose another one.');
                }

                $assignments->placeStudent($id, $classId);
            }

            $this->updateRow('profiles', $profile, $id);
            $this->updateRow(self::DETAILS_TABLE[$role], $details, $id);

            if ($qualifications !== null)
            {
                $this->db->prepare('DELETE FROM staff_qualifications WHERE account_id = :id')->execute(['id' => $id]);
                $this->saveQualifications($id, $qualifications);
            }

            // A parent's email is also how they sign in
            if ($role === 'parent' && isset($profile['personal_email']))
            {
                $this->db->prepare('UPDATE user_accounts SET identifier = :email WHERE id = :id')
                    ->execute(['email' => strtolower($profile['personal_email']), 'id' => $id]);
            }

            // The new parent replaces the main one (and is not kept twice if they were already a second parent)
            if ($role === 'student' && $parentId !== null)
            {
                $this->db->prepare('DELETE FROM student_parents WHERE student_account_id = :student AND (is_primary = 1 OR parent_account_id = :parent)')
                    ->execute(['student' => $id, 'parent' => $parentId]);
                $this->db->prepare('INSERT INTO student_parents (student_account_id, parent_account_id, is_primary) VALUES (:student, :parent, 1)')
                    ->execute(['student' => $id, 'parent' => $parentId]);
            }
        });
    }

    // A teacher leaves: each of their duties (duties(), each with 'to' = the teacher taking it over, or null = nobody
    // yet: "Assignment pending" on the Academic page) is passed on, then their account is switched off. All or nothing.
    public function handOver(int $teacherId, array $duties): void
    {
        $this->transaction(function () use ($teacherId, $duties): void
        {
            $assignments = new Assignments();

            if ($duties['homeroom'] !== null)
            {
                $assignments->setClassTeacher((int) $duties['homeroom']['id'], $duties['homeroom']['to']);
            }

            foreach ($duties['subjects'] as $subject)
            {
                $assignments->setSubjectTeacher((int) $subject['class_id'], (int) $subject['subject_id'], $subject['to']);
            }

            foreach ($duties['extracurriculars'] as $extracurricular)
            {
                $assignments->setTeacherInCharge((int) $extracurricular['id'], $extracurricular['to']);
            }

            $this->switchAccount($teacherId, false);
        });
    }

    // Switch an account off (INACTIVE) or back on (ACTIVE if they have a password, otherwise PENDING).
    // Switching a student off ends their club / sport memberships that day (kept in the history; $by switched them
    // off) and switches off a parent who has no other children still at the school; switching them back on brings such
    // a parent back (not the memberships). Returns the names of anyone else who changed with them.
    public function setActive(int $id, bool $active, int $by): array
    {
        return $this->transaction(function () use ($id, $active, $by): array
        {
            $alsoChanged = [];
            $this->switchAccount($id, $active);

            if (!$active)
            {
                (new ExtracurricularModel())->endMemberships(null, $id, 'left_school', $by);
            }

            foreach ($this->linked('student_account_id', 'parent_account_id', $id) as $parent)
            {
                $stillHere = $this->activeChildren((int) $parent['id']);

                if (($active && $parent['activation_status'] === 'INACTIVE') || (!$active && $stillHere === 0))
                {
                    $this->switchAccount((int) $parent['id'], $active);
                    $alsoChanged[] = $parent['full_name'];
                }
            }

            return $alsoChanged;
        });
    }

    // ---------------------------------------------------------------------
    // Small helpers
    // ---------------------------------------------------------------------

    // "STU-2026-0013" from the format in config/white_label/academic.php
    private function code(string $role, int $id): string
    {
        $format = config("white_label.academic.id_formats.{$role}", strtoupper(substr($role, 0, 3)) . '-{year}-{number}');

        return str_replace(['{year}', '{number}'], [date('Y'), str_pad((string) $id, 4, '0', STR_PAD_LEFT)], $format);
    }

    // "nimal_fernando@" + the school's domain, or "nimal_fernando2@…" if that's taken
    private function schoolEmail(string $firstName, string $lastName): string
    {
        $tidy   = fn (string $name): string => preg_replace('/[^a-z]/', '', strtolower($name));
        $base   = $tidy($firstName) . '_' . $tidy($lastName);
        $domain = school('email_domain');
        $email  = "{$base}@{$domain}";

        for ($number = 2; $this->identifierTaken($email); $number++)
        {
            $email = "{$base}{$number}@{$domain}";
        }

        return $email;
    }

    // Writes a teacher's qualifications in the order given; empty degrees are left out
    private function saveQualifications(int $id, array $qualifications): void
    {
        $insert = $this->db->prepare(
            'INSERT INTO staff_qualifications (account_id, degree, institution, year_obtained, sort_order) VALUES (:id, :degree, :institution, :year, :position)'
        );

        foreach (array_values(array_filter($qualifications, fn (array $q): bool => ($q['degree'] ?? '') !== '')) as $position => $q)
        {
            $insert->execute(['id' => $id, 'degree' => $q['degree'], 'institution' => $q['institution'] ?: null,
                'year' => $q['year_obtained'] ?: null, 'position' => $position]);
        }
    }

    // The people linked to $id through student_parents (a student's parents, or a parent's children), primary first
    private function linked(string $myColumn, string $theirColumn, int $id): array
    {
        // Both column names come from the calls in this file, never from a form
        $statement = $this->db->prepare(
            "SELECT u.id, u.identifier, u.role, u.activation_status, p.code, p.first_name, p.full_name, p.phone, c.name AS class_name
             FROM student_parents link
             JOIN user_accounts u ON u.id = link.{$theirColumn}
             JOIN profiles p ON p.account_id = u.id
             LEFT JOIN current_class_students cs ON cs.student_id = u.id
             LEFT JOIN classes c ON c.id = cs.class_id
             WHERE link.{$myColumn} = :id
             ORDER BY link.is_primary DESC, p.full_name"
        );
        $statement->execute(['id' => $id]);

        return $statement->fetchAll();
    }

    // "contains this text" for LIKE: a typed % or _ is looked for as itself, not as a wildcard
    private static function like(string $text): string
    {
        return '%' . addcslashes($text, '%_\\') . '%';
    }

    // Parents not switched off who match $where (written in this file, never from a form), with their children
    private function parentRows(string $where, array $params): array
    {
        $statement = $this->db->prepare(
            "SELECT u.id, u.identifier, p.code, p.full_name, p.phone, pd.relationship,
                    (SELECT GROUP_CONCAT(CONCAT(cp.full_name, ' (', COALESCE(c.name, '—'), ')') ORDER BY cp.full_name SEPARATOR ', ')
                     FROM student_parents sp JOIN profiles cp ON cp.account_id = sp.student_account_id
                     LEFT JOIN current_class_students cs ON cs.student_id = sp.student_account_id LEFT JOIN classes c ON c.id = cs.class_id
                     WHERE sp.parent_account_id = u.id) AS children
             FROM user_accounts u
             JOIN profiles p ON p.account_id = u.id
             JOIN parent_details pd ON pd.account_id = u.id
             WHERE u.role = 'parent' AND u.activation_status <> 'INACTIVE' AND {$where}
             ORDER BY p.full_name
             LIMIT 20"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function switchAccount(int $id, bool $active): void
    {
        $this->db->prepare(
            "UPDATE user_accounts
             SET activation_status = IF(:active, IF(password_hash IS NULL, 'PENDING', 'ACTIVE'), 'INACTIVE')
             WHERE id = :id"
        )->execute(['active' => (int) $active, 'id' => $id]);
    }

    private function exists(string $sql, string $value, ?int $exceptId): bool
    {
        $statement = $this->db->prepare($sql . ' LIMIT 1');
        $statement->execute(['value' => $value, 'except' => $exceptId ?? 0]);

        return $statement->fetchColumn() !== false;
    }

    // INSERT INTO table (column names from the array keys) — table and keys come from our own code, never from users
    private function insert(string $table, array $values): void
    {
        $columns = implode(', ', array_keys($values));
        $holes   = implode(', ', array_map(fn (string $column): string => ":{$column}", array_keys($values)));

        $this->db->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$holes})")->execute($values);
    }

    // UPDATE table SET column = :column ... WHERE account_id = :id — keys from our own code, never from users
    private function updateRow(string $table, array $values, int $id): void
    {
        if ($values === [])
        {
            return;
        }

        $set = implode(', ', array_map(fn (string $column): string => "{$column} = :{$column}", array_keys($values)));

        $this->db->prepare("UPDATE {$table} SET {$set} WHERE account_id = :account_id")->execute($values + ['account_id' => $id]);
    }
}
