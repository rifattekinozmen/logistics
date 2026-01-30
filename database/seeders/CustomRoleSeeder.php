<?php

namespace Database\Seeders;

use App\Models\CustomRole;
use Illuminate\Database\Seeder;

class CustomRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Sistem yöneticisi - Tüm modüllerde tam yetki'],
            ['name' => 'company_admin', 'description' => 'Firma yöneticisi - Kendi firmasında tam yetki'],
            ['name' => 'operation_manager', 'description' => 'Operasyon sorumlusu - Sipariş, sevkiyat ve iş emirleri yönetimi'],
            ['name' => 'dispatcher', 'description' => 'Planlama sorumlusu - Sipariş ve sevkiyat planlama'],
            ['name' => 'warehouse', 'description' => 'Depo sorumlusu - Depo ve stok yönetimi'],
            ['name' => 'driver', 'description' => 'Şoför - Kendine atanan sevkiyatları yönetme'],
            ['name' => 'accounting', 'description' => 'Muhasebe sorumlusu - Finans ve ödeme yönetimi'],
            ['name' => 'read_only', 'description' => 'Sadece görüntüleme yetkisi olan kullanıcı'],
            ['name' => 'customer', 'description' => 'Müşteri - Müşteri portalı tam erişim (tüm yetkiler)'],
            ['name' => 'customer_user', 'description' => 'Müşteri Kullanıcısı - Sipariş görüntüleme ve oluşturma'],
            ['name' => 'customer_viewer', 'description' => 'Müşteri Görüntüleyici - Sadece görüntüleme yetkisi'],
        ];

        foreach ($roles as $role) {
            CustomRole::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
