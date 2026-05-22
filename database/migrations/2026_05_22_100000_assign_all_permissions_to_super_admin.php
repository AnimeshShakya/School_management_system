<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::findByName('Super Admin', 'web');
        $role->syncPermissions(Permission::all());
    }

    public function down(): void
    {
        //
    }
};
