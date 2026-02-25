<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CustomRoleSeeder::class,
            CustomPermissionSeeder::class,
            RolePermissionSeeder::class,
            LocationSeeder::class,
            TaxOfficeSeeder::class,
            CompanySeeder::class,
        ]);

        // Admin kullanıcı oluştur
        User::where('email', 'admin@logistics.com')->delete();

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@logistics.com',
            'password' => 'password', // Model otomatik hash'leyecek (casts: hashed)
            'status' => 1,
        ]);

        Log::info('seed_admin_created', [
            'id' => $admin->id,
            'email' => $admin->email,
            'status' => $admin->status,
        ]);

        // Admin rolünü ata
        $adminRole = \App\Models\CustomRole::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole->id);
        }

        // Admin kullanıcısına Ana Şirket'i ata
        $company = \App\Models\Company::where('name', 'Ana Şirket')->first();
        if ($company) {
            $admin->companies()->attach($company->id, [
                'role' => 'admin',
                'is_default' => true,
            ]);

            Log::info('admin_company_attached', [
                'user_id' => $admin->id,
                'company_id' => $company->id,
                'company_name' => $company->name,
            ]);
        }
    }
}
