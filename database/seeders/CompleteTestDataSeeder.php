<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Position;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteTestDataSeeder extends Seeder
{
    private $company;

    private $branch;

    private $users = [];

    private $customers = [];

    private $employees = [];

    private $vehicles = [];

    private $orders = [];

    private $shipments = [];

    public function run(): void
    {
        if ($this->command) {
            $this->command->info('🚀 Kapsamlı test verileri oluşturuluyor...');
        }

        DB::beginTransaction();

        try {
            $this->company = Company::where('name', 'Ana Şirket')->first();

            if (! $this->company) {
                if ($this->command) {
                    $this->command->error('Ana Şirket bulunamadı. Önce CompanySeeder çalıştırın.');
                }

                return;
            }

            // 1. Şube oluştur
            $this->createBranches();

            // 2. Departmanlar ve pozisyonlar
            $this->createDepartmentsAndPositions();

            // 3. Personel oluştur
            $this->createEmployees();

            // 4. Kullanıcılar oluştur
            $this->createUsers();

            // 5. Müşteriler oluştur
            $this->createCustomers();

            // 6. Depolar oluştur
            $this->createWarehouses();

            // 7. Araçlar oluştur
            $this->createVehicles();

            // 8. Siparişler oluştur
            $this->createOrders();

            // 9. Sevkiyatlar oluştur
            $this->createShipments();

            // 10. Ödemeler oluştur
            $this->createPayments();

            // 11. Motorin fiyatları
            $this->createFuelPrices();

            // 12. Takvim etkinlikleri
            $this->createCalendarEvents();

            DB::commit();

            if ($this->command) {
                $this->command->info('✅ Tüm test verileri başarıyla oluşturuldu!');
                $this->command->newLine();
                $this->command->table(
                    ['Tablo', 'Kayıt Sayısı'],
                    [
                        ['Müşteriler', count($this->customers)],
                        ['Siparişler', count($this->orders)],
                        ['Sevkiyatlar', count($this->shipments)],
                        ['Araçlar', count($this->vehicles)],
                        ['Personel', count($this->employees)],
                        ['Kullanıcılar', count($this->users)],
                    ]
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if ($this->command) {
                $this->command->error('Hata: '.$e->getMessage());
            }
            throw $e;
        }
    }

    private function createBranches(): void
    {
        if ($this->command) {
            $this->command->info('📍 Şube oluşturuluyor...');
        }

        $this->branch = Branch::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Merkez Şube',
            'address' => 'İstanbul, Türkiye',
            'phone' => '0212 555 0000',
        ]);
    }

    private function createDepartmentsAndPositions(): void
    {
        if ($this->command) {
            $this->command->info('🏢 Departmanlar ve pozisyonlar oluşturuluyor...');
        }

        $departments = [
            'Operasyon' => ['Operasyon Müdürü', 'Operasyon Uzmanı', 'Sevkiyat Koordinatörü'],
            'Lojistik' => ['Lojistik Müdürü', 'Lojistik Uzmanı', 'Depo Sorumlusu'],
            'Finans' => ['Finans Müdürü', 'Muhasebe Uzmanı', 'Ödeme Uzmanı'],
            'Satış' => ['Satış Müdürü', 'Satış Temsilcisi', 'Müşteri İlişkileri'],
        ];

        foreach ($departments as $deptName => $positions) {
            $dept = Department::factory()->create([
                'name' => $deptName,
                'branch_id' => $this->branch->id,
            ]);

            foreach ($positions as $posName) {
                Position::factory()->create([
                    'name' => $posName,
                    'department_id' => $dept->id,
                ]);
            }
        }
    }

    private function createEmployees(): void
    {
        if ($this->command) {
            $this->command->info('👥 Personel oluşturuluyor...');
        }

        $positions = Position::all();

        // Sürücüler (drivers)
        for ($i = 0; $i < 20; $i++) {
            $this->employees[] = Employee::factory()->create([
                'branch_id' => $this->branch->id,
                'position_id' => $positions->random()->id,
                'status' => 1,
            ]);
        }

        // Diğer personel
        for ($i = 0; $i < 10; $i++) {
            $this->employees[] = Employee::factory()->create([
                'branch_id' => $this->branch->id,
                'position_id' => $positions->random()->id,
                'status' => 1,
            ]);
        }
    }

    private function createUsers(): void
    {
        if ($this->command) {
            $this->command->info('👤 Kullanıcılar oluşturuluyor...');
        }

        // 5 çalışan kullanıcı
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create([
                'status' => 1,
            ]);

            $user->companies()->attach($this->company->id, [
                'role' => 'employee',
                'is_default' => true,
            ]);

            $this->users[] = $user;
        }
    }

    private function createCustomers(): void
    {
        if ($this->command) {
            $this->command->info('🏪 Müşteriler oluşturuluyor...');
        }

        for ($i = 0; $i < 50; $i++) {
            $this->customers[] = Customer::factory()->create([
                'company_id' => $this->company->id,
                'status' => 1,
            ]);
        }
    }

    private function createWarehouses(): void
    {
        if ($this->command) {
            $this->command->info('🏭 Depolar oluşturuluyor...');
        }

        $cities = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya'];

        foreach ($cities as $city) {
            Warehouse::factory()->create([
                'branch_id' => $this->branch->id,
                'name' => $city.' Depo',
                'address' => $city.', Türkiye',
            ]);
        }
    }

    private function createVehicles(): void
    {
        if ($this->command) {
            $this->command->info('🚚 Araçlar oluşturuluyor...');
        }

        $types = ['truck', 'van', 'truck', 'truck', 'van'];

        for ($i = 0; $i < 25; $i++) {
            $this->vehicles[] = Vehicle::factory()->create([
                'branch_id' => $this->branch->id,
                'vehicle_type' => $types[array_rand($types)],
                'status' => 1,
            ]);
        }
    }

    private function createOrders(): void
    {
        if ($this->command) {
            $this->command->info('📦 Siparişler oluşturuluyor...');
        }

        $statuses = ['pending', 'assigned', 'in_transit', 'delivered', 'invoiced', 'cancelled'];
        $statusWeights = [5, 10, 15, 35, 30, 5];

        // Son 6 ay için siparişler
        for ($i = 0; $i < 200; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 180));
            $status = $this->weightedRandom($statuses, $statusWeights);

            $deliveredAt = null;
            if (in_array($status, ['delivered', 'invoiced'], true)) {
                $deliveredAt = $createdAt->copy()->addHours(rand(24, 168));
            }

            $this->orders[] = Order::factory()->create([
                'company_id' => $this->company->id,
                'customer_id' => $this->customers[array_rand($this->customers)]->id,
                'status' => $status,
                'freight_price' => rand(3000, 80000),
                'created_at' => $createdAt,
                'delivered_at' => $deliveredAt,
                'created_by' => $this->users[array_rand($this->users)]->id ?? null,
            ]);
        }
    }

    private function createShipments(): void
    {
        if ($this->command) {
            $this->command->info('🚛 Sevkiyatlar oluşturuluyor...');
        }

        $shipmentStatuses = ['pending', 'assigned', 'loaded', 'in_transit', 'delivered'];

        // Her siparişe 0-2 sevkiyat
        foreach ($this->orders as $order) {
            $shipmentCount = rand(0, 2);

            for ($i = 0; $i < $shipmentCount; $i++) {
                $status = $shipmentStatuses[array_rand($shipmentStatuses)];
                $pickupDate = $order->created_at->copy()->addHours(rand(12, 72));
                $deliveryDate = null;

                if ($status === 'delivered') {
                    // %85 zamanında teslimat
                    $onTime = rand(1, 100) <= 85;
                    $deliveryDate = $onTime
                        ? $pickupDate->copy()->addHours(rand(6, 48))
                        : $pickupDate->copy()->addHours(rand(50, 120));
                }

                $this->shipments[] = Shipment::factory()->create([
                    'order_id' => $order->id,
                    'vehicle_id' => $this->vehicles[array_rand($this->vehicles)]->id,
                    'driver_id' => $this->employees[array_rand($this->employees)]->id,
                    'status' => $status,
                    'pickup_date' => $pickupDate,
                    'delivery_date' => $deliveryDate,
                    'created_at' => $order->created_at,
                ]);
            }
        }
    }

    private function createDocuments(): void
    {
        if ($this->command) {
            $this->command->info('📄 Belgeler oluşturuluyor...');
        }

        $documentTypes = ['invoice', 'waybill', 'contract', 'other'];

        // Her siparişe 1-3 belge
        foreach ($this->orders as $order) {
            $docCount = rand(1, 3);

            for ($i = 0; $i < $docCount; $i++) {
                Document::factory()->create([
                    'documentable_type' => Order::class,
                    'documentable_id' => $order->id,
                    'category' => $documentTypes[array_rand($documentTypes)],
                    'uploaded_by' => $this->users[array_rand($this->users)]->id ?? null,
                    'created_at' => $order->created_at->copy()->addHours(rand(1, 48)),
                ]);
            }
        }
    }

    private function createPayments(): void
    {
        if ($this->command) {
            $this->command->info('💰 Ödemeler oluşturuluyor...');
        }

        // Faturalanmış siparişler için gelir ödemeleri
        foreach ($this->orders as $order) {
            if ($order->status === 'invoiced' && $order->freight_price) {
                Payment::factory()->create([
                    'payment_type' => 'incoming',
                    'related_type' => Order::class,
                    'related_id' => $order->id,
                    'amount' => $order->freight_price,
                    'status' => rand(0, 1),
                    'paid_date' => $order->delivered_at ? $order->delivered_at->copy()->addDays(rand(7, 30)) : null,
                    'due_date' => $order->delivered_at ? $order->delivered_at->copy()->addDays(rand(15, 45)) : null,
                    'created_at' => $order->created_at,
                ]);
            }
        }

        // Rastgele gider ödemeleri
        for ($i = 0; $i < 100; $i++) {
            Payment::factory()->create([
                'payment_type' => 'outgoing',
                'amount' => rand(1000, 25000),
                'status' => 1,
                'paid_date' => Carbon::now()->subDays(rand(0, 180)),
                'created_at' => Carbon::now()->subDays(rand(0, 180)),
            ]);
        }
    }

    private function createFuelPrices(): void
    {
        if ($this->command) {
            $this->command->info('⛽ Motorin fiyatları oluşturuluyor...');
        }

        $basePrice = 35.50;

        for ($i = 0; $i < 90; $i++) {
            $date = Carbon::now()->subDays($i);
            $variation = rand(-200, 200) / 100;

            DB::table('fuel_prices')->insert([
                'company_id' => $this->company->id,
                'price_date' => $date,
                'price_type' => 'motorin',
                'price' => $basePrice + $variation,
                'supplier_name' => 'TP',
                'region' => 'İstanbul',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function createCalendarEvents(): void
    {
        if ($this->command) {
            $this->command->info('📅 Takvim etkinlikleri oluşturuluyor...');
        }

        $eventTypes = ['meeting', 'maintenance', 'delivery', 'document', 'payment'];
        $statuses = ['pending', 'completed', 'overdue', 'cancelled'];
        $priorities = ['low', 'medium', 'high'];

        for ($i = 0; $i < 30; $i++) {
            $startDate = Carbon::now()->addDays(rand(-15, 45));
            $endDate = $startDate->copy()->addHours(rand(1, 8));

            DB::table('calendar_events')->insert([
                'title' => fake()->sentence(3),
                'description' => fake()->optional()->sentence(),
                'event_type' => $eventTypes[array_rand($eventTypes)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'start_time' => $startDate->format('H:i:s'),
                'end_time' => $endDate->format('H:i:s'),
                'is_all_day' => 0,
                'priority' => $priorities[array_rand($priorities)],
                'status' => $statuses[array_rand($statuses)],
                'color' => '#'.substr(md5(rand()), 0, 6),
                'company_id' => $this->company->id,
                'created_by' => $this->users[array_rand($this->users)]->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createNotifications(): void
    {
        if ($this->command) {
            $this->command->info('🔔 Bildirimler oluşturuluyor...');
        }

        foreach ($this->users as $user) {
            for ($i = 0; $i < rand(3, 10); $i++) {
                DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\OrderStatusChanged',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => fake()->sentence(3),
                        'message' => fake()->sentence(),
                        'url' => '/admin/orders/'.rand(1, 100),
                    ]),
                    'read_at' => rand(0, 1) ? now() : null,
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

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
