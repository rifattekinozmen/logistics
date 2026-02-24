<?php

namespace App\Providers;

use App\Events\InvoiceIssued;
use App\Events\OrderPaid;
use App\Events\ShipmentDelivered;
use App\Listeners\CloseOrderAfterInvoice;
use App\Listeners\CreateInvoiceDraft;
use App\Listeners\MoveOrderToPreparing;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Order::class => \App\Order\Policies\OrderPolicy::class,
        \App\Models\Vehicle::class => \App\Vehicle\Policies\VehiclePolicy::class,
        \App\Models\Employee::class => \App\Employee\Policies\EmployeePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('personnel', fn (string $value) => \App\Models\Personel::findOrFail($value));

        Paginator::useBootstrapFive();

        $this->commands([
            \App\Notification\Console\Commands\SendDailyNotifications::class,
            \App\Notification\Console\Commands\CheckDocumentExpiryCommand::class,
            \App\Notification\Console\Commands\CheckPaymentDueCommand::class,
        ]);

        // Register observers
        \App\Models\Document::observe(\App\Observers\DocumentObserver::class);
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);

        Event::listen(OrderPaid::class, MoveOrderToPreparing::class);
        Event::listen(ShipmentDelivered::class, CreateInvoiceDraft::class);
        Event::listen(InvoiceIssued::class, CloseOrderAfterInvoice::class);

        View::share('activeCompanyForLayout', null);

        View::composer('layouts.navbar', function ($view): void {
            $view->with('navBreadcrumbs', $this->buildAdminBreadcrumbs());
        });

        View::composer('layouts.sidebar', function ($view): void {
            if (auth()->check()) {
                $userId = auth()->id();
                $unreadCount = Cache::remember(
                    'sidebar_unread_'.$userId,
                    60,
                    fn () => \App\Models\Notification::where('user_id', $userId)
                        ->where('is_read', false)
                        ->count()
                );
                $view->with('unreadCount', $unreadCount);
            } else {
                $view->with('unreadCount', 0);
            }
        });
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function buildAdminBreadcrumbs(): array
    {
        $routeName = Route::currentRouteName();
        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return [
                ['label' => 'Ana Sayfa', 'url' => route('admin.dashboard')],
            ];
        }

        $items = [
            ['label' => 'Ana Sayfa', 'url' => route('admin.dashboard')],
        ];

        if ($routeName === 'admin.dashboard') {
            return $items;
        }

        $resourceLabels = [
            'orders' => 'Siparişler',
            'customers' => 'Müşteriler',
            'shipments' => 'Sevkiyatlar',
            'warehouses' => 'Depo & Stok',
            'delivery-imports' => 'Teslimat Raporları',
            'vehicles' => 'Araçlar',
            'work-orders' => 'İş Emirleri & Bakım',
            'fuel-prices' => 'Motorin Fiyat',
            'employees' => 'Personel',
            'personnel' => 'Personel (Kimlik)',
            'personnel_attendance' => 'Puantaj',
            'shifts' => 'Vardiyalar',
            'leaves' => 'İzinler',
            'advances' => 'Avanslar',
            'payrolls' => 'Bordrolar',
            'notifications' => 'Bildirimler',
            'companies' => 'Firmalar',
            'users' => 'Kullanıcılar',
            'documents' => 'Belgeler',
            'payments' => 'Ödemeler',
            'profile' => 'Profil',
            'settings' => 'Ayarlar',
        ];

        $parts = explode('.', $routeName);
        if (count($parts) < 2) {
            return $items;
        }

        $resource = $parts[1];
        $resourceLabel = $resourceLabels[$resource] ?? ucfirst(str_replace('-', ' ', $resource));
        $action = $parts[2] ?? 'index';

        $indexRoute = 'admin.'.$resource.'.index';
        $hasIndex = Route::has($indexRoute);

        $actionLabels = [
            'create' => 'Yeni',
            'edit' => 'Düzenle',
            'edit-roles' => 'Rol Düzenle',
            'show' => 'Detay',
            'settings' => 'Ayarlar',
            'select' => 'Firma Seç',
            'templates' => 'Şablonlar',
            'planning' => 'Planlama',
            'veri-analiz-raporu' => 'Veri Analiz Raporu',
            'index' => null,
        ];

        if ($action === 'index') {
            $items[] = ['label' => $resourceLabel, 'url' => null];

            return $items;
        }

        if ($hasIndex) {
            $items[] = ['label' => $resourceLabel, 'url' => route($indexRoute)];
        }

        $currentLabel = $actionLabels[$action] ?? ucfirst($action);
        if ($action === 'create') {
            $currentLabel = 'Yeni '.$resourceLabel;
        } elseif ($currentLabel === null || ($action === 'show' && ! $hasIndex)) {
            $currentLabel = $resourceLabel;
        }
        $items[] = ['label' => $currentLabel, 'url' => null];

        return $items;
    }
}
