<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\OrderTemplate;
use App\Order\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderTemplateController extends Controller
{
    use ResolvesCustomerFromUser;

    public function __construct(
        protected OrderService $orderService
    ) {}

    public function orderTemplates(): View
    {
        $this->authorizeCustomerPermission('customer.portal.order-templates.manage');
        $customer = $this->resolveCustomer();

        $templates = OrderTemplate::where('customer_id', $customer->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('customer.order-templates.index', compact('templates'));
    }

    public function storeOrderTemplate(Request $request): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.order-templates.manage');
        $customer = $this->resolveCustomer();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        OrderTemplate::create(array_merge($validated, ['customer_id' => $customer->id]));

        return back()->with('success', 'Sipariş şablonu başarıyla eklendi.');
    }

    public function createOrderFromTemplate(OrderTemplate $orderTemplate): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.orders.create');
        $customer = $this->resolveCustomer();

        if ($orderTemplate->customer_id !== $customer->id) {
            abort(403, 'Bu şablona erişim yetkiniz yok.');
        }

        $orderData = [
            'customer_id' => $customer->id,
            'pickup_address' => $orderTemplate->pickup_address,
            'delivery_address' => $orderTemplate->delivery_address,
            'planned_delivery_date' => now()->addDays(1),
            'total_weight' => $orderTemplate->total_weight,
            'total_volume' => $orderTemplate->total_volume,
            'is_dangerous' => $orderTemplate->is_dangerous,
            'notes' => $orderTemplate->notes,
        ];

        $order = $this->orderService->create($orderData, Auth::user());

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Sipariş şablondan başarıyla oluşturuldu.');
    }

    public function deleteOrderTemplate(OrderTemplate $orderTemplate): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.order-templates.manage');
        $customer = $this->resolveCustomer();

        if ($orderTemplate->customer_id !== $customer->id) {
            abort(403, 'Bu şablona erişim yetkiniz yok.');
        }

        $orderTemplate->delete();

        return back()->with('success', 'Sipariş şablonu başarıyla silindi.');
    }
}
