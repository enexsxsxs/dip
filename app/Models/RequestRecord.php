<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestRecord extends Model
{
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'registry_number',
        'data',
        'created_by',
        'request_layout_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'registry_number' => 'integer',
        ];
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(RequestLayout::class, 'request_layout_id')->withTrashed();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
