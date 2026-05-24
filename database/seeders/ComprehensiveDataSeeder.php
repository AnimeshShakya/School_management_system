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
use App\Models\Section;
use App\Models\Semester;
use App\Models\SessionYear;
use App\Models\Settings;
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

class ComprehensiveDataSeeder extends Seeder
{
    private int $sessionYearId;

    private int $mediumId;

    private array $sectionIds = [];

    private array $classIds = [];

    private array $classSectionIds = [];

    private array $subjectIds = [];

    private array $teacherIds = [];

    private array $parentIds = [];

    private array $studentIds = [];

    private array $categoryIds = [];

    private int $semesterId1;

    private int $semesterId2;

    private int $streamId;

    private int $shiftId;

    private int $educationalProgramId;

    public function run(): void
    {
        $this->command->info('Starting comprehensive data seeding...');

        $this->seedFoundation();
        $this->seedUsersAndProfiles();
        $this->seedAcademic();
        $this->seedFees();
        $this->seedExams();
        $this->seedOnlineExams();
        $this->seedAttendance();
        $this->seedTimetable();
        $this->seedCommunication();
        $this->seedLeave();
        $this->seedOther();
        $this->seedWebSettings();

        $this->command->info('Comprehensive data seeding completed!');
    }

    private function seedFoundation(): void
    {
        $this->command->info('Seeding foundation tables...');

        // Session Year
        $sessionYear = SessionYear::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'default' => 1,
                'start_date' => '2024-04-01',
                'end_date' => '2025-03-31',
                'include_fee_installments' => 1,
                'fee_due_date' => '2024-07-01',
                'fee_due_charges' => 50,
                'free_app_use_date' => '2024-04-01',
            ]
        );
        $this->sessionYearId = $sessionYear->id;

        // Second session year
        SessionYear::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'default' => 0,
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'include_fee_installments' => 1,
                'fee_due_date' => '2025-07-01',
                'fee_due_charges' => 50,
                'free_app_use_date' => '2025-04-01',
            ]
        );

        // Settings
        if (Schema::hasTable('settings')) {
            $existing = DB::table('settings')->count();
            if ($existing < 2) {
                DB::table('settings')->updateOrInsert(
                    ['type' => 'session_year'],
                    ['type' => 'session_year', 'message' => (string) $this->sessionYearId]
                );
                DB::table('settings')->updateOrInsert(
                    ['type' => 'app_settings'],
                    ['type' => 'app_settings', 'message' => json_encode([
                        'school_name' => 'Demo School',
                        'school_email' => 'demo@school.com',
                        'school_phone' => '1234567890',
                        'school_address' => 'Kathmandu, Nepal',
                        'timezone' => 'Asia/Kathmandu',
                        'currency_symbol' => 'Rs.',
                        'currency_code' => 'NPR',
                        'payment_options' => ['cash' => 'Cash'],
                    ])]
                );
            }
        }

        // Mediums
        $medium1 = Mediums::firstOrCreate(['name' => 'English'], ['name' => 'English']);
        $medium2 = Mediums::firstOrCreate(['name' => 'Nepali'], ['name' => 'Nepali']);
        $this->mediumId = $medium1->id;

        // Streams
        $stream = Stream::firstOrCreate(['name' => 'Science'], ['name' => 'Science']);
        $this->streamId = $stream->id;
        Stream::firstOrCreate(['name' => 'Management'], ['name' => 'Management']);

        // Shifts
        $shift = Shift::firstOrCreate(
            ['title' => 'Morning'],
            ['title' => 'Morning', 'start_time' => '06:00:00', 'end_time' => '12:00:00', 'status' => 1]
        );
        $this->shiftId = $shift->id;
        Shift::firstOrCreate(
            ['title' => 'Day'],
            ['title' => 'Day', 'start_time' => '12:00:00', 'end_time' => '18:00:00', 'status' => 1]
        );

        // Educational Programs
        $edu = EducationalProgram::firstOrCreate(
            ['title' => 'NEB +2'],
            ['title' => 'NEB +2', 'image' => 'logo.svg']
        );
        $this->educationalProgramId = $edu->id;
        EducationalProgram::firstOrCreate(
            ['title' => 'Bachelor'],
            ['title' => 'Bachelor', 'image' => 'logo.svg']
        );

        // Categories
        $cat1 = Category::firstOrCreate(['name' => 'General'], ['name' => 'General', 'status' => 1]);
        $cat2 = Category::firstOrCreate(['name' => 'OBC'], ['name' => 'OBC', 'status' => 1]);
        $this->categoryIds = [$cat1->id, $cat2->id];

        // Sections
        $secA = Section::firstOrCreate(['name' => 'A'], ['name' => 'A']);
        $secB = Section::firstOrCreate(['name' => 'B'], ['name' => 'B']);
        $this->sectionIds = [$secA->id, $secB->id];

        // Semesters
        $sem1 = Semester::firstOrCreate(
            ['name' => 'First Semester'],
            ['name' => 'First Semester', 'start_date' => '2024-04-01', 'end_date' => '2024-09-30', 'status' => 1]
        );
        $sem2 = Semester::firstOrCreate(
            ['name' => 'Second Semester'],
            ['name' => 'Second Semester', 'start_date' => '2024-10-01', 'end_date' => '2025-03-31', 'status' => 1]
        );
        $this->semesterId1 = $sem1->id;
        $this->semesterId2 = $sem2->id;

        // Classes
        $class10 = ClassSchool::firstOrCreate(
            ['name' => 'Grade 10'],
            [
                'name' => 'Grade 10',
                'include_semesters' => 0,
                'medium_id' => $this->mediumId,
                'stream_id' => $this->streamId,
                'shift_id' => $this->shiftId,
                'educational_program_id' => $this->educationalProgramId,
            ]
        );
        $class11 = ClassSchool::firstOrCreate(
            ['name' => 'Grade 11'],
            [
                'name' => 'Grade 11',
                'include_semesters' => 1,
                'medium_id' => $this->mediumId,
                'stream_id' => $this->streamId,
                'shift_id' => $this->shiftId,
                'educational_program_id' => $this->educationalProgramId,
            ]
        );
        $this->classIds = [$class10->id, $class11->id];

        // Class Sections
        $cs1 = ClassSection::firstOrCreate(
            ['class_id' => $class10->id, 'section_id' => $secA->id],
            ['class_id' => $class10->id, 'section_id' => $secA->id]
        );
        $cs2 = ClassSection::firstOrCreate(
            ['class_id' => $class10->id, 'section_id' => $secB->id],
            ['class_id' => $class10->id, 'section_id' => $secB->id]
        );
        $cs3 = ClassSection::firstOrCreate(
            ['class_id' => $class11->id, 'section_id' => $secA->id],
            ['class_id' => $class11->id, 'section_id' => $secA->id]
        );
        $cs4 = ClassSection::firstOrCreate(
            ['class_id' => $class11->id, 'section_id' => $secB->id],
            ['class_id' => $class11->id, 'section_id' => $secB->id]
        );
        $this->classSectionIds = [$cs1->id, $cs2->id, $cs3->id, $cs4->id];

        // Subjects
        $sub1 = Subject::firstOrCreate(
            ['name' => 'Mathematics'],
            ['name' => 'Mathematics', 'code' => 'MATH101', 'bg_color' => '#FF5733', 'medium_id' => $this->mediumId, 'type' => 'Theory', 'class_level' => 'secondary']
        );
        $sub2 = Subject::firstOrCreate(
            ['name' => 'English'],
            ['name' => 'English', 'code' => 'ENG101', 'bg_color' => '#33FF57', 'medium_id' => $this->mediumId, 'type' => 'Theory', 'class_level' => 'secondary']
        );
        $sub3 = Subject::firstOrCreate(
            ['name' => 'Science'],
            ['name' => 'Science', 'code' => 'SCI101', 'bg_color' => '#3357FF', 'medium_id' => $this->mediumId, 'type' => 'Theory', 'class_level' => 'secondary']
        );
        $sub4 = Subject::firstOrCreate(
            ['name' => 'Nepali'],
            ['name' => 'Nepali', 'code' => 'NEP101', 'bg_color' => '#FF33A8', 'medium_id' => $this->mediumId, 'type' => 'Theory', 'class_level' => 'secondary']
        );
        $this->subjectIds = [$sub1->id, $sub2->id, $sub3->id, $sub4->id];

        // Class Subjects (core subjects for each class)
        foreach ($this->classIds as $classId) {
            foreach ([$sub1->id, $sub2->id, $sub3->id] as $subId) {
                ClassSubject::firstOrCreate(
                    ['class_id' => $classId, 'subject_id' => $subId],
                    ['class_id' => $classId, 'subject_id' => $subId, 'type' => 'Compulsory', 'semester_id' => null]
                );
            }
            // Elective subject
            ClassSubject::firstOrCreate(
                ['class_id' => $classId, 'subject_id' => $sub4->id],
                ['class_id' => $classId, 'subject_id' => $sub4->id, 'type' => 'Elective', 'semester_id' => null, 'elective_subject_group_id' => null]
            );
        }

        // Elective Subject Groups
        ElectiveSubjectGroup::firstOrCreate(
            ['total_subjects' => 2, 'total_selectable_subjects' => 1, 'class_id' => $class10->id],
            ['total_subjects' => 2, 'total_selectable_subjects' => 1, 'class_id' => $class10->id, 'semester_id' => null]
        );
        ElectiveSubjectGroup::firstOrCreate(
            ['total_subjects' => 3, 'total_selectable_subjects' => 2, 'class_id' => $class11->id],
            ['total_subjects' => 3, 'total_selectable_subjects' => 2, 'class_id' => $class11->id, 'semester_id' => $this->semesterId1]
        );

        // Grades
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
        Grade::firstOrCreate(
            ['starting_range' => 40, 'ending_range' => 59],
            ['starting_range' => 40, 'ending_range' => 59, 'grade' => 'C']
        );
        Grade::firstOrCreate(
            ['starting_range' => 0, 'ending_range' => 39],
            ['starting_range' => 0, 'ending_range' => 39, 'grade' => 'F']
        );

        // Leave Masters
        LeaveMaster::firstOrCreate(
            ['total_leave' => '30', 'session_year_id' => $this->sessionYearId],
            ['total_leave' => '30', 'holiday_days' => '0', 'session_year_id' => $this->sessionYearId]
        );
        LeaveMaster::firstOrCreate(
            ['total_leave' => '15', 'session_year_id' => $this->sessionYearId],
            ['total_leave' => '15', 'holiday_days' => '0', 'session_year_id' => $this->sessionYearId]
        );

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

        $this->command->info('Foundation tables seeded.');
    }

    private function seedUsersAndProfiles(): void
    {
        $this->command->info('Seeding users and profiles...');

        // Create extra teachers
        $teacherRole = Role::where('name', 'Teacher')->first();
        $teacherUser1 = User::firstOrCreate(
            ['email' => 'math.teacher@school.com'],
            [
                'first_name' => 'Ram',
                'last_name' => 'Sharma',
                'password' => Hash::make('teacher123'),
                'gender' => 'Male',
                'image' => 'logo.svg',
                'mobile' => '9800000001',
                'current_address' => 'Kathmandu',
                'permanent_address' => 'Kathmandu',
                'status' => 1,
            ]
        );
        if ($teacherRole && ! $teacherUser1->hasRole('Teacher')) {
            $teacherUser1->assignRole('Teacher');
        }
        $teacherUser2 = User::firstOrCreate(
            ['email' => 'english.teacher@school.com'],
            [
                'first_name' => 'Sita',
                'last_name' => 'Adhikari',
                'password' => Hash::make('teacher123'),
                'gender' => 'Female',
                'image' => 'logo.svg',
                'mobile' => '9800000002',
                'current_address' => 'Lalitpur',
                'permanent_address' => 'Lalitpur',
                'status' => 1,
            ]
        );
        if ($teacherRole && ! $teacherUser2->hasRole('Teacher')) {
            $teacherUser2->assignRole('Teacher');
        }

        // Teacher profiles
        $teacher1 = Teacher::firstOrCreate(
            ['user_id' => $teacherUser1->id],
            ['user_id' => $teacherUser1->id, 'qualification' => 'M.Sc. Mathematics']
        );
        $teacher2 = Teacher::firstOrCreate(
            ['user_id' => $teacherUser2->id],
            ['user_id' => $teacherUser2->id, 'qualification' => 'M.A. English']
        );
        $this->teacherIds = [$teacher1->id, $teacher2->id];

        // Create extra parents
        $parentRole = Role::where('name', 'Parent')->first();
        $parentUser1 = User::firstOrCreate(
            ['email' => 'parent1@example.com'],
            [
                'first_name' => 'Krishna',
                'last_name' => 'Thapa',
                'password' => Hash::make('parent123'),
                'gender' => 'Male',
                'image' => 'parents/user.png',
                'mobile' => '9800000003',
                'current_address' => 'Pokhara',
                'permanent_address' => 'Pokhara',
                'status' => 1,
            ]
        );
        if ($parentRole && ! $parentUser1->hasRole('Parent')) {
            $parentUser1->assignRole('Parent');
        }
        $parentUser2 = User::firstOrCreate(
            ['email' => 'parent2@example.com'],
            [
                'first_name' => 'Gita',
                'last_name' => 'Rai',
                'password' => Hash::make('parent123'),
                'gender' => 'Female',
                'image' => 'parents/user.png',
                'mobile' => '9800000004',
                'current_address' => 'Bhaktapur',
                'permanent_address' => 'Bhaktapur',
                'status' => 1,
            ]
        );
        if ($parentRole && ! $parentUser2->hasRole('Parent')) {
            $parentUser2->assignRole('Parent');
        }

        $parent1 = Parents::firstOrCreate(
            ['user_id' => $parentUser1->id],
            [
                'user_id' => $parentUser1->id,
                'first_name' => 'Krishna',
                'last_name' => 'Thapa',
                'gender' => 'Male',
                'email' => 'parent1@example.com',
                'mobile' => '9800000003',
                'occupation' => 'Engineer',
                'image' => 'parents/user.png',
            ]
        );
        $parent2 = Parents::firstOrCreate(
            ['user_id' => $parentUser2->id],
            [
                'user_id' => $parentUser2->id,
                'first_name' => 'Gita',
                'last_name' => 'Rai',
                'gender' => 'Female',
                'email' => 'parent2@example.com',
                'mobile' => '9800000004',
                'occupation' => 'Doctor',
                'image' => 'parents/user.png',
            ]
        );
        $this->parentIds = [$parent1->id, $parent2->id];

        // Create students
        $studentRole = Role::where('name', 'Student')->first();
        $studentUser1 = User::firstOrCreate(
            ['email' => 'hari.student@school.com'],
            [
                'first_name' => 'Hari',
                'last_name' => 'Thapa',
                'password' => Hash::make('student123'),
                'gender' => 'Male',
                'image' => 'students/user.png',
                'mobile' => '9800000005',
                'current_address' => 'Pokhara',
                'permanent_address' => 'Pokhara',
                'status' => 1,
            ]
        );
        if ($studentRole && ! $studentUser1->hasRole('Student')) {
            $studentUser1->assignRole('Student');
        }
        $studentUser2 = User::firstOrCreate(
            ['email' => 'gita.student@school.com'],
            [
                'first_name' => 'Gita',
                'last_name' => 'Rai',
                'password' => Hash::make('student123'),
                'gender' => 'Female',
                'image' => 'students/user.png',
                'mobile' => '9800000006',
                'current_address' => 'Bhaktapur',
                'permanent_address' => 'Bhaktapur',
                'status' => 1,
            ]
        );
        if ($studentRole && ! $studentUser2->hasRole('Student')) {
            $studentUser2->assignRole('Student');
        }

        $student1 = Students::firstOrCreate(
            ['user_id' => $studentUser1->id],
            [
                'user_id' => $studentUser1->id,
                'class_id' => $this->classIds[0],
                'class_section_id' => $this->classSectionIds[0],
                'category_id' => $this->categoryIds[0],
                'admission_no' => 'STU001',
                'roll_number' => 1,
                'admission_date' => '2024-04-01',
                'father_id' => $parent1->id,
                'mother_id' => $parent2->id,
                'guardian_id' => $parent1->id,
                'registration_payment_status' => 1,
                'is_new_admission' => 0,
            ]
        );
        $student2 = Students::firstOrCreate(
            ['user_id' => $studentUser2->id],
            [
                'user_id' => $studentUser2->id,
                'class_id' => $this->classIds[0],
                'class_section_id' => $this->classSectionIds[1],
                'category_id' => $this->categoryIds[1],
                'admission_no' => 'STU002',
                'roll_number' => 2,
                'admission_date' => '2024-04-01',
                'father_id' => $parent1->id,
                'mother_id' => $parent2->id,
                'guardian_id' => $parent2->id,
                'registration_payment_status' => 1,
                'is_new_admission' => 0,
            ]
        );
        $this->studentIds = [$student1->id, $student2->id];

        // Update demo student registration_payment_status if exists
        $demoStudent = Students::whereHas('user', fn ($q) => $q->where('email', 'student@gmail.com'))->first();
        if ($demoStudent && ! $demoStudent->registration_payment_status) {
            $demoStudent->registration_payment_status = 1;
            $demoStudent->save();
        }

        // Student Sessions — seed for ALL existing students
        $allStudents = Students::all();
        foreach ($allStudents as $student) {
            StudentSessions::firstOrCreate(
                ['student_id' => $student->id, 'session_year_id' => $this->sessionYearId],
                [
                    'student_id' => $student->id,
                    'class_section_id' => $student->class_section_id,
                    'session_year_id' => $this->sessionYearId,
                    'status' => 1,
                    'result' => 1,
                ]
            );
            // Set registration_payment_status for all students
            if (! $student->registration_payment_status) {
                $student->registration_payment_status = 1;
                $student->save();
            }
        }

        // Class Teachers — seed for ALL existing class sections
        $allClassSections = ClassSection::all();
        $allTeachers = Teacher::all();
        if ($allTeachers->count() >= 2) {
            $teacherIds = $allTeachers->pluck('id')->toArray();
            foreach ($allClassSections as $idx => $cs) {
                $tid = $teacherIds[$idx % count($teacherIds)];
                ClassTeacher::firstOrCreate(
                    ['class_section_id' => $cs->id, 'class_teacher_id' => $tid],
                    ['class_section_id' => $cs->id, 'class_teacher_id' => $tid]
                );
            }
        }

        // Subject Teachers — seed for ALL existing class sections
        foreach ($allClassSections as $cs) {
            $classId = $cs->class_id;
            $classSubjects = ClassSubject::where('class_id', $classId)->pluck('subject_id')->toArray();
            foreach (array_slice($classSubjects, 0, 2) as $idx => $subId) {
                if (isset($teacherIds[$idx])) {
                    SubjectTeacher::firstOrCreate(
                        ['class_section_id' => $cs->id, 'subject_id' => $subId, 'teacher_id' => $teacherIds[$idx]],
                        ['class_section_id' => $cs->id, 'subject_id' => $subId, 'teacher_id' => $teacherIds[$idx]]
                    );
                }
            }
        }

        // Student Subjects — seed for ALL existing students
        foreach ($allStudents as $student) {
            $cs = $student->class_section;
            if (! $cs) {
                continue;
            }
            $classSubjects = ClassSubject::where('class_id', $cs->class_id)->pluck('subject_id')->toArray();
            foreach (array_slice($classSubjects, 0, 4) as $subId) {
                StudentSubject::firstOrCreate(
                    ['student_id' => $student->id, 'subject_id' => $subId, 'class_section_id' => $cs->id],
                    ['student_id' => $student->id, 'subject_id' => $subId, 'class_section_id' => $cs->id, 'session_year_id' => $this->sessionYearId]
                );
            }
        }

        // Staff
        if (Schema::hasTable('staffs')) {
            $staffUser = User::firstOrCreate(
                ['email' => 'staff1@school.com'],
                [
                    'first_name' => 'Staff',
                    'last_name' => 'One',
                    'password' => Hash::make('staff123'),
                    'gender' => 'Male',
                    'image' => 'logo.svg',
                    'status' => 1,
                ]
            );
            DB::table('staffs')->updateOrInsert(
                ['user_id' => $staffUser->id],
                ['user_id' => $staffUser->id]
            );
            $staffUser2 = User::firstOrCreate(
                ['email' => 'staff2@school.com'],
                [
                    'first_name' => 'Staff',
                    'last_name' => 'Two',
                    'password' => Hash::make('staff123'),
                    'gender' => 'Female',
                    'image' => 'logo.svg',
                    'status' => 1,
                ]
            );
            DB::table('staffs')->updateOrInsert(
                ['user_id' => $staffUser2->id],
                ['user_id' => $staffUser2->id]
            );
        }

        $this->command->info('Users and profiles seeded.');
    }

    private function seedAcademic(): void
    {
        $this->command->info('Seeding academic data...');

        // Lessons
        $lesson1 = Lesson::firstOrCreate(
            ['name' => 'Introduction to Algebra', 'class_section_id' => $this->classSectionIds[0], 'subject_id' => $this->subjectIds[0]],
            ['name' => 'Introduction to Algebra', 'description' => 'Basic algebraic concepts', 'class_section_id' => $this->classSectionIds[0], 'subject_id' => $this->subjectIds[0]]
        );
        $lesson2 = Lesson::firstOrCreate(
            ['name' => 'Grammar - Parts of Speech', 'class_section_id' => $this->classSectionIds[0], 'subject_id' => $this->subjectIds[1]],
            ['name' => 'Grammar - Parts of Speech', 'description' => 'Nouns, verbs, adjectives', 'class_section_id' => $this->classSectionIds[0], 'subject_id' => $this->subjectIds[1]]
        );
        $lesson3 = Lesson::firstOrCreate(
            ['name' => 'Geometry Basics', 'class_section_id' => $this->classSectionIds[1], 'subject_id' => $this->subjectIds[0]],
            ['name' => 'Geometry Basics', 'description' => 'Points, lines, angles', 'class_section_id' => $this->classSectionIds[1], 'subject_id' => $this->subjectIds[0]]
        );
        $lesson4 = Lesson::firstOrCreate(
            ['name' => 'Reading Comprehension', 'class_section_id' => $this->classSectionIds[1], 'subject_id' => $this->subjectIds[1]],
            ['name' => 'Reading Comprehension', 'description' => 'Comprehension strategies', 'class_section_id' => $this->classSectionIds[1], 'subject_id' => $this->subjectIds[1]]
        );

        // Lesson Topics
        LessonTopic::firstOrCreate(
            ['lesson_id' => $lesson1->id, 'name' => 'Variables and Constants'],
            ['lesson_id' => $lesson1->id, 'name' => 'Variables and Constants', 'description' => 'Understanding variables']
        );
        LessonTopic::firstOrCreate(
            ['lesson_id' => $lesson1->id, 'name' => 'Linear Equations'],
            ['lesson_id' => $lesson1->id, 'name' => 'Linear Equations', 'description' => 'Solving linear equations']
        );
        LessonTopic::firstOrCreate(
            ['lesson_id' => $lesson2->id, 'name' => 'Nouns'],
            ['lesson_id' => $lesson2->id, 'name' => 'Nouns', 'description' => 'Types and usage']
        );
        LessonTopic::firstOrCreate(
            ['lesson_id' => $lesson2->id, 'name' => 'Verbs'],
            ['lesson_id' => $lesson2->id, 'name' => 'Verbs', 'description' => 'Action and linking verbs']
        );

        // Assignments
        $assignment1 = Assignment::firstOrCreate(
            ['name' => 'Algebra Practice Set', 'class_section_id' => $this->classSectionIds[0], 'subject_id' => $this->subjectIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'name' => 'Algebra Practice Set',
                'instructions' => 'Complete all 20 questions from chapter 1.',
                'due_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'points' => 20,
                'resubmission' => 1,
                'extra_days_for_resubmission' => 3,
                'class_section_id' => $this->classSectionIds[0],
                'subject_id' => $this->subjectIds[0],
                'session_year_id' => $this->sessionYearId,
            ]
        );
        $assignment2 = Assignment::firstOrCreate(
            ['name' => 'English Essay Writing', 'class_section_id' => $this->classSectionIds[0], 'subject_id' => $this->subjectIds[1], 'session_year_id' => $this->sessionYearId],
            [
                'name' => 'English Essay Writing',
                'instructions' => 'Write a 500-word essay on "My School".',
                'due_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'points' => 15,
                'resubmission' => 0,
                'extra_days_for_resubmission' => 0,
                'class_section_id' => $this->classSectionIds[0],
                'subject_id' => $this->subjectIds[1],
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Assignment Submissions
        AssignmentSubmission::firstOrCreate(
            ['assignment_id' => $assignment1->id, 'student_id' => $this->studentIds[0]],
            [
                'assignment_id' => $assignment1->id,
                'student_id' => $this->studentIds[0],
                'text_submission' => 'Here are my answers to the algebra practice set.',
                'session_year_id' => $this->sessionYearId,
                'status' => 1,
                'points' => 18,
                'feedback' => 'Good work!',
            ]
        );
        AssignmentSubmission::firstOrCreate(
            ['assignment_id' => $assignment2->id, 'student_id' => $this->studentIds[1]],
            [
                'assignment_id' => $assignment2->id,
                'student_id' => $this->studentIds[1],
                'text_submission' => 'My essay about school.',
                'session_year_id' => $this->sessionYearId,
                'status' => 1,
                'points' => 13,
                'feedback' => 'Needs improvement.',
            ]
        );

        // Holidays
        Holiday::firstOrCreate(
            ['date' => '2024-10-15'],
            ['date' => '2024-10-15', 'title' => 'Dashain Festival', 'description' => 'Major Hindu festival']
        );
        Holiday::firstOrCreate(
            ['date' => '2024-12-25'],
            ['date' => '2024-12-25', 'title' => 'Christmas Day', 'description' => 'Christian holiday celebration']
        );
        Holiday::firstOrCreate(
            ['date' => '2025-01-01'],
            ['date' => '2025-01-01', 'title' => 'New Year Day', 'description' => 'International New Year']
        );

        // Academic Calendars
        DB::table('academic_calendars')->updateOrInsert(
            ['date' => '2024-04-01'],
            ['date' => '2024-04-01', 'title' => 'Session Start', 'description' => 'Academic session begins', 'session_year_id' => $this->sessionYearId]
        );
        DB::table('academic_calendars')->updateOrInsert(
            ['date' => '2024-05-15'],
            ['date' => '2024-05-15', 'title' => 'First Term Exam', 'description' => 'First term examinations', 'session_year_id' => $this->sessionYearId]
        );

        // Events
        $event1 = Event::firstOrCreate(
            ['title' => 'Annual Sports Day'],
            [
                'title' => 'Annual Sports Day',
                'type' => 'sport',
                'start_date' => '2024-08-15',
                'end_date' => '2024-08-16',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'description' => 'Annual sports competition across all grades.',
                'image' => 'logo.svg',
            ]
        );
        $event2 = Event::firstOrCreate(
            ['title' => 'Science Exhibition'],
            [
                'title' => 'Science Exhibition',
                'type' => 'academic',
                'start_date' => '2024-09-01',
                'end_date' => '2024-09-02',
                'start_time' => '10:00:00',
                'end_time' => '16:00:00',
                'description' => 'Students showcase their science projects.',
                'image' => 'logo.svg',
            ]
        );

        // Multiple Events
        MultipleEvent::firstOrCreate(
            ['event_id' => $event1->id, 'date' => '2024-08-15'],
            ['event_id' => $event1->id, 'date' => '2024-08-15', 'title' => 'Day 1 - Track Events', 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'description' => 'Running and relay races']
        );
        MultipleEvent::firstOrCreate(
            ['event_id' => $event1->id, 'date' => '2024-08-16'],
            ['event_id' => $event1->id, 'date' => '2024-08-16', 'title' => 'Day 2 - Field Events', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'description' => 'Long jump, high jump, shot put']
        );
        MultipleEvent::firstOrCreate(
            ['event_id' => $event2->id, 'date' => '2024-09-01'],
            ['event_id' => $event2->id, 'date' => '2024-09-01', 'title' => 'Projects Display', 'start_time' => '10:00:00', 'end_time' => '16:00:00', 'description' => 'Science fair display']
        );

        // Form Fields
        FormField::firstOrCreate(
            ['name' => 'blood_group', 'for' => 'students'],
            ['name' => 'blood_group', 'type' => 'dropdown', 'for' => 'students', 'is_required' => 1, 'default_values' => '["A+","B+","O+","AB+"]', 'rank' => 1]
        );
        FormField::firstOrCreate(
            ['name' => 'parent_occupation', 'for' => 'students'],
            ['name' => 'parent_occupation', 'type' => 'text', 'for' => 'students', 'is_required' => 0, 'default_values' => '', 'rank' => 2]
        );

        // Fees Types
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

        $feesTypes = FeesType::pluck('id')->toArray();
        $feesTypeId1 = $feesTypes[0];
        $feesTypeId2 = $feesTypes[1];

        $this->command->info('Academic data seeded.');
    }

    private function seedFees(): void
    {
        $this->command->info('Seeding fees data...');

        $feesTypes = FeesType::pluck('id')->toArray();
        $feesTypeId1 = $feesTypes[0];
        $feesTypeId2 = $feesTypes[1];

        // Fees Classes — seed for ALL existing classes
        $allClassIds = ClassSchool::pluck('id')->toArray();
        foreach ($allClassIds as $clsId) {
            FeesClass::firstOrCreate(
                ['class_id' => $clsId, 'fees_type_id' => $feesTypeId1],
                ['class_id' => $clsId, 'fees_type_id' => $feesTypeId1, 'amount' => 1500.00, 'choiceable' => 0]
            );
            FeesClass::firstOrCreate(
                ['class_id' => $clsId, 'fees_type_id' => $feesTypeId2],
                ['class_id' => $clsId, 'fees_type_id' => $feesTypeId2, 'amount' => 500.00, 'choiceable' => 0]
            );
        }

        // Class Subjects for all existing classes that don't already have entries
        foreach ($allClassIds as $clsId) {
            foreach ([$this->subjectIds[0], $this->subjectIds[1], $this->subjectIds[2]] as $subId) {
                ClassSubject::firstOrCreate(
                    ['class_id' => $clsId, 'subject_id' => $subId],
                    ['class_id' => $clsId, 'subject_id' => $subId, 'type' => 'Compulsory', 'semester_id' => null]
                );
            }
            ClassSubject::firstOrCreate(
                ['class_id' => $clsId, 'subject_id' => $this->subjectIds[3]],
                ['class_id' => $clsId, 'subject_id' => $this->subjectIds[3], 'type' => 'Elective', 'semester_id' => null, 'elective_subject_group_id' => null]
            );
        }

        // Payment Transactions
        $txn1 = PaymentTransaction::firstOrCreate(
            ['order_id' => 'ORD-'.uniqid()],
            [
                'student_id' => $this->studentIds[0],
                'class_id' => $this->classIds[0],
                'parent_id' => $this->parentIds[0],
                'mode' => 1,
                'type_of_fee' => 1,
                'payment_gateway' => 1,
                'order_id' => 'ORD-'.uniqid(),
                'payment_id' => 'PAY-'.uniqid(),
                'payment_status' => 1,
                'total_amount' => 1500.00,
                'date' => now(),
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Fees Paid
        FeesPaid::firstOrCreate(
            ['student_id' => $this->studentIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'parent_id' => $this->parentIds[0],
                'student_id' => $this->studentIds[0],
                'class_id' => $this->classIds[0],
                'mode' => 1,
                'payment_transaction_id' => (string) $txn1->id,
                'total_amount' => 1500.00,
                'due_charges' => 0,
                'is_fully_paid' => 0,
                'date' => now(),
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Another payment transaction for student 2
        $txn2 = PaymentTransaction::firstOrCreate(
            ['order_id' => 'ORD-'.uniqid()],
            [
                'student_id' => $this->studentIds[1],
                'class_id' => $this->classIds[0],
                'parent_id' => $this->parentIds[1],
                'mode' => 1,
                'type_of_fee' => 1,
                'payment_gateway' => 1,
                'order_id' => 'ORD-'.uniqid(),
                'payment_id' => 'PAY-'.uniqid(),
                'payment_status' => 1,
                'total_amount' => 500.00,
                'date' => now(),
                'session_year_id' => $this->sessionYearId,
            ]
        );

        FeesPaid::firstOrCreate(
            ['student_id' => $this->studentIds[1], 'session_year_id' => $this->sessionYearId],
            [
                'parent_id' => $this->parentIds[1],
                'student_id' => $this->studentIds[1],
                'class_id' => $this->classIds[0],
                'mode' => 1,
                'payment_transaction_id' => (string) $txn2->id,
                'total_amount' => 500.00,
                'due_charges' => 0,
                'is_fully_paid' => 0,
                'date' => now(),
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Installment Fees
        InstallmentFee::firstOrCreate(
            ['name' => 'First Installment', 'session_year_id' => $this->sessionYearId],
            ['name' => 'First Installment', 'due_date' => '2024-06-01', 'due_charges' => 50, 'session_year_id' => $this->sessionYearId]
        );
        InstallmentFee::firstOrCreate(
            ['name' => 'Second Installment', 'session_year_id' => $this->sessionYearId],
            ['name' => 'Second Installment', 'due_date' => '2024-09-01', 'due_charges' => 50, 'session_year_id' => $this->sessionYearId]
        );

        // Fees Choiceables
        FeesChoiceable::firstOrCreate(
            ['student_id' => $this->studentIds[0], 'fees_type_id' => $feesTypeId2, 'session_year_id' => $this->sessionYearId],
            [
                'student_id' => $this->studentIds[0],
                'class_id' => $this->classIds[0],
                'fees_type_id' => $feesTypeId2,
                'is_due_charges' => 0,
                'total_amount' => 500.00,
                'session_year_id' => $this->sessionYearId,
                'date' => now(),
                'status' => 1,
            ]
        );
        FeesChoiceable::firstOrCreate(
            ['student_id' => $this->studentIds[1], 'fees_type_id' => $feesTypeId2, 'session_year_id' => $this->sessionYearId],
            [
                'student_id' => $this->studentIds[1],
                'class_id' => $this->classIds[0],
                'fees_type_id' => $feesTypeId2,
                'is_due_charges' => 0,
                'total_amount' => 500.00,
                'session_year_id' => $this->sessionYearId,
                'date' => now(),
                'status' => 0,
            ]
        );

        $this->command->info('Fees data seeded.');
    }

    private function seedExams(): void
    {
        $this->command->info('Seeding exams data...');

        // Exams
        $exam1 = Exam::firstOrCreate(
            ['name' => 'First Term Examination', 'session_year_id' => $this->sessionYearId],
            ['name' => 'First Term Examination', 'description' => 'First term exams for all subjects', 'session_year_id' => $this->sessionYearId, 'publish' => 1]
        );
        $exam2 = Exam::firstOrCreate(
            ['name' => 'Final Examination', 'session_year_id' => $this->sessionYearId],
            ['name' => 'Final Examination', 'description' => 'Year-end final exams', 'session_year_id' => $this->sessionYearId, 'publish' => 1]
        );

        // Exam Classes — seed for ALL existing classes
        $allClassIdsForExam = ClassSchool::pluck('id')->toArray();
        foreach ($allClassIdsForExam as $clsId) {
            ExamClass::firstOrCreate(
                ['exam_id' => $exam1->id, 'class_id' => $clsId],
                ['exam_id' => $exam1->id, 'class_id' => $clsId]
            );
            ExamClass::firstOrCreate(
                ['exam_id' => $exam2->id, 'class_id' => $clsId],
                ['exam_id' => $exam2->id, 'class_id' => $clsId]
            );
        }

        // Exam Timetables
        $et1 = ExamTimetable::firstOrCreate(
            ['exam_id' => $exam1->id, 'subject_id' => $this->subjectIds[0], 'class_id' => $this->classIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'exam_id' => $exam1->id,
                'class_id' => $this->classIds[0],
                'subject_id' => $this->subjectIds[0],
                'total_marks' => 100,
                'passing_marks' => 40,
                'date' => '2024-05-15',
                'start_time' => '10:00:00',
                'end_time' => '13:00:00',
                'session_year_id' => $this->sessionYearId,
            ]
        );
        $et2 = ExamTimetable::firstOrCreate(
            ['exam_id' => $exam1->id, 'subject_id' => $this->subjectIds[1], 'class_id' => $this->classIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'exam_id' => $exam1->id,
                'class_id' => $this->classIds[0],
                'subject_id' => $this->subjectIds[1],
                'total_marks' => 100,
                'passing_marks' => 40,
                'date' => '2024-05-17',
                'start_time' => '10:00:00',
                'end_time' => '13:00:00',
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Exam Marks
        ExamMarks::firstOrCreate(
            ['exam_timetable_id' => $et1->id, 'student_id' => $this->studentIds[0], 'subject_id' => $this->subjectIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'exam_timetable_id' => $et1->id,
                'student_id' => $this->studentIds[0],
                'subject_id' => $this->subjectIds[0],
                'obtained_marks' => 85,
                'passing_status' => 1,
                'session_year_id' => $this->sessionYearId,
                'grade' => 'A',
            ]
        );
        ExamMarks::firstOrCreate(
            ['exam_timetable_id' => $et2->id, 'student_id' => $this->studentIds[0], 'subject_id' => $this->subjectIds[1], 'session_year_id' => $this->sessionYearId],
            [
                'exam_timetable_id' => $et2->id,
                'student_id' => $this->studentIds[0],
                'subject_id' => $this->subjectIds[1],
                'obtained_marks' => 72,
                'passing_status' => 1,
                'session_year_id' => $this->sessionYearId,
                'grade' => 'B+',
            ]
        );
        ExamMarks::firstOrCreate(
            ['exam_timetable_id' => $et1->id, 'student_id' => $this->studentIds[1], 'subject_id' => $this->subjectIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'exam_timetable_id' => $et1->id,
                'student_id' => $this->studentIds[1],
                'subject_id' => $this->subjectIds[0],
                'obtained_marks' => 65,
                'passing_status' => 1,
                'session_year_id' => $this->sessionYearId,
                'grade' => 'B',
            ]
        );

        // Exam Results
        $totalMarks1 = 100 + 100;
        $obtainedMarks1 = 85 + 72;
        ExamResult::firstOrCreate(
            ['exam_id' => $exam1->id, 'student_id' => $this->studentIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'exam_id' => $exam1->id,
                'class_section_id' => $this->classSectionIds[0],
                'student_id' => $this->studentIds[0],
                'total_marks' => $totalMarks1,
                'obtained_marks' => $obtainedMarks1,
                'percentage' => round(($obtainedMarks1 / $totalMarks1) * 100, 2),
                'grade' => 'B+',
                'session_year_id' => $this->sessionYearId,
            ]
        );
        ExamResult::firstOrCreate(
            ['exam_id' => $exam1->id, 'student_id' => $this->studentIds[1], 'session_year_id' => $this->sessionYearId],
            [
                'exam_id' => $exam1->id,
                'class_section_id' => $this->classSectionIds[1],
                'student_id' => $this->studentIds[1],
                'total_marks' => 100,
                'obtained_marks' => 65,
                'percentage' => 65.00,
                'grade' => 'B',
                'session_year_id' => $this->sessionYearId,
            ]
        );

        $this->command->info('Exams data seeded.');
    }

    private function seedOnlineExams(): void
    {
        $this->command->info('Seeding online exams data...');

        // Online Exams
        $oe1 = OnlineExam::firstOrCreate(
            ['title' => 'Math Unit Test 1', 'subject_id' => $this->subjectIds[0], 'session_year_id' => $this->sessionYearId],
            [
                'model_type' => 'App\\Models\\ClassSchool',
                'model_id' => $this->classIds[0],
                'subject_id' => $this->subjectIds[0],
                'title' => 'Math Unit Test 1',
                'exam_key' => 12345,
                'duration' => 60,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(7),
                'session_year_id' => $this->sessionYearId,
            ]
        );
        $oe2 = OnlineExam::firstOrCreate(
            ['title' => 'English Quiz 1', 'subject_id' => $this->subjectIds[1], 'session_year_id' => $this->sessionYearId],
            [
                'model_type' => 'App\\Models\\ClassSchool',
                'model_id' => $this->classIds[0],
                'subject_id' => $this->subjectIds[1],
                'title' => 'English Quiz 1',
                'exam_key' => 54321,
                'duration' => 45,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(14),
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Online Exam Questions
        $q1 = OnlineExamQuestion::firstOrCreate(
            ['question' => 'What is 2 + 2?'],
            [
                'class_subject_id' => ClassSubject::where('class_id', $this->classIds[0])->where('subject_id', $this->subjectIds[0])->first()?->id ?? 1,
                'question_type' => 0,
                'question' => 'What is 2 + 2?',
                'note' => 'Basic arithmetic',
            ]
        );
        $q2 = OnlineExamQuestion::firstOrCreate(
            ['question' => 'What is the square root of 16?'],
            [
                'class_subject_id' => ClassSubject::where('class_id', $this->classIds[0])->where('subject_id', $this->subjectIds[0])->first()?->id ?? 1,
                'question_type' => 0,
                'question' => 'What is the square root of 16?',
                'note' => 'Square roots',
            ]
        );
        $q3 = OnlineExamQuestion::firstOrCreate(
            ['question' => 'What is a noun?'],
            [
                'class_subject_id' => ClassSubject::where('class_id', $this->classIds[0])->where('subject_id', $this->subjectIds[1])->first()?->id ?? 2,
                'question_type' => 0,
                'question' => 'What is a noun?',
                'note' => 'Parts of speech',
            ]
        );
        $q4 = OnlineExamQuestion::firstOrCreate(
            ['question' => 'Identify the verb: "She runs fast."'],
            [
                'class_subject_id' => ClassSubject::where('class_id', $this->classIds[0])->where('subject_id', $this->subjectIds[1])->first()?->id ?? 2,
                'question_type' => 0,
                'question' => 'Identify the verb: "She runs fast."',
                'note' => 'Verbs',
            ]
        );

        // Online Exam Question Choices (link questions to exams)
        OnlineExamQuestionChoice::firstOrCreate(
            ['online_exam_id' => $oe1->id, 'question_id' => $q1->id],
            ['online_exam_id' => $oe1->id, 'question_id' => $q1->id, 'marks' => 5]
        );
        OnlineExamQuestionChoice::firstOrCreate(
            ['online_exam_id' => $oe1->id, 'question_id' => $q2->id],
            ['online_exam_id' => $oe1->id, 'question_id' => $q2->id, 'marks' => 5]
        );
        OnlineExamQuestionChoice::firstOrCreate(
            ['online_exam_id' => $oe2->id, 'question_id' => $q3->id],
            ['online_exam_id' => $oe2->id, 'question_id' => $q3->id, 'marks' => 5]
        );
        OnlineExamQuestionChoice::firstOrCreate(
            ['online_exam_id' => $oe2->id, 'question_id' => $q4->id],
            ['online_exam_id' => $oe2->id, 'question_id' => $q4->id, 'marks' => 5]
        );

        // Online Exam Question Options
        $opt1a = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q1->id, 'option' => '3'], ['question_id' => $q1->id, 'option' => '3']);
        $opt1b = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q1->id, 'option' => '4'], ['question_id' => $q1->id, 'option' => '4']);
        $opt1c = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q1->id, 'option' => '5'], ['question_id' => $q1->id, 'option' => '5']);
        $opt1d = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q1->id, 'option' => '6'], ['question_id' => $q1->id, 'option' => '6']);

        $opt2a = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q2->id, 'option' => '2'], ['question_id' => $q2->id, 'option' => '2']);
        $opt2b = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q2->id, 'option' => '4'], ['question_id' => $q2->id, 'option' => '4']);
        $opt2c = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q2->id, 'option' => '8'], ['question_id' => $q2->id, 'option' => '8']);
        $opt2d = OnlineExamQuestionOption::firstOrCreate(['question_id' => $q2->id, 'option' => '16'], ['question_id' => $q2->id, 'option' => '16']);

        // Online Exam Question Answers
        OnlineExamQuestionAnswer::firstOrCreate(
            ['question_id' => $q1->id],
            ['question_id' => $q1->id, 'answer' => $opt1b->id]
        );
        OnlineExamQuestionAnswer::firstOrCreate(
            ['question_id' => $q2->id],
            ['question_id' => $q2->id, 'answer' => $opt2b->id]
        );

        // Student Online Exam Status
        StudentOnlineExamStatus::firstOrCreate(
            ['student_id' => $this->studentIds[0], 'online_exam_id' => $oe1->id],
            ['student_id' => $this->studentIds[0], 'online_exam_id' => $oe1->id, 'status' => 2]
        );
        StudentOnlineExamStatus::firstOrCreate(
            ['student_id' => $this->studentIds[1], 'online_exam_id' => $oe1->id],
            ['student_id' => $this->studentIds[1], 'online_exam_id' => $oe1->id, 'status' => 1]
        );

        // Online Exam Student Answers
        OnlineExamStudentAnswer::firstOrCreate(
            ['student_id' => $this->studentIds[0], 'online_exam_id' => $oe1->id, 'question_id' => $q1->id],
            [
                'student_id' => $this->studentIds[0],
                'online_exam_id' => $oe1->id,
                'question_id' => $q1->id,
                'option_id' => $opt1b->id,
                'submitted_date' => now()->toDateString(),
            ]
        );
        OnlineExamStudentAnswer::firstOrCreate(
            ['student_id' => $this->studentIds[0], 'online_exam_id' => $oe1->id, 'question_id' => $q2->id],
            [
                'student_id' => $this->studentIds[0],
                'online_exam_id' => $oe1->id,
                'question_id' => $q2->id,
                'option_id' => $opt2b->id,
                'submitted_date' => now()->toDateString(),
            ]
        );

        $this->command->info('Online exams data seeded.');
    }

    private function seedAttendance(): void
    {
        $this->command->info('Seeding attendance data...');

        $allStudents = Students::all();
        foreach ($allStudents as $student) {
            Attendance::firstOrCreate(
                ['student_id' => $student->id, 'class_section_id' => $student->class_section_id, 'date' => now()->toDateString(), 'session_year_id' => $this->sessionYearId],
                ['class_section_id' => $student->class_section_id, 'student_id' => $student->id, 'session_year_id' => $this->sessionYearId, 'type' => 1, 'date' => now()->toDateString(), 'remark' => 'On time']
            );
            Attendance::firstOrCreate(
                ['student_id' => $student->id, 'class_section_id' => $student->class_section_id, 'date' => now()->subDay()->toDateString(), 'session_year_id' => $this->sessionYearId],
                ['class_section_id' => $student->class_section_id, 'student_id' => $student->id, 'session_year_id' => $this->sessionYearId, 'type' => 1, 'date' => now()->subDay()->toDateString(), 'remark' => 'On time']
            );
        }

        $this->command->info('Attendance data seeded.');
    }

    private function seedTimetable(): void
    {
        $this->command->info('Seeding timetable data...');

        $allClassSections = ClassSection::all();
        foreach ($allClassSections as $cs) {
            $subTeachers = SubjectTeacher::where('class_section_id', $cs->id)->get();
            foreach ($subTeachers as $idx => $st) {
                Timetable::firstOrCreate(
                    ['subject_teacher_id' => $st->id, 'day' => 1 + $idx, 'class_section_id' => $cs->id],
                    [
                        'subject_teacher_id' => $st->id,
                        'class_section_id' => $cs->id,
                        'start_time' => '10:00:00',
                        'end_time' => '10:45:00',
                        'day' => 1 + $idx,
                        'day_name' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'][$idx % 5],
                        'note' => 'Regular class',
                    ]
                );
            }
        }

        $this->command->info('Timetable data seeded.');
    }

    private function seedCommunication(): void
    {
        $this->command->info('Seeding communication data...');

        // Announcements
        Announcement::firstOrCreate(
            ['title' => 'Parent-Teacher Meeting', 'session_year_id' => $this->sessionYearId],
            [
                'title' => 'Parent-Teacher Meeting',
                'description' => 'PTM scheduled for next Saturday at 10 AM.',
                'table_type' => 'App\\Models\\User',
                'table_id' => $this->teacherIds[0],
                'session_year_id' => $this->sessionYearId,
            ]
        );
        Announcement::firstOrCreate(
            ['title' => 'Exam Schedule Released', 'session_year_id' => $this->sessionYearId],
            [
                'title' => 'Exam Schedule Released',
                'description' => 'First term exam schedule has been published.',
                'table_type' => 'App\\Models\\User',
                'table_id' => $this->teacherIds[1],
                'session_year_id' => $this->sessionYearId,
            ]
        );

        // Notifications
        $notif1 = Notification::firstOrCreate(
            ['title' => 'Assignment Due'],
            [
                'title' => 'Assignment Due',
                'type' => 'Assignment',
                'message' => 'Your algebra assignment is due tomorrow.',
                'send_to' => 3,
                'date' => now(),
            ]
        );
        $notif2 = Notification::firstOrCreate(
            ['title' => 'Holiday Notice'],
            [
                'title' => 'Holiday Notice',
                'type' => 'Holiday',
                'message' => 'School will remain closed for Dashain.',
                'send_to' => 3,
                'date' => now(),
            ]
        );

        // User Notifications
        $studentUser1 = Students::find($this->studentIds[0])->user_id ?? null;
        $studentUser2 = Students::find($this->studentIds[1])->user_id ?? null;
        if ($studentUser1) {
            UserNotification::firstOrCreate(
                ['notification_id' => $notif1->id, 'user_id' => $studentUser1],
                ['notification_id' => $notif1->id, 'user_id' => $studentUser1]
            );
            UserNotification::firstOrCreate(
                ['notification_id' => $notif2->id, 'user_id' => $studentUser1],
                ['notification_id' => $notif2->id, 'user_id' => $studentUser1]
            );
        }
        if ($studentUser2) {
            UserNotification::firstOrCreate(
                ['notification_id' => $notif1->id, 'user_id' => $studentUser2],
                ['notification_id' => $notif1->id, 'user_id' => $studentUser2]
            );
        }

        $this->command->info('Communication data seeded.');
    }

    private function seedLeave(): void
    {
        $this->command->info('Seeding leave data...');

        $leaveMaster = LeaveMaster::where('session_year_id', $this->sessionYearId)->first();
        $leaveMasterId = $leaveMaster->id ?? 1;

        $studentUser1 = Students::find($this->studentIds[0])->user_id ?? null;
        $studentUser2 = Students::find($this->studentIds[1])->user_id ?? null;
        $teacherUser1 = Teacher::find($this->teacherIds[0])->user_id ?? null;

        if ($studentUser1) {
            $leave1 = Leave::firstOrCreate(
                ['user_id' => $studentUser1, 'from_date' => '2024-06-01', 'to_date' => '2024-06-02', 'session_year_id' => $this->sessionYearId],
                [
                    'user_id' => $studentUser1,
                    'leave_master_id' => $leaveMasterId,
                    'reason' => 'Family function',
                    'from_date' => '2024-06-01',
                    'to_date' => '2024-06-02',
                    'status' => 'Approved',
                    'session_year_id' => $this->sessionYearId,
                ]
            );
            LeaveDetail::firstOrCreate(
                ['leave_id' => $leave1->id, 'date' => '2024-06-01'],
                ['leave_id' => $leave1->id, 'date' => '2024-06-01', 'type' => 'Full Day']
            );
            LeaveDetail::firstOrCreate(
                ['leave_id' => $leave1->id, 'date' => '2024-06-02'],
                ['leave_id' => $leave1->id, 'date' => '2024-06-02', 'type' => 'Full Day']
            );
        }

        if ($teacherUser1) {
            $leave2 = Leave::firstOrCreate(
                ['user_id' => $teacherUser1, 'from_date' => '2024-07-01', 'to_date' => '2024-07-01', 'session_year_id' => $this->sessionYearId],
                [
                    'user_id' => $teacherUser1,
                    'leave_master_id' => $leaveMasterId,
                    'reason' => 'Medical checkup',
                    'from_date' => '2024-07-01',
                    'to_date' => '2024-07-01',
                    'status' => 'Approved',
                    'session_year_id' => $this->sessionYearId,
                ]
            );
            LeaveDetail::firstOrCreate(
                ['leave_id' => $leave2->id, 'date' => '2024-07-01'],
                ['leave_id' => $leave2->id, 'date' => '2024-07-01', 'type' => 'Full Day']
            );
        }

        $this->command->info('Leave data seeded.');
    }

    private function seedOther(): void
    {
        $this->command->info('Seeding other data...');

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
            ['question' => 'How to pay fees online?', 'answer' => 'You can pay fees through the student/parent app using eSewa or Stripe.', 'status' => 1]
        );

        // QR Attendance Logs
        if (Schema::hasTable('qr_attendance_logs')) {
            DB::table('qr_attendance_logs')->updateOrInsert(
                ['student_id' => $this->studentIds[0], 'attendance_date' => now()->toDateString()],
                [
                    'student_id' => $this->studentIds[0],
                    'scanned_by' => User::whereHas('roles', fn ($q) => $q->where('name', 'Super Admin'))->first()?->id ?? 1,
                    'class_section_id' => $this->classSectionIds[0],
                    'session_year_id' => $this->sessionYearId,
                    'attendance_date' => now()->toDateString(),
                    'status' => 'present',
                    'ip_address' => '127.0.0.1',
                ]
            );
            DB::table('qr_attendance_logs')->updateOrInsert(
                ['student_id' => $this->studentIds[1], 'attendance_date' => now()->toDateString()],
                [
                    'student_id' => $this->studentIds[1],
                    'scanned_by' => User::whereHas('roles', fn ($q) => $q->where('name', 'Super Admin'))->first()?->id ?? 1,
                    'class_section_id' => $this->classSectionIds[1],
                    'session_year_id' => $this->sessionYearId,
                    'attendance_date' => now()->toDateString(),
                    'status' => 'present',
                    'ip_address' => '127.0.0.1',
                ]
            );
        }

        // Contact Us
        if (Schema::hasTable('contact_us')) {
            DB::table('contact_us')->updateOrInsert(
                ['email' => 'parent1@example.com', 'date' => now()->toDateString()],
                [
                    'first_name' => 'Parent',
                    'last_name' => 'One',
                    'email' => 'parent1@example.com',
                    'phone' => '9800000003',
                    'date' => now()->toDateString(),
                    'message' => 'I need information about admission.',
                ]
            );
            DB::table('contact_us')->updateOrInsert(
                ['email' => 'parent2@example.com', 'date' => now()->toDateString()],
                [
                    'first_name' => 'Parent',
                    'last_name' => 'Two',
                    'email' => 'parent2@example.com',
                    'phone' => '9800000004',
                    'date' => now()->toDateString(),
                    'message' => 'When is the next PTM?',
                ]
            );
        }

        $this->command->info('Other data seeded.');
    }

    private function seedWebSettings(): void
    {
        $this->command->info('Seeding web settings...');

        WebSetting::firstOrCreate(
            ['name' => 'School Name'],
            ['name' => 'School Name', 'tag' => 'header', 'heading' => 'Demo School', 'content' => 'A Place for Learning', 'image' => 'logo.svg', 'status' => 1]
        );
        WebSetting::firstOrCreate(
            ['name' => 'Contact Email'],
            ['name' => 'Contact Email', 'tag' => 'footer', 'heading' => 'Email Us', 'content' => 'info@school.com', 'image' => '', 'status' => 1]
        );
        WebSetting::firstOrCreate(
            ['name' => 'About Us'],
            ['name' => 'About Us', 'tag' => 'about', 'heading' => 'About Our School', 'content' => 'We provide quality education since 2000.', 'image' => 'logo.svg', 'status' => 1]
        );

        $this->command->info('Web settings seeded.');
    }
}
