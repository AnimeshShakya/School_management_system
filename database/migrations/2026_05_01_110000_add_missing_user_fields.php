<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name', 191)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name', 191)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 16)->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'current_address')) {
                $table->text('current_address')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'permanent_address')) {
                $table->text('permanent_address')->nullable()->after('current_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'permanent_address')) {
                $table->dropColumn('permanent_address');
            }
            if (Schema::hasColumn('users', 'current_address')) {
                $table->dropColumn('current_address');
            }
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }
        });
    }
};
