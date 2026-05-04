<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\Mediums;
use App\Models\Parents;
use App\Models\Section;
use App\Models\SessionYear;
use App\Models\Settings;
use App\Models\Students;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $medium = [
            ['id' => 1, 'name' => 'Hindi'],
            ['id' => 2, 'name' => 'English'],
            ['id' => 3, 'name' => 'Gujarati'],
        ];
        Mediums::upsert($medium, ['id'], ['name']);

        $sections = [
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
            ['id' => 3, 'name' => 'C'],
        ];
        Section::upsert($sections, ['id'], ['name']);

        $classes = [
            ['id' => 1, 'name' => '9', 'medium_id' => 2],
            ['id' => 2, 'name' => '10', 'medium_id' => 2],
        ];
        ClassSchool::upsert($classes, ['id'], ['name', 'medium_id']);

        $class_sections = [
            ['id' => 1, 'class_id' => 1, 'section_id' => 1],
            ['id' => 2, 'class_id' => 1, 'section_id' => 2],
            ['id' => 3, 'class_id' => 2, 'section_id' => 1],
            ['id' => 4, 'class_id' => 2, 'section_id' => 2],
            ['id' => 5, 'class_id' => 2, 'section_id' => 3],
        ];
        ClassSection::upsert($class_sections, ['id'], ['class_id', 'section_id']);

        $subjects = [
            // 1-4
            ['id' => 1, 'name' => 'English', 'code' => 'ENG', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 2, 'name' => 'Nepali', 'code' => 'NEP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 3, 'name' => 'Maths', 'code' => 'MTH', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 4, 'name' => 'Science', 'code' => 'SCI', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 5, 'name' => 'Serofero', 'code' => 'SRO', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 6, 'name' => 'Computer', 'code' => 'CMP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 7, 'name' => 'Moral', 'code' => 'MRL', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 8, 'name' => 'GK', 'code' => 'GK', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 9, 'name' => 'Dance', 'code' => 'DNCE', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '1-4', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 10, 'name' => 'Music', 'code' => 'MSC', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '1-4', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 11, 'name' => 'Art', 'code' => 'ART', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '1-4', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 12, 'name' => 'Sports', 'code' => 'SPT', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '1-4', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 13, 'name' => 'Taekwondo', 'code' => 'TKD', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '1-4', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 14, 'name' => 'Library', 'code' => 'LIB', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '1-4', 'selection_type' => 'ECA', 'eca_type' => 'single'],

            // 5-7
            ['id' => 15, 'name' => 'English', 'code' => 'ENG', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 16, 'name' => 'Nepali', 'code' => 'NEP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 17, 'name' => 'Maths', 'code' => 'MTH', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 18, 'name' => 'Science', 'code' => 'SCI', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 19, 'name' => 'Social', 'code' => 'SOC', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 20, 'name' => 'H&PE', 'code' => 'HPE', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 21, 'name' => 'Nepal Bhasa', 'code' => 'NBH', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 22, 'name' => 'Computer', 'code' => 'CMP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 23, 'name' => 'Dance', 'code' => 'DNCE', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 24, 'name' => 'Music', 'code' => 'MSC', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 25, 'name' => 'Art', 'code' => 'ART', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 26, 'name' => 'Sports', 'code' => 'SPT', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 27, 'name' => 'Taekwondo', 'code' => 'TKD', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '5-7', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 28, 'name' => 'Library', 'code' => 'LIB', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '5-7', 'selection_type' => 'ECA', 'eca_type' => 'single'],

            // 8
            ['id' => 29, 'name' => 'English', 'code' => 'ENG', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 30, 'name' => 'Nepali', 'code' => 'NEP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 31, 'name' => 'Maths', 'code' => 'MTH', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 32, 'name' => 'Science', 'code' => 'SCI', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 33, 'name' => 'Social', 'code' => 'SOC', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 34, 'name' => 'H&PE', 'code' => 'HPE', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 35, 'name' => 'Opt. Math', 'code' => 'OMT', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 36, 'name' => 'Nepal Bhasa', 'code' => 'NBH', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 37, 'name' => 'Computer', 'code' => 'CMP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '8', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 38, 'name' => 'Dance/Music', 'code' => 'DNCE', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '8', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 39, 'name' => 'Art', 'code' => 'ART', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '8', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 40, 'name' => 'Sports', 'code' => 'SPT', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '8', 'selection_type' => 'ECA', 'eca_type' => 'combined'],

            // 9-10
            ['id' => 41, 'name' => 'English', 'code' => 'ENG', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '9-10', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 42, 'name' => 'Nepali', 'code' => 'NEP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '9-10', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 43, 'name' => 'Maths', 'code' => 'MTH', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '9-10', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 44, 'name' => 'Science', 'code' => 'SCI', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '9-10', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 45, 'name' => 'Social', 'code' => 'SOC', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '9-10', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 46, 'name' => 'Opt. Math', 'code' => 'OMT', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Theory', 'class_level' => '9-10', 'selection_type' => 'compulsory', 'eca_type' => null],
            ['id' => 47, 'name' => 'Computer/Account', 'code' => 'CMP', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '9-10', 'selection_type' => 'optional', 'eca_type' => 'single'],
            ['id' => 48, 'name' => 'Dance/Music/Art', 'code' => 'DMA', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '9-10', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
            ['id' => 49, 'name' => 'Sports', 'code' => 'SPT', 'bg_color' => '#5031f7', 'image' => 'subject.png', 'medium_id' => 1, 'type' => 'Practical', 'class_level' => '9-10', 'selection_type' => 'ECA', 'eca_type' => 'combined'],
        ];
        Subject::upsert($subjects, ['id'], ['name', 'code', 'bg_color', 'image', 'medium_id', 'type', 'class_level', 'selection_type', 'eca_type']);

        $session_years = [
            ['id' => 1, 'name' => '2022', 'default' => 1, 'start_date' => Carbon::create('2022', '06', '01'), 'end_date' => Carbon::create('2023', '04', '30')],
            ['id' => 2, 'name' => '2023', 'default' => 0, 'start_date' => Carbon::create('2023', '06', '01'), 'end_date' => Carbon::create('2024', '04', '30')],
            ['id' => 3, 'name' => '2024', 'default' => 0, 'start_date' => Carbon::create('2024', '06', '01'), 'end_date' => Carbon::create('2025', '04', '30')],
            ['id' => 4, 'name' => '2025', 'default' => 0, 'start_date' => Carbon::create('2025', '06', '01'), 'end_date' => Carbon::create('2026', '04', '30')],
        ];
        SessionYear::upsert($session_years, ['id'], ['name', 'default', 'start_date', 'end_date']);

        $session_year_settings = [
            ['id' => 9, 'type' => 'session_year', 'message' => 1],
        ];
        Settings::upsert($session_year_settings, ['id'], ['type', 'message']);

        $student_categories = [
            ['id' => 1, 'name' => 'SC', 'status' => 1],
            ['id' => 2, 'name' => 'ST', 'status' => 1],
            ['id' => 3, 'name' => 'OBC', 'status' => 1],
            ['id' => 4, 'name' => 'General', 'status' => 1],
        ];
        Category::upsert($student_categories, ['id'], ['name', 'status']);

        // Users
        $user = [
            [
                'id' => 2,
                'image' => 'parents/user.png',
                'password' => Hash::make('01011999'),
                'first_name' => 'Sachin',
                'last_name' => 'Tendulkar',
                'email' => 'father@gmail.com',
                'mobile' => 1234567890,
                'gender' => 'Male',
                'current_address' => 'Mumbai',
                'permanent_address' => 'Mumbai',
            ],
            [
                'id' => 3,
                'image' => 'parents/user.png',
                'password' => Hash::make('01011999'),
                'first_name' => 'Anjali',
                'last_name' => 'Tendulkar',
                'email' => 'mother@gmail.com',
                'mobile' => 1234567890,
                'gender' => 'Female',
                'current_address' => 'Mumbai',
                'permanent_address' => 'Mumbai',
            ],
            [
                'id' => 4,
                'image' => 'students/user.png',
                'password' => Hash::make('01011999'),
                'first_name' => 'Arjun',
                'last_name' => 'Tendulkar',
                'email' => 'student@gmail.com',
                'mobile' => 1234567890,
                'gender' => 'Male',
                'current_address' => 'Mumbai',
                'permanent_address' => 'Mumbai',
            ],
        ];

        User::upsert($user, ['id'], ['image', 'password', 'first_name', 'last_name', 'email', 'mobile', 'current_address', 'permanent_address']);

        // Parents
        $parent = [
            [
                'id' => 1,
                'user_id' => 2,
                'first_name' => 'Sachin',
                'last_name' => 'Tendulkar',
                'image' => 'parents/user.png',
                'occupation' => 'Cricketer',
                'email' => 'father@gmail.com',
                'mobile' => 1234567890,
                'gender' => 'Male',
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'first_name' => 'Anjali',
                'last_name' => 'Tendulkar',
                'image' => 'parents/user.png',
                'occupation' => 'Housewife',
                'email' => 'mother@gmail.com',
                'mobile' => 1234567890,
                'gender' => 'Female',

            ],
            [
                'id' => 2,
                'user_id' => 3,
                'first_name' => 'Ajit',
                'last_name' => 'Tendulkar',
                'image' => 'parents/user.png',
                'occupation' => 'Job',
                'email' => 'guardian@gmail.com',
                'mobile' => 1234567890,
                'gender' => 'Male',

            ],
        ];
        Parents::upsert($parent, ['id'], ['user_id', 'first_name', 'last_name', 'image', 'occupation', 'email', 'mobile', 'dob', 'gender']);

        // Student
        $student = [
            'id' => 1,
            'user_id' => 4,
            'class_section_id' => 3,
            'category_id' => 1,
            'admission_no' => 12345667,
            'roll_number' => 1,
            'caste' => 'Hindu',
            'religion' => 'Hindu',
            'blood_group' => 'B+',
            'height' => '5.5',
            'weight' => '59',
            'father_id' => 1,
            'mother_id' => 2,
            'guardian_id' => 3,
            'admission_date' => Carbon::create('2022', '04', '01'),
        ];
        Students::upsert($student, ['id'], ['user_id', 'class_section_id', 'category_id', 'admission_no', 'roll_number', 'caste', 'religion', 'admission_date', 'blood_group', 'height', 'weight', 'father_id', 'mother_id', 'guardian_id']);
    }
}
