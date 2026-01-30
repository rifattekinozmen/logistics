<?php

namespace Database\Seeders;

use App\Models\CustomPermission;
use App\Models\CustomRole;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            // Sistem yöneticisi - her şeye sahip
            'admin' => ['*'],

            // Firma yöneticisi - tüm iş modülleri + firma yönetimi
            'company_admin' => [
                'order.*',
                'customer.*',
                'shipment.*',
                'warehouse.*',
                'workorder.*',
                'vehicle.*',
                'employee.*',
                'document.*',
                'payment.*',
                'company.manage',
            ],

            // Operasyon sorumlusu
            'operation_manager' => [
                'order.*',
                'customer.*',
                'shipment.*',
                'workorder.*',
                'warehouse.view',
                'vehicle.view',
                'employee.view',
            ],

            // Planlama (dispatcher)
            'dispatcher' => [
                'order.view',
                'order.update',
                'customer.view',
                'shipment.view',
                'shipment.create',
                'shipment.update',
                'warehouse.view',
            ],

            // Depo sorumlusu
            'warehouse' => [
                'warehouse.view',
                'warehouse.create',
                'warehouse.update',
                'order.view',
                'shipment.view',
                'document.upload',
            ],

            // Şoför
            'driver' => [
                'shipment.view',
                'shipment.update',
                'document.upload',
            ],

            // Muhasebe
            'accounting' => [
                'payment.*',
                'document.view',
                'document.upload',
                'order.view',
                'shipment.view',
            ],

            // Sadece görüntüleme
            'read_only' => [
                'order.view',
                'customer.view',
                'shipment.view',
                'warehouse.view',
                'workorder.view',
                'vehicle.view',
                'employee.view',
                'document.view',
                'payment.view',
            ],

            // Müşteri - Müşteri portalı tam erişim (tüm yetkiler)
            'customer' => [
                'customer.portal.dashboard',
                'customer.portal.orders.view',
                'customer.portal.orders.create',
                'customer.portal.orders.cancel',
                'customer.portal.documents.view',
                'customer.portal.documents.download',
                'customer.portal.profile.view',
                'customer.portal.profile.update',
                'customer.portal.payments.view',
                'customer.portal.invoices.view',
                'customer.portal.invoices.download',
                'customer.portal.notifications.view',
                'customer.portal.favorite-addresses.manage',
                'customer.portal.order-templates.manage',
            ],

            // Müşteri Kullanıcısı - Sipariş görüntüleme ve oluşturma, belgeler, profil
            'customer_user' => [
                'customer.portal.dashboard',
                'customer.portal.orders.view',
                'customer.portal.orders.create',
                'customer.portal.documents.view',
                'customer.portal.documents.download',
                'customer.portal.profile.view',
                'customer.portal.profile.update',
                'customer.portal.payments.view',
                'customer.portal.invoices.view',
                'customer.portal.invoices.download',
                'customer.portal.notifications.view',
                'customer.portal.favorite-addresses.manage',
                'customer.portal.order-templates.manage',
            ],

            // Müşteri Görüntüleyici - Sadece görüntüleme yetkisi
            'customer_viewer' => [
                'customer.portal.dashboard',
                'customer.portal.orders.view',
                'customer.portal.documents.view',
                'customer.portal.profile.view',
                'customer.portal.payments.view',
                'customer.portal.invoices.view',
                'customer.portal.notifications.view',
                'customer.portal.favorite-addresses.manage',
                'customer.portal.order-templates.manage',
            ],
        ];

        $allPermissions = CustomPermission::all()->keyBy('code');

        foreach ($map as $roleName => $patterns) {
            $role = CustomRole::where('name', $roleName)->first();

            if (! $role) {
                continue;
            }

            $permissionIds = [];

            // Admin: tüm permission'lar
            if (in_array('*', $patterns, true)) {
                $permissionIds = $allPermissions->pluck('id')->all();
            } else {
                foreach ($patterns as $pattern) {
                    if (str_ends_with($pattern, '.*')) {
                        $prefix = substr($pattern, 0, -2);
                        $matched = $allPermissions->filter(
                            fn (CustomPermission $perm) => str_starts_with($perm->code, $prefix.'.')
                        );
                        $permissionIds = array_merge($permissionIds, $matched->pluck('id')->all());
                    } else {
                        $permission = $allPermissions->get($pattern);
                        if ($permission) {
                            $permissionIds[] = $permission->id;
                        }
                    }
                }
            }

            $permissionIds = array_values(array_unique($permissionIds));

            if ($permissionIds === []) {
                continue;
            }

            $role->permissions()->sync($permissionIds);
        }
    }
}

