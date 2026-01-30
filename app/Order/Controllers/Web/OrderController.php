<?php

namespace App\Order\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Order\Requests\StoreOrderRequest;
use App\Order\Requests\UpdateOrderRequest;
use App\Order\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $orders = $this->orderService->getPaginated($filters);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): View
    {
        $customers = \App\Models\Customer::where('status', 1)->orderBy('name')->get();

        return view('admin.orders.create', compact('customers'));
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create($request->validated(), $request->user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Sipariş başarıyla oluşturuldu.');
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): View
    {
        $order = \App\Models\Order::with(['customer', 'shipments', 'creator'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(int $id): View
    {
        $order = \App\Models\Order::findOrFail($id);

        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, int $id): RedirectResponse
    {
        $order = \App\Models\Order::findOrFail($id);

        $this->orderService->update($order, $request->validated());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Sipariş başarıyla güncellendi.');
    }

    /**
     * Remove the specified order.
     */
    public function destroy(int $id): RedirectResponse
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Sipariş başarıyla silindi.');
    }
}
