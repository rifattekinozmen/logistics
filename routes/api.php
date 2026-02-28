<?php

use App\Customer\Controllers\Api\V2\CustomerMobileController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Driver\Controllers\Api\DriverController;
use App\Driver\Controllers\Api\V2\DriverV2Controller;
use App\Employee\Controllers\Api\EmployeeController;
use App\Finance\Controllers\Api\PaymentCallbackController;
use App\Order\Controllers\Api\OrderController;
use App\Vehicle\Controllers\Api\VehicleController;
use App\Analytics\Controllers\Api\ReportingController;
use App\Vehicle\Controllers\Api\VehicleGpsController;
use App\Warehouse\Controllers\Api\BarcodeController;
use Illuminate\Support\Facades\Route;

// Public API routes for location data
Route::get('/cities', function (\Illuminate\Http\Request $request) {
    $query = \App\Models\City::where('is_active', true);

    if ($request->has('country_id')) {
        $query->where('country_id', $request->country_id);
    }

    return response()->json($query->orderBy('name_tr')->get(['id', 'name_tr', 'name_en']));
});

Route::get('/districts', function (\Illuminate\Http\Request $request) {
    $query = \App\Models\District::where('is_active', true);

    if ($request->has('city_id')) {
        $query->where('city_id', $request->city_id);
    }

    return response()->json($query->orderBy('name_tr')->get(['id', 'name_tr', 'name_en']));
});

Route::post('payment/callback', [PaymentCallbackController::class, 'handle'])
    ->name('api.payment.callback');

Route::post('login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    $user = User::where('email', $request->email)->first();
    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages(['email' => ['E-posta veya şifre hatalı.']]);
    }
    $token = $user->createToken('mobile')->plainTextToken;

    return response()->json(['token' => $token, 'user' => ['id' => $user->id, 'email' => $user->email, 'name' => $user->name]]);
})->name('api.login');

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Orders
    Route::apiResource('orders', OrderController::class);

    // Vehicles
    Route::apiResource('vehicles', VehicleController::class);

    // GPS (Faz 3 - real-time araç konumu placeholder)
    Route::prefix('gps')->name('gps.')->group(function () {
        Route::get('/positions', [VehicleGpsController::class, 'index'])->name('positions.index');
        Route::post('/positions', [VehicleGpsController::class, 'store'])->name('positions.store');
    });
    Route::get('/vehicles/{vehicle}/gps/latest', [VehicleGpsController::class, 'latest'])->name('vehicles.gps.latest');

    // Employees
    Route::apiResource('employees', EmployeeController::class);

    // Driver API (Mobil Saha)
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/shipments', [DriverController::class, 'shipments'])->name('shipments');
        Route::put('/shipments/{shipment}/status', [DriverController::class, 'updateShipmentStatus'])->name('shipments.update-status');
        Route::post('/shipments/{shipment}/pod', [DriverController::class, 'uploadPod'])->name('shipments.upload-pod');
        Route::post('/location', [DriverController::class, 'updateLocation'])->name('location');
    });

    // Warehouse Barcode API
    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::post('/barcode/scan', [BarcodeController::class, 'scan'])->name('barcode.scan');
        Route::post('/barcode/stock-in', [BarcodeController::class, 'stockIn'])->name('barcode.stock-in');
        Route::post('/barcode/stock-out', [BarcodeController::class, 'stockOut'])->name('barcode.stock-out');
        Route::post('/stock/transfer', [BarcodeController::class, 'transfer'])->name('stock.transfer');
        Route::get('/stock/alerts', [BarcodeController::class, 'criticalStockAlerts'])->name('stock.alerts');
    });

    // Reporting API (Faz 3 – BI / Power BI vb.; dakikada 10 istek)
    Route::prefix('reporting')->name('reporting.')->middleware('throttle:reporting')->group(function () {
        Route::get('/finance-summary', [ReportingController::class, 'financeSummary'])->name('finance-summary');
        Route::get('/fleet-utilization', [ReportingController::class, 'fleetUtilization'])->name('fleet-utilization');
        Route::get('/operations-kpi', [ReportingController::class, 'operationsKpi'])->name('operations-kpi');
    });
});

// API v2 - Enhanced mobile endpoints
Route::prefix('v2')->middleware(['auth:sanctum'])->group(function () {
    // Driver API v2
    Route::prefix('driver')->name('v2.driver.')->group(function () {
        Route::get('/dashboard', [DriverV2Controller::class, 'dashboard'])->name('dashboard');
        Route::post('/checkin', [DriverV2Controller::class, 'checkIn'])->name('checkin');
    });

    // Customer Mobile API v2
    Route::prefix('customer')->name('v2.customer.')->group(function () {
        Route::get('/orders/tracking', [CustomerMobileController::class, 'tracking'])->name('orders.tracking');
        Route::post('/orders/quick-create', [CustomerMobileController::class, 'quickCreate'])->name('orders.quick-create');
    });
});
