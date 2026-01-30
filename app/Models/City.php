<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'country_id',
        'code',
        'name_tr',
        'name_en',
        'plate_code',
        'population',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'population' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the country that owns the city.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the districts for the city.
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
