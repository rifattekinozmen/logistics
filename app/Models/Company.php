<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyDigitalService;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'title',
        'tax_office',
        'tax_number',
        'tax_office_city_id',
        'mersis_no',
        'trade_registry_no',
        'currency',
        'default_vat_rate',
        'logo_path',
        'stamp_path',
        'phone',
        'mobile_phone',
        'landline_phone',
        'fax',
        'email',
        'authorized_email',
        'website',
        'country_id',
        'city_id',
        'district_id',
        'address',
        'postal_code',
        'headquarters_city',
        'authorized_person_name',
        'e_invoice_pk_tag',
        'e_waybill_pk_tag',
        'e_invoice_gb_tag',
        'e_waybill_gb_tag',
        'capital_amount',
        'api_key',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_vat_rate' => 'decimal:2',
            'capital_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the country that owns the company.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the city that owns the company.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the district that owns the company.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the tax office city.
     */
    public function taxOfficeCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'tax_office_city_id');
    }

    /**
     * Get the branches for the company.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the warehouses for the company.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get the addresses for the company.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    /**
     * Get the settings for the company.
     */
    public function settings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }

    /**
     * Get the digital services for the company.
     */
    public function digitalServices(): HasMany
    {
        return $this->hasMany(CompanyDigitalService::class);
    }

    /**
     * Get the users for the company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies')
            ->withPivot('role', 'is_default')
            ->withTimestamps();
    }

    /**
     * Get a setting value by key.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('setting_key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->setting_value;

        // Try to decode JSON
        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Set a setting value by key.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $this->settings()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => is_array($value) ? json_encode($value) : $value]
        );
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        // Önce is_active kolonunu kontrol et, yoksa status kullan
        if ($this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), 'is_active')) {
            return $query->where('is_active', true);
        }

        return $query->where('status', 1);
    }

    /**
     * Retrieve the model for route model binding.
     * Soft deleted models are also included.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withTrashed()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    /**
     * Get the theme attribute for the company.
     */
    public function getThemeAttribute(): array
    {
        return [
            'primary_color' => $this->getSetting('primary_color', '#3B82F6'),
            'secondary_color' => $this->getSetting('secondary_color', '#10B981'),
            'logo_path' => $this->logo_path,
        ];
    }

    /**
     * Get PDF template for a specific type.
     */
    public function getPdfTemplate(string $type): string
    {
        $template = $this->getSetting("pdf_template_{$type}");

        return $template ?? "default.{$type}";
    }

    /**
     * Get the logo URL attribute.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->logo_path
                ? Storage::disk('public')->url($this->logo_path)
                : null,
        );
    }
}
