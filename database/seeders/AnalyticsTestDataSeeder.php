<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnalyticsTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'Ana Şirket')->first();

        if (! $company) {
            if ($this->command) {
                $this->command->error('Ana Şirket bulunamadı. Önce CompanySeeder çalıştırın.');
            }

            return;
        }

        if ($this->command) {
            $this->command->info('Test verileri oluşturuluyor...');
        }

        // Müşteriler oluştur
        if ($this->command) {
            $this->command->info('- Müşteriler oluşturuluyor...');
        }
        $customers = Customer::factory()->count(20)->create([
            'company_id' => $company->id,
        ]);

        // Son 90 günde siparişler oluştur
        if ($this->command) {
            $this->command->info('- Siparişler oluşturuluyor...');
        }
        $statuses = ['pending', 'assigned', 'in_transit', 'delivered', 'invoiced', 'cancelled'];
        $statusWeights = [10, 15, 20, 30, 20, 5]; // Delivered ve invoiced daha yüksek ağırlıkta

        for ($i = 0; $i < 150; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 90));
            $status = $this->weightedRandom($statuses, $statusWeights);

            $deliveredAt = null;
            if (in_array($status, ['delivered', 'invoiced'])) {
                $deliveredAt = $createdAt->copy()->addHours(rand(24, 120));
            }

            Order::factory()->create([
                'company_id' => $company->id,
                'customer_id' => $customers->random()->id,
                'status' => $status,
                'freight_price' => rand(5000, 50000),
                'created_at' => $createdAt,
                'delivered_at' => $deliveredAt,
            ]);
        }

        // Araçlar oluştur
        if ($this->command) {
            $this->command->info('- Araçlar oluşturuluyor...');
        }
        $branch = $company->branches()->first();

        if (! $branch) {
            if ($this->command) {
                $this->command->warn('Şube bulunamadı. Araçlar oluşturulamadı.');
            }
        } else {
            $vehicles = Vehicle::factory()->count(15)->create([
                'branch_id' => $branch->id,
            ]);

            // Sevkiyatlar oluştur
            if ($this->command) {
                $this->command->info('- Sevkiyatlar oluşturuluyor...');
            }
            $shipmentStatuses = ['pending', 'assigned', 'loaded', 'in_transit', 'delivered'];
            $orders = Order::where('company_id', $company->id)->get();

            if ($orders->isEmpty()) {
                if ($this->command) {
                    $this->command->warn('Sipariş bulunamadı. Sevkiyatlar oluşturulamadı.');
                }
            } else {
                for ($i = 0; $i < 100; $i++) {
                    $createdAt = Carbon::now()->subDays(rand(0, 90));
                    $status = $shipmentStatuses[array_rand($shipmentStatuses)];

                    $pickupDate = $createdAt->copy()->addHours(rand(12, 48));
                    $deliveryDate = null;

                    if ($status === 'delivered') {
                        // %80 zamanında teslimat
                        $onTime = rand(1, 100) <= 80;
                        if ($onTime) {
                            $deliveryDate = $pickupDate->copy()->addHours(rand(6, 24));
                        } else {
                            $deliveryDate = $pickupDate->copy()->addHours(rand(30, 72));
                        }
                    }

                    Shipment::factory()->create([
                        'order_id' => $orders->random()->id,
                        'vehicle_id' => $vehicles->random()->id,
                        'driver_id' => null,
                        'status' => $status,
                        'pickup_date' => $pickupDate,
                        'delivery_date' => $deliveryDate,
                        'created_at' => $createdAt,
                    ]);
                }
            }
        }

        // Ödemeler oluştur (Giderler)
        if ($this->command) {
            $this->command->info('- Ödemeler oluşturuluyor...');
        }
        for ($i = 0; $i < 80; $i++) {
            $paidDate = Carbon::now()->subDays(rand(0, 90));

            Payment::factory()->create([
                'payment_type' => 'outgoing',
                'amount' => rand(2000, 20000),
                'status' => 1,
                'paid_date' => $paidDate,
                'created_at' => $paidDate,
            ]);
        }

        if ($this->command) {
            $this->command->info('✓ Analytics test verileri başarıyla oluşturuldu!');
        }
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $values, array $weights): mixed
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $cumulative = 0;
        foreach ($values as $index => $value) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $value;
            }
        }

        return $values[0];
    }
}
