<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('class_level', 32)->nullable()->after('type');
            $table->string('selection_type', 32)->nullable()->after('class_level');
            $table->string('eca_type', 32)->nullable()->after('selection_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['class_level', 'selection_type', 'eca_type']);
        });
    }
};
