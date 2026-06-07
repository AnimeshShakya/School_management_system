<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'staffs';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
