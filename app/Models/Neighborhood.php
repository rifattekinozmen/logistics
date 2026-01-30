<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Neighborhood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'district_id',
        'code',
        'name_tr',
        'name_en',
        'postal_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the district that owns the neighborhood.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
