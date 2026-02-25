<?php

namespace Database\Factories;

use App\Models\DeliveryImportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryImportBatch>
 */
class DeliveryImportBatchFactory extends Factory
{
    protected $model = DeliveryImportBatch::class;

    public function definition(): array
    {
        return [
            'file_name' => $this->faker->word().'.xlsx',
            'file_path' => 'delivery_imports/'.$this->faker->uuid().'.xlsx',
            'report_type' => 'dokme_cimento',
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'import_errors' => null,
            'status' => 'completed',
            'invoice_status' => DeliveryImportBatch::INVOICE_STATUS_PENDING,
            'petrokok_route_preference' => DeliveryImportBatch::PETROKOK_ROUTE_EKINCILER,
            'klinker_daily_overrides' => null,
            'imported_by' => null,
        ];
    }
}
