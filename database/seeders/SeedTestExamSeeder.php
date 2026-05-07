<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamClass;
use Illuminate\Support\Facades\DB;

class SeedTestExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            // Create or find a session year id 1
            $sessionYearId = 1;

            $exam = Exam::create([
                'name' => 'seed_test_exam_' . time(),
                'description' => 'Seeded test exam',
                'session_year_id' => $sessionYearId,
            ]);

            // Attach to class id 1 if it exists
            $classId = 1;
            ExamClass::create([
                'exam_id' => $exam->id,
                'class_id' => $classId,
            ]);

            DB::commit();
            $this->command->info('SeedTestExamSeeder: created exam id ' . $exam->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('SeedTestExamSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
