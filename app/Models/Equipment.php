<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

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
        'instruction_pdf',
        'registration_certificate_pdf',
        'supplier_id',
        'service_organization_id',
        'writeoff_status',
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

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentDocument::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(EquipmentHistory::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(EquipmentRequest::class);
    }
}
