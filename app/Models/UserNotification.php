<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Notification;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserNotification extends Model
{
    use BelongsToSchool;
    use HasFactory;

    public function notification()
    {
        return $this->belongsTo(Notification::class,'notification_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}

