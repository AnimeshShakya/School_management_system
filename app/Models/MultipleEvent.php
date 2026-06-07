<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Event;
use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MultipleEvent extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $fillable = [
        'id',
        'event_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'description'
    ];

    public function events()
    {
        return $this->belongsTo(Event::class);
    }
}
