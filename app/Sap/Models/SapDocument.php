<?php

namespace App\Sap\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SapDocument extends Model
{
    protected $fillable = [
        'company_id',
        'local_model_type',
        'local_model_id',
        'sap_doc_type',
        'sap_doc_number',
        'sap_doc_year',
        'sap_status',
        'sync_status',
        'last_synced_at',
        'sync_error',
        'sap_payload',
        'sap_response',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    /** SAP doküman tür etiketleri */
    public const DOC_TYPES = [
        'TA' => 'Satış Siparişi',
        'LF' => 'Teslimat',
        'FV' => 'Fatura',
    ];

    /** Sync durum etiketleri */
    public const SYNC_STATUSES = [
        'pending' => 'Beklemede',
        'synced' => 'Senkronize',
        'error' => 'Hata',
        'skipped' => 'Atlandı',
    ];

    public function localModel(): MorphTo
    {
        return $this->morphTo('local_model');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SapSyncLog::class);
    }

    public function isSynced(): bool
    {
        return $this->sync_status === 'synced';
    }

    public function hasFailed(): bool
    {
        return $this->sync_status === 'error';
    }

    public function getDocTypeLabelAttribute(): string
    {
        return self::DOC_TYPES[$this->sap_doc_type] ?? $this->sap_doc_type;
    }

    public function getSyncStatusLabelAttribute(): string
    {
        return self::SYNC_STATUSES[$this->sync_status] ?? $this->sync_status;
    }
}
