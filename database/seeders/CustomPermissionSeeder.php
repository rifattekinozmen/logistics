<?php

namespace Database\Seeders;

use App\Models\CustomPermission;
use Illuminate\Database\Seeder;

class CustomPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Order permissions
            ['code' => 'order.view', 'description' => 'Sipariş görüntüleme'],
            ['code' => 'order.create', 'description' => 'Sipariş oluşturma'],
            ['code' => 'order.update', 'description' => 'Sipariş güncelleme'],
            ['code' => 'order.delete', 'description' => 'Sipariş silme'],

            // Customer permissions
            ['code' => 'customer.view', 'description' => 'Müşteri görüntüleme'],
            ['code' => 'customer.create', 'description' => 'Müşteri oluşturma'],
            ['code' => 'customer.update', 'description' => 'Müşteri güncelleme'],
            ['code' => 'customer.delete', 'description' => 'Müşteri silme'],

            // Shipment permissions
            ['code' => 'shipment.view', 'description' => 'Sevkiyat görüntüleme'],
            ['code' => 'shipment.create', 'description' => 'Sevkiyat oluşturma'],
            ['code' => 'shipment.update', 'description' => 'Sevkiyat güncelleme'],
            ['code' => 'shipment.delete', 'description' => 'Sevkiyat silme'],

            // Warehouse permissions
            ['code' => 'warehouse.view', 'description' => 'Depo görüntüleme'],
            ['code' => 'warehouse.create', 'description' => 'Depo kaydı oluşturma'],
            ['code' => 'warehouse.update', 'description' => 'Depo kaydı güncelleme'],
            ['code' => 'warehouse.delete', 'description' => 'Depo kaydı silme'],

            // Work order permissions
            ['code' => 'workorder.view', 'description' => 'İş emri görüntüleme'],
            ['code' => 'workorder.create', 'description' => 'İş emri oluşturma'],
            ['code' => 'workorder.update', 'description' => 'İş emri güncelleme'],
            ['code' => 'workorder.delete', 'description' => 'İş emri silme'],

            // Vehicle permissions
            ['code' => 'vehicle.view', 'description' => 'Araç görüntüleme'],
            ['code' => 'vehicle.create', 'description' => 'Araç oluşturma'],
            ['code' => 'vehicle.update', 'description' => 'Araç güncelleme'],
            ['code' => 'vehicle.delete', 'description' => 'Araç silme'],

            // Employee permissions
            ['code' => 'employee.view', 'description' => 'Personel görüntüleme'],
            ['code' => 'employee.create', 'description' => 'Personel oluşturma'],
            ['code' => 'employee.update', 'description' => 'Personel güncelleme'],
            ['code' => 'employee.delete', 'description' => 'Personel silme'],

            // Document permissions
            ['code' => 'document.view', 'description' => 'Belge görüntüleme'],
            ['code' => 'document.upload', 'description' => 'Belge yükleme'],
            ['code' => 'document.delete', 'description' => 'Belge silme'],

            // Payment permissions
            ['code' => 'payment.view', 'description' => 'Ödeme görüntüleme'],
            ['code' => 'payment.create', 'description' => 'Ödeme oluşturma'],
            ['code' => 'payment.update', 'description' => 'Ödeme güncelleme'],
            ['code' => 'payment.delete', 'description' => 'Ödeme silme'],

            // System management
            ['code' => 'company.manage', 'description' => 'Firma ve firma ayarlarını yönetme'],
            ['code' => 'user.manage', 'description' => 'Kullanıcılar ve rollerini yönetme'],
            ['code' => 'settings.manage', 'description' => 'Sistem ayarlarını yönetme'],

            // Customer Portal permissions
            ['code' => 'customer.portal.dashboard', 'description' => 'Müşteri portalı dashboard görüntüleme'],
            ['code' => 'customer.portal.orders.view', 'description' => 'Kendi siparişlerini görüntüleme'],
            ['code' => 'customer.portal.orders.create', 'description' => 'Sipariş oluşturma'],
            ['code' => 'customer.portal.documents.view', 'description' => 'Kendi belgelerini görüntüleme'],
            ['code' => 'customer.portal.documents.download', 'description' => 'Belge indirme'],
            ['code' => 'customer.portal.profile.view', 'description' => 'Profil görüntüleme'],
            ['code' => 'customer.portal.profile.update', 'description' => 'Profil güncelleme'],
            ['code' => 'customer.portal.payments.view', 'description' => 'Kendi ödemelerini görüntüleme'],
            ['code' => 'customer.portal.invoices.view', 'description' => 'Faturaları görüntüleme'],
            ['code' => 'customer.portal.invoices.download', 'description' => 'Fatura indirme'],
            ['code' => 'customer.portal.notifications.view', 'description' => 'Bildirimleri görüntüleme'],
            ['code' => 'customer.portal.orders.cancel', 'description' => 'Sipariş iptal etme'],
            ['code' => 'customer.portal.favorite-addresses.manage', 'description' => 'Favori adresler yönetimi'],
            ['code' => 'customer.portal.order-templates.manage', 'description' => 'Sipariş şablonları yönetimi'],
        ];

        foreach ($permissions as $permission) {
            CustomPermission::firstOrCreate(
                ['code' => $permission['code']],
                $permission
            );
        }
    }
}
