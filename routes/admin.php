<?php

use App\Analytics\Controllers\Web\AnalyticsController;
use App\BusinessPartner\Controllers\Web\BusinessPartnerController;
use App\Core\Services\CalendarService;
use App\Core\Services\ExportService;
use App\Customer\Controllers\Web\CustomerController;
use App\Delivery\Controllers\Web\DeliveryImportController;
use App\Document\Controllers\Web\DocumentController;
use App\DocumentFlow\Controllers\Web\DocumentFlowController;
use App\Employee\Controllers\Web\AdvanceController;
use App\Employee\Controllers\Web\EmployeeController;
use App\Employee\Controllers\Web\LeaveController;
use App\Employee\Controllers\Web\PayrollController;
use App\Employee\Controllers\Web\PersonnelAttendanceController;
use App\Finance\Controllers\Web\PaymentController;
use App\Finance\Services\FinanceDashboardService;
use App\FuelPrice\Controllers\Web\FuelPriceController;
use App\Notification\Controllers\Web\NotificationController;
use App\Order\Controllers\Web\OrderController;
use App\Order\Services\OperationsPerformanceService;
use App\Pricing\Controllers\Web\PricingConditionController;
use App\Shift\Controllers\Web\ShiftController;
use App\Shipment\Controllers\Web\ShipmentController;
use App\Vehicle\Controllers\Web\VehicleController;
use App\Warehouse\Controllers\Web\WarehouseController;
use App\WorkOrder\Controllers\Web\WorkOrderController;
use App\Http\Controllers\Admin\CalendarController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.company'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        // Müşteri büyüme istatistiği
        $now = now();
        $thisMonth = $now->month;
        $thisYear = $now->year;
        $lastMonthDate = $now->copy()->subMonth();
        $lastMonth = $lastMonthDate->month;
        $lastMonthYear = $lastMonthDate->year;

        $customerMonthlyStats = DB::table('customers')
            ->selectRaw('
                COUNT(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 END) as this_month,
                COUNT(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 END) as last_month
            ', [$thisMonth, $thisYear, $lastMonth, $lastMonthYear])
            ->first();

        $customersThisMonth = (int) ($customerMonthlyStats->this_month ?? 0);
        $customersLastMonth = (int) ($customerMonthlyStats->last_month ?? 0);
        $customersChangePercent = $customersLastMonth > 0
            ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100)
            : ($customersThisMonth > 0 ? 100 : 0);

        // Order istatistiklerini tek sorguda topla
        $orderStats = DB::table('orders')->selectRaw("
            COUNT(CASE WHEN status != 'cancelled' THEN 1 END) as orders_count,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as orders_pending,
            COUNT(CASE WHEN status = 'delivered' THEN 1 END) as orders_delivered,
            COUNT(CASE WHEN status = 'invoiced' THEN 1 END) as invoiced_orders
        ")->first();

        // Shipment istatistiklerini tek sorguda topla
        $shipmentStats = DB::table('shipments')->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status IN ('pending', 'in_transit') THEN 1 END) as active
        ")->first();

        $stats = [
            'orders_count' => (int) ($orderStats->orders_count ?? 0),
            'orders_pending' => (int) ($orderStats->orders_pending ?? 0),
            'orders_delivered' => (int) ($orderStats->orders_delivered ?? 0),
            'invoiced_orders' => (int) ($orderStats->invoiced_orders ?? 0),
            'shipments_count' => (int) ($shipmentStats->total ?? 0),
            'shipments_active' => (int) ($shipmentStats->active ?? 0),
            'vehicles_count' => \App\Models\Vehicle::where('status', 1)->count(),
            'vehicles_active' => \App\Models\Vehicle::where('status', 1)
                ->whereNotIn('id', \App\Models\Shipment::whereIn('status', ['pending', 'in_transit'])->select('vehicle_id'))
                ->count(),
            'employees_count' => \App\Models\Employee::where('status', 1)->count(),
            'customers_count' => \App\Models\Customer::where('status', 1)->count(),
            'customers_change_percent' => $customersChangePercent,
            'warehouses_count' => \App\Models\Warehouse::count(),
        ];

        // SAP istatistiklerini tek sorguda topla
        $sapStats = DB::table('sap_documents')->selectRaw("
            COUNT(CASE WHEN sync_status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN sync_status = 'synced' THEN 1 END) as synced,
            COUNT(CASE WHEN sync_status = 'error' THEN 1 END) as errors
        ")->first();

        $bpCount = \App\BusinessPartner\Models\BusinessPartner::where('status', 1)->count();

        $activePricing = \App\Pricing\Models\PricingCondition::where('status', 1)
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            })
            ->count();

        // Son aktiviteleri çek
        $recentActivities = \App\Models\AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($log) {
                $actionLabels = [
                    'created' => 'Oluşturuldu',
                    'updated' => 'Güncellendi',
                    'deleted' => 'Silindi',
                ];

                $tableLabels = [
                    'orders' => 'Sipariş',
                    'shipments' => 'Sevkiyat',
                    'vehicles' => 'Araç',
                    'employees' => 'Personel',
                    'customers' => 'Müşteri',
                    'warehouses' => 'Depo',
                ];

                $icons = [
                    'orders' => 'shopping_cart',
                    'shipments' => 'local_shipping',
                    'vehicles' => 'directions_car',
                    'employees' => 'groups',
                    'customers' => 'person',
                    'warehouses' => 'warehouse',
                ];

                return [
                    'id' => $log->id,
                    'title' => ($tableLabels[$log->table_name] ?? ucfirst($log->table_name)).' '.($actionLabels[$log->action] ?? $log->action),
                    'description' => $log->user?->name ?? 'Sistem',
                    'time' => $log->created_at->diffForHumans(),
                    'icon' => $icons[$log->table_name] ?? 'notifications',
                    'color' => match ($log->action) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'primary',
                    },
                ];
            });

        // AI özetlerini çek (son 5 rapor)
        $aiReports = \App\Models\AiReport::query()
            ->latest('generated_at')
            ->limit(5)
            ->get();

        // Finans dashboard verileri
        $user = auth()->user();
        $company = $user->activeCompany();
        $financeService = app(FinanceDashboardService::class);
        $financeData = $financeService->getDashboardData($company);

        // Operasyon performans verileri
        $operationsService = app(OperationsPerformanceService::class);
        $operationsData = $operationsService->getPerformanceSummary();

        // Calendar upcoming events
        $calendarService = app(CalendarService::class);
        $upcomingEvents = $calendarService->getUpcomingEvents(7);

        // Critical stock alerts
        // TODO: Stok kontrolünü inventory_stocks tablosu üzerinden yap
        $criticalStocks = collect(); // Geçici olarak boş koleksiyon

        return view('admin.dashboard', compact(
            'stats',
            'recentActivities',
            'aiReports',
            'financeData',
            'operationsData',
            'sapStats',
            'bpCount',
            'activePricing',
            'upcomingEvents',
            'criticalStocks',
        ));
    })->name('dashboard');

    Route::get('/dashboard/export', function () {
        $user = auth()->user();
        $company = $user->activeCompany();
        $financeService = app(FinanceDashboardService::class);
        $financeData = $financeService->getDashboardData($company);
        $operationsService = app(OperationsPerformanceService::class);
        $operationsData = $operationsService->getPerformanceSummary();

        $headers = ['Metrik', 'Değer'];
        $rows = [
            ['Toplam Sipariş', \App\Models\Order::where('status', '!=', 'cancelled')->count()],
            ['Bekleyen Sipariş', \App\Models\Order::where('status', 'pending')->count()],
            ['Teslim Edilen Sipariş', \App\Models\Order::where('status', 'delivered')->count()],
            ['Toplam Sevkiyat', \App\Models\Shipment::count()],
            ['Aktif Sevkiyat', \App\Models\Shipment::whereIn('status', ['pending', 'in_transit'])->count()],
            ['Toplam Araç', \App\Models\Vehicle::where('status', 1)->count()],
            ['Aktif Müşteri', \App\Models\Customer::where('status', 1)->count()],
            ['Personel', \App\Models\Employee::where('status', 1)->count()],
            ['Depo', \App\Models\Warehouse::count()],
            ['Geciken Ödemeler (₺)', number_format($financeData['overdue']['total_amount'] ?? 0, 2, ',', '.')],
            ['Bugün Vadesi Gelen (₺)', number_format($financeData['due_today']['total_amount'] ?? 0, 2, ',', '.')],
            ['7 Gün İçinde (₺)', number_format($financeData['due_in_7_days']['total_amount'] ?? 0, 2, ',', '.')],
            ['Bu Ay Ödenen (₺)', number_format($financeData['paid_this_month']['total_amount'] ?? 0, 2, ',', '.')],
            ['Teslimat Performans Puanı', number_format($operationsData['delivery_performance_score'] ?? 0, 1)],
            ['Geciken Sipariş Oranı (%)', number_format($operationsData['delayed_order_rate']['rate'] ?? 0, 1)],
            ['Araç Doluluk Oranı (%)', number_format($operationsData['vehicle_utilization']['utilization_rate'] ?? 0, 1)],
            ['Ortalama Teslimat Süresi (saat)', $operationsData['average_delivery_time'] ?? '-'],
        ];

        return app(ExportService::class)->csv($headers, $rows, 'dashboard_ozet');
    })->name('dashboard.export');

    // Orders – import routes first (so "import" is not captured by resource {order})
    Route::middleware('permission:order.view')->get('orders/import', [OrderController::class, 'importForm'])->name('orders.import');
    Route::middleware('permission:order.view')->get('orders/import-template', [OrderController::class, 'importTemplate'])->name('orders.import-template');
    Route::middleware('permission:order.view')->post('orders/import', [OrderController::class, 'importStore'])->name('orders.import.store');
    Route::middleware('permission:order.view')->resource('orders', OrderController::class);
    // SAP uyumlu durum geçişi
    Route::middleware('permission:order.view')->post('orders/{order}/transition', [OrderController::class, 'transition'])->name('orders.transition');
    // Doküman Akışı
    Route::middleware('permission:order.view')->get('orders/{order}/document-flow', [DocumentFlowController::class, 'show'])->name('orders.document-flow');

    // Business Partners (SAP BP uyumu)
    Route::middleware('permission:customer.view')->resource('business-partners', BusinessPartnerController::class);

    // Pricing Conditions (SAP navlun fiyatlandırma)
    Route::middleware('permission:order.view')->resource('pricing-conditions', PricingConditionController::class)->except(['show']);

    // Customers
    Route::middleware('permission:customer.view')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/favorite-addresses', [CustomerController::class, 'storeFavoriteAddress'])->name('customers.favorite-addresses.store');
        Route::put('customers/{customer}/favorite-addresses/{favoriteAddress}', [CustomerController::class, 'updateFavoriteAddress'])->name('customers.favorite-addresses.update');
        Route::delete('customers/{customer}/favorite-addresses/{favoriteAddress}', [CustomerController::class, 'destroyFavoriteAddress'])->name('customers.favorite-addresses.destroy');
    });

    // Shipments
    Route::middleware('permission:shipment.view')->resource('shipments', ShipmentController::class);

    // Delivery Imports (Teslimat Raporları Excel Yükleme)
    Route::prefix('delivery-imports')->name('delivery-imports.')->group(function () {
        Route::get('/', [DeliveryImportController::class, 'index'])->name('index');
        Route::get('/create', [DeliveryImportController::class, 'create'])->name('create');
        Route::get('/template', [DeliveryImportController::class, 'downloadTemplate'])->name('template');
        Route::post('/', [DeliveryImportController::class, 'store'])->name('store');
        Route::get('/{batch}', [DeliveryImportController::class, 'show'])->name('show');
        Route::get('/{batch}/veri-analiz-raporu', [DeliveryImportController::class, 'veriAnalizRaporu'])->name('veri-analiz-raporu');
        Route::patch('/{batch}/invoice-status', [DeliveryImportController::class, 'updateInvoiceStatus'])->name('invoice-status.update');
        Route::patch('/{batch}/petrokok-route', [DeliveryImportController::class, 'updatePetrokokRoute'])->name('petrokok-route.update');
        Route::patch('/{batch}/klinker-overrides', [DeliveryImportController::class, 'updateKlinkerOverrides'])->name('klinker-overrides.update');
        Route::get('/{batch}/export', [DeliveryImportController::class, 'export'])->name('export');
        Route::get('/{batch}/download-original', [DeliveryImportController::class, 'downloadOriginal'])->name('download-original');
        Route::post('/{batch}/reprocess', [DeliveryImportController::class, 'reprocess'])->name('reprocess');
        Route::delete('/{batch}', [DeliveryImportController::class, 'destroy'])->name('destroy');
    });

    // Vehicles
    Route::middleware('permission:vehicle.view')->resource('vehicles', VehicleController::class);

    // Employees
    Route::middleware('permission:employee.view')->resource('employees', EmployeeController::class);

    // Leaves (İzin Yönetimi)
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
    });

    // Personnel Attendance (Puantaj / Yoklama)
    Route::prefix('personnel-attendance')->name('personnel_attendance.')->group(function () {
        Route::get('/', [PersonnelAttendanceController::class, 'index'])->name('index');
        Route::post('/', [PersonnelAttendanceController::class, 'store'])->name('store');
        Route::get('/api/table', [PersonnelAttendanceController::class, 'apiTable'])->name('api.table');
    });

    // Advances (Avans Yönetimi)
    Route::prefix('advances')->name('advances.')->group(function () {
        Route::get('/', [AdvanceController::class, 'index'])->name('index');
        Route::get('/create', [AdvanceController::class, 'create'])->name('create');
        Route::post('/', [AdvanceController::class, 'store'])->name('store');
        Route::post('/{advance}/approve', [AdvanceController::class, 'approve'])->name('approve');
    });

    // Payrolls (Bordro Yönetimi)
    Route::prefix('payrolls')->name('payrolls.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::get('/create', [PayrollController::class, 'create'])->name('create');
        Route::post('/', [PayrollController::class, 'store'])->name('store');
        Route::get('/{payroll}/pdf', [PayrollController::class, 'pdf'])->name('pdf');
        Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
    });

    // Warehouses
    Route::middleware('permission:warehouse.view')->resource('warehouses', WarehouseController::class);

    // Documents
    Route::middleware('permission:document.view')->resource('documents', DocumentController::class);

    // Payments (Finance)
    Route::middleware('permission:payment.view')->resource('payments', PaymentController::class);

    // Shifts
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::get('/templates', [ShiftController::class, 'templates'])->name('templates');
        Route::get('/planning', [ShiftController::class, 'planning'])->name('planning');
    });

    // Work Orders
    Route::resource('work-orders', WorkOrderController::class);

    // Fuel Prices (Motorin Fiyat Takibi)
    Route::middleware('permission:fuel_price.view')->resource('fuel-prices', FuelPriceController::class);

    // Notifications (Bildirim Yönetimi)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Users
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::get('/users/{user}/edit-roles', [\App\Http\Controllers\Admin\UserController::class, 'editRoles'])->name('users.edit-roles');
    Route::put('/users/{user}/roles', [\App\Http\Controllers\Admin\UserController::class, 'updateRoles'])->name('users.update-roles');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'show'])->name('settings.show');
    Route::put('/settings/password', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePassword'])->name('settings.password.update');

    // Companies
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CompanyController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CompanyController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CompanyController::class, 'store'])->name('store');
        Route::get('/select', [\App\Http\Controllers\Admin\CompanyController::class, 'select'])->name('select');
        Route::post('/switch', [\App\Http\Controllers\Admin\CompanyController::class, 'switch'])->name('switch');
        Route::get('/{company}/settings', [\App\Http\Controllers\Admin\CompanyController::class, 'settings'])->name('settings');
        Route::post('/{company}/digital-services/{service}/toggle', [\App\Http\Controllers\Admin\CompanyController::class, 'toggleDigitalService'])->name('digital-services.toggle');
        Route::post('/{company}/digital-services/{service}/close-request', [\App\Http\Controllers\Admin\CompanyController::class, 'requestDigitalServiceClose'])->name('digital-services.close-request');
        Route::put('/{company}/settings/general', [\App\Http\Controllers\Admin\CompanyController::class, 'updateGeneral'])->name('settings.general.update');
        Route::put('/{company}/settings/logo', [\App\Http\Controllers\Admin\CompanyController::class, 'updateLogo'])->name('settings.logo.update');
        Route::delete('/{company}/settings/logo', [\App\Http\Controllers\Admin\CompanyController::class, 'deleteLogo'])->name('settings.logo.delete');
        Route::put('/{company}/settings/stamp', [\App\Http\Controllers\Admin\CompanyController::class, 'updateStamp'])->name('settings.stamp.update');
        Route::put('/{company}/settings', [\App\Http\Controllers\Admin\CompanyController::class, 'updateSettings'])->name('settings.update');
        Route::post('/{company}/addresses', [\App\Http\Controllers\Admin\CompanyController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/{company}/addresses/{addressId}', [\App\Http\Controllers\Admin\CompanyController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/{company}/addresses/{addressId}', [\App\Http\Controllers\Admin\CompanyController::class, 'deleteAddress'])->name('addresses.delete');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/finance', [AnalyticsController::class, 'finance'])->name('finance');
        Route::get('/operations', [AnalyticsController::class, 'operations'])->name('operations');
        Route::get('/fleet', [AnalyticsController::class, 'fleet'])->name('fleet');
    });

    // Calendar
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/events', [CalendarController::class, 'getEvents'])->name('events');
        Route::post('/', [CalendarController::class, 'store'])->name('store');
        Route::get('/{event}', [CalendarController::class, 'show'])->name('show');
        Route::put('/{event}', [CalendarController::class, 'update'])->name('update');
        Route::delete('/{event}', [CalendarController::class, 'destroy'])->name('destroy');
    });
});
