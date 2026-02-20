<?php

use App\Customer\Controllers\Api\V2\CustomerMobileController;
use App\Driver\Controllers\Api\DriverController;
use App\Driver\Controllers\Api\V2\DriverV2Controller;
use App\Employee\Controllers\Api\EmployeeController;
use App\Order\Controllers\Api\OrderController;
use App\Vehicle\Controllers\Api\VehicleController;
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

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Orders
    Route::apiResource('orders', OrderController::class);

    // Vehicles
    Route::apiResource('vehicles', VehicleController::class);

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
