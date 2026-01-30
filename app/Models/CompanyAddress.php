<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'address',
        'city',
        'district',
        'country',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the address.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
