<?php

namespace App\Customer\Controllers\Web;

use App\Core\Services\ExportService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FavoriteAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['status', 'search']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $customers = \App\Models\Customer::query()
            ->withCount('favoriteAddresses')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(25);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Müşteri listesini CSV veya XML olarak dışa aktar.
     */
    protected function export(array $filters, string $format): StreamedResponse|Response
    {
        $customers = Customer::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $headers = ['Müşteri Adı', 'E-posta', 'Telefon', 'Vergi No', 'Adres', 'Durum', 'Oluşturulma'];

        $rows = $customers->map(fn ($c) => [
            $c->name,
            $c->email ?? '-',
            $c->phone ?? '-',
            $c->tax_number ?? '-',
            $c->address ?? '-',
            $c->status ? 'Aktif' : 'Pasif',
            $c->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'musteriler';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'customers', 'customer')
            : $this->exportService->csv($headers, $rows, $filename);
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

        $customer = Customer::create($validated);

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Müşteri başarıyla oluşturuldu.');
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): View
    {
        $customer = Customer::with([
            'orders',
            'favoriteAddresses' => fn ($q) => $q->orderBy('sort_order')->orderBy('name'),
        ])->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(int $id): View
    {
        $customer = Customer::with([
            'favoriteAddresses' => fn ($q) => $q->orderBy('sort_order')->orderBy('name'),
        ])->findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

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
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Müşteri başarıyla silindi.');
    }

    /**
     * Müşteriye favori/teslimat adresi ekle (düzenle sayfasından).
     */
    public function storeFavoriteAddress(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:pickup,delivery,both',
            'address' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $customer->favoriteAddresses()->create(array_merge($validated, [
            'sort_order' => $validated['sort_order'] ?? 0,
        ]));

        return redirect()->route('admin.customers.edit', $customer)
            ->with('success', 'Favori adres eklendi.');
    }

    /**
     * Müşteri favori/teslimat adresini güncelle.
     */
    public function updateFavoriteAddress(Request $request, Customer $customer, FavoriteAddress $favoriteAddress): RedirectResponse
    {
        if ($favoriteAddress->customer_id !== $customer->id) {
            abort(403, 'Bu adres bu müşteriye ait değil.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:pickup,delivery,both',
            'address' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $favoriteAddress->update(array_merge($validated, [
            'sort_order' => $validated['sort_order'] ?? 0,
        ]));

        return redirect()->route('admin.customers.edit', $customer)
            ->with('success', 'Favori adres güncellendi.');
    }

    /**
     * Müşteri favori/teslimat adresini sil.
     */
    public function destroyFavoriteAddress(Customer $customer, FavoriteAddress $favoriteAddress): RedirectResponse
    {
        if ($favoriteAddress->customer_id !== $customer->id) {
            abort(403, 'Bu adres bu müşteriye ait değil.');
        }

        $favoriteAddress->delete();

        return redirect()->route('admin.customers.edit', $customer)
            ->with('success', 'Favori adres silindi.');
    }
}
