<?php

namespace Database\Seeders;

use App\Models\ClassSchool;
use App\Models\ClassSubject;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ClassSchool::query()
            ->whereIn('id', [1, 2])
            ->pluck('id')
            ->all();

        if (empty($classes)) {
            return;
        }

        $compulsorySubjectIds = [41, 42, 43, 44, 45, 46];
        $electiveSubjectIds = [47, 48, 49];

        foreach ($classes as $classId) {
            foreach ($compulsorySubjectIds as $subjectId) {
                ClassSubject::query()->updateOrCreate(
                    [
                        'class_id' => $classId,
                        'semester_id' => null,
                        'subject_id' => $subjectId,
                        'type' => 'Compulsory',
                    ],
                    [
                        'elective_subject_group_id' => null,
                    ]
                );
            }

            foreach ($electiveSubjectIds as $subjectId) {
                ClassSubject::query()->updateOrCreate(
                    [
                        'class_id' => $classId,
                        'semester_id' => null,
                        'subject_id' => $subjectId,
                        'type' => 'Elective',
                    ],
                    [
                        'elective_subject_group_id' => null,
                    ]
                );
            }
        }
    }
}
