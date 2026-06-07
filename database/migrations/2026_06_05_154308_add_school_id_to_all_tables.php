<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'students', 'teachers', 'staffs', 'parents',
        'classes', 'class_sections', 'sections', 'subjects', 'subject_teachers', 'class_subjects', 'class_teachers',
        'mediums', 'streams', 'shifts', 'semesters', 'session_years', 'student_sessions', 'student_subjects', 'elective_subject_groups',
        'timetables',
        'exams', 'exam_classes', 'exam_timetables', 'exam_marks', 'exam_results',
        'fees_types', 'fees_classes', 'fees_paids', 'fees_choiceables', 'installment_fees', 'paid_installment_fees', 'payment_transactions',
        'attendances', 'qr_attendance_logs',
        'assignments', 'assignment_submissions',
        'lessons', 'lesson_topics',
        'online_exams', 'online_exam_questions', 'online_exam_question_options', 'online_exam_question_answers',
        'online_exam_question_choices', 'online_exam_student_answers', 'student_online_exam_statuses',
        'announcements', 'notifications', 'user_notifications',
        'leaves', 'leave_details', 'leave_masters',
        'holidays', 'events', 'multiple_events',
        'academic_calendars',
        'categories', 'grades', 'form_fields',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('school_id')->nullable()->after('id');
                    $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['school_id']);
                    $table->dropColumn('school_id');
                });
            }
        }
    }
};
