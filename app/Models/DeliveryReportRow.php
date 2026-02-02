<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReportRow extends Model
{
    protected $fillable = [
        'delivery_import_batch_id',
        'row_index',
        'row_data',
    ];

    protected function casts(): array
    {
        return [
            'row_index' => 'integer',
            'row_data' => 'array',
        ];
    }

    public function deliveryImportBatch(): BelongsTo
    {
        return $this->belongsTo(DeliveryImportBatch::class);
    }
}
