<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamTimetable extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    protected $appends = ['starting_time', 'ending_time'];

    public function getStartingTimeAttribute(): ?string
    {
        return $this->start_time;
    }

    public function getEndingTimeAttribute(): ?string
    {
        return $this->end_time;
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id')->withTrashed();
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassSchool::class, 'class_id');
    }

    public function session_year()
    {
        return $this->belongsTo(SessionYear::class, 'session_year_id');
    }

    public function exam_marks()
    {
        return $this->hasMany(ExamMarks::class, 'exam_timetable_id');
    }
}
