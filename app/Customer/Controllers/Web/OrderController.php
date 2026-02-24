<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Order\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    use ResolvesCustomerFromUser;

    public function __construct(
        protected OrderService $orderService
    ) {}

    public function orders(Request $request): View|StreamedResponse
    {
        $this->authorizeCustomerPermission('customer.portal.orders.view');
        $customer = $this->resolveCustomer();

        $query = Order::with(['customer'])->where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportOrdersToCsv($query->get());
        }

        $orders = $query->latest()->paginate(20);

        return view('customer.orders.index', compact('orders'));
    }

    protected function exportOrdersToCsv(iterable $orders): StreamedResponse
    {
        $filename = 'siparisler_'.now()->format('Y-m-d_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Sipariş No',
                'Durum',
                'Alış Adresi',
                'Teslimat Adresi',
                'Planlanan Teslimat',
                'Teslim Tarihi',
                'Ağırlık (kg)',
                'Hacim (m³)',
                'Oluşturulma Tarihi',
            ], ';');

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->status,
                    $order->pickup_address,
                    $order->delivery_address,
                    $order->planned_delivery_date?->format('d.m.Y H:i') ?? '-',
                    $order->delivered_at?->format('d.m.Y H:i') ?? '-',
                    $order->total_weight ?? '-',
                    $order->total_volume ?? '-',
                    $order->created_at->format('d.m.Y H:i'),
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function createOrder(): View
    {
        $this->authorizeCustomerPermission('customer.portal.orders.create');
        $customer = $this->resolveCustomer();

        $pickupAddresses = $customer->favoriteAddresses()
            ->whereIn('type', ['pickup', 'both'])
            ->orderBy('sort_order')
            ->get();

        $deliveryAddresses = $customer->favoriteAddresses()
            ->whereIn('type', ['delivery', 'both'])
            ->orderBy('sort_order')
            ->get();

        return view('customer.orders.create', compact('pickupAddresses', 'deliveryAddresses'));
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.orders.create');
        $customer = $this->resolveCustomer();

        $validated = $request->validate([
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'planned_delivery_date' => 'required|date|after:today',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['customer_id'] = $customer->id;
        $order = $this->orderService->create($validated, Auth::user());

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Siparişiniz başarıyla oluşturuldu.');
    }

    public function showOrder(Order $order): View
    {
        $this->authorizeCustomerPermission('customer.portal.orders.view');
        $customer = $this->resolveCustomer();

        if ($order->customer_id !== $customer->id) {
            abort(403, 'Bu siparişe erişim yetkiniz yok.');
        }

        $order->load(['customer', 'shipments.vehicle', 'shipments.driver']);

        return view('customer.orders.show', compact('order'));
    }

    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.orders.cancel');
        $customer = $this->resolveCustomer();

        if ($order->customer_id !== $customer->id) {
            abort(403, 'Bu siparişe erişim yetkiniz yok.');
        }

        if (! in_array($order->status, ['pending', 'assigned'], true)) {
            abort(403, 'Bu sipariş iptal edilemez. Sadece beklemede veya atanmış siparişler iptal edilebilir.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes."\n\n" : '').'İptal Nedeni: '.($validated['cancellation_reason'] ?? 'Müşteri talebi'),
        ]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Sipariş başarıyla iptal edildi.');
    }
}
