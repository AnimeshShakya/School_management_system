<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Mediums extends Model
{
    use BelongsToSchool;
    protected $hidden = ["deleted_at", "created_at", "updated_at"];
    use SoftDeletes;
    use HasFactory;
}
