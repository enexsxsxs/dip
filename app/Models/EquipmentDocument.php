<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDocument extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['document', 'name', 'document_type_id', 'uploaded_at', 'equipment_id'];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    /** Код типа документа (из справочника equipment_document_types), в БД хранится только document_type_id. */
    public function getTypeAttribute(): ?string
    {
        if ($this->document_type_id === null) {
            return null;
        }
        if ($this->relationLoaded('documentType')) {
            return $this->documentType?->code;
        }

        return EquipmentDocumentType::query()->whereKey($this->document_type_id)->value('code');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentDocumentType::class, 'document_type_id');
    }
}
