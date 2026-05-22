<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendances', 'status')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendances', 'status')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
