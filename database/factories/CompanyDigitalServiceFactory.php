<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyDigitalService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyDigitalService>
 */
class CompanyDigitalServiceFactory extends Factory
{
    protected $model = CompanyDigitalService::class;

    public function definition(): array
    {
        $serviceType = $this->faker->randomElement([
            CompanyDigitalService::TYPE_E_INVOICE,
            CompanyDigitalService::TYPE_E_ARCHIVE,
            CompanyDigitalService::TYPE_E_WAYBILL,
        ]);

        return [
            'company_id' => Company::factory(),
            'service_type' => $serviceType,
            'is_active' => true,
            'activated_at' => now()->subDays($this->faker->numberBetween(1, 60)),
            'added_at' => now()->subDays($this->faker->numberBetween(30, 90)),
            'activation_code' => $this->faker->regexify('[A-Z0-9]{16}'),
            'gb_label' => $this->faker->optional()->bothify('GB########'),
            'pk_label' => $this->faker->optional()->bothify('PK########'),
            'close_request_status' => 'none',
            'close_requested_at' => null,
            'last_activity_at' => now()->subHours($this->faker->numberBetween(1, 48)),
            'stats_last_24h' => $this->faker->numberBetween(0, 500),
        ];
    }
}

