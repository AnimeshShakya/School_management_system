<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedDefaultGrades extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only insert grades if none exist (idempotent)
        if (DB::table('grades')->count() === 0) {
            DB::table('grades')->insert([
                [
                    'starting_range' => 90,
                    'ending_range' => 100,
                    'grade' => 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'starting_range' => 80,
                    'ending_range' => 89,
                    'grade' => 'B',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'starting_range' => 70,
                    'ending_range' => 79,
                    'grade' => 'C',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'starting_range' => 60,
                    'ending_range' => 69,
                    'grade' => 'D',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'starting_range' => 0,
                    'ending_range' => 59,
                    'grade' => 'F',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $this->command->info('Default grades seeded successfully (A, B, C, D, F)');
        } else {
            $this->command->info('Grades already exist; skipping seed.');
        }
    }
}
