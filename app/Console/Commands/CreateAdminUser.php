<?php

namespace App\Console\Commands;

use App\Models\CustomRole;
use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin';

    protected $description = 'Create admin user';

    public function handle(): int
    {
        // Mevcut admin kullanıcıyı sil (soft delete dahil)
        User::withTrashed()->where('email', 'admin@logistics.com')->forceDelete();

        // Yeni admin kullanıcı oluştur
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@logistics.com',
            'password' => 'password', // Model otomatik hash'leyecek
            'status' => 1,
            'email_verified_at' => now(),
        ]);

        $this->info("Admin kullanıcı oluşturuldu: {$admin->email}");

        // Admin rolünü ata
        $adminRole = CustomRole::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole->id);
            $this->info("Admin rolü atandı.");
        } else {
            $this->warn("Admin rolü bulunamadı. Önce seeder'ı çalıştırın.");
        }

        return Command::SUCCESS;
    }
}
