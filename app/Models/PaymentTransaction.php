<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentTransaction extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $hidden = ["deleted_at", "created_at", "updated_at"];

    protected $fillable = [
        'initiated_by',
        'ip_address',
        'user_agent',
        'notes',
    ];

    public function student(){
        return $this->belongsTo(Students::class ,'student_id')->withTrashed();
    }
    public function class() {
        return $this->belongsTo(ClassSchool::class, 'class_id');
    }
    public function session_year() {
        return $this->belongsTo(SessionYear::class);
    }
    public function initiatedBy() {
        return $this->belongsTo(User::class, 'initiated_by')->withTrashed();
    }
}
