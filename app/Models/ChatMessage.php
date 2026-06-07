<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ChatFile;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use BelongsToSchool;
    use HasFactory;

    public function modal()
    {
        return $this->morphTo();
    }

    public function file()
    {
        return $this->hasMany(ChatFile::class, 'message_id','id');
    }

    public function getFileUrlAttribute($value)
    {
        return url(Storage::url($value));
    }
}
