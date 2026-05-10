<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AttendeeTeacherRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the qr-attendance-scan permission exists
        $qrScanPermission = Permission::firstOrCreate(
            ['name' => 'qr-attendance-scan', 'guard_name' => 'web']
        );

        $attendanceListPermission = Permission::firstOrCreate(
            ['name' => 'attendance-list', 'guard_name' => 'web']
        );

        $attendanceCreatePermission = Permission::firstOrCreate(
            ['name' => 'attendance-create', 'guard_name' => 'web']
        );

        // Create the Attendee Teacher role if it doesn't already exist
        $role = Role::firstOrCreate(
            ['name' => 'Attendee Teacher', 'guard_name' => 'web'],
            ['custom_role' => 1]
        );

        // Assign only the permissions relevant to QR-based attendance scanning
        $role->syncPermissions([
            $qrScanPermission,
            $attendanceListPermission,
            $attendanceCreatePermission,
        ]);
    }
}
