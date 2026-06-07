<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionYear extends Model
{
    use BelongsToSchool;
    use SoftDeletes;
    use HasFactory;

    public function fee_installments() {
        return $this->hasMany(InstallmentFee::class, 'session_year_id');
    }
}
