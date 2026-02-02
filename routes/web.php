<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PersonelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/identity/form', function () {
    return view('identity.form');
})->name('identity.form');

Route::get('/identity/verification', function () {
    return view('identity.verification');
})->name('identity.verification');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('geocode/reverse', [\App\Http\Controllers\GeocodingController::class, 'reverse'])->name('geocode.reverse');

    Route::resource('personel', PersonelController::class);
});

// Admin routes
require __DIR__.'/admin.php';
