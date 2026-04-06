<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentRequestStatus extends Model
{
    public $timestamps = false;

    protected $fillable = ['code'];

    public function equipmentRequests(): HasMany
    {
        return $this->hasMany(EquipmentRequest::class, 'request_status_id');
    }
}
