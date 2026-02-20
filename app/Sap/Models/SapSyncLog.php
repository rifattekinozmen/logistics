<?php

namespace App\Sap\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapSyncLog extends Model
{
    protected $fillable = [
        'company_id',
        'sap_document_id',
        'operation',
        'direction',
        'http_status',
        'result',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function sapDocument(): BelongsTo
    {
        return $this->belongsTo(SapDocument::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuccess(): bool
    {
        return $this->result === 'success';
    }
}
