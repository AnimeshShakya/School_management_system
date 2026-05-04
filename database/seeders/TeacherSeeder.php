<?php

namespace Database\Seeders;

use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = [
            [
                'email' => 'teacher.english@gmail.com',
                'first_name' => 'Rahul',
                'last_name' => 'Sharma',
                'gender' => 'Male',
                'mobile' => '9876500001',
                'image' => 'teachers/user.png',
                'qualification' => 'M.A. English',
                'class_section_ids' => [1, 3],
                'subject_ids' => [41, 42],
            ],
            [
                'email' => 'teacher.science@gmail.com',
                'first_name' => 'Priya',
                'last_name' => 'Patel',
                'gender' => 'Female',
                'mobile' => '9876500002',
                'image' => 'teachers/user.png',
                'qualification' => 'M.Sc. Mathematics',
                'class_section_ids' => [2, 4],
                'subject_ids' => [43, 44],
            ],
            [
                'email' => 'teacher.social@gmail.com',
                'first_name' => 'Amit',
                'last_name' => 'Verma',
                'gender' => 'Male',
                'mobile' => '9876500003',
                'image' => 'teachers/user.png',
                'qualification' => 'M.A. Social Science',
                'class_section_ids' => [5],
                'subject_ids' => [45, 46, 47, 48, 49],
            ],
        ];

        $teacherRole = Role::query()->where('name', 'Teacher')->first();

        foreach ($teachers as $teacherData) {
            $user = User::withTrashed()->firstOrNew(['email' => $teacherData['email']]);
            $user->fill([
                'first_name' => $teacherData['first_name'],
                'last_name' => $teacherData['last_name'],
                'email' => $teacherData['email'],
                'gender' => $teacherData['gender'],
                'mobile' => $teacherData['mobile'],
                'image' => $teacherData['image'],
                'password' => Hash::make('teacher123'),
                'current_address' => 'Mumbai',
                'permanent_address' => 'Mumbai',
            ]);
            $user->save();

            if (method_exists($user, 'trashed') && $user->trashed()) {
                $user->restore();
            }

            if ($teacherRole) {
                $user->syncRoles([$teacherRole->name]);
            }

            $teacher = Teacher::withTrashed()->firstOrNew(['user_id' => $user->id]);
            $teacher->qualification = $teacherData['qualification'];
            $teacher->save();

            if (method_exists($teacher, 'trashed') && $teacher->trashed()) {
                $teacher->restore();
            }

            foreach ($teacherData['class_section_ids'] as $classSectionId) {
                ClassTeacher::query()->updateOrCreate([
                    'class_section_id' => $classSectionId,
                    'class_teacher_id' => $teacher->id,
                ]);

                foreach ($teacherData['subject_ids'] as $subjectId) {
                    SubjectTeacher::query()->updateOrCreate([
                        'class_section_id' => $classSectionId,
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacher->id,
                    ]);
                }
            }
        }
    }
}
