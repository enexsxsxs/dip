<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestLayout extends Model
{
    use SoftDeletes;

    protected $table = 'request_layout';

    protected $fillable = [
        'title',
        'schema',
        'scores',
        'has_header',
        'type',
        'version',
        'approver_id',
        'user_assigner_id',
        'division_assigner_id',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'has_header' => 'boolean',
            'scores' => 'decimal:2',
        ];
    }

    public function divisionAssigner(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'division_assigner_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_assigner_id');
    }

    public function requestRecords(): HasMany
    {
        return $this->hasMany(RequestRecord::class, 'request_layout_id');
    }
}
