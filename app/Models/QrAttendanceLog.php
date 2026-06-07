<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrAttendanceLog extends Model
{
    use BelongsToSchool;
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'scanned_by',
        'class_section_id',
        'session_year_id',
        'attendance_date',
        'status',
        'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Students::class, 'student_id')->with('user');
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id')->with('class', 'section');
    }

    public function sessionYear(): BelongsTo
    {
        return $this->belongsTo(SessionYear::class, 'session_year_id');
    }
}
