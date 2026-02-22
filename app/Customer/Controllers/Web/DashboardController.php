<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ResolvesCustomerFromUser;

    public function dashboard(): View
    {
        $this->authorizeCustomerPermission('customer.portal.dashboard');
        $customer = $this->resolveCustomer();

        $activeOrders = Order::with(['customer'])
            ->where('customer_id', $customer->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->limit(5)
            ->get();

        $recentDelivered = Order::with(['customer'])
            ->where('customer_id', $customer->id)
            ->where('status', 'delivered')
            ->latest('delivered_at')
            ->limit(5)
            ->get();

        $stats = [
            'total_orders' => Order::where('customer_id', $customer->id)->count(),
            'active_orders' => Order::where('customer_id', $customer->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->count(),
            'delivered_orders' => Order::where('customer_id', $customer->id)
                ->where('status', 'delivered')
                ->count(),
            'pending_payments' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->sum('amount'),
            'overdue_payments_count' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->whereDate('due_date', '<', now())
                ->count(),
        ];

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpr = $isSqlite ? "strftime('%Y', created_at)" : 'YEAR(created_at)';
        $monthExpr = $isSqlite ? "strftime('%m', created_at)" : 'MONTH(created_at)';
        $monthlyOrders = Order::where('customer_id', $customer->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("{$yearExpr} as year, {$monthExpr} as month, COUNT(*) as count")
            ->groupByRaw("{$yearExpr}, {$monthExpr}")
            ->orderByRaw("{$yearExpr} ASC, {$monthExpr} ASC")
            ->get()
            ->map(function ($item) {
                return [
                    'label' => \Carbon\Carbon::create((int) $item->year, (int) $item->month, 1)->format('M Y'),
                    'count' => $item->count,
                ];
            });

        $statusDistribution = Order::where('customer_id', $customer->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return view('customer.dashboard', compact('customer', 'activeOrders', 'recentDelivered', 'stats', 'monthlyOrders', 'statusDistribution'));
    }
}
