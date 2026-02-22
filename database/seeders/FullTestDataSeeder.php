<?php

namespace Database\Seeders;

use App\BusinessPartner\Models\BusinessPartner;
use App\Models\Advance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PersonnelAttendance;
use App\Models\Position;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use App\Pricing\Models\PricingCondition;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use const STR_PAD_LEFT;

class FullTestDataSeeder extends Seeder
{
    private $company;

    private $branch;

    private $users = [];

    private $customers = [];

    private $employees = [];

    private $drivers = [];

    private $vehicles = [];

    private $orders = [];

    private $businessPartners = [];

    private $employeeToPersonelMap = [];

    public function run(): void
    {
        if ($this->command) {
            $this->command->info('🚀 TÜM sayfalar için test verileri oluşturuluyor...');
        }

        DB::beginTransaction();

        try {
            $this->company = Company::where('name', 'Ana Şirket')->first();

            if (! $this->company) {
                if ($this->command) {
                    $this->command->error('Ana Şirket bulunamadı.');
                }

                return;
            }

            $this->createBasicData();
            $this->createBusinessPartners();
            $this->createPricingConditions();
            $this->createOrdersAndShipments();
            $this->createPayments();
            $this->createWorkOrders();
            $this->createHRData();
            $this->createFuelPrices();
            $this->createCalendarEvents();

            DB::commit();

            $this->showSummary();
        } catch (Exception $e) {
            DB::rollBack();
            if ($this->command) {
                $this->command->error('Hata: '.$e->getMessage());
            }
            throw $e;
        }
    }

    private function createBasicData(): void
    {
        if ($this->command) {
            $this->command->info('📍 Temel veriler oluşturuluyor...');
        }

        // Şube
        $this->branch = Branch::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Merkez Şube',
        ]);

        // Departmanlar ve Pozisyonlar
        $departments = [
            'Lojistik' => ['Müdür', 'Koordinatör', 'Uzman'],
            'Operasyon' => ['Müdür', 'Planlama Uzmanı', 'Koordinatör'],
            'Satış' => ['Müdür', 'Temsilci'],
            'Finans' => ['Müdür', 'Muhasebeci'],
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

        // Personel
        $positions = Position::all();

        // 25 Sürücü
        for ($i = 0; $i < 25; $i++) {
            $employee = Employee::factory()->create([
                'branch_id' => $this->branch->id,
                'position_id' => $positions->random()->id,
                'status' => 1,
            ]);
            $this->employees[] = $employee;
            $this->drivers[] = $employee;
        }

        // 15 Ofis personeli
        for ($i = 0; $i < 15; $i++) {
            $employee = Employee::factory()->create([
                'branch_id' => $this->branch->id,
                'position_id' => $positions->random()->id,
                'status' => 1,
            ]);
            $this->employees[] = $employee;
        }

        // Her employee için personel kaydı oluştur
        foreach ($this->employees as $employee) {
            $personelId = DB::table('personels')->insertGetId([
                'ad_soyad' => $employee->first_name.' '.$employee->last_name,
                'email' => $employee->email,
                'telefon' => $employee->phone,
                'departman' => 'Lojistik',
                'pozisyon' => 'Çalışan',
                'ise_baslama_tarihi' => $employee->hire_date,
                'maas' => $employee->salary,
                'aktif' => 1,
                'tckn' => fake()->numerify('###########'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->employeeToPersonelMap[$employee->id] = $personelId;
        }

        // Kullanıcılar
        for ($i = 0; $i < 8; $i++) {
            $user = User::factory()->create(['status' => 1]);
            $user->companies()->attach($this->company->id, [
                'role' => 'employee',
                'is_default' => true,
            ]);
            $this->users[] = $user;
        }

        // Müşteriler
        for ($i = 0; $i < 60; $i++) {
            $this->customers[] = Customer::factory()->create([
                'company_id' => $this->company->id,
                'status' => 1,
            ]);
        }

        // Depolar
        $cities = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Antalya', 'Adana'];
        foreach ($cities as $city) {
            Warehouse::factory()->create([
                'branch_id' => $this->branch->id,
                'name' => $city.' Merkez Depo',
            ]);
        }

        // Araçlar
        for ($i = 0; $i < 30; $i++) {
            $this->vehicles[] = Vehicle::factory()->create([
                'branch_id' => $this->branch->id,
                'vehicle_type' => $i % 4 === 0 ? 'van' : 'truck',
                'status' => 1,
            ]);
        }
    }

    private function createBusinessPartners(): void
    {
        if ($this->command) {
            $this->command->info('🤝 İş ortakları oluşturuluyor...');
        }

        $types = ['customer', 'vendor', 'carrier', 'both'];

        for ($i = 0; $i < 40; $i++) {
            $this->businessPartners[] = BusinessPartner::create([
                'company_id' => $this->company->id,
                'partner_number' => 'BP-'.str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'partner_type' => $types[array_rand($types)],
                'name' => fake()->company(),
                'short_name' => fake()->companySuffix(),
                'tax_number' => fake()->numerify('##########'),
                'tax_office' => fake()->city(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->companyEmail(),
                'address' => fake()->address(),
                'currency' => 'TRY',
                'payment_terms' => rand(15, 60),
                'credit_limit' => rand(10000, 500000),
                'status' => 1,
            ]);
        }
    }

    private function createPricingConditions(): void
    {
        if ($this->command) {
            $this->command->info('💵 Fiyatlandırma koşulları oluşturuluyor...');
        }

        $routes = [
            ['İstanbul', 'Ankara'],
            ['İstanbul', 'İzmir'],
            ['Ankara', 'İzmir'],
            ['İstanbul', 'Bursa'],
            ['İstanbul', 'Antalya'],
            ['Ankara', 'Bursa'],
            ['İzmir', 'Antalya'],
            ['Bursa', 'Antalya'],
        ];

        foreach ($routes as $index => $route) {
            PricingCondition::create([
                'company_id' => $this->company->id,
                'condition_type' => 'route_based',
                'name' => $route[0].' - '.$route[1].' Navlun',
                'route_origin' => $route[0],
                'route_destination' => $route[1],
                'weight_from' => 0,
                'weight_to' => 10000,
                'flat_rate' => rand(3000, 15000),
                'min_charge' => rand(1000, 3000),
                'currency' => 'TRY',
                'vehicle_type' => 'truck',
                'valid_from' => Carbon::now()->subDays(30),
                'valid_to' => Carbon::now()->addDays(90),
                'status' => 1,
            ]);
        }
    }

    private function createOrdersAndShipments(): void
    {
        if ($this->command) {
            $this->command->info('📦 Siparişler ve sevkiyatlar oluşturuluyor...');
        }

        $statuses = ['pending', 'assigned', 'in_transit', 'delivered', 'invoiced', 'cancelled'];
        $weights = [5, 10, 15, 35, 30, 5];

        for ($i = 0; $i < 250; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 180));
            $status = $this->weightedRandom($statuses, $weights);

            $deliveredAt = null;
            if (in_array($status, ['delivered', 'invoiced'], true)) {
                $deliveredAt = $createdAt->copy()->addHours(rand(24, 168));
            }

            $order = Order::factory()->create([
                'company_id' => $this->company->id,
                'customer_id' => $this->customers[array_rand($this->customers)]->id,
                'status' => $status,
                'freight_price' => rand(3000, 90000),
                'created_at' => $createdAt,
                'delivered_at' => $deliveredAt,
                'created_by' => $this->users[array_rand($this->users)]->id ?? null,
            ]);

            $this->orders[] = $order;

            // Sevkiyatlar
            $shipmentCount = rand(0, 2);
            for ($j = 0; $j < $shipmentCount; $j++) {
                $shipmentStatus = match ($status) {
                    'pending' => 'pending',
                    'assigned' => 'assigned',
                    'in_transit' => 'in_transit',
                    'delivered', 'invoiced' => 'delivered',
                    default => 'pending',
                };

                $pickupDate = $order->created_at->copy()->addHours(rand(12, 72));
                $deliveryDate = $shipmentStatus === 'delivered'
                    ? $pickupDate->copy()->addHours(rand(6, 120))
                    : null;

                Shipment::factory()->create([
                    'order_id' => $order->id,
                    'vehicle_id' => $this->vehicles[array_rand($this->vehicles)]->id,
                    'driver_id' => $this->drivers[array_rand($this->drivers)]->id,
                    'status' => $shipmentStatus,
                    'pickup_date' => $pickupDate,
                    'delivery_date' => $deliveryDate,
                    'created_at' => $order->created_at,
                ]);
            }
        }
    }

    private function createPayments(): void
    {
        if ($this->command) {
            $this->command->info('💰 Ödemeler oluşturuluyor...');
        }

        // Faturalanmış siparişler için gelir
        foreach ($this->orders as $order) {
            if ($order->status === 'invoiced' && $order->freight_price) {
                Payment::factory()->create([
                    'payment_type' => 'incoming',
                    'related_type' => Order::class,
                    'related_id' => $order->id,
                    'amount' => $order->freight_price,
                    'status' => rand(0, 1),
                    'paid_date' => $order->delivered_at ? $order->delivered_at->copy()->addDays(rand(7, 30)) : null,
                    'due_date' => $order->delivered_at ? $order->delivered_at->copy()->addDays(30) : null,
                    'created_at' => $order->created_at,
                ]);
            }
        }

        // Gider ödemeleri
        for ($i = 0; $i < 120; $i++) {
            Payment::factory()->create([
                'payment_type' => 'outgoing',
                'amount' => rand(1000, 30000),
                'status' => 1,
                'paid_date' => Carbon::now()->subDays(rand(0, 180)),
                'created_at' => Carbon::now()->subDays(rand(0, 180)),
            ]);
        }
    }

    private function createWorkOrders(): void
    {
        if ($this->command) {
            $this->command->info('🔧 İş emirleri oluşturuluyor...');
        }

        $types = ['maintenance', 'repair', 'inspection', 'service'];
        $statuses = ['pending', 'approved', 'in_progress', 'completed'];
        $counter = 1;

        foreach ($this->vehicles as $vehicle) {
            // Her araca 1-3 iş emri
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $status = $statuses[array_rand($statuses)];
                $startedAt = in_array($status, ['in_progress', 'completed'], true)
                    ? Carbon::now()->subDays(rand(1, 30))
                    : null;
                $completedAt = $status === 'completed'
                    ? ($startedAt ? $startedAt->copy()->addHours(rand(2, 48)) : null)
                    : null;

                WorkOrder::create([
                    'work_order_number' => 'WO-'.str_pad($counter++, 6, '0', STR_PAD_LEFT),
                    'vehicle_id' => $vehicle->id,
                    'work_order_type' => $types[array_rand($types)],
                    'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                    'description' => fake()->sentence(),
                    'estimated_duration' => rand(2, 24),
                    'estimated_cost' => rand(500, 5000),
                    'actual_duration' => $completedAt ? rand(2, 30) : null,
                    'actual_cost' => $completedAt ? rand(500, 6000) : null,
                    'status' => $status,
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                ]);
            }
        }
    }

    private function createHRData(): void
    {
        if ($this->command) {
            $this->command->info('👥 İK verileri oluşturuluyor...');
        }

        $currentMonth = Carbon::now()->startOfMonth();

        // İzinler
        foreach ($this->employees as $employee) {
            if (rand(0, 100) < 30) {
                $startDate = Carbon::now()->addDays(rand(-15, 30));
                $totalDays = rand(1, 10);
                $endDate = $startDate->copy()->addDays($totalDays);

                Leave::create([
                    'employee_id' => $employee->id,
                    'leave_type' => ['annual', 'sick', 'unpaid'][array_rand(['annual', 'sick', 'unpaid'])],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => $totalDays,
                    'reason' => fake()->sentence(),
                    'status' => ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])],
                ]);
            }
        }

        // Puantaj (son 30 gün)
        $attendanceRecords = [];
        for ($day = 0; $day < 30; $day++) {
            $date = Carbon::now()->subDays($day);
            if ($date->isWeekday()) {
                foreach ($this->employees as $employee) {
                    if (rand(0, 100) < 90) {
                        $checkIn = $date->copy()->setTime(8, rand(0, 30));
                        $checkOut = $date->copy()->setTime(17, rand(0, 59));
                        $totalHours = $checkIn->diffInMinutes($checkOut) / 60;

                        $key = $employee->id.'_'.$date->format('Y-m-d');
                        if (! isset($attendanceRecords[$key]) && isset($this->employeeToPersonelMap[$employee->id])) {
                            PersonnelAttendance::create([
                                'employee_id' => $employee->id,
                                'personel_id' => $this->employeeToPersonelMap[$employee->id],
                                'attendance_date' => $date,
                                'attendance_type' => 'full_day',
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'total_hours' => round($totalHours, 2),
                                'overtime_hours' => max(0, round($totalHours - 9, 2)),
                            ]);
                            $attendanceRecords[$key] = true;
                        }
                    }
                }
            }
        }

        // Avanslar
        foreach ($this->employees as $employee) {
            if (rand(0, 100) < 40) {
                $status = ['pending', 'approved', 'paid', 'rejected'][array_rand(['pending', 'approved', 'paid', 'rejected'])];

                Advance::create([
                    'employee_id' => $employee->id,
                    'amount' => rand(1000, 5000),
                    'reason' => fake()->sentence(),
                    'requested_date' => Carbon::now()->subDays(rand(5, 30)),
                    'approved_at' => in_array($status, ['approved', 'paid'], true) ? Carbon::now()->subDays(rand(1, 20)) : null,
                    'payment_date' => $status === 'paid' ? Carbon::now()->subDays(rand(1, 15)) : null,
                    'status' => $status,
                ]);
            }
        }

        // Bordrolar (son 3 ay)
        $payrollCounter = 1;
        for ($month = 0; $month < 3; $month++) {
            $periodDate = $currentMonth->copy()->subMonths($month);
            $periodStart = $periodDate->copy()->startOfMonth();
            $periodEnd = $periodDate->copy()->endOfMonth();

            foreach ($this->employees as $employee) {
                $baseSalary = $employee->salary ?? rand(15000, 50000);
                $bonus = rand(0, 2000);
                $overtime = rand(0, 1000);
                $deduction = rand(500, 1500);
                $tax = $baseSalary * 0.15;
                $socialSecurity = $baseSalary * 0.14;
                $netSalary = $baseSalary + $bonus + $overtime - $deduction - $tax - $socialSecurity;

                Payroll::create([
                    'employee_id' => $employee->id,
                    'payroll_number' => 'PR-'.str_pad($payrollCounter++, 6, '0', STR_PAD_LEFT),
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'base_salary' => $baseSalary,
                    'overtime_amount' => $overtime,
                    'bonus' => $bonus,
                    'deduction' => $deduction,
                    'tax' => $tax,
                    'social_security' => $socialSecurity,
                    'net_salary' => $netSalary,
                    'payment_date' => $periodEnd->copy()->addDays(5),
                    'status' => 'paid',
                ]);
            }
        }
    }

    private function createFuelPrices(): void
    {
        if ($this->command) {
            $this->command->info('⛽ Motorin fiyatları oluşturuluyor...');
        }

        $basePrice = 36.50;
        for ($i = 0; $i < 120; $i++) {
            $date = Carbon::now()->subDays($i);
            DB::table('fuel_prices')->insert([
                'company_id' => $this->company->id,
                'price_date' => $date,
                'price_type' => 'motorin',
                'price' => $basePrice + (rand(-300, 300) / 100),
                'supplier_name' => ['TP', 'Opet', 'Shell', 'BP'][array_rand(['TP', 'Opet', 'Shell', 'BP'])],
                'region' => ['İstanbul', 'Ankara', 'İzmir'][array_rand(['İstanbul', 'Ankara', 'İzmir'])],
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

        for ($i = 0; $i < 50; $i++) {
            $startDate = Carbon::now()->addDays(rand(-30, 60));
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

    private function weightedRandom(array $values, array $weights): mixed
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        $cumulative = 0;

        foreach ($values as $index => $value) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $value;
            }
        }

        return $values[0];
    }

    private function showSummary(): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->info('');
        $this->command->info('✅ TÜM test verileri başarıyla oluşturuldu!');
        $this->command->newLine();

        $counts = [
            ['İş Ortakları', count($this->businessPartners)],
            ['Fiyatlandırma', PricingCondition::count()],
            ['Müşteriler', count($this->customers)],
            ['Siparişler', count($this->orders)],
            ['Sevkiyatlar', Shipment::count()],
            ['Araçlar', count($this->vehicles)],
            ['İş Emirleri', WorkOrder::count()],
            ['Personel', count($this->employees)],
            ['İzinler', Leave::count()],
            ['Puantaj Kayıtları', PersonnelAttendance::count()],
            ['Avanslar', Advance::count()],
            ['Bordrolar', Payroll::count()],
            ['Ödemeler', Payment::count()],
            ['Depolar', Warehouse::count()],
            ['Motorin Fiyatları', DB::table('fuel_prices')->count()],
            ['Takvim Etkinlikleri', DB::table('calendar_events')->count()],
        ];

        $this->command->table(['Kategori', 'Kayıt Sayısı'], $counts);
    }
}
