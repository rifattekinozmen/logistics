<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDigitalService extends Model
{
    use HasFactory;

    public const TYPE_E_INVOICE = 'e_invoice';

    public const TYPE_E_INVOICE_STORAGE = 'e_invoice_storage';

    public const TYPE_E_ARCHIVE = 'e_archive';

    public const TYPE_E_ARCHIVE_STORAGE = 'e_archive_storage';

    public const TYPE_E_WAYBILL = 'e_waybill';

    public const TYPE_E_WAYBILL_STORAGE = 'e_waybill_storage';

    public const TYPE_E_SMM = 'e_smm';

    public const TYPE_E_SMM_STORAGE = 'e_smm_storage';

    public const TYPE_E_LEDGER_STORAGE = 'e_ledger_storage';

    protected $fillable = [
        'company_id',
        'service_type',
        'is_active',
        'activated_at',
        'added_at',
        'activation_code',
        'gb_label',
        'pk_label',
        'close_request_status',
        'close_requested_at',
        'last_activity_at',
        'stats_last_24h',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'added_at' => 'datetime',
            'close_requested_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getDisplayName(): string
    {
        return match ($this->service_type) {
            self::TYPE_E_INVOICE => 'e-Fatura',
            self::TYPE_E_INVOICE_STORAGE => 'e-Fatura Saklama',
            self::TYPE_E_ARCHIVE => 'e-Arşiv',
            self::TYPE_E_ARCHIVE_STORAGE => 'e-Arşiv Saklama',
            self::TYPE_E_WAYBILL => 'e-İrsaliye',
            self::TYPE_E_WAYBILL_STORAGE => 'e-İrsaliye Saklama',
            self::TYPE_E_SMM => 'e-Serbest Meslek Makbuzu',
            self::TYPE_E_SMM_STORAGE => 'e-Serbest Meslek Makbuzu Saklama',
            self::TYPE_E_LEDGER_STORAGE => 'e-Defter Saklama',
            default => ucfirst(str_replace('_', ' ', $this->service_type)),
        };
    }
}
