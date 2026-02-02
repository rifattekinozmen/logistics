<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        $this->commands([
            \App\Notification\Console\Commands\SendDailyNotifications::class,
        ]);

        View::share('activeCompanyForLayout', null);

        View::composer('layouts.navbar', function ($view): void {
            $view->with('navBreadcrumbs', $this->buildAdminBreadcrumbs());
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
