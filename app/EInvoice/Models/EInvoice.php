<?php

namespace App\EInvoice\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'related_type',
        'related_id',
        'invoice_uuid',
        'invoice_type',
        'invoice_number',
        'invoice_date',
        'customer_name',
        'customer_tax_number',
        'total_amount',
        'currency',
        'xml_content',
        'gib_status',
        'gib_response',
        'gib_error',
        'sent_at',
        'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'total_amount' => 'decimal:2',
            'sent_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /** E-Fatura tipleri */
    public const TYPES = [
        'e-fatura' => 'E-Fatura',
        'e-arsiv' => 'E-Arşiv',
        'e-irsaliye' => 'E-İrsaliye',
    ];

    /** GIB durum etiketleri */
    public const GIB_STATUSES = [
        'pending' => 'Beklemede',
        'sent' => 'Gönderildi',
        'approved' => 'Onaylandı',
        'rejected' => 'Reddedildi',
        'error' => 'Hata',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSent(): bool
    {
        return in_array($this->gib_status, ['sent', 'approved'], true);
    }

    public function isApproved(): bool
    {
        return $this->gib_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->gib_status === 'rejected';
    }

    public function getInvoiceTypeLabelAttribute(): string
    {
        return self::TYPES[$this->invoice_type] ?? $this->invoice_type;
    }

    public function getGibStatusLabelAttribute(): string
    {
        return self::GIB_STATUSES[$this->gib_status] ?? $this->gib_status;
    }
}
