<?php

namespace App\Admin\Controllers\Web;

use App\BusinessPartner\Models\BusinessPartner;
use App\Core\Services\CalendarService;
use App\Core\Services\ExportService;
use App\Finance\Services\FinanceDashboardService;
use App\Http\Controllers\Controller;
use App\Models\AiReport;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Order\Services\OperationsPerformanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(FinanceDashboardService $financeService, OperationsPerformanceService $operationsService, CalendarService $calendarService): View
    {
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

        $orderStats = DB::table('orders')->selectRaw("
            COUNT(CASE WHEN status != 'cancelled' THEN 1 END) as orders_count,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as orders_pending,
            COUNT(CASE WHEN status = 'delivered' THEN 1 END) as orders_delivered,
            COUNT(CASE WHEN status = 'invoiced' THEN 1 END) as invoiced_orders
        ")->first();

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
            'vehicles_count' => Vehicle::where('status', 1)->count(),
            'vehicles_active' => Vehicle::query()
                ->where('vehicles.status', 1)
                ->whereNotIn('vehicles.id', function ($q) {
                    $q->select('vehicle_id')
                        ->from('shipments')
                        ->whereIn('status', ['pending', 'in_transit'])
                        ->whereNotNull('vehicle_id');
                })
                ->count(),
            'employees_count' => Employee::where('status', 1)->count(),
            'customers_count' => Customer::where('status', 1)->count(),
            'customers_change_percent' => $customersChangePercent,
            'warehouses_count' => Warehouse::count(),
        ];

        $sapStats = DB::table('sap_documents')->selectRaw("
            COUNT(CASE WHEN sync_status = 'pending' THEN 1 END) as pending,
            COUNT(CASE WHEN sync_status = 'synced' THEN 1 END) as synced,
            COUNT(CASE WHEN sync_status = 'error' THEN 1 END) as errors
        ")->first();

        $bpCount = BusinessPartner::where('status', 1)->count();

        $activePricing = \App\Pricing\Models\PricingCondition::where('status', 1)
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', now());
            })
            ->count();

        $recentActivities = AuditLog::with('user')
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

        $aiReportsHighCount = AiReport::whereIn('severity', ['high'])->count();
        $aiReports = AiReport::query()
            ->orderByRaw("CASE severity WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->latest('generated_at')
            ->limit(8)
            ->get();

        $user = auth()->user();
        $company = $user->activeCompany();
        $financeData = $financeService->getDashboardData($company);
        $operationsData = $operationsService->getPerformanceSummary();
        $upcomingEvents = $calendarService->getUpcomingEvents(7);
        $criticalStocks = collect();

        return view('admin.dashboard', compact(
            'stats',
            'recentActivities',
            'aiReports',
            'aiReportsHighCount',
            'financeData',
            'operationsData',
            'sapStats',
            'bpCount',
            'activePricing',
            'upcomingEvents',
            'criticalStocks',
        ));
    }

    public function export(ExportService $exportService): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $company = $user->activeCompany();
        $financeService = app(FinanceDashboardService::class);
        $financeData = $financeService->getDashboardData($company);
        $operationsService = app(OperationsPerformanceService::class);
        $operationsData = $operationsService->getPerformanceSummary();

        $headers = ['Metrik', 'Değer'];
        $rows = [
            ['Toplam Sipariş', Order::where('status', '!=', 'cancelled')->count()],
            ['Bekleyen Sipariş', Order::where('status', 'pending')->count()],
            ['Teslim Edilen Sipariş', Order::where('status', 'delivered')->count()],
            ['Toplam Sevkiyat', Shipment::count()],
            ['Aktif Sevkiyat', Shipment::whereIn('status', ['pending', 'in_transit'])->count()],
            ['Toplam Araç', Vehicle::where('status', 1)->count()],
            ['Aktif Müşteri', Customer::where('status', 1)->count()],
            ['Personel', Employee::where('status', 1)->count()],
            ['Depo', Warehouse::count()],
            ['Geciken Ödemeler (₺)', number_format($financeData['overdue']['total_amount'] ?? 0, 2, ',', '.')],
            ['Bugün Vadesi Gelen (₺)', number_format($financeData['due_today']['total_amount'] ?? 0, 2, ',', '.')],
            ['7 Gün İçinde (₺)', number_format($financeData['due_in_7_days']['total_amount'] ?? 0, 2, ',', '.')],
            ['Bu Ay Ödenen (₺)', number_format($financeData['paid_this_month']['total_amount'] ?? 0, 2, ',', '.')],
            ['Teslimat Performans Puanı', number_format($operationsData['delivery_performance_score'] ?? 0, 1)],
            ['Geciken Sipariş Oranı (%)', number_format($operationsData['delayed_order_rate']['rate'] ?? 0, 1)],
            ['Araç Doluluk Oranı (%)', number_format($operationsData['vehicle_utilization']['utilization_rate'] ?? 0, 1)],
            ['Ortalama Teslimat Süresi (saat)', $operationsData['average_delivery_time'] ?? '-'],
        ];

        return $exportService->csv($headers, $rows, 'dashboard_ozet');
    }
}
