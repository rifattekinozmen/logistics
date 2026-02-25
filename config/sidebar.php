<?php

return [
    'admin' => [
        'top' => [
            [
                'route' => 'admin.dashboard',
                'label' => 'Dashboard',
                'icon' => 'dashboard',
                'pattern' => 'admin.dashboard',
            ],
        ],
        'groups' => [
            [
                'heading' => 'Müşteri & Sipariş',
                'items' => [
                    ['route' => 'admin.customers.index', 'label' => 'Müşteriler', 'icon' => 'people', 'pattern' => 'admin.customers.*', 'permission' => 'customer.view'],
                    [
                        'route' => 'admin.orders.index',
                        'label' => 'Siparişler',
                        'icon' => 'shopping_cart',
                        'pattern' => 'admin.orders.*',
                        'permission' => 'order.view',
                        'children' => [
                            ['params' => ['workflow' => 'waiting_payment'], 'label' => 'Ödeme Bekleyenler'],
                            ['params' => ['workflow' => 'preparing'], 'label' => 'Hazırlanan Siparişler'],
                            ['params' => ['workflow' => 'ready_for_shipment'], 'label' => 'Sevkiyata Hazır'],
                            ['params' => ['workflow' => 'delivered'], 'label' => 'Teslim Edilenler'],
                        ],
                    ],
                    ['route' => 'admin.business-partners.index', 'label' => 'İş Ortakları', 'icon' => 'handshake', 'pattern' => 'admin.business-partners.*', 'permission' => 'customer.view'],
                    ['route' => 'admin.pricing-conditions.index', 'label' => 'Fiyatlandırma', 'icon' => 'price_check', 'pattern' => 'admin.pricing-conditions.*', 'permission' => 'order.view'],
                ],
            ],
            [
                'heading' => 'Depo & Sevkiyat',
                'items' => [
                    ['route' => 'admin.warehouses.index', 'label' => 'Depo & Stok', 'icon' => 'warehouse', 'pattern' => 'admin.warehouses.*', 'permission' => 'warehouse.view'],
                    [
                        'route' => 'admin.shipments.index',
                        'label' => 'Sevkiyatlar',
                        'icon' => 'inventory_2',
                        'pattern' => 'admin.shipments.*',
                        'permission' => 'shipment.view',
                        'children' => [
                            ['params' => ['workflow' => 'planned'], 'label' => 'Planlanan'],
                            ['params' => ['workflow' => 'loading'], 'label' => 'Yüklemede'],
                            ['params' => ['workflow' => 'in_transit'], 'label' => 'Yolda Olanlar'],
                            ['params' => ['workflow' => 'delivered'], 'label' => 'Teslim Edilenler'],
                        ],
                    ],
                    ['route' => 'admin.delivery-imports.index', 'label' => 'Teslimat Raporları', 'icon' => 'upload_file', 'pattern' => 'admin.delivery-imports.*'],
                ],
            ],
            [
                'heading' => 'Finans & Belgeler',
                'items' => [
                    ['route' => 'admin.payments.index', 'label' => 'Finans', 'icon' => 'payments', 'pattern' => 'admin.payments.*', 'permission' => 'payment.view'],
                    ['route' => 'admin.documents.index', 'label' => 'Belgeler', 'icon' => 'description', 'pattern' => 'admin.documents.*', 'permission' => 'document.view'],
                ],
            ],
            [
                'heading' => 'Analitik & Raporlama',
                'items' => [
                    ['route' => 'admin.analytics.finance', 'label' => 'Finans Analitik', 'icon' => 'analytics', 'pattern' => 'admin.analytics.finance'],
                    ['route' => 'admin.analytics.operations', 'label' => 'Operasyon Analitik', 'icon' => 'donut_small', 'pattern' => 'admin.analytics.operations'],
                    ['route' => 'admin.analytics.fleet', 'label' => 'Filo Analitik', 'icon' => 'speed', 'pattern' => 'admin.analytics.fleet'],
                ],
            ],
            [
                'heading' => 'Filo Yönetimi',
                'items' => [
                    ['route' => 'admin.vehicles.index', 'label' => 'Araçlar', 'icon' => 'local_shipping', 'pattern' => 'admin.vehicles.*', 'permission' => 'vehicle.view'],
                    ['route' => 'admin.work-orders.index', 'label' => 'İş Emirleri & Bakım', 'icon' => 'build', 'pattern' => 'admin.work-orders.*'],
                    ['route' => 'admin.fuel-prices.index', 'label' => 'Motorin Fiyat', 'icon' => 'local_gas_station', 'pattern' => 'admin.fuel-prices.*', 'permission' => 'fuel_price.view'],
                ],
            ],
            [
                'heading' => 'Personel & İK',
                'items' => [
                    ['route' => 'admin.personnel.index', 'label' => 'Personel', 'icon' => 'groups', 'pattern' => 'admin.personnel.*', 'permission' => 'employee.view'],
                    ['route' => 'admin.shifts.index', 'label' => 'Vardiyalar', 'icon' => 'schedule', 'pattern' => 'admin.shifts.*'],
                    ['route' => 'admin.leaves.index', 'label' => 'İzinler', 'icon' => 'event_available', 'pattern' => 'admin.leaves.*'],
                    ['route' => 'admin.personnel_attendance.index', 'label' => 'Puantaj', 'icon' => 'calendar_month', 'pattern' => 'admin.personnel_attendance.*'],
                    ['route' => 'admin.advances.index', 'label' => 'Avanslar', 'icon' => 'account_balance_wallet', 'pattern' => 'admin.advances.*'],
                    ['route' => 'admin.payrolls.index', 'label' => 'Bordrolar', 'icon' => 'receipt_long', 'pattern' => 'admin.payrolls.*'],
                ],
            ],
            [
                'heading' => 'Sistem & Yönetimi',
                'items' => [
                    ['route' => 'admin.calendar.index', 'label' => 'Takvim', 'icon' => 'calendar_today', 'pattern' => 'admin.calendar.*'],
                    ['route' => 'admin.notifications.index', 'label' => 'Bildirimler', 'icon' => 'notifications', 'pattern' => 'admin.notifications.*', 'badge' => 'unread'],
                    ['route' => 'admin.companies.index', 'label' => 'Firmalar', 'icon' => 'business', 'pattern' => 'admin.companies.index|admin.companies.create'],
                    ['url_key' => 'settings', 'label' => 'Firma Ayarları', 'icon' => 'settings', 'pattern' => 'admin.companies.settings|admin.companies.select'],
                    ['route' => 'admin.profile.show', 'label' => 'Profil', 'icon' => 'person', 'pattern' => 'admin.profile.*'],
                    ['route' => 'admin.settings.show', 'label' => 'Ayarlar', 'icon' => 'settings', 'pattern' => 'admin.settings.*'],
                    ['route' => 'admin.users.index', 'label' => 'Kullanıcılar', 'icon' => 'group', 'pattern' => 'admin.users.*'],
                ],
            ],
        ],
        'customer_portal' => [
            'heading' => 'Müşteri',
            'route' => 'customer.dashboard',
            'label' => 'Müşteri Portalı',
            'icon' => 'store',
            'pattern' => 'customer.*',
            'role' => 'customer',
        ],
    ],
];
