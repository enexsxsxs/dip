<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'number',
        'equipment_type_id',
        'name',
        'serial_number',
        'production_date',
        'year_of_manufacture',
        'date_accepted_to_accounting',
        'inventory_number',
        'department_id',
        'cabinet_id',
        'group_id',
        'equipment_condition_id',
        'ru_number',
        'ru_date',
        'grsi',
        'registration_certificate',
        'date_of_registration',
        'valid_until',
        'valid_to',
        'verification_period',
        'last_verification_date',
        'supplier_id',
        'service_organization_id',
        'writeoff_state_id',
        'utilization_state_id',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'date_accepted_to_accounting' => 'date',
            'ru_date' => 'date',
        ];
    }

    public function isWriteoffRequested(): bool
    {
        return $this->writeoff_status === 'requested';
    }

    public function isWrittenOff(): bool
    {
        return $this->writeoff_status === 'approved';
    }

    public function isUtilized(): bool
    {
        return $this->utilization_status === 'utilized';
    }

    /** Код состояния утилизации (справочник utilization_states, 3НФ). */
    public function getUtilizationStatusAttribute(): ?string
    {
        if (! array_key_exists('utilization_state_id', $this->attributes) || $this->attributes['utilization_state_id'] === null) {
            return null;
        }
        if ($this->relationLoaded('utilizationState')) {
            return $this->utilizationState?->code;
        }

        return UtilizationState::query()->whereKey($this->attributes['utilization_state_id'])->value('code');
    }

    /** Код состояния списания (столбца writeoff_status в БД нет — связь writeoff_states, 3НФ). */
    public function getWriteoffStatusAttribute(): ?string
    {
        if (! array_key_exists('writeoff_state_id', $this->attributes) || $this->attributes['writeoff_state_id'] === null) {
            return null;
        }
        if ($this->relationLoaded('writeoffState')) {
            return $this->writeoffState?->code;
        }

        return WriteoffState::query()->whereKey($this->attributes['writeoff_state_id'])->value('code');
    }

    public function setWriteoffStatusAttribute(?string $value): void
    {
        unset($this->attributes['writeoff_status']);
        $code = $value === null || $value === '' ? 'none' : $value;
        $this->attributes['writeoff_state_id'] = WriteoffState::query()->where('code', $code)->value('id');
    }

    public function writeoffState(): BelongsTo
    {
        return $this->belongsTo(WriteoffState::class, 'writeoff_state_id');
    }

    public function utilizationState(): BelongsTo
    {
        return $this->belongsTo(UtilizationState::class, 'utilization_state_id');
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function equipmentCondition(): BelongsTo
    {
        return $this->belongsTo(EquipmentCondition::class, 'equipment_condition_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function serviceOrganization(): BelongsTo
    {
        return $this->belongsTo(ServiceOrganization::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(EquipmentImage::class);
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentDocument::class,
            'equipment_document_equipment',
            'equipment_id',
            'equipment_document_id'
        );
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'entity');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(EquipmentRequest::class);
    }
}
