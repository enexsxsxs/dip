<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /** Роли, которые могут входить в систему и работать в интерфейсе. */
    public const LOGIN_ROLES = ['admin', 'user', 'senior_nurse', 'nurse', 'accountant', 'disposal_officer'];

    /** Служебные роли подписантов PDF (учетные записи создаются как неактивные, без входа в систему). */
    public const SYSTEM_SIGNER_ROLES = ['sign_chief_doctor', 'sign_writeoff_head', 'sign_move_head'];

    /** Все роли, существующие в БД. */
    public const ROLES = [...self::LOGIN_ROLES, ...self::SYSTEM_SIGNER_ROLES];

    /** Названия ролей для отображения. */
    public const ROLE_LABELS = [
        'admin' => 'Администратор',
        'user' => 'Пользователь',
        'senior_nurse' => 'Старшая медсестра',
        'nurse' => 'Медсестра',
        'accountant' => 'Бухгалтер',
        'disposal_officer' => 'Ответственный за утилизацию',
        'sign_chief_doctor' => 'Подписант: главный врач',
        'sign_writeoff_head' => 'Подписант: заведующая отделением (списание)',
        'sign_move_head' => 'Подписант: заведующая отделением (перемещение)',
    ];

    /** Является ли пользователь администратором. */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Является ли пользователь старшей медсестрой. */
    public function isSeniorNurse(): bool
    {
        return $this->role === 'senior_nurse';
    }

    /** Является ли пользователь бухгалтером. */
    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    /** Может ли пользователь присваивать инвентарный номер. */
    public function canAssignInventoryNumber(): bool
    {
        return $this->isAccountant();
    }

    /** Может ли пользователь управлять оборудованием (просмотр, добавление, редактирование). */
    public function canManageEquipment(): bool
    {
        return $this->role === 'admin' || $this->role === 'senior_nurse';
    }

    /** Может ли пользователь добавлять отчёты. */
    public function canAddReports(): bool
    {
        return $this->role === 'admin' || $this->role === 'senior_nurse';
    }

    /** Отметка утилизации списанного оборудования (администратор или ответственный за утилизацию). */
    public function canManageUtilization(): bool
    {
        return $this->isAdmin() || $this->role === 'disposal_officer';
    }

    protected $fillable = [
        'name',
        'username',
        'first_name',
        'last_name',
        'patronymic',
        'role',
        'role_id',
        'email',
        'password',
        'last_login',
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
            'is_active' => 'boolean',
            'date_joined' => 'datetime',
        ];
    }

    /**
     * Полное имя для отображения (в БД нет столбца name — ФИО только в частях, 3НФ).
     */
    public function getNameAttribute($value): string
    {
        return trim(implode(' ', array_filter([
            $this->attributes['last_name'] ?? '',
            $this->attributes['first_name'] ?? '',
            $this->attributes['patronymic'] ?? '',
        ], fn ($p) => $p !== null && $p !== '')));
    }

    public function setNameAttribute(string $value): void
    {
        unset($this->attributes['name']);
        $parts = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 3) {
            $this->attributes['last_name'] = $parts[0];
            $this->attributes['first_name'] = $parts[1];
            $this->attributes['patronymic'] = implode(' ', array_slice($parts, 2));
        } elseif (count($parts) === 2) {
            $this->attributes['last_name'] = $parts[0];
            $this->attributes['first_name'] = $parts[1];
            $this->attributes['patronymic'] = null;
        } elseif (count($parts) === 1) {
            $this->attributes['last_name'] = '';
            $this->attributes['first_name'] = $parts[0];
            $this->attributes['patronymic'] = null;
        } else {
            $this->attributes['last_name'] = '';
            $this->attributes['first_name'] = '';
            $this->attributes['patronymic'] = null;
        }
    }

    /** Название роли для отображения в интерфейсе. */
    public function getRoleLabelAttribute(): ?string
    {
        $key = $this->role;

        return $key ? (self::ROLE_LABELS[$key] ?? $key) : null;
    }

    /** Строковый ключ роли (из связи roles); колонки role в таблице нет — 3НФ. */
    public function getRoleAttribute($value): ?string
    {
        if ($this->role_id === null) {
            return null;
        }
        if ($this->relationLoaded('roleModel')) {
            return $this->roleModel?->name;
        }

        return Role::query()->whereKey($this->role_id)->value('name');
    }

    public function setRoleAttribute(?string $value): void
    {
        unset($this->attributes['role']);
        if ($value === null || $value === '') {
            $this->attributes['role_id'] = null;

            return;
        }
        $this->attributes['role_id'] = Role::query()->where('name', $value)->value('id');
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function equipmentRequests(): HasMany
    {
        return $this->hasMany(EquipmentRequest::class);
    }

    public function authoredRequestRecords(): HasMany
    {
        return $this->hasMany(RequestRecord::class, 'created_by');
    }
}
