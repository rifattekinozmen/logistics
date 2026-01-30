<?php

namespace App\Customer\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'search']);
        $customers = \App\Models\Customer::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(25);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ]);

        $customer = \App\Models\Customer::create($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Müşteri başarıyla oluşturuldu.');
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): View
    {
        $customer = \App\Models\Customer::with(['orders'])->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(int $id): View
    {
        $customer = \App\Models\Customer::findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $customer = \App\Models\Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'status' => 'required|integer|in:0,1',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Müşteri başarıyla güncellendi.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(int $id): RedirectResponse
    {
        $customer = \App\Models\Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Müşteri başarıyla silindi.');
    }
}
