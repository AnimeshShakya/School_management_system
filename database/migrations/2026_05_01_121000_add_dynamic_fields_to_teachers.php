<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('teachers') && !Schema::hasColumn('teachers', 'dynamic_fields')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->json('dynamic_fields')->nullable()->after('qualification');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'dynamic_fields')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('dynamic_fields');
            });
        }
    }
};
