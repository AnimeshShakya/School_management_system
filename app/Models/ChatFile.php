<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ChatMessage;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatFile extends Model
{
    use BelongsToSchool;
    use HasFactory;

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function getFileUrlAttribute($value)
    {
        return url(Storage::url($value));
    }
}
