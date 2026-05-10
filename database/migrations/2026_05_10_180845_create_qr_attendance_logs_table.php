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
        Schema::create('qr_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('scanned_by');
            $table->unsignedBigInteger('class_section_id');
            $table->unsignedBigInteger('session_year_id');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'holiday'])->default('present');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('scanned_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('class_section_id')->references('id')->on('class_sections')->onDelete('cascade');
            $table->foreign('session_year_id')->references('id')->on('session_years')->onDelete('cascade');

            $table->index(['student_id', 'attendance_date']);
            $table->index(['class_section_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_attendance_logs');
    }
};
