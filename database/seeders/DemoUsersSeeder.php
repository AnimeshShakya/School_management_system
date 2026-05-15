<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClassSection;
use App\Models\Parents;
use App\Models\SessionYear;
use App\Models\Students;
use App\Models\StudentSessions;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DemoUsersSeeder extends Seeder
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $tableColumnsCache = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = $this->getRole('Super Admin');
        $adminRole = $this->getRole('Admin');
        $teacherRole = $this->getRole('Teacher');
        $parentRole = $this->getRole('Parent');
        $studentRole = $this->getRole('Student');
        $attendeeRole = $this->getRole('Attendee Teacher');

        $allPermissionNames = Permission::query()->pluck('name')->all();

        // Super User: all permissions in the project.
        $this->syncRolePermissions($superAdminRole, $allPermissionNames);

        // Admin User: all permissions except web settings, system settings, system update and slider.
        $webSettingsPermissions = [
            'content-create',
            'content-list',
            'content-edit',
            'program-create',
            'program-list',
            'program-edit',
            'program-delete',
            'media-create',
            'media-list',
            'media-edit',
            'media-delete',
            'faq-create',
            'faq-list',
            'faq-edit',
            'faq-delete',
            'privacy-policy',
            'contact-us',
            'about-us',
            'terms-condition',
        ];

        $systemSettingsPermissions = [
            'setting-create',
            'fcm-setting-create',
            'email-setting-create',
        ];

        $systemUpdatePermissions = [
            'update-admin-profile',
        ];

        $sliderPermissions = [
            'slider-list',
            'slider-create',
            'slider-edit',
            'slider-delete',
        ];

        $adminExcludedPermissions = array_values(array_unique(array_merge(
            $webSettingsPermissions,
            $systemSettingsPermissions,
            $systemUpdatePermissions,
            $sliderPermissions
        )));

        $adminPermissions = array_values(array_diff($allPermissionNames, $adminExcludedPermissions));
        $this->syncRolePermissions($adminRole, $adminPermissions);

        // Teacher User: academics, students, parents, leave, timetable(view), attendance(self),
        // student assignment, exam, holiday list, session year, announcement.
        $teacherPermissions = [
            'medium-list',
            'section-list',
            'class-list',
            'subject-list',
            'subject-teacher-list',
            'lesson-list',
            'lesson-create',
            'lesson-edit',
            'lesson-delete',
            'topic-list',
            'topic-create',
            'topic-edit',
            'topic-delete',
            'semester-list',
            'stream-list',
            'shift-list',
            'student-list',
            'parents-list',
            'leave-create',
            'leave-edit',
            'leave-list',
            'leave-delete',
            'timetable-list',
            'class-timetable',
            'teacher-timetable',
            'class-attendance',
            'attendance-create',
            'attendance-edit',
            'student-assignment',
            'assignment-create',
            'assignment-list',
            'assignment-edit',
            'assignment-delete',
            'assignment-submission',
            'exam-list',
            'exam-result',
            'holiday-list',
            'session-year-list',
            'announcement-list',
            'announcement-create',
            'announcement-edit',
            'announcement-delete',
        ];
        $this->syncRolePermissions($teacherRole, $teacherPermissions);

        // Student & Parent share the same login credentials — merged permission set.
        $studentParentPermissions = [
            'student-list',
            'student-assignment',
            'assignment-submission',
            'timetable-list',
            'class-timetable',
            'class-attendance',
            'exam-list',
            'exam-result',
            'fees-paid',
            'announcement-list',
            'event-list',
            'session-year-list',
        ];
        $this->syncRolePermissions($studentRole, $studentParentPermissions);
        $this->syncRolePermissions($parentRole, $studentParentPermissions);

        // Attendee Teacher: can only scan QR codes and view attendance records.
        $attendeePermissions = [
            'qr-attendance-scan',
            'attendance-list',
            'attendance-create',
        ];
        $this->syncRolePermissions($attendeeRole, $attendeePermissions);

        $this->seedUser($superAdminRole, 'superadmin@gmail.com', [
            'first_name' => 'super',
            'last_name' => 'admin',
            'password' => Hash::make('superadmin'),
            'gender' => 'Male',
            'image' => 'logo.svg',
            'mobile' => '',
        ]);

        $this->seedUser($adminRole, 'admin@gmail.com', [
            'first_name' => 'school',
            'last_name' => 'admin',
            'password' => Hash::make('admin123'),
            'gender' => 'Male',
            'image' => 'logo.svg',
            'mobile' => '',
        ]);

        $teacherUser = $this->seedUser($teacherRole, 'teacher@gmail.com', [
            'first_name' => 'teacher',
            'last_name' => 'user',
            'password' => Hash::make('teacher123'),
            'gender' => 'Male',
            'image' => 'logo.svg',
            'mobile' => '',
        ]);

        if (Schema::hasTable('teachers')) {
            $teacherProfile = Teacher::firstOrNew(['user_id' => $teacherUser->id]);
            $teacherProfile->qualification = 'Graduate';
            $teacherProfile->save();
        }

        $this->seedUser($attendeeRole, 'attendee@gmail.com', [
            'first_name' => 'attendee',
            'last_name' => 'user',
            'password' => Hash::make('attendee123'),
            'gender' => 'Male',
            'image' => 'logo.svg',
            'mobile' => '',
        ]);

        $parentUser = $this->seedUser($parentRole, 'parent@gmail.com', [
            'first_name' => 'parent',
            'last_name' => 'user',
            'password' => Hash::make('parent123'),
            'gender' => 'Male',
            'image' => 'parents/user.png',
            'mobile' => '1234567890',
        ]);

        $parentProfile = null;
        if (Schema::hasTable('parents')) {
            $parentProfile = Parents::updateOrCreate(
                ['user_id' => $parentUser->id],
                $this->filterTableData('parents', [
                    'first_name' => 'parent',
                    'last_name' => 'user',
                    'image' => 'parents/user.png',
                    'occupation' => 'Guardian',
                    'email' => 'parent@gmail.com',
                    'mobile' => '1234567890',
                    'gender' => 'Male',
                    'dynamic_fields' => '[]',
                ])
            );
        }

        $studentUser = $this->seedUser($studentRole, 'student@gmail.com', [
            'first_name' => 'student',
            'last_name' => 'user',
            'password' => Hash::make('student123'),
            'gender' => 'Male',
            'image' => 'students/user.png',
            'mobile' => '1234567890',
        ]);

        $classSectionId = (Schema::hasTable('class_sections') && Schema::hasColumn('class_sections', 'id'))
            ? ClassSection::query()->value('id')
            : null;
        $categoryId = (Schema::hasTable('categories') && Schema::hasColumn('categories', 'id'))
            ? Category::query()->value('id')
            : null;
        $sessionYearId = Schema::hasTable('session_years')
            ? (SessionYear::query()->where('default', 1)->value('id') ?? SessionYear::query()->value('id'))
            : null;

        if ($classSectionId && $categoryId && $sessionYearId && Schema::hasTable('students')) {
            $studentData = [
                'class_section_id' => $classSectionId,
                'category_id' => $categoryId,
                'admission_no' => sprintf('STD-%04d', $studentUser->id),
                'roll_number' => (int) Students::max('roll_number') + 1,
                'caste' => 'General',
                'religion' => 'None',
                'admission_date' => Carbon::now(),
                'blood_group' => 'B+',
                'height' => '150',
                'weight' => '45',
                'father_id' => null,
                'mother_id' => null,
                'guardian_id' => $parentProfile?->id,
                'parent_id' => $parentProfile?->id,
                'dynamic_fields' => '[]',
                'is_new_admission' => 1,
            ];

            $studentProfile = Students::updateOrCreate(
                ['user_id' => $studentUser->id],
                $this->filterTableData('students', $studentData)
            );

            if (Schema::hasTable('student_sessions')) {
                StudentSessions::updateOrCreate(
                    ['student_id' => $studentProfile->id, 'session_year_id' => $sessionYearId],
                    $this->filterTableData('student_sessions', [
                        'class_section_id' => $classSectionId,
                        'previous_session_year_id' => null,
                    ])
                );
            }
        }
    }

    private function getRole(string $name): Role
    {
        return Role::firstOrCreate([
            'name' => $name,
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);
    }

    private function seedUser(Role $role, string $email, array $attributes): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        $columns = Schema::getColumnListing('users');

        if (in_array('name', $columns, true) && ! array_key_exists('name', $attributes)) {
            $first = $attributes['first_name'] ?? '';
            $last = $attributes['last_name'] ?? '';
            $attributes['name'] = trim($first.' '.$last);
        }

        $attributes = array_intersect_key($attributes, array_flip($columns));

        $user->forceFill($attributes);
        $user->save();

        if (method_exists($user, 'trashed') && $user->trashed()) {
            $user->restore();
        }

        $user->syncRoles([$role->name]);

        return $user;
    }

    private function filterTableData(string $table, array $data): array
    {
        $columns = $this->tableColumnsCache[$table] ??= Schema::getColumnListing($table);

        return array_intersect_key($data, array_flip($columns));
    }

    private function syncRolePermissions(Role $role, array $permissions): void
    {
        $availablePermissions = Permission::query()
            ->whereIn('name', $permissions)
            ->pluck('name')
            ->all();

        $role->syncPermissions($availablePermissions);
    }
}
