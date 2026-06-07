<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $user = Auth::user();

            if ($user && $user->school_id && ! $user->hasRole('Super Admin')) {
                $table = (new static)->getTable();

                $builder->where($table.'.school_id', $user->school_id);
            }
        });

        static::creating(function (Model $model): void {
            if ($model->school_id) {
                return;
            }

            $user = Auth::user();

            if ($user && $user->school_id) {
                $model->school_id = $user->school_id;
            }
        });
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function scopeWithoutSchoolScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('school');
    }
}
