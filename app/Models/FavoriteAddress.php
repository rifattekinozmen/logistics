<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FavoriteAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'type',
        'address',
        'latitude',
        'longitude',
        'contact_name',
        'contact_phone',
        'notes',
        'sort_order',
        'working_days',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'sort_order' => 'integer',
            'working_days' => 'array',
        ];
    }

    /**
     * Get working days as formatted Turkish string.
     */
    public function getWorkingDaysFormattedAttribute(): string
    {
        $map = [
            'monday' => 'Pazartesi',
            'tuesday' => 'Salı',
            'wednesday' => 'Çarşamba',
            'thursday' => 'Perşembe',
            'friday' => 'Cuma',
            'saturday' => 'Cumartesi',
            'sunday' => 'Pazar',
        ];

        if (! is_array($this->working_days) || empty($this->working_days)) {
            return '';
        }

        $labels = array_map(fn ($day) => $map[$day] ?? $day, $this->working_days);

        return implode(', ', $labels);
    }

    /**
     * Get the customer that owns the favorite address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
