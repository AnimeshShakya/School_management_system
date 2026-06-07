<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DummyDataSeeder::class,
            ClassSeeder::class,
            InstallationSeeder::class,
            TeacherSeeder::class,
            DemoUsersSeeder::class,
            AddSuperAdminSeeder::class,
            MultiSchoolDataSeeder::class,
        ]);
    }
}
