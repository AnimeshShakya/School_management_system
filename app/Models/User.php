<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected string $guard_name = 'web';

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'school_id',
        'created_by',
        'first_name',
        'last_name',
        'gender',
        'email',
        'device_type',
        'fcm_id',
        'password',
        'mobile',
        'image',
        'dob',
        'current_address',
        'permanent_address',
        'status',
        'reset_request',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['full_name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Students::class, 'user_id', 'id');
    }

    public function parent(): HasOne
    {
        return $this->hasOne(Parents::class, 'user_id', 'id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id', 'id');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'user_id')->with('leave_detail');
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'user_notifications');
    }

    public function messages()
    {
        return $this->morphMany(ChatMessage::class, 'modal');
    }

    // Getter Attributes
    public function getImageAttribute($value): string
    {
        return url(Storage::url($value));
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
