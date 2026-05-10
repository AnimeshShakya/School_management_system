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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('initiated_by')->nullable()->after('session_year_id')->comment('User who triggered the payment');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable()->after('initiated_by')->comment('Client IP address');
            $table->text('user_agent')->nullable()->after('ip_address')->comment('Browser/app identifier');
            $table->tinyInteger('previous_status')->nullable()->after('user_agent')->comment('Previous payment status for auditing (0 - failed 1 - succeed 2 - pending)');
            $table->text('notes')->nullable()->after('previous_status')->comment('Admin notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['initiated_by']);
            $table->dropColumn(['initiated_by', 'ip_address', 'user_agent', 'previous_status', 'notes']);
        });
    }
};
