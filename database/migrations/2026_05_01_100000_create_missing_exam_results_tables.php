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
        // Create exam_marks if not exists
        if (!Schema::hasTable('exam_marks')) {
            Schema::create('exam_marks', function (Blueprint $table) {
                $table->id();
                $table->integer('exam_timetable_id');
                $table->integer('student_id');
                $table->integer('subject_id');
                $table->integer('obtained_marks');
                $table->string('teacher_review', 1024)->nullable();
                $table->boolean('passing_status')->comment('1=Pass, 0=Fail')->default(0);
                $table->integer('session_year_id');
                $table->tinyText('grade')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create exam_results if not exists
        if (!Schema::hasTable('exam_results')) {
            Schema::create('exam_results', function (Blueprint $table) {
                $table->id();
                $table->integer('exam_id');
                $table->integer('class_section_id');
                $table->integer('student_id');
                $table->integer('total_marks');
                $table->integer('obtained_marks');
                $table->float('percentage');
                $table->tinyText('grade');
                $table->integer('session_year_id');
                $table->timestamps();
            });
        }

        // Create grades if not exists
        if (!Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->integer('starting_range')->nullable();
                $table->integer('ending_range')->nullable();
                $table->tinyText('grade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('exam_marks')) {
            Schema::dropIfExists('exam_marks');
        }
        if (Schema::hasTable('exam_results')) {
            Schema::dropIfExists('exam_results');
        }
        if (Schema::hasTable('grades')) {
            Schema::dropIfExists('grades');
        }
    }
};
