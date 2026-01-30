<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyDigitalService;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::factory()->create([
            'name' => 'Ana Şirket',
            'tax_number' => '1234567890',
            'status' => 1,
        ]);

        // Ana şirket için örnek dijital mali hizmetler oluştur
        $services = [
            CompanyDigitalService::TYPE_E_INVOICE,
            CompanyDigitalService::TYPE_E_ARCHIVE,
            CompanyDigitalService::TYPE_E_WAYBILL,
        ];

        foreach ($services as $serviceType) {
            CompanyDigitalService::factory()->create([
                'company_id' => $company->id,
                'service_type' => $serviceType,
            ]);
        }
    }
}
