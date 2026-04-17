<?php

namespace Database\Seeders;

use App\Models\SessionYear;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

        $user->fill([
            'first_name' => 'super',
            'last_name' => 'admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin'),
            'gender' => 'Male',
            'image' => 'logo.svg',
            'mobile' => ""
        ]);
        $user->save();

        if (method_exists($user, 'trashed') && $user->trashed()) {
            $user->restore();
        }

        $user->syncRoles([$super_admin_role->name]);

        SessionYear::updateOrCreate(['id' => 1],[
            'name' => '2022-23',
            'default' => 1,
            'start_date' => '2022-06-01',
            'end_date' => '2023-04-30',
        ]);

        // add session year in setting table
        Settings::updateOrCreate(
            ['type' => 'session_year'],
            ['message' => 1]
        );
    }
}
