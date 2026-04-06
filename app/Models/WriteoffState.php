<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WriteoffState extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
    ];

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'writeoff_state_id');
    }
}
