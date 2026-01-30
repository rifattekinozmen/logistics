<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'setting_key',
        'setting_value',
    ];

    /**
     * Get the company that owns the setting.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get a setting value by company ID and key.
     */
    public static function get(int $companyId, string $key, mixed $default = null): mixed
    {
        $setting = self::where('company_id', $companyId)
            ->where('setting_key', $key)
            ->first();

        if (! $setting) {
            return $default;
        }

        $value = $setting->setting_value;

        // Try to decode JSON
        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Get AI threshold value for a company.
     */
    public static function getAiThreshold(int $companyId, string $key, mixed $default = null): mixed
    {
        return self::get($companyId, "ai_threshold_{$key}", $default);
    }
}
