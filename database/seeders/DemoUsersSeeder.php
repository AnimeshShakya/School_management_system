<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClassSection;
use App\Models\Parents;
use App\Models\SessionYear;
use App\Models\StudentSessions;
use App\Models\Students;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DemoUsersSeeder extends Seeder
{
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

        $adminRole->syncPermissions(Permission::query()->pluck('name')->all());

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

        $teacherProfile = Teacher::firstOrNew(['user_id' => $teacherUser->id]);
        $teacherProfile->qualification = 'Graduate';
        $teacherProfile->save();

        $parentUser = $this->seedUser($parentRole, 'parent@gmail.com', [
            'first_name' => 'parent',
            'last_name' => 'user',
            'password' => Hash::make('parent123'),
            'gender' => 'Male',
            'image' => 'parents/user.png',
            'mobile' => '1234567890',
        ]);

        $parentProfile = Parents::updateOrCreate(
            ['user_id' => $parentUser->id],
            [
                'first_name' => 'parent',
                'last_name' => 'user',
                'image' => 'parents/user.png',
                'occupation' => 'Guardian',
                'email' => 'parent@gmail.com',
                'mobile' => '1234567890',
                'gender' => 'Male',
                'dynamic_fields' => '[]',
            ]
        );

        $studentUser = $this->seedUser($studentRole, 'student@gmail.com', [
            'first_name' => 'student',
            'last_name' => 'user',
            'password' => Hash::make('student123'),
            'gender' => 'Male',
            'image' => 'students/user.png',
            'mobile' => '1234567890',
        ]);

        $classSectionId = ClassSection::query()->value('id');
        $categoryId = Category::query()->value('id');
        $sessionYearId = SessionYear::query()->where('default', 1)->value('id')
            ?? SessionYear::query()->value('id');

        if ($classSectionId && $categoryId && $sessionYearId) {
            $studentProfile = Students::updateOrCreate(
                ['user_id' => $studentUser->id],
                [
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
                    'guardian_id' => $parentProfile->id,
                    'dynamic_fields' => '[]',
                    'is_new_admission' => 1,
                ]
            );

            StudentSessions::updateOrCreate(
                ['student_id' => $studentProfile->id, 'session_year_id' => $sessionYearId],
                [
                    'class_section_id' => $classSectionId,
                    'previous_session_year_id' => null,
                ]
            );
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
        $user->fill($attributes);
        $user->save();

        if (method_exists($user, 'trashed') && $user->trashed()) {
            $user->restore();
        }

        $user->syncRoles([$role->name]);

        return $user;
    }
}
