<?php

use App\Customer\Controllers\Web\CustomerPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])
        ->middleware('permission:customer.portal.dashboard')
        ->name('dashboard');

    // Siparişler
    Route::get('/orders', [CustomerPortalController::class, 'orders'])
        ->middleware('permission:customer.portal.orders.view')
        ->name('orders.index');
    Route::get('/orders/create', [CustomerPortalController::class, 'createOrder'])
        ->middleware('permission:customer.portal.orders.create')
        ->name('orders.create');
    Route::post('/orders', [CustomerPortalController::class, 'storeOrder'])
        ->middleware('permission:customer.portal.orders.create')
        ->name('orders.store');
    Route::get('/orders/{order}', [CustomerPortalController::class, 'showOrder'])
        ->middleware('permission:customer.portal.orders.view')
        ->name('orders.show');
    Route::post('/orders/{order}/cancel', [CustomerPortalController::class, 'cancelOrder'])
        ->middleware('permission:customer.portal.orders.cancel')
        ->name('orders.cancel');

    // Belgeler
    Route::get('/documents', [CustomerPortalController::class, 'documents'])
        ->middleware('permission:customer.portal.documents.view')
        ->name('documents.index');
    Route::get('/documents/{document}/download', [CustomerPortalController::class, 'downloadDocument'])
        ->middleware('permission:customer.portal.documents.download')
        ->name('documents.download');

    // Profil
    Route::get('/profile', [CustomerPortalController::class, 'profile'])
        ->middleware('permission:customer.portal.profile.view')
        ->name('profile');
    Route::put('/profile', [CustomerPortalController::class, 'updateProfile'])
        ->middleware('permission:customer.portal.profile.update')
        ->name('profile.update');
    Route::post('/profile/change-password', [CustomerPortalController::class, 'changePassword'])
        ->middleware('permission:customer.portal.profile.update')
        ->name('profile.change-password');

    // Ödemeler
    Route::get('/payments', [CustomerPortalController::class, 'payments'])
        ->middleware('permission:customer.portal.payments.view')
        ->name('payments.index');
    Route::get('/payments/{payment}', [CustomerPortalController::class, 'showPayment'])
        ->middleware('permission:customer.portal.payments.view')
        ->name('payments.show');

    // Faturalar
    Route::get('/invoices', [CustomerPortalController::class, 'invoices'])
        ->middleware('permission:customer.portal.invoices.view')
        ->name('invoices.index');
    Route::get('/invoices/{document}/download', [CustomerPortalController::class, 'downloadInvoice'])
        ->middleware('permission:customer.portal.invoices.download')
        ->name('invoices.download');

    // Bildirimler
    Route::get('/notifications', [CustomerPortalController::class, 'notifications'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.index');
    Route::get('/notifications/{notification}', [CustomerPortalController::class, 'showNotification'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.show');
    Route::post('/notifications/{notification}/read', [CustomerPortalController::class, 'markNotificationAsRead'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [CustomerPortalController::class, 'markAllNotificationsAsRead'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.mark-all-read');

    // Favori Adresler
    Route::get('/favorite-addresses', [CustomerPortalController::class, 'favoriteAddresses'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.index');
    Route::post('/favorite-addresses', [CustomerPortalController::class, 'storeFavoriteAddress'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.store');
    Route::delete('/favorite-addresses/{favoriteAddress}', [CustomerPortalController::class, 'deleteFavoriteAddress'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.destroy');
    Route::get('/favorite-addresses/geocode', [CustomerPortalController::class, 'geocodeAddress'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.geocode');

    // Sipariş Şablonları
    Route::get('/order-templates', [CustomerPortalController::class, 'orderTemplates'])
        ->middleware('permission:customer.portal.order-templates.manage')
        ->name('order-templates.index');
    Route::post('/order-templates', [CustomerPortalController::class, 'storeOrderTemplate'])
        ->middleware('permission:customer.portal.order-templates.manage')
        ->name('order-templates.store');
    Route::post('/order-templates/{orderTemplate}/create-order', [CustomerPortalController::class, 'createOrderFromTemplate'])
        ->middleware('permission:customer.portal.orders.create')
        ->name('order-templates.create-order');
    Route::delete('/order-templates/{orderTemplate}', [CustomerPortalController::class, 'deleteOrderTemplate'])
        ->middleware('permission:customer.portal.order-templates.manage')
        ->name('order-templates.destroy');
});
