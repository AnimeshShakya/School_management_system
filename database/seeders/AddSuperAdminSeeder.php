<?php

namespace Database\Seeders;

use App\Models\SessionYear;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AddSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //Add Super Admin User
        $super_admin_role = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        $user = User::withTrashed()->where('email', 'superadmin@gmail.com')->first();

        if (!$user) {
            $user = new User();
        }

        $attributes = [
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin'),
            'image' => 'logo.svg',
            'mobile' => '',
            'status' => 1,
            // Compatible with both user schemas
            'name' => 'super admin',
            'first_name' => 'super',
            'last_name' => 'admin',
            'gender' => 'Male',
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
