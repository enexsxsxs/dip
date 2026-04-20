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
        'has_header',
        'document_header_id',
        'type',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'has_header' => 'boolean',
        ];
    }

    public function documentHeader(): BelongsTo
    {
        return $this->belongsTo(DocumentHeader::class);
    }

    /**
     * Схема макета с подставленной шапкой из {@see DocumentHeader}, если задана связь.
     *
     * @return array<string, mixed>
     */
    public function effectiveSchema(): array
    {
        $schema = is_array($this->schema) ? $this->schema : [];
        if (! $this->document_header_id) {
            return $schema;
        }

        $header = $this->relationLoaded('documentHeader')
            ? $this->documentHeader
            : DocumentHeader::query()->find($this->document_header_id);

        if ($header !== null && ! $header->trashed() && is_array($header->schema)) {
            $schema['header'] = $header->schema;
        }

        return $schema;
    }

    public function requestRecords(): HasMany
    {
        return $this->hasMany(RequestRecord::class, 'request_layout_id');
    }
}
