<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'activity_logs';

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'field_name',
        'old_value',
        'new_value',
        'title',
        'details',
        'snapshot',
        'occurred_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public static function record(
        string $entityType,
        ?int $entityId,
        string $action,
        ?string $title = null,
        ?string $details = null,
        ?string $snapshot = null,
        ?string $fieldName = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?int $userId = null,
    ): self {
        return self::query()->create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'title' => $title,
            'details' => $details,
            'snapshot' => $snapshot,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'occurred_at' => now(),
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }
}
