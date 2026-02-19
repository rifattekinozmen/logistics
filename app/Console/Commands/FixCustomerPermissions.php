<?php

namespace App\Console\Commands;

use App\Models\CustomRole;
use App\Models\User;
use Illuminate\Console\Command;

class FixCustomerPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:fix-permissions 
                            {--email= : Belirli bir kullanıcının email adresi (boşsa tüm müşteri kullanıcıları)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Müşteri kullanıcılarının müşteri rollerine sahip olduğundan ve permission\'ların atandığından emin olur';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email');

        // Tüm müşteri rollerini kontrol et
        $customerRoles = CustomRole::whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->get();

        if ($customerRoles->isEmpty()) {
            $this->error('Müşteri rolleri bulunamadı! Önce CustomRoleSeeder çalıştırın.');
            $this->info('Komut: php artisan db:seed --class=CustomRoleSeeder');

            return Command::FAILURE;
        }

        // Her rolün permission sayısını kontrol et
        foreach ($customerRoles as $role) {
            $permissionCount = $role->permissions()->count();
            $this->info("{$role->name} rolü {$permissionCount} permission'a sahip.");

            if ($permissionCount === 0) {
                $this->warn("{$role->name} rolüne hiç permission atanmamış!");
            }
        }

        // Permission'lar eksikse seed et
        $needsSeed = $customerRoles->some(fn ($role) => $role->permissions()->count() === 0);
        if ($needsSeed) {
            $this->info('RolePermissionSeeder çalıştırılıyor...');
            $this->call('db:seed', ['--class' => 'RolePermissionSeeder', '--no-interaction' => true]);
            $this->info("✅ Permission'lar atandı.");
        }

        // Kullanıcıları bul
        if ($email) {
            $users = User::where('email', $email)->get();
        } else {
            // Herhangi bir müşteri rolüne sahip tüm kullanıcıları bul
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['customer', 'customer_user', 'customer_viewer']);
            })->get();
        }

        if ($users->isEmpty()) {
            $this->warn('Müşteri rolüne sahip kullanıcı bulunamadı.');
            $this->info('Yeni müşteri kullanıcısı oluşturmak için: php artisan customer:create-user email@example.com');

            return Command::SUCCESS;
        }

        $this->info("{$users->count()} kullanıcı bulundu.");

        $fixed = 0;
        $alreadyOk = 0;

        foreach ($users as $user) {
            $userRoles = $user->roles()->whereIn('name', ['customer', 'customer_user', 'customer_viewer'])->pluck('name')->toArray();

            if (empty($userRoles)) {
                // Varsayılan olarak customer_user rolü ata
                $defaultRole = CustomRole::where('name', 'customer_user')->first();
                if ($defaultRole) {
                    $user->roles()->attach($defaultRole->id);
                    $this->info("✅ {$user->email} - customer_user rolü atandı (varsayılan).");
                    $fixed++;
                }
            } else {
                $this->line("✓ {$user->email} - Roller: ".implode(', ', $userRoles));
                $alreadyOk++;
            }
        }

        $this->newLine();
        $this->info('✅ İşlem tamamlandı!');
        $this->info("   - Düzeltilen: {$fixed}");
        $this->info("   - Zaten uygun: {$alreadyOk}");

        return Command::SUCCESS;
    }
}
