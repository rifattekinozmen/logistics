<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'imported_by',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
        ];
    }

    /**
     * Get the user who imported the batch.
     */
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Get the delivery numbers for the batch.
     */
    public function deliveryNumbers(): HasMany
    {
        return $this->hasMany(DeliveryNumber::class, 'import_batch_id');
    }
}
