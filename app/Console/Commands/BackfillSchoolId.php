<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillSchoolId extends Command
{
    protected $signature = 'school:backfill-school-id {--force : Skip confirmation prompt}';

    protected $description = 'Backfill school_id on all tenant tables for existing data';

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
        'promote_students', 'academic_calendars',
        'categories', 'grades', 'form_fields',
    ];

    private array $userLinked = [
        'students' => 'students.user_id = users.id',
        'teachers' => 'teachers.user_id = users.id',
        'staffs' => 'staffs.user_id = users.id',
        'parents' => 'parents.user_id = users.id',
    ];

    public function handle(): int
    {
        $schoolCount = School::count();

        if ($schoolCount === 0) {
            if (! $this->option('force') && ! $this->confirm('No schools found. Create a "Default School" to backfill data?', true)) {
                $this->warn('Backfill cancelled.');

                return Command::FAILURE;
            }

            $school = School::create([
                'name' => 'Default School',
                'status' => 1,
            ]);
            $defaultSchoolId = $school->id;
            $this->info('Created default school with ID: '.$defaultSchoolId);
        } elseif ($schoolCount === 1) {
            $defaultSchoolId = School::first()->id;
            $this->info('Using existing school ID: '.$defaultSchoolId);
        } else {
            $this->warn("Found {$schoolCount} schools.");
            $schools = School::pluck('name', 'id')->toArray();
            $defaultSchoolId = (int) $this->anticipate(
                'Multiple schools exist. Enter the school ID to use as default for orphaned records:',
                array_keys($schools)
            );
        }

        $bar = $this->output->createProgressBar(count($this->tables));
        $bar->start();

        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'school_id')) {
                $bar->advance();

                continue;
            }

            $nullCount = DB::table($table)->whereNull('school_id')->count();

            if ($nullCount === 0) {
                $bar->advance();

                continue;
            }

            if (isset($this->userLinked[$table])) {
                $joinCondition = $this->userLinked[$table];
                DB::statement(
                    "UPDATE {$table} 
                     JOIN users ON {$joinCondition}
                     SET {$table}.school_id = users.school_id
                     WHERE {$table}.school_id IS NULL 
                     AND users.school_id IS NOT NULL"
                );
            }

            $remainingNull = DB::table($table)->whereNull('school_id')->count();
            if ($remainingNull > 0) {
                DB::table($table)
                    ->whereNull('school_id')
                    ->update(['school_id' => $defaultSchoolId]);

                $this->line("\n<info>Set {$remainingNull} orphaned record(s) in {$table} to school_id={$defaultSchoolId}</info>");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('School ID backfill complete.');

        return Command::SUCCESS;
    }
}
