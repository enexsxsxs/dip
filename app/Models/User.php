<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /** Ключи ролей (для БД и валидации). */
    public const ROLES = ['admin', 'user', 'senior_nurse', 'nurse', 'accountant'];

    /** Названия ролей для отображения. */
    public const ROLE_LABELS = [
        'admin' => 'Админ',
        'user' => 'Пользователь',
        'senior_nurse' => 'Старшая медсестра',
        'nurse' => 'Медсестра',
        'accountant' => 'Бухгалтер',
    ];

    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'patronymic',
        'role',
        'email',
        'password',
        'last_login',
        'is_superuser',
        'is_staff',
        'is_active',
        'date_joined',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'last_login' => 'datetime',
            'is_superuser' => 'boolean',
            'is_staff' => 'boolean',
            'is_active' => 'boolean',
            'date_joined' => 'datetime',
        ];
    }

    /** Название роли для отображения в интерфейсе. */
    public function getRoleLabelAttribute(): ?string
    {
        return $this->role ? (self::ROLE_LABELS[$this->role] ?? $this->role) : null;
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function equipmentHistory(): HasMany
    {
        return $this->hasMany(EquipmentHistory::class);
    }
}
