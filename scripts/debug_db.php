<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Staff;
use App\Models\User;

$hasStaff = Schema::hasTable((new Staff())->getTable());
echo "has_staff_table:" . ($hasStaff? 'yes' : 'no') . PHP_EOL;
if ($hasStaff) {
    echo "staff_count:" . Staff::count() . PHP_EOL;
}
$superCount = User::whereHas('roles', function($q) { $q->where('name', 'Super Admin'); })->count();
echo "super_admin_count:" . $superCount . PHP_EOL;

$super = User::whereHas('roles', function($q) { $q->where('name', 'Super Admin'); })->get();
foreach ($super as $s) {
    echo "super: " . $s->id . " " . $s->email . PHP_EOL;
}
