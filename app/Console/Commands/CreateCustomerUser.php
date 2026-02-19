<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateCustomerUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:create-user 
                            {email : Kullanıcı email adresi}
                            {--name= : Müşteri adı}
                            {--password= : Şifre (boşsa otomatik oluşturulur)}
                            {--tax-number= : Vergi numarası}
                            {--role=customer : Müşteri rolü (customer, customer_user, customer_viewer)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Müşteri portalı için kullanıcı ve müşteri kaydı oluşturur';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?? $this->ask('Müşteri adı');
        $password = $this->option('password') ?? $this->secret('Şifre (boş bırakırsanız otomatik oluşturulur)');

        // Kullanıcı zaten var mı kontrol et
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Şifre oluştur
            if (empty($password)) {
                $password = \Illuminate\Support\Str::random(12);
                $this->info("Otomatik oluşturulan şifre: {$password}");
            }

            // Kullanıcı oluştur
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $email,
                'password' => Hash::make($password),
                'status' => 1,
            ]);

            $this->info("Kullanıcı oluşturuldu: {$user->email}");
        } else {
            $this->info("Kullanıcı zaten mevcut: {$user->email}");
        }

        // Müşteri rolünü belirle
        $roleName = $this->option('role');
        $allowedRoles = ['customer', 'customer_user', 'customer_viewer'];

        if (! in_array($roleName, $allowedRoles, true)) {
            $this->error("Geçersiz rol: {$roleName}");
            $this->info('Geçerli roller: '.implode(', ', $allowedRoles));

            return Command::FAILURE;
        }

        // Rol bilgileri
        $roleDescriptions = [
            'customer' => 'Müşteri - Tam erişim',
            'customer_user' => 'Müşteri Kullanıcısı - Sipariş görüntüleme ve oluşturma',
            'customer_viewer' => 'Müşteri Görüntüleyici - Sadece görüntüleme',
        ];

        // Customer rolünü bul veya oluştur
        $customerRole = CustomRole::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => $roleDescriptions[$roleName] ?? 'Müşteri', 'description' => $roleDescriptions[$roleName] ?? 'Müşteri portalı kullanıcısı']
        );

        // Eski müşteri rollerini kaldır
        foreach ($allowedRoles as $oldRole) {
            $oldRoleModel = CustomRole::where('name', $oldRole)->first();
            if ($oldRoleModel && $user->roles()->where('name', $oldRole)->exists()) {
                $user->roles()->detach($oldRoleModel->id);
            }
        }

        // Kullanıcıya yeni rolü ata
        if (! $user->roles()->where('name', $roleName)->exists()) {
            $user->roles()->attach($customerRole->id);
            $this->info("✅ {$roleName} rolü atandı ({$roleDescriptions[$roleName]}).");
        } else {
            $this->info("Kullanıcı zaten {$roleName} rolüne sahip.");
        }

        // Tax number oluştur (verilmemişse)
        $taxNumber = $this->option('tax-number');
        if (empty($taxNumber)) {
            // Benzersiz bir tax number oluştur
            do {
                $taxNumber = 'T'.str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            } while (Customer::where('tax_number', $taxNumber)->exists());
        }

        // Customer kaydı oluştur veya güncelle
        $customer = Customer::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'tax_number' => $taxNumber,
                'status' => 1,
            ]
        );

        $this->info("Müşteri kaydı oluşturuldu/güncellendi: {$customer->name}");

        // Permission'ların atandığından emin ol
        $permissionCount = $customerRole->permissions()->count();
        if ($permissionCount === 0) {
            $this->warn('Customer rolüne permission atanmamış! RolePermissionSeeder çalıştırılıyor...');
            $this->call('db:seed', ['--class' => 'RolePermissionSeeder', '--no-interaction' => true]);
            $permissionCount = $customerRole->fresh()->permissions()->count();
            $this->info("✅ Customer rolüne {$permissionCount} permission atandı.");
        }

        $this->newLine();
        $this->info('✅ Müşteri portalı kullanıcısı hazır!');
        $this->info("📧 Email: {$email}");
        if (empty($this->option('password'))) {
            $this->warn("🔑 Şifre: {$password}");
        }
        $this->info('🌐 Giriş URL: '.route('login'));
        $this->newLine();
        $this->info("💡 Not: Kullanıcı {$roleName} rolüne sahip ve bu role ait permission'lara erişebilir.");

        return Command::SUCCESS;
    }
}
