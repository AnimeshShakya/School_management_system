<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class Stream extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use softDeletes;

    protected $fillable=[
        'name',   
    ];

    
}
