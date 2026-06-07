<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Category;
use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\ClassTeacher;
use App\Models\EducationalProgram;
use App\Models\ElectiveSubjectGroup;
use App\Models\Event;
use App\Models\Exam;
use App\Models\ExamClass;
use App\Models\ExamMarks;
use App\Models\ExamResult;
use App\Models\ExamTimetable;
use App\Models\Faq;
use App\Models\FeesChoiceable;
use App\Models\FeesClass;
use App\Models\FeesPaid;
use App\Models\FeesType;
use App\Models\FormField;
use App\Models\Grade;
use App\Models\Holiday;
use App\Models\InstallmentFee;
use App\Models\Leave;
use App\Models\LeaveDetail;
use App\Models\LeaveMaster;
use App\Models\Lesson;
use App\Models\LessonTopic;
use App\Models\Mediums;
use App\Models\MultipleEvent;
use App\Models\Notification;
use App\Models\OnlineExam;
use App\Models\OnlineExamQuestion;
use App\Models\OnlineExamQuestionAnswer;
use App\Models\OnlineExamQuestionChoice;
use App\Models\OnlineExamQuestionOption;
use App\Models\OnlineExamStudentAnswer;
use App\Models\Parents;
use App\Models\PaymentTransaction;
use App\Models\School;
use App\Models\Section;
use App\Models\Semester;
use App\Models\SessionYear;
use App\Models\Shift;
use App\Models\Slider;
use App\Models\Stream;
use App\Models\StudentOnlineExamStatus;
use App\Models\Students;
use App\Models\StudentSessions;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\WebSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class MultiSchoolDataSeeder extends Seeder
{
    /**
     * School definitions. Each gets a complete data set.
     *
     * @var array<int, array<string, string>>
     */
    private array $schoolDefs = [
        [
            'name' => 'Greenwood International School',
            'email' => 'info@greenwood.edu',
            'phone' => '9800000001',
            'address' => 'Kathmandu, Nepal',
            'admin_email' => 'admin@greenwood.edu',
            'admin_name' => 'Rajesh',
            'admin_last' => 'Shrestha',
        ],
        [
            'name' => 'Bluebell Academy',
            'email' => 'info@bluebell.edu',
            'phone' => '9800000002',
            'address' => 'Pokhara, Nepal',
            'admin_email' => 'admin@bluebell.edu',
            'admin_name' => 'Sunita',
            'admin_last' => 'Karki',
        ],
    ];

    /**
     * Per-school runtime data store.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $schoolData = [];

    public function run(): void
    {
        $this->command->info('Starting multi-school data seeding...');

        $this->seedGlobalData();
        $this->createSchools();
        $this->seedRolesAndPermissions();

        foreach ($this->schoolDefs as $idx => $def) {
            $school = School::where('email', $def['email'])->first();
            if (! $school) {
                continue;
            }
            $this->command->info("Seeding data for school: {$school->name} (ID: {$school->id})");
            $this->seedForSchool($school, $idx);
        }

        $this->command->info('Multi-school data seeding completed!');
    }

    private function createSchools(): void
    {
        $this->command->info('Creating schools...');
        foreach ($this->schoolDefs as $def) {
            School::firstOrCreate(
                ['email' => $def['email']],
                [
                    'name' => $def['name'],
                    'phone' => $def['phone'],
                    'address' => $def['address'],
                    'status' => 1,
                ]
            );
        }
    }

    private function seedRolesAndPermissions(): void
    {
        $this->command->info('Ensuring roles and global super admin exist...');

        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Parent', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Attendee Teacher', 'guard_name' => 'web']);

        User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'first_name' => 'super',
                'last_name' => 'admin',
                'password' => Hash::make('superadmin'),
                'gender' => 'Male',
                'image' => 'logo.svg',
                'mobile' => '',
                'school_id' => null,
                'created_by' => null,
                'status' => 1,
            ]
        );
    }

    private function seedGlobalData(): void
    {
        $this->command->info('Seeding global data (shared across all schools)...');

        // Settings
        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['type' => 'app_settings'],
                ['type' => 'app_settings', 'message' => json_encode([
                    'school_name' => 'Multi-School Platform',
                    'school_email' => 'platform@school.com',
                    'school_phone' => '0000000000',
                    'school_address' => 'Nepal',
                    'timezone' => 'Asia/Kathmandu',
                    'currency_symbol' => 'Rs.',
                    'currency_code' => 'NPR',
                    'payment_options' => ['cash' => 'Cash'],
                ])]
            );
        }

        // Languages
        if (Schema::hasTable('languages')) {
            DB::table('languages')->updateOrInsert(
                ['code' => 'en'],
                ['name' => 'English', 'code' => 'en', 'file' => 'en.json', 'is_rtl' => 0, 'status' => 1]
            );
            DB::table('languages')->updateOrInsert(
                ['code' => 'ne'],
                ['name' => 'Nepali', 'code' => 'ne', 'file' => 'ne.json', 'is_rtl' => 0, 'status' => 1]
            );
        }

        // Sliders
        Slider::firstOrCreate(
            ['image' => 'sliders/school.jpg'],
            ['image' => 'sliders/school.jpg', 'type' => 'school']
        );
        Slider::firstOrCreate(
            ['image' => 'sliders/event.jpg'],
            ['image' => 'sliders/event.jpg', 'type' => 'event']
        );

        // FAQs
        Faq::firstOrCreate(
            ['question' => 'What are the school timings?'],
            ['question' => 'What are the school timings?', 'answer' => 'School runs from 9 AM to 4 PM, Monday through Friday.', 'status' => 1]
        );
        Faq::firstOrCreate(
            ['question' => 'How to pay fees online?'],
            ['question' => 'How to pay fees online?', 'answer' => 'You can pay fees through the student/parent app.', 'status' => 1]
        );

        // Web Settings
        WebSetting::firstOrCreate(
            ['name' => 'Platform Name'],
            ['name' => 'Platform Name', 'tag' => 'header', 'heading' => 'Multi-School Platform', 'content' => 'Learning for Everyone', 'image' => 'logo.svg', 'status' => 1]
        );
        WebSetting::firstOrCreate(
            ['name' => 'Contact Email'],
            ['name' => 'Contact Email', 'tag' => 'footer', 'heading' => 'Email Us', 'content' => 'info@platform.com', 'image' => '', 'status' => 1]
        );

        // Form Fields (global)
        FormField::firstOrCreate(
            ['name' => 'blood_group', 'for' => 'students'],
            ['name' => 'blood_group', 'type' => 'dropdown', 'for' => 'students', 'is_required' => 1, 'default_values' => '["A+","B+","O+","AB+"]', 'rank' => 1]
        );
        FormField::firstOrCreate(
            ['name' => 'parent_occupation', 'for' => 'students'],
            ['name' => 'parent_occupation', 'type' => 'text', 'for' => 'students', 'is_required' => 0, 'default_values' => '', 'rank' => 2]
        );

        // Fees Types (global)
        FeesType::firstOrCreate(
            ['name' => 'Tuition Fee'],
            ['name' => 'Tuition Fee', 'description' => 'Monthly tuition']
        );
        FeesType::firstOrCreate(
            ['name' => 'Lab Fee'],
            ['name' => 'Lab Fee', 'description' => 'Science lab charges']
        );
        FeesType::firstOrCreate(
            ['name' => 'Library Fee'],
            ['name' => 'Library Fee', 'description' => 'Library access fee']
        );

        // Grades (global)
        Grade::firstOrCreate(
            ['starting_range' => 90, 'ending_range' => 100],
            ['starting_range' => 90, 'ending_range' => 100, 'grade' => 'A+']
        );
        Grade::firstOrCreate(
            ['starting_range' => 80, 'ending_range' => 89],
            ['starting_range' => 80, 'ending_range' => 89, 'grade' => 'A']
        );
        Grade::firstOrCreate(
            ['starting_range' => 70, 'ending_range' => 79],
            ['starting_range' => 70, 'ending_range' => 79, 'grade' => 'B+']
        );
        Grade::firstOrCreate(
            ['starting_range' => 60, 'ending_range' => 69],
            ['starting_range' => 60, 'ending_range' => 69, 'grade' => 'B']
        );

        $this->command->info('Global data seeded.');
    }

    private function seedForSchool(School $school, int $idx): void
    {
        $schoolId = $school->id;
        $data = [];

        $data['school_id'] = $schoolId;
        $data['admin'] = $this->createSchoolAdmin($school, $idx);
        $data['super_admin'] = User::where('email', 'superadmin@gmail.com')->first();

        $this->seedSessionYear($school, $data);
        $this->seedMediums($school, $data);
        $this->seedStreams($school, $data);
        $this->seedShifts($school, $data);
        $this->seedEducationalPrograms($school, $data);
        $this->seedCategories($school, $data);
        $this->seedSections($school, $data);
        $this->seedSemesters($school, $data);
        $this->seedSubjects($school, $data);

        $this->seedTeachers($school, $data);
        $this->seedParents($school, $data);
        $this->seedStaff($school, $data);
        $this->seedClasses($school, $data);
        $this->seedClassSubjects($school, $data);
        $this->seedStudents($school, $data);
        $this->seedElectiveSubjectGroups($school, $data);
        $this->seedClassTeachers($school, $data);
        $this->seedSubjectTeachers($school, $data);
        $this->seedStudentSubjects($school, $data);
        $this->seedStudentSessions($school, $data);

        $this->seedLessons($school, $data);
        $this->seedLessonTopics($school, $data);
        $this->seedAssignments($school, $data);
        $this->seedAssignmentSubmissions($school, $data);

        $this->seedHolidays($school, $data);
        $this->seedAcademicCalendars($school, $data);
        $this->seedEvents($school, $data);
        $this->seedMultipleEvents($school, $data);

        $this->seedFeesClasses($school, $data);
        $this->seedPaymentTransactions($school, $data);
        $this->seedFeesPaid($school, $data);
        $this->seedInstallmentFees($school, $data);
        $this->seedFeesChoiceables($school, $data);

        $this->seedExams($school, $data);
        $this->seedExamClasses($school, $data);
        $this->seedExamTimetables($school, $data);
        $this->seedExamMarks($school, $data);
        $this->seedExamResults($school, $data);

        $this->seedOnlineExams($school, $data);
        $this->seedOnlineExamQuestions($school, $data);
        $this->seedOnlineExamQuestionChoices($school, $data);
        $this->seedOnlineExamQuestionOptions($school, $data);
        $this->seedOnlineExamQuestionAnswers($school, $data);
        $this->seedOnlineExamStudentAnswers($school, $data);
        $this->seedStudentOnlineExamStatuses($school, $data);

        $this->seedAttendances($school, $data);
        $this->seedQrAttendanceLogs($school, $data);
        $this->seedTimetables($school, $data);

        $this->seedAnnouncements($school, $data);
        $this->seedNotifications($school, $data);
        $this->seedUserNotifications($school, $data);

        $this->seedLeaveMasters($school, $data);
        $this->seedLeaves($school, $data);
        $this->seedLeaveDetails($school, $data);

        $this->schoolData[$schoolId] = $data;
    }

    private function createSchoolAdmin(School $school, int $idx): User
    {
        $def = $this->schoolDefs[$idx];
        $superAdmin = User::where('email', 'superadmin@gmail.com')->first();

        $admin = User::firstOrCreate(
            ['email' => $def['admin_email']],
            [
                'first_name' => $def['admin_name'],
                'last_name' => $def['admin_last'],
                'password' => Hash::make('admin123'),
                'gender' => 'Male',
                'image' => 'logo.svg',
                'mobile' => '98'.str_pad((string) (1000000 + $idx), 7, '0', STR_PAD_LEFT),
                'school_id' => $school->id,
                'created_by' => $superAdmin?->id,
                'status' => 1,
            ]
        );

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && ! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        return $admin;
    }

    private function seedSessionYear(School $school, array &$data): void
    {
        $data['session_years'] = [];
        $years = [
            ['name' => $school->id.'-2024-2025', 'default' => 1, 'start' => '2024-04-01', 'end' => '2025-03-31', 'fee_due' => '2024-07-01'],
            ['name' => $school->id.'-2025-2026', 'default' => 0, 'start' => '2025-04-01', 'end' => '2026-03-31', 'fee_due' => '2025-07-01'],
        ];

        foreach ($years as $y) {
            $sy = SessionYear::firstOrCreate(
                ['name' => $y['name'], 'school_id' => $school->id],
                [
                    'default' => $y['default'],
                    'start_date' => $y['start'],
                    'end_date' => $y['end'],
                    'include_fee_installments' => 1,
                    'fee_due_date' => $y['fee_due'],
                    'fee_due_charges' => 50,
                    'free_app_use_date' => $y['start'],
                ]
            );
            $data['session_years'][] = $sy->id;
        }
        $data['session_year_id'] = $data['session_years'][0];
    }

    private function seedMediums(School $school, array &$data): void
    {
        $data['mediums'] = [];
        foreach (['English', 'Nepali'] as $name) {
            $m = Mediums::firstOrCreate(
                ['name' => $name, 'school_id' => $school->id],
                ['name' => $name]
            );
            $data['mediums'][] = $m->id;
        }
        $data['medium_id'] = $data['mediums'][0];
    }

    private function seedStreams(School $school, array &$data): void
    {
        $data['streams'] = [];
        foreach (['Science', 'Management'] as $name) {
            $s = Stream::firstOrCreate(
                ['name' => $name, 'school_id' => $school->id],
                ['name' => $name]
            );
            $data['streams'][] = $s->id;
        }
        $data['stream_id'] = $data['streams'][0];
    }

    private function seedShifts(School $school, array &$data): void
    {
        $data['shifts'] = [];
        $shifts = [
            ['title' => 'Morning', 'start' => '06:00:00', 'end' => '12:00:00'],
            ['title' => 'Day', 'start' => '12:00:00', 'end' => '18:00:00'],
        ];
        foreach ($shifts as $s) {
            $shift = Shift::firstOrCreate(
                ['title' => $s['title'], 'school_id' => $school->id],
                ['title' => $s['title'], 'start_time' => $s['start'], 'end_time' => $s['end'], 'status' => 1]
            );
            $data['shifts'][] = $shift->id;
        }
        $data['shift_id'] = $data['shifts'][0];
    }

    private function seedEducationalPrograms(School $school, array &$data): void
    {
        $data['edu_programs'] = [];
        foreach (['NEB +2 - '.$school->id, 'Bachelor - '.$school->id] as $title) {
            $ep = EducationalProgram::firstOrCreate(
                ['title' => $title],
                ['title' => $title, 'image' => 'logo.svg']
            );
            $data['edu_programs'][] = $ep->id;
        }
        $data['edu_program_id'] = $data['edu_programs'][0];
    }

    private function seedCategories(School $school, array &$data): void
    {
        $data['categories'] = [];
        foreach (['General', 'OBC'] as $name) {
            $c = Category::firstOrCreate(
                ['name' => $name, 'school_id' => $school->id],
                ['name' => $name, 'status' => 1]
            );
            $data['categories'][] = $c->id;
        }
    }

    private function seedSections(School $school, array &$data): void
    {
        $data['sections'] = [];
        foreach (['A', 'B'] as $name) {
            $s = Section::firstOrCreate(
                ['name' => $name, 'school_id' => $school->id],
                ['name' => $name]
            );
            $data['sections'][] = $s->id;
        }
    }

    private function seedSemesters(School $school, array &$data): void
    {
        $data['semesters'] = [];
        $sems = [
            ['name' => 'First Semester', 'start' => '2024-04-01', 'end' => '2024-09-30'],
            ['name' => 'Second Semester', 'start' => '2024-10-01', 'end' => '2025-03-31'],
        ];
        foreach ($sems as $s) {
            $sem = Semester::firstOrCreate(
                ['name' => $s['name'], 'school_id' => $school->id],
                ['name' => $s['name'], 'start_date' => $s['start'], 'end_date' => $s['end'], 'status' => 1]
            );
            $data['semesters'][] = $sem->id;
        }
    }

    private function seedTeachers(School $school, array &$data): void
    {
        $data['teachers'] = [];
        $teacherRole = Role::where('name', 'Teacher')->first();
        $teacherData = [
            ['Ram', 'Sharma', 'math.teacher.'.$school->id.'@school.com', 'M.Sc. Mathematics'],
            ['Sita', 'Adhikari', 'english.teacher.'.$school->id.'@school.com', 'M.A. English'],
        ];

        foreach ($teacherData as $t) {
            $user = User::firstOrCreate(
                ['email' => $t[2]],
                [
                    'first_name' => $t[0],
                    'last_name' => $t[1],
                    'password' => Hash::make('teacher123'),
                    'gender' => $t[0] === 'Ram' ? 'Male' : 'Female',
                    'image' => 'logo.svg',
                    'mobile' => '98'.str_pad((string) (2000000 + $school->id * 10 + count($data['teachers'])), 7, '0', STR_PAD_LEFT),
                    'current_address' => $school->address,
                    'permanent_address' => $school->address,
                    'school_id' => $school->id,
                    'created_by' => $data['admin']->id,
                    'status' => 1,
                ]
            );
            if ($teacherRole && ! $user->hasRole('Teacher')) {
                $user->assignRole('Teacher');
            }
            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id, 'school_id' => $school->id],
                ['user_id' => $user->id, 'qualification' => $t[3], 'school_id' => $school->id]
            );
            $data['teachers'][] = $teacher->id;
        }
    }

    private function seedParents(School $school, array &$data): void
    {
        $data['parents'] = [];
        $parentRole = Role::where('name', 'Parent')->first();
        $parentData = [
            ['Krishna', 'Thapa', 'parent1.'.$school->id.'@school.com', 'Engineer', 'Male'],
            ['Gita', 'Rai', 'parent2.'.$school->id.'@school.com', 'Doctor', 'Female'],
        ];

        foreach ($parentData as $p) {
            $user = User::firstOrCreate(
                ['email' => $p[2]],
                [
                    'first_name' => $p[0],
                    'last_name' => $p[1],
                    'password' => Hash::make('parent123'),
                    'gender' => $p[4],
                    'image' => 'parents/user.png',
                    'mobile' => '98'.str_pad((string) (3000000 + $school->id * 10 + count($data['parents'])), 7, '0', STR_PAD_LEFT),
                    'current_address' => $school->address,
                    'permanent_address' => $school->address,
                    'school_id' => $school->id,
                    'created_by' => $data['admin']->id,
                    'status' => 1,
                ]
            );
            if ($parentRole && ! $user->hasRole('Parent')) {
                $user->assignRole('Parent');
            }
            $parent = Parents::firstOrCreate(
                ['user_id' => $user->id, 'school_id' => $school->id],
                [
                    'user_id' => $user->id,
                    'first_name' => $p[0],
                    'last_name' => $p[1],
                    'gender' => $p[4],
                    'email' => $p[2],
                    'mobile' => $user->mobile,
                    'occupation' => $p[3],
                    'image' => 'parents/user.png',
                    'school_id' => $school->id,
                ]
            );
            $data['parents'][] = $parent->id;
        }
    }

    private function seedStaff(School $school, array &$data): void
    {
        if (! Schema::hasTable('staffs')) {
            return;
        }
        $staffData = [
            ['Staff', 'One-'.$school->id, 'staff1.'.$school->id.'@school.com', 'Male'],
            ['Staff', 'Two-'.$school->id, 'staff2.'.$school->id.'@school.com', 'Female'],
        ];

        foreach ($staffData as $s) {
            $user = User::firstOrCreate(
                ['email' => $s[2]],
                [
                    'first_name' => $s[0],
                    'last_name' => $s[1],
                    'password' => Hash::make('staff123'),
                    'gender' => $s[3],
                    'image' => 'logo.svg',
                    'school_id' => $school->id,
                    'created_by' => $data['admin']->id,
                    'status' => 1,
                ]
            );
            DB::table('staffs')->updateOrInsert(
                ['user_id' => $user->id, 'school_id' => $school->id],
                ['user_id' => $user->id, 'school_id' => $school->id]
            );
        }
    }

    private function seedStudents(School $school, array &$data): void
    {
        $data['students'] = [];
        $data['student_users'] = [];
        $studentRole = Role::where('name', 'Student')->first();
        $studentData = [
            ['Hari', 'Thapa-'.$school->id, 'hari.student.'.$school->id.'@school.com', 'Male'],
            ['Gita', 'Rai-'.$school->id, 'gita.student.'.$school->id.'@school.com', 'Female'],
        ];

        foreach ($studentData as $i => $s) {
            $user = User::firstOrCreate(
                ['email' => $s[2]],
                [
                    'first_name' => $s[0],
                    'last_name' => $s[1],
                    'password' => Hash::make('student123'),
                    'gender' => $s[3],
                    'image' => 'students/user.png',
                    'mobile' => '98'.str_pad((string) (4000000 + $school->id * 10 + $i), 7, '0', STR_PAD_LEFT),
                    'current_address' => $school->address,
                    'permanent_address' => $school->address,
                    'school_id' => $school->id,
                    'created_by' => $data['admin']->id,
                    'status' => 1,
                ]
            );
            if ($studentRole && ! $user->hasRole('Student')) {
                $user->assignRole('Student');
            }
            $data['student_users'][] = $user->id;

            $csIndex = $i % max(count($data['class_sections'] ?? []), 1);
            $classSectionId = $data['class_sections'][$csIndex] ?? null;
            $classId = $data['classes'][$csIndex] ?? null;
            $categoryId = $data['categories'][$i % count($data['categories'])];

            if (! $classSectionId || ! $classId) {
                $this->command->warn("Skipping student {$user->email} - no class sections available yet.");

                continue;
            }

            $student = Students::firstOrCreate(
                ['user_id' => $user->id, 'school_id' => $school->id],
                [
                    'user_id' => $user->id,
                    'class_id' => $classId,
                    'class_section_id' => $classSectionId,
                    'category_id' => $categoryId,
                    'admission_no' => 'STU-'.$school->id.'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'roll_number' => $i + 1,
                    'admission_date' => '2024-04-01',
                    'father_id' => $data['parents'][0] ?? null,
                    'mother_id' => $data['parents'][1] ?? null,
                    'guardian_id' => $data['parents'][$i % count($data['parents'])] ?? null,
                    'registration_payment_status' => 1,
                    'is_new_admission' => 0,
                    'school_id' => $school->id,
                ]
            );
            $data['students'][] = $student->id;
        }
    }

    private function seedClasses(School $school, array &$data): void
    {
        $data['classes'] = [];
        $data['class_sections'] = [];
        $classDefs = [
            ['name' => 'Grade 10-'.$school->id, 'include_semesters' => 0],
            ['name' => 'Grade 11-'.$school->id, 'include_semesters' => 1],
        ];

        foreach ($classDefs as $c) {
            $class = ClassSchool::firstOrCreate(
                ['name' => $c['name'], 'school_id' => $school->id],
                [
                    'name' => $c['name'],
                    'include_semesters' => $c['include_semesters'],
                    'medium_id' => $data['medium_id'],
                    'stream_id' => $data['stream_id'],
                    'shift_id' => $data['shift_id'],
                    'educational_program_id' => $data['edu_program_id'],
                    'school_id' => $school->id,
                ]
            );
            $data['classes'][] = $class->id;

            foreach ($data['sections'] as $sectionId) {
                $cs = ClassSection::firstOrCreate(
                    ['class_id' => $class->id, 'section_id' => $sectionId, 'school_id' => $school->id],
                    ['class_id' => $class->id, 'section_id' => $sectionId, 'school_id' => $school->id]
                );
                $data['class_sections'][] = $cs->id;
            }
        }
    }

    private function seedSubjects(School $school, array &$data): void
    {
        $data['subjects'] = [];
        $subjectDefs = [
            ['name' => 'Mathematics-'.$school->id, 'code' => 'MATH'.$school->id.'01', 'bg_color' => '#FF5733'],
            ['name' => 'English-'.$school->id, 'code' => 'ENG'.$school->id.'01', 'bg_color' => '#33FF57'],
            ['name' => 'Science-'.$school->id, 'code' => 'SCI'.$school->id.'01', 'bg_color' => '#3357FF'],
            ['name' => 'Nepali-'.$school->id, 'code' => 'NEP'.$school->id.'01', 'bg_color' => '#FF33A8'],
        ];
        foreach ($subjectDefs as $s) {
            $sub = Subject::firstOrCreate(
                ['name' => $s['name'], 'school_id' => $school->id],
                [
                    'name' => $s['name'],
                    'code' => $s['code'],
                    'bg_color' => $s['bg_color'],
                    'medium_id' => $data['medium_id'],
                    'type' => 'Theory',
                    'class_level' => 'secondary',
                    'school_id' => $school->id,
                ]
            );
            $data['subjects'][] = $sub->id;
        }
    }

    private function seedClassSubjects(School $school, array &$data): void
    {
        foreach ($data['classes'] as $classId) {
            foreach (array_slice($data['subjects'], 0, 3) as $subId) {
                ClassSubject::firstOrCreate(
                    ['class_id' => $classId, 'subject_id' => $subId, 'school_id' => $school->id],
                    ['class_id' => $classId, 'subject_id' => $subId, 'type' => 'Compulsory', 'semester_id' => null, 'school_id' => $school->id]
                );
            }
            ClassSubject::firstOrCreate(
                ['class_id' => $classId, 'subject_id' => $data['subjects'][3], 'school_id' => $school->id],
                ['class_id' => $classId, 'subject_id' => $data['subjects'][3], 'type' => 'Elective', 'semester_id' => null, 'elective_subject_group_id' => null, 'school_id' => $school->id]
            );
        }
    }

    private function seedElectiveSubjectGroups(School $school, array &$data): void
    {
        foreach ($data['classes'] as $idx => $classId) {
            ElectiveSubjectGroup::firstOrCreate(
                ['class_id' => $classId, 'total_subjects' => 2, 'total_selectable_subjects' => 1, 'school_id' => $school->id],
                [
                    'class_id' => $classId,
                    'total_subjects' => 2,
                    'total_selectable_subjects' => 1,
                    'semester_id' => $data['semesters'][$idx % count($data['semesters'])] ?? null,
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedClassTeachers(School $school, array &$data): void
    {
        foreach ($data['class_sections'] as $idx => $csId) {
            $tid = $data['teachers'][$idx % count($data['teachers'])];
            ClassTeacher::firstOrCreate(
                ['class_section_id' => $csId, 'class_teacher_id' => $tid, 'school_id' => $school->id],
                ['class_section_id' => $csId, 'class_teacher_id' => $tid, 'school_id' => $school->id]
            );
        }
    }

    private function seedSubjectTeachers(School $school, array &$data): void
    {
        $data['subject_teachers'] = [];
        foreach ($data['class_sections'] as $csId) {
            $cs = ClassSection::find($csId);
            if (! $cs) {
                continue;
            }
            $classSubjectIds = ClassSubject::where('class_id', $cs->class_id)->pluck('subject_id')->toArray();
            foreach (array_slice($classSubjectIds, 0, 2) as $idx => $subId) {
                $tid = $data['teachers'][$idx % count($data['teachers'])];
                $st = SubjectTeacher::firstOrCreate(
                    ['class_section_id' => $csId, 'subject_id' => $subId, 'teacher_id' => $tid, 'school_id' => $school->id],
                    ['class_section_id' => $csId, 'subject_id' => $subId, 'teacher_id' => $tid, 'school_id' => $school->id]
                );
                $data['subject_teachers'][] = $st->id;
            }
        }
    }

    private function seedStudentSubjects(School $school, array &$data): void
    {
        foreach ($data['students'] as $studentId) {
            $student = Students::find($studentId);
            if (! $student || ! $student->class_section) {
                continue;
            }
            $cs = $student->class_section;
            $classSubjectIds = ClassSubject::where('class_id', $cs->class_id)->pluck('subject_id')->toArray();
            foreach (array_slice($classSubjectIds, 0, 4) as $subId) {
                StudentSubject::firstOrCreate(
                    ['student_id' => $studentId, 'subject_id' => $subId, 'class_section_id' => $cs->id, 'school_id' => $school->id],
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subId,
                        'class_section_id' => $cs->id,
                        'session_year_id' => $data['session_year_id'],
                        'school_id' => $school->id,
                    ]
                );
            }
        }
    }

    private function seedStudentSessions(School $school, array &$data): void
    {
        foreach ($data['students'] as $studentId) {
            $student = Students::find($studentId);
            if (! $student) {
                continue;
            }
            StudentSessions::firstOrCreate(
                ['student_id' => $studentId, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'student_id' => $studentId,
                    'class_section_id' => $student->class_section_id,
                    'session_year_id' => $data['session_year_id'],
                    'status' => 1,
                    'result' => 1,
                    'school_id' => $school->id,
                ]
            );
            if (! $student->registration_payment_status) {
                $student->registration_payment_status = 1;
                $student->save();
            }
        }
    }

    private function seedLessons(School $school, array &$data): void
    {
        $data['lessons'] = [];
        $lessonDefs = [
            ['name' => 'Introduction to Algebra - '.$school->id, 'desc' => 'Basic algebraic concepts', 'csIdx' => 0, 'subIdx' => 0],
            ['name' => 'Grammar - Parts of Speech - '.$school->id, 'desc' => 'Nouns, verbs, adjectives', 'csIdx' => 0, 'subIdx' => 1],
            ['name' => 'Geometry Basics - '.$school->id, 'desc' => 'Points, lines, angles', 'csIdx' => 1, 'subIdx' => 0],
            ['name' => 'Reading Comprehension - '.$school->id, 'desc' => 'Comprehension strategies', 'csIdx' => 1, 'subIdx' => 1],
        ];
        foreach ($lessonDefs as $l) {
            $csId = $data['class_sections'][$l['csIdx']] ?? null;
            $subId = $data['subjects'][$l['subIdx']] ?? null;
            if (! $csId || ! $subId) {
                continue;
            }
            $lesson = Lesson::firstOrCreate(
                ['name' => $l['name'], 'class_section_id' => $csId, 'subject_id' => $subId, 'school_id' => $school->id],
                [
                    'name' => $l['name'],
                    'description' => $l['desc'],
                    'class_section_id' => $csId,
                    'subject_id' => $subId,
                    'school_id' => $school->id,
                ]
            );
            $data['lessons'][] = $lesson->id;
        }
    }

    private function seedLessonTopics(School $school, array &$data): void
    {
        $topicDefs = [
            ['lessonIdx' => 0, 'name' => 'Variables and Constants', 'desc' => 'Understanding variables'],
            ['lessonIdx' => 0, 'name' => 'Linear Equations', 'desc' => 'Solving linear equations'],
            ['lessonIdx' => 1, 'name' => 'Nouns', 'desc' => 'Types and usage'],
            ['lessonIdx' => 1, 'name' => 'Verbs', 'desc' => 'Action and linking verbs'],
        ];
        foreach ($topicDefs as $t) {
            $lessonId = $data['lessons'][$t['lessonIdx']] ?? null;
            if (! $lessonId) {
                continue;
            }
            LessonTopic::firstOrCreate(
                ['lesson_id' => $lessonId, 'name' => $t['name'], 'school_id' => $school->id],
                ['lesson_id' => $lessonId, 'name' => $t['name'], 'description' => $t['desc'], 'school_id' => $school->id]
            );
        }
    }

    private function seedAssignments(School $school, array &$data): void
    {
        $data['assignments'] = [];
        $assignmentDefs = [
            ['name' => 'Algebra Practice Set - '.$school->id, 'desc' => 'Complete all 20 questions from chapter 1.', 'csIdx' => 0, 'subIdx' => 0, 'points' => 20, 'resub' => 1],
            ['name' => 'English Essay Writing - '.$school->id, 'desc' => 'Write a 500-word essay on "My School".', 'csIdx' => 0, 'subIdx' => 1, 'points' => 15, 'resub' => 0],
        ];
        foreach ($assignmentDefs as $a) {
            $csId = $data['class_sections'][$a['csIdx']] ?? null;
            $subId = $data['subjects'][$a['subIdx']] ?? null;
            if (! $csId || ! $subId) {
                continue;
            }
            $assignment = Assignment::firstOrCreate(
                ['name' => $a['name'], 'class_section_id' => $csId, 'subject_id' => $subId, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'name' => $a['name'],
                    'instructions' => $a['desc'],
                    'due_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
                    'points' => $a['points'],
                    'resubmission' => $a['resub'],
                    'extra_days_for_resubmission' => $a['resub'] ? 3 : 0,
                    'class_section_id' => $csId,
                    'subject_id' => $subId,
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
            $data['assignments'][] = $assignment->id;
        }
    }

    private function seedAssignmentSubmissions(School $school, array &$data): void
    {
        foreach ($data['assignments'] as $idx => $assignmentId) {
            $studentId = $data['students'][$idx] ?? null;
            if (! $studentId) {
                continue;
            }
            AssignmentSubmission::firstOrCreate(
                ['assignment_id' => $assignmentId, 'student_id' => $studentId, 'school_id' => $school->id],
                [
                    'assignment_id' => $assignmentId,
                    'student_id' => $studentId,
                    'text_submission' => 'My submission for the assignment.',
                    'session_year_id' => $data['session_year_id'],
                    'status' => 1,
                    'points' => 15,
                    'feedback' => 'Good work!',
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedHolidays(School $school, array &$data): void
    {
        $holidays = [
            ['date' => '2024-10-15', 'title' => 'Dashain Festival - '.$school->id, 'description' => 'Major Hindu festival'],
            ['date' => '2024-12-25', 'title' => 'Christmas Day - '.$school->id, 'description' => 'Christian holiday celebration'],
        ];
        foreach ($holidays as $h) {
            Holiday::firstOrCreate(
                ['date' => $h['date'], 'title' => $h['title'], 'school_id' => $school->id],
                ['date' => $h['date'], 'title' => $h['title'], 'description' => $h['description'], 'school_id' => $school->id]
            );
        }
    }

    private function seedAcademicCalendars(School $school, array &$data): void
    {
        $entries = [
            ['date' => '2024-04-01', 'title' => 'Session Start - '.$school->id, 'description' => 'Academic session begins'],
            ['date' => '2024-05-15', 'title' => 'First Term Exam - '.$school->id, 'description' => 'First term examinations'],
        ];
        foreach ($entries as $e) {
            DB::table('academic_calendars')->updateOrInsert(
                ['date' => $e['date'], 'title' => $e['title'], 'school_id' => $school->id],
                [
                    'date' => $e['date'],
                    'title' => $e['title'],
                    'description' => $e['description'],
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedEvents(School $school, array &$data): void
    {
        $data['events'] = [];
        $eventDefs = [
            ['title' => 'Annual Sports Day - '.$school->id, 'type' => 'sport', 'start' => '2024-08-15', 'end' => '2024-08-16'],
            ['title' => 'Science Exhibition - '.$school->id, 'type' => 'academic', 'start' => '2024-09-01', 'end' => '2024-09-02'],
        ];
        foreach ($eventDefs as $e) {
            $event = Event::firstOrCreate(
                ['title' => $e['title'], 'school_id' => $school->id],
                [
                    'title' => $e['title'],
                    'type' => $e['type'],
                    'start_date' => $e['start'],
                    'end_date' => $e['end'],
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'description' => 'Event for '.$school->name,
                    'image' => 'logo.svg',
                    'school_id' => $school->id,
                ]
            );
            $data['events'][] = $event->id;
        }
    }

    private function seedMultipleEvents(School $school, array &$data): void
    {
        $multi = [
            ['eventIdx' => 0, 'date' => '2024-08-15', 'title' => 'Day 1 - Track Events - '.$school->id],
            ['eventIdx' => 0, 'date' => '2024-08-16', 'title' => 'Day 2 - Field Events - '.$school->id],
            ['eventIdx' => 1, 'date' => '2024-09-01', 'title' => 'Projects Display - '.$school->id],
        ];
        foreach ($multi as $m) {
            $eventId = $data['events'][$m['eventIdx']] ?? null;
            if (! $eventId) {
                continue;
            }
            MultipleEvent::firstOrCreate(
                ['event_id' => $eventId, 'date' => $m['date'], 'school_id' => $school->id],
                [
                    'event_id' => $eventId,
                    'date' => $m['date'],
                    'title' => $m['title'],
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'description' => $m['title'],
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedFeesClasses(School $school, array &$data): void
    {
        $feesTypes = FeesType::pluck('id')->toArray();
        foreach ($data['classes'] as $classId) {
            foreach (array_slice($feesTypes, 0, 2) as $ftId) {
                FeesClass::firstOrCreate(
                    ['class_id' => $classId, 'fees_type_id' => $ftId, 'school_id' => $school->id],
                    [
                        'class_id' => $classId,
                        'fees_type_id' => $ftId,
                        'amount' => 1500.00,
                        'choiceable' => 0,
                        'school_id' => $school->id,
                    ]
                );
            }
        }
    }

    private function seedInstallmentFees(School $school, array &$data): void
    {
        $installments = [
            ['name' => 'First Installment - '.$school->id, 'due' => '2024-06-01'],
            ['name' => 'Second Installment - '.$school->id, 'due' => '2024-09-01'],
        ];
        foreach ($installments as $i) {
            InstallmentFee::firstOrCreate(
                ['name' => $i['name'], 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'name' => $i['name'],
                    'due_date' => $i['due'],
                    'due_charges' => 50,
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
        }

        if (Schema::hasTable('paid_installment_fees')) {
            $installmentIds = InstallmentFee::where('session_year_id', $data['session_year_id'])
                ->where('school_id', $school->id)
                ->pluck('id')
                ->toArray();
            foreach ($data['students'] as $sIdx => $studentId) {
                $student = Students::find($studentId);
                $classId = $student?->class_id ?? $data['classes'][0];
                $parentId = $data['parents'][$sIdx % count($data['parents'])] ?? null;
                $txnId = $data['payment_transactions'][$sIdx] ?? null;
                foreach ($installmentIds as $instId) {
                    DB::table('paid_installment_fees')->updateOrInsert(
                        [
                            'student_id' => $studentId,
                            'installment_fee_id' => $instId,
                            'school_id' => $school->id,
                        ],
                        [
                            'student_id' => $studentId,
                            'class_id' => $classId,
                            'parent_id' => $parentId,
                            'installment_fee_id' => $instId,
                            'amount' => 750.00,
                            'due_charges' => 0,
                            'date' => now(),
                            'session_year_id' => $data['session_year_id'],
                            'payment_transaction_id' => $txnId,
                            'school_id' => $school->id,
                        ]
                    );
                }
            }
        }
    }

    private function seedPaymentTransactions(School $school, array &$data): void
    {
        $data['payment_transactions'] = [];
        foreach ($data['students'] as $idx => $studentId) {
            $orderId = 'ORD-'.$school->id.'-'.$studentId.'-'.uniqid();
            $txn = PaymentTransaction::firstOrCreate(
                ['order_id' => $orderId, 'school_id' => $school->id],
                [
                    'student_id' => $studentId,
                    'class_id' => $data['classes'][$idx % count($data['classes'])],
                    'parent_id' => $data['parents'][$idx % count($data['parents'])],
                    'mode' => 1,
                    'type_of_fee' => 1,
                    'payment_gateway' => 1,
                    'order_id' => $orderId,
                    'payment_id' => 'PAY-'.uniqid(),
                    'payment_status' => 1,
                    'total_amount' => 1500.00,
                    'date' => now(),
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
            $data['payment_transactions'][] = $txn->id;
        }
    }

    private function seedFeesPaid(School $school, array &$data): void
    {
        foreach ($data['students'] as $idx => $studentId) {
            $txnId = $data['payment_transactions'][$idx] ?? null;
            if (! $txnId) {
                continue;
            }
            FeesPaid::firstOrCreate(
                ['student_id' => $studentId, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'parent_id' => $data['parents'][$idx % count($data['parents'])],
                    'student_id' => $studentId,
                    'class_id' => $data['classes'][$idx % count($data['classes'])],
                    'mode' => 1,
                    'payment_transaction_id' => (string) $txnId,
                    'total_amount' => 1500.00,
                    'due_charges' => 0,
                    'is_fully_paid' => 0,
                    'date' => now(),
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedFeesChoiceables(School $school, array &$data): void
    {
        $feesTypeId2 = FeesType::skip(1)->value('id') ?? FeesType::first()->id;
        foreach ($data['students'] as $idx => $studentId) {
            FeesChoiceable::firstOrCreate(
                ['student_id' => $studentId, 'fees_type_id' => $feesTypeId2, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'student_id' => $studentId,
                    'class_id' => $data['classes'][$idx % count($data['classes'])],
                    'fees_type_id' => $feesTypeId2,
                    'is_due_charges' => 0,
                    'total_amount' => 500.00,
                    'session_year_id' => $data['session_year_id'],
                    'date' => now(),
                    'status' => $idx === 0 ? 1 : 0,
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedExams(School $school, array &$data): void
    {
        $data['exams'] = [];
        $examDefs = [
            ['name' => 'First Term Examination - '.$school->id, 'desc' => 'First term exams', 'publish' => 1],
            ['name' => 'Final Examination - '.$school->id, 'desc' => 'Year-end final exams', 'publish' => 1],
        ];
        foreach ($examDefs as $e) {
            $exam = Exam::firstOrCreate(
                ['name' => $e['name'], 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'name' => $e['name'],
                    'description' => $e['desc'],
                    'session_year_id' => $data['session_year_id'],
                    'publish' => $e['publish'],
                    'school_id' => $school->id,
                ]
            );
            $data['exams'][] = $exam->id;
        }
    }

    private function seedExamClasses(School $school, array &$data): void
    {
        foreach ($data['exams'] as $examId) {
            foreach ($data['classes'] as $classId) {
                ExamClass::firstOrCreate(
                    ['exam_id' => $examId, 'class_id' => $classId, 'school_id' => $school->id],
                    ['exam_id' => $examId, 'class_id' => $classId, 'school_id' => $school->id]
                );
            }
        }
    }

    private function seedExamTimetables(School $school, array &$data): void
    {
        $data['exam_timetables'] = [];
        $examId = $data['exams'][0];
        $classId = $data['classes'][0];
        $tt = [
            ['subIdx' => 0, 'date' => '2024-05-15', 'start' => '10:00:00', 'end' => '13:00:00'],
            ['subIdx' => 1, 'date' => '2024-05-17', 'start' => '10:00:00', 'end' => '13:00:00'],
        ];
        foreach ($tt as $t) {
            $subId = $data['subjects'][$t['subIdx']] ?? null;
            if (! $subId) {
                continue;
            }
            $et = ExamTimetable::firstOrCreate(
                ['exam_id' => $examId, 'subject_id' => $subId, 'class_id' => $classId, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'exam_id' => $examId,
                    'class_id' => $classId,
                    'subject_id' => $subId,
                    'total_marks' => 100,
                    'passing_marks' => 40,
                    'date' => $t['date'],
                    'start_time' => $t['start'],
                    'end_time' => $t['end'],
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
            $data['exam_timetables'][] = $et->id;
        }
    }

    private function seedExamMarks(School $school, array &$data): void
    {
        foreach ($data['students'] as $sIdx => $studentId) {
            foreach ($data['exam_timetables'] as $etIdx => $etId) {
                $et = ExamTimetable::find($etId);
                if (! $et) {
                    continue;
                }
                ExamMarks::firstOrCreate(
                    ['exam_timetable_id' => $etId, 'student_id' => $studentId, 'subject_id' => $et->subject_id, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                    [
                        'exam_timetable_id' => $etId,
                        'student_id' => $studentId,
                        'subject_id' => $et->subject_id,
                        'obtained_marks' => 70 + ($sIdx * 5) + $etIdx,
                        'passing_status' => 1,
                        'session_year_id' => $data['session_year_id'],
                        'grade' => 'B+',
                        'school_id' => $school->id,
                    ]
                );
            }
        }
    }

    private function seedExamResults(School $school, array &$data): void
    {
        $examId = $data['exams'][0];
        $csId = $data['class_sections'][0];
        $totalMarks = count($data['exam_timetables']) * 100;
        foreach ($data['students'] as $idx => $studentId) {
            $obtained = 150 + ($idx * 10);
            ExamResult::firstOrCreate(
                ['exam_id' => $examId, 'student_id' => $studentId, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'exam_id' => $examId,
                    'class_section_id' => $csId,
                    'student_id' => $studentId,
                    'total_marks' => $totalMarks,
                    'obtained_marks' => $obtained,
                    'percentage' => round(($obtained / $totalMarks) * 100, 2),
                    'grade' => 'B+',
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedOnlineExams(School $school, array &$data): void
    {
        $data['online_exams'] = [];
        $oeDefs = [
            ['title' => 'Math Unit Test 1 - '.$school->id, 'subIdx' => 0, 'classIdx' => 0, 'key' => 12345 + $school->id, 'duration' => 60],
            ['title' => 'English Quiz 1 - '.$school->id, 'subIdx' => 1, 'classIdx' => 0, 'key' => 54321 + $school->id, 'duration' => 45],
        ];
        foreach ($oeDefs as $oe) {
            $classId = $data['classes'][$oe['classIdx']] ?? null;
            $subId = $data['subjects'][$oe['subIdx']] ?? null;
            if (! $classId || ! $subId) {
                continue;
            }
            $oeModel = OnlineExam::firstOrCreate(
                ['title' => $oe['title'], 'subject_id' => $subId, 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'model_type' => 'App\\Models\\ClassSchool',
                    'model_id' => $classId,
                    'subject_id' => $subId,
                    'title' => $oe['title'],
                    'exam_key' => $oe['key'],
                    'duration' => $oe['duration'],
                    'start_date' => now()->subDays(7),
                    'end_date' => now()->addDays(7),
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
            $data['online_exams'][] = $oeModel->id;
        }
    }

    private function seedOnlineExamQuestions(School $school, array &$data): void
    {
        $data['online_exam_questions'] = [];
        $qDefs = [
            ['question' => 'What is 2 + 2? - '.$school->id, 'classIdx' => 0, 'subIdx' => 0, 'note' => 'Basic arithmetic'],
            ['question' => 'What is the square root of 16? - '.$school->id, 'classIdx' => 0, 'subIdx' => 0, 'note' => 'Square roots'],
            ['question' => 'What is a noun? - '.$school->id, 'classIdx' => 0, 'subIdx' => 1, 'note' => 'Parts of speech'],
            ['question' => 'Identify the verb - '.$school->id, 'classIdx' => 0, 'subIdx' => 1, 'note' => 'Verbs'],
        ];
        foreach ($qDefs as $q) {
            $classId = $data['classes'][$q['classIdx']] ?? null;
            $subId = $data['subjects'][$q['subIdx']] ?? null;
            if (! $classId || ! $subId) {
                continue;
            }
            $cs = ClassSubject::where('class_id', $classId)->where('subject_id', $subId)->first();
            $question = OnlineExamQuestion::firstOrCreate(
                ['question' => $q['question'], 'school_id' => $school->id],
                [
                    'class_subject_id' => $cs?->id ?? 1,
                    'question_type' => 0,
                    'question' => $q['question'],
                    'note' => $q['note'],
                    'school_id' => $school->id,
                ]
            );
            $data['online_exam_questions'][] = $question->id;
        }
    }

    private function seedOnlineExamQuestionChoices(School $school, array &$data): void
    {
        $data['online_exam_question_choices'] = [];
        $pairs = [
            ['oeIdx' => 0, 'qIdx' => 0],
            ['oeIdx' => 0, 'qIdx' => 1],
            ['oeIdx' => 1, 'qIdx' => 2],
            ['oeIdx' => 1, 'qIdx' => 3],
        ];
        foreach ($pairs as $p) {
            $oeId = $data['online_exams'][$p['oeIdx']] ?? null;
            $qId = $data['online_exam_questions'][$p['qIdx']] ?? null;
            if (! $oeId || ! $qId) {
                continue;
            }
            $choice = OnlineExamQuestionChoice::firstOrCreate(
                ['online_exam_id' => $oeId, 'question_id' => $qId, 'school_id' => $school->id],
                ['online_exam_id' => $oeId, 'question_id' => $qId, 'marks' => 5, 'school_id' => $school->id]
            );
            $data['online_exam_question_choices'][] = $choice->id;
        }
    }

    private function seedOnlineExamQuestionOptions(School $school, array &$data): void
    {
        $data['online_exam_question_options'] = [];
        $options = [
            ['qIdx' => 0, 'option' => '3'],
            ['qIdx' => 0, 'option' => '4'],
            ['qIdx' => 1, 'option' => '2'],
            ['qIdx' => 1, 'option' => '4'],
        ];
        foreach ($options as $o) {
            $qId = $data['online_exam_questions'][$o['qIdx']] ?? null;
            if (! $qId) {
                continue;
            }
            $opt = OnlineExamQuestionOption::firstOrCreate(
                ['question_id' => $qId, 'option' => $o['option'].' - '.$school->id, 'school_id' => $school->id],
                ['question_id' => $qId, 'option' => $o['option'].' - '.$school->id, 'school_id' => $school->id]
            );
            $data['online_exam_question_options'][] = $opt->id;
        }
    }

    private function seedOnlineExamQuestionAnswers(School $school, array &$data): void
    {
        foreach ([0, 1] as $qIdx) {
            $qId = $data['online_exam_questions'][$qIdx] ?? null;
            if (! $qId) {
                continue;
            }
            $opt = OnlineExamQuestionOption::where('question_id', $qId)->skip(1)->first();
            if (! $opt) {
                continue;
            }
            OnlineExamQuestionAnswer::firstOrCreate(
                ['question_id' => $qId, 'school_id' => $school->id],
                ['question_id' => $qId, 'answer' => $opt->id, 'school_id' => $school->id]
            );
        }
    }

    private function seedOnlineExamStudentAnswers(School $school, array &$data): void
    {
        $oeId = $data['online_exams'][0] ?? null;
        if (! $oeId) {
            return;
        }
        foreach ($data['students'] as $sIdx => $studentId) {
            $qId = $data['online_exam_questions'][$sIdx % 2] ?? null;
            if (! $qId) {
                continue;
            }
            $opt = OnlineExamQuestionOption::where('question_id', $qId)->skip(1)->first();
            if (! $opt) {
                continue;
            }
            OnlineExamStudentAnswer::firstOrCreate(
                ['student_id' => $studentId, 'online_exam_id' => $oeId, 'question_id' => $qId, 'school_id' => $school->id],
                [
                    'student_id' => $studentId,
                    'online_exam_id' => $oeId,
                    'question_id' => $qId,
                    'option_id' => $opt->id,
                    'submitted_date' => now()->toDateString(),
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedStudentOnlineExamStatuses(School $school, array &$data): void
    {
        $oeId = $data['online_exams'][0] ?? null;
        if (! $oeId) {
            return;
        }
        foreach ($data['students'] as $idx => $studentId) {
            StudentOnlineExamStatus::firstOrCreate(
                ['student_id' => $studentId, 'online_exam_id' => $oeId, 'school_id' => $school->id],
                [
                    'student_id' => $studentId,
                    'online_exam_id' => $oeId,
                    'status' => $idx === 0 ? 2 : 1,
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedAttendances(School $school, array &$data): void
    {
        foreach ($data['students'] as $studentId) {
            $student = Students::find($studentId);
            if (! $student) {
                continue;
            }
            Attendance::firstOrCreate(
                ['student_id' => $studentId, 'class_section_id' => $student->class_section_id, 'date' => now()->toDateString(), 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'class_section_id' => $student->class_section_id,
                    'student_id' => $studentId,
                    'session_year_id' => $data['session_year_id'],
                    'type' => 1,
                    'date' => now()->toDateString(),
                    'remark' => 'On time',
                    'school_id' => $school->id,
                ]
            );
            Attendance::firstOrCreate(
                ['student_id' => $studentId, 'class_section_id' => $student->class_section_id, 'date' => now()->subDay()->toDateString(), 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'class_section_id' => $student->class_section_id,
                    'student_id' => $studentId,
                    'session_year_id' => $data['session_year_id'],
                    'type' => 1,
                    'date' => now()->subDay()->toDateString(),
                    'remark' => 'On time',
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedQrAttendanceLogs(School $school, array &$data): void
    {
        if (! Schema::hasTable('qr_attendance_logs')) {
            return;
        }
        foreach ($data['students'] as $idx => $studentId) {
            DB::table('qr_attendance_logs')->updateOrInsert(
                ['student_id' => $studentId, 'attendance_date' => now()->toDateString(), 'school_id' => $school->id],
                [
                    'student_id' => $studentId,
                    'scanned_by' => $data['admin']->id,
                    'class_section_id' => $data['class_sections'][$idx % count($data['class_sections'])],
                    'session_year_id' => $data['session_year_id'],
                    'attendance_date' => now()->toDateString(),
                    'status' => 'present',
                    'ip_address' => '127.0.0.1',
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedTimetables(School $school, array &$data): void
    {
        foreach ($data['class_sections'] as $csId) {
            $subTeachers = SubjectTeacher::where('class_section_id', $csId)->get();
            foreach ($subTeachers as $idx => $st) {
                Timetable::firstOrCreate(
                    ['subject_teacher_id' => $st->id, 'day' => 1 + $idx, 'class_section_id' => $csId, 'school_id' => $school->id],
                    [
                        'subject_teacher_id' => $st->id,
                        'class_section_id' => $csId,
                        'start_time' => '10:00:00',
                        'end_time' => '10:45:00',
                        'day' => 1 + $idx,
                        'day_name' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'][$idx % 5],
                        'note' => 'Regular class',
                        'school_id' => $school->id,
                    ]
                );
            }
        }
    }

    private function seedAnnouncements(School $school, array &$data): void
    {
        $teacherUserId = Teacher::find($data['teachers'][0])?->user_id ?? $data['admin']->id;
        $annDefs = [
            ['title' => 'Parent-Teacher Meeting - '.$school->id, 'desc' => 'PTM scheduled for next Saturday at 10 AM.'],
            ['title' => 'Exam Schedule Released - '.$school->id, 'desc' => 'First term exam schedule has been published.'],
        ];
        foreach ($annDefs as $a) {
            Announcement::firstOrCreate(
                ['title' => $a['title'], 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'title' => $a['title'],
                    'description' => $a['desc'],
                    'table_type' => 'App\\Models\\User',
                    'table_id' => $teacherUserId,
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
        }
    }

    private function seedNotifications(School $school, array &$data): void
    {
        $data['notifications'] = [];
        $notifDefs = [
            ['title' => 'Assignment Due - '.$school->id, 'type' => 'Assignment', 'message' => 'Your assignment is due tomorrow.'],
            ['title' => 'Holiday Notice - '.$school->id, 'type' => 'Holiday', 'message' => 'School will remain closed for the holiday.'],
        ];
        foreach ($notifDefs as $n) {
            $notif = Notification::firstOrCreate(
                ['title' => $n['title'], 'school_id' => $school->id],
                [
                    'title' => $n['title'],
                    'type' => $n['type'],
                    'message' => $n['message'],
                    'send_to' => 3,
                    'date' => now(),
                    'school_id' => $school->id,
                ]
            );
            $data['notifications'][] = $notif->id;
        }
    }

    private function seedUserNotifications(School $school, array &$data): void
    {
        foreach ($data['student_users'] as $userId) {
            foreach ($data['notifications'] as $notifId) {
                UserNotification::firstOrCreate(
                    ['notification_id' => $notifId, 'user_id' => $userId, 'school_id' => $school->id],
                    ['notification_id' => $notifId, 'user_id' => $userId, 'school_id' => $school->id]
                );
            }
        }
    }

    private function seedLeaveMasters(School $school, array &$data): void
    {
        LeaveMaster::firstOrCreate(
            ['session_year_id' => $data['session_year_id'], 'total_leave' => '30 - '.$school->id, 'school_id' => $school->id],
            [
                'total_leave' => '30 - '.$school->id,
                'holiday_days' => '0',
                'session_year_id' => $data['session_year_id'],
                'school_id' => $school->id,
            ]
        );
        LeaveMaster::firstOrCreate(
            ['session_year_id' => $data['session_year_id'], 'total_leave' => '15 - '.$school->id, 'school_id' => $school->id],
            [
                'total_leave' => '15 - '.$school->id,
                'holiday_days' => '0',
                'session_year_id' => $data['session_year_id'],
                'school_id' => $school->id,
            ]
        );
    }

    private function seedLeaves(School $school, array &$data): void
    {
        $leaveMaster = LeaveMaster::where('session_year_id', $data['session_year_id'])->where('school_id', $school->id)->first();
        $leaveMasterId = $leaveMaster->id ?? 1;

        foreach ($data['student_users'] as $idx => $userId) {
            $leave = Leave::firstOrCreate(
                ['user_id' => $userId, 'from_date' => '2024-06-0'.($idx + 1), 'session_year_id' => $data['session_year_id'], 'school_id' => $school->id],
                [
                    'user_id' => $userId,
                    'leave_master_id' => $leaveMasterId,
                    'reason' => 'Family function',
                    'from_date' => '2024-06-0'.($idx + 1),
                    'to_date' => '2024-06-0'.($idx + 2),
                    'status' => 'Approved',
                    'session_year_id' => $data['session_year_id'],
                    'school_id' => $school->id,
                ]
            );
            $data['leaves'][$userId] = $leave->id;
        }
    }

    private function seedLeaveDetails(School $school, array &$data): void
    {
        foreach ($data['leaves'] ?? [] as $userId => $leaveId) {
            LeaveDetail::firstOrCreate(
                ['leave_id' => $leaveId, 'date' => '2024-06-01', 'school_id' => $school->id],
                ['leave_id' => $leaveId, 'date' => '2024-06-01', 'type' => 'Full Day', 'school_id' => $school->id]
            );
            LeaveDetail::firstOrCreate(
                ['leave_id' => $leaveId, 'date' => '2024-06-02', 'school_id' => $school->id],
                ['leave_id' => $leaveId, 'date' => '2024-06-02', 'type' => 'Full Day', 'school_id' => $school->id]
            );
        }
    }
}
