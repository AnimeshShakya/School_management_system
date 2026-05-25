<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SessionYear;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AddSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultSchoolId = null;
        if (Schema::hasTable('schools')) {
            $defaultSchool = School::firstOrCreate(
                ['name' => 'Default School'],
                [
                    'email' => 'school@example.com',
                    'phone' => '0000000000',
                    'address' => '123 School Street',
                    'status' => 1,
                ]
            );
            $defaultSchoolId = $defaultSchool->id;
        }

        // Add Super Admin User
        $super_admin_role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        $user = User::withTrashed()->where('email', 'superadmin@gmail.com')->first();

        if (! $user) {
            $user = new User;
        }

        $attributes = [
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin'),
            'image' => 'logo.svg',
            'mobile' => '',
            'status' => 1,
            'name' => 'super admin',
            'first_name' => 'super',
            'last_name' => 'admin',
            'gender' => 'Male',
            'school_id' => null,
            'created_by' => null,
        ];

        $userColumns = Schema::getColumnListing('users');
        $attributes = array_intersect_key($attributes, array_flip($userColumns));

        $user->forceFill($attributes);
        $user->save();

        if (method_exists($user, 'trashed') && $user->trashed()) {
            $user->restore();
        }

        $user->syncRoles([$super_admin_role->name]);

        $sessionYearColumns = Schema::getColumnListing('session_years');
        $sessionYearData = [
            'name' => '2022-23',
            'default' => 1,
            'start_date' => '2022-06-01',
            'end_date' => '2023-04-30',
        ];
        $sessionYearData = array_intersect_key($sessionYearData, array_flip($sessionYearColumns));

        SessionYear::updateOrCreate(['id' => 1], $sessionYearData);

        // add session year in setting table
        Settings::updateOrCreate(
            ['type' => 'session_year'],
            ['message' => 1]
        );
    }
}
