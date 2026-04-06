<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentRequest extends Model
{
    use HasFactory;

    public const TYPE_WRITEOFF = 'writeoff';
    public const TYPE_MOVE = 'move';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'equipment_id',
        'user_id',
        'request_type_id',
        'request_status_id',
        'type',
        'status',
        'from_department_id',
        'to_department_id',
        'comment',
        'photo',
    ];

    public function getTypeAttribute($value): ?string
    {
        if (! array_key_exists('request_type_id', $this->attributes) || $this->attributes['request_type_id'] === null) {
            return null;
        }
        if ($this->relationLoaded('requestType')) {
            return $this->requestType?->code;
        }

        return EquipmentRequestType::query()->whereKey($this->attributes['request_type_id'])->value('code');
    }

    public function setTypeAttribute(?string $value): void
    {
        unset($this->attributes['type']);
        $this->attributes['request_type_id'] = $value
            ? EquipmentRequestType::query()->where('code', $value)->value('id')
            : null;
    }

    public function getStatusAttribute($value): ?string
    {
        if (! array_key_exists('request_status_id', $this->attributes) || $this->attributes['request_status_id'] === null) {
            return null;
        }
        if ($this->relationLoaded('requestStatus')) {
            return $this->requestStatus?->code;
        }

        return EquipmentRequestStatus::query()->whereKey($this->attributes['request_status_id'])->value('code');
    }

    public function setStatusAttribute(?string $value): void
    {
        unset($this->attributes['status']);
        $this->attributes['request_status_id'] = $value
            ? EquipmentRequestStatus::query()->where('code', $value)->value('id')
            : null;
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(EquipmentRequestType::class, 'request_type_id');
    }

    public function requestStatus(): BelongsTo
    {
        return $this->belongsTo(EquipmentRequestStatus::class, 'request_status_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }
}
