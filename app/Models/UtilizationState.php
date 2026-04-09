<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilizationState extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
    ];

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'utilization_state_id');
    }
}
