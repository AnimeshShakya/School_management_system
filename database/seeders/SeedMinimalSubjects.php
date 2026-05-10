<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedMinimalSubjects extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('subjects')->count() == 0) {
            DB::table('subjects')->insert([
                ['name' => 'Mathematics', 'code' => 'MATH', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'English', 'code' => 'ENG', 'created_at' => now(), 'updated_at' => now()],
            ]);
            echo "Inserted default subjects\n";
        } else {
            echo "Subjects table already has data, skipping.\n";
        }
    }
}
