<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoom extends Model
{
    use BelongsToSchool;
    use HasFactory;

    public function members()
    {
        return $this->hasMany(ChatMember::class, 'chat_room_id');
    }

    public function messages()
    {
        return $this->morphMany(ChatMessage::class, 'modal');
    }
    
}
