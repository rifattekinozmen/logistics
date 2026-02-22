<?php

use App\Customer\Controllers\Web\DashboardController;
use App\Customer\Controllers\Web\DocumentController;
use App\Customer\Controllers\Web\FavoriteAddressController;
use App\Customer\Controllers\Web\InvoiceController;
use App\Customer\Controllers\Web\NotificationController;
use App\Customer\Controllers\Web\OrderController;
use App\Customer\Controllers\Web\OrderTemplateController;
use App\Customer\Controllers\Web\PaymentController;
use App\Customer\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])
        ->middleware('permission:customer.portal.dashboard')
        ->name('dashboard');

    Route::get('/orders', [OrderController::class, 'orders'])
        ->middleware('permission:customer.portal.orders.view')
        ->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'createOrder'])
        ->middleware('permission:customer.portal.orders.create')
        ->name('orders.create');
    Route::post('/orders', [OrderController::class, 'storeOrder'])
        ->middleware('permission:customer.portal.orders.create')
        ->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'showOrder'])
        ->middleware('permission:customer.portal.orders.view')
        ->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder'])
        ->middleware('permission:customer.portal.orders.cancel')
        ->name('orders.cancel');

    Route::get('/documents', [DocumentController::class, 'documents'])
        ->middleware('permission:customer.portal.documents.view')
        ->name('documents.index');
    Route::get('/documents/{document}/download', [DocumentController::class, 'downloadDocument'])
        ->middleware('permission:customer.portal.documents.download')
        ->name('documents.download');

    Route::get('/profile', [ProfileController::class, 'profile'])
        ->middleware('permission:customer.portal.profile.view')
        ->name('profile');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])
        ->middleware('permission:customer.portal.profile.update')
        ->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])
        ->middleware('permission:customer.portal.profile.update')
        ->name('profile.change-password');

    Route::get('/payments', [PaymentController::class, 'payments'])
        ->middleware('permission:customer.portal.payments.view')
        ->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'showPayment'])
        ->middleware('permission:customer.portal.payments.view')
        ->name('payments.show');

    Route::get('/invoices', [InvoiceController::class, 'invoices'])
        ->middleware('permission:customer.portal.invoices.view')
        ->name('invoices.index');
    Route::get('/invoices/{document}/download', [InvoiceController::class, 'downloadInvoice'])
        ->middleware('permission:customer.portal.invoices.download')
        ->name('invoices.download');

    Route::get('/notifications', [NotificationController::class, 'notifications'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'showNotification'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markNotificationAsRead'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllNotificationsAsRead'])
        ->middleware('permission:customer.portal.notifications.view')
        ->name('notifications.mark-all-read');

    Route::get('/favorite-addresses', [FavoriteAddressController::class, 'favoriteAddresses'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.index');
    Route::post('/favorite-addresses', [FavoriteAddressController::class, 'storeFavoriteAddress'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.store');
    Route::delete('/favorite-addresses/{favoriteAddress}', [FavoriteAddressController::class, 'deleteFavoriteAddress'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.destroy');
    Route::get('/favorite-addresses/geocode', [FavoriteAddressController::class, 'geocodeAddress'])
        ->middleware('permission:customer.portal.favorite-addresses.manage')
        ->name('favorite-addresses.geocode');

    Route::get('/order-templates', [OrderTemplateController::class, 'orderTemplates'])
        ->middleware('permission:customer.portal.order-templates.manage')
        ->name('order-templates.index');
    Route::post('/order-templates', [OrderTemplateController::class, 'storeOrderTemplate'])
        ->middleware('permission:customer.portal.order-templates.manage')
        ->name('order-templates.store');
    Route::post('/order-templates/{orderTemplate}/create-order', [OrderTemplateController::class, 'createOrderFromTemplate'])
        ->middleware('permission:customer.portal.orders.create')
        ->name('order-templates.create-order');
    Route::delete('/order-templates/{orderTemplate}', [OrderTemplateController::class, 'deleteOrderTemplate'])
        ->middleware('permission:customer.portal.order-templates.manage')
        ->name('order-templates.destroy');
});
