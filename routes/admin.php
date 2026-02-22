<?php

use App\Analytics\Controllers\Web\AnalyticsController;
use App\BusinessPartner\Controllers\Web\BusinessPartnerController;
use App\Customer\Controllers\Web\CustomerController;
use App\Delivery\Controllers\Web\DeliveryImportController;
use App\Document\Controllers\Web\DocumentController;
use App\DocumentFlow\Controllers\Web\DocumentFlowController;
use App\Employee\Controllers\Web\AdvanceController;
use App\Employee\Controllers\Web\EmployeeController;
use App\Employee\Controllers\Web\LeaveController;
use App\Employee\Controllers\Web\PayrollController;
use App\Employee\Controllers\Web\PersonnelAttendanceController;
use App\Employee\Controllers\Web\PersonelController;
use App\Finance\Controllers\Web\PaymentController;
use App\FuelPrice\Controllers\Web\FuelPriceController;
use App\Admin\Controllers\Web\CalendarController;
use App\Notification\Controllers\Web\NotificationController;
use App\Order\Controllers\Web\OrderController;
use App\Pricing\Controllers\Web\PricingConditionController;
use App\Shift\Controllers\Web\ShiftController;
use App\Shipment\Controllers\Web\ShipmentController;
use App\Vehicle\Controllers\Web\VehicleController;
use App\Warehouse\Controllers\Web\WarehouseController;
use App\WorkOrder\Controllers\Web\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.company'])->prefix('admin')->name('admin.')->group(function () {
    require __DIR__.'/admin/dashboard.php';

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
    Route::middleware('permission:employee.view')->resource('personnel', PersonelController::class);

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
    Route::resource('users', \App\Admin\Controllers\Web\UserController::class);
    Route::get('/users/{user}/edit-roles', [\App\Admin\Controllers\Web\UserController::class, 'editRoles'])->name('users.edit-roles');
    Route::put('/users/{user}/roles', [\App\Admin\Controllers\Web\UserController::class, 'updateRoles'])->name('users.update-roles');

    // Profile
    Route::get('/profile', [\App\Admin\Controllers\Web\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [\App\Admin\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings', [\App\Admin\Controllers\Web\SettingsController::class, 'show'])->name('settings.show');
    Route::put('/settings/password', [\App\Admin\Controllers\Web\SettingsController::class, 'updatePassword'])->name('settings.password.update');

    // Companies
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [\App\Admin\Controllers\Web\CompanyController::class, 'index'])->name('index');
        Route::get('/create', [\App\Admin\Controllers\Web\CompanyController::class, 'create'])->name('create');
        Route::post('/', [\App\Admin\Controllers\Web\CompanyController::class, 'store'])->name('store');
        Route::get('/select', [\App\Admin\Controllers\Web\CompanyController::class, 'select'])->name('select');
        Route::post('/switch', [\App\Admin\Controllers\Web\CompanyController::class, 'switch'])->name('switch');
        Route::get('/{company}/settings', [\App\Admin\Controllers\Web\CompanyController::class, 'settings'])->name('settings');
        Route::post('/{company}/digital-services/{service}/toggle', [\App\Admin\Controllers\Web\CompanyController::class, 'toggleDigitalService'])->name('digital-services.toggle');
        Route::post('/{company}/digital-services/{service}/close-request', [\App\Admin\Controllers\Web\CompanyController::class, 'requestDigitalServiceClose'])->name('digital-services.close-request');
        Route::put('/{company}/settings/general', [\App\Admin\Controllers\Web\CompanyController::class, 'updateGeneral'])->name('settings.general.update');
        Route::put('/{company}/settings/logo', [\App\Admin\Controllers\Web\CompanyController::class, 'updateLogo'])->name('settings.logo.update');
        Route::delete('/{company}/settings/logo', [\App\Admin\Controllers\Web\CompanyController::class, 'deleteLogo'])->name('settings.logo.delete');
        Route::put('/{company}/settings/stamp', [\App\Admin\Controllers\Web\CompanyController::class, 'updateStamp'])->name('settings.stamp.update');
        Route::put('/{company}/settings', [\App\Admin\Controllers\Web\CompanyController::class, 'updateSettings'])->name('settings.update');
        Route::post('/{company}/addresses', [\App\Admin\Controllers\Web\CompanyController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/{company}/addresses/{addressId}', [\App\Admin\Controllers\Web\CompanyController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/{company}/addresses/{addressId}', [\App\Admin\Controllers\Web\CompanyController::class, 'deleteAddress'])->name('addresses.delete');
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
