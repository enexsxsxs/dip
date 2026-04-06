<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentDocumentType extends Model
{
    public $timestamps = false;

    protected $fillable = ['code'];

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentDocument::class, 'document_type_id');
    }

    public static function idForCode(string $code): ?int
    {
        return self::query()->where('code', $code)->value('id');
    }
}
