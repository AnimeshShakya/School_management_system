<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedMinimalClassSection extends Seeder
{
    public function run()
    {
        // Create a medium if not exists
        $mediumId = DB::table('mediums')->value('id');
        if (!$mediumId) {
            $mediumId = DB::table('mediums')->insertGetId([
                'name' => 'Default Medium',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created medium id: '.$mediumId);
        }

        // Create a class if not exists
        $classId = DB::table('classes')->value('id');
        if (!$classId) {
            $classId = DB::table('classes')->insertGetId([
                'name' => 'Class 1',
                'medium_id' => $mediumId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created class id: '.$classId);
        }

        // Create a section if not exists
        $sectionId = DB::table('sections')->value('id');
        if (!$sectionId) {
            $sectionId = DB::table('sections')->insertGetId([
                'name' => 'A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created section id: '.$sectionId);
        }

        // Create a class_section if not exists
        $exists = DB::table('class_sections')->where('class_id', $classId)->where('section_id', $sectionId)->exists();
        if (!$exists) {
            DB::table('class_sections')->insert([
                'class_id' => $classId,
                'section_id' => $sectionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Created class_section for class '.$classId.' and section '.$sectionId);
        }
    }
}
