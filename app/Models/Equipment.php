<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'inventory_number',
        'serial_number',
        'registration_certificate',
        'date_of_registration',
        'valid_until',
        'year_of_manufacture',
        'valid_to',
        'verification_period',
        'last_verification_date',
        'instruction_pdf',
        'registration_certificate_pdf',
        'cabinet_id',
        'department_id',
        'supplier_id',
        'service_organization_id',
    ];

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
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
}
