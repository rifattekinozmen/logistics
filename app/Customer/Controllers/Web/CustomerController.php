<?php

namespace App\Customer\Controllers\Web;

use App\Core\Services\ExportService;
use App\Customer\Requests\StoreCustomerRequest;
use App\Customer\Requests\UpdateCustomerRequest;
use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FavoriteAddress;
use App\Models\TaxOffice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

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

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 1)->count(),
            'inactive' => Customer::where('status', 0)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
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

        $headers = ['Müşteri Kodu', 'Müşteri Adı', 'Müşteri Türü', 'Öncelik', 'E-posta', 'Telefon', 'Vergi No', 'Vergi Dairesi', 'Adres', 'Durum', 'Oluşturulma'];

        $customers->load('taxOffice');

        $rows = $customers->map(fn ($c) => [
            $c->customer_code ?? $c->id,
            $c->name,
            $c->customer_type ?? '-',
            $c->priority_level ?? '-',
            $c->email ?? '-',
            $c->phone ?? '-',
            $c->tax_number ?? '-',
            $c->taxOffice?->name ?? $c->tax_office ?? '-',
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
        $taxOffices = TaxOffice::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        return view('admin.customers.create', compact('taxOffices'));
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($request->validated());

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Müşteri başarıyla oluşturuldu.');
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): View
    {
        $customer = Customer::with([
            'taxOffice',
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
            'taxOffice',
            'favoriteAddresses' => fn ($q) => $q->orderBy('sort_order')->orderBy('name'),
        ])->findOrFail($id);

        $taxOffices = TaxOffice::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        return view('admin.customers.edit', compact('customer', 'taxOffices'));
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, int $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        $customer->update($request->validated());

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
            'type' => 'required|string|in:'.implode(',', AddressType::values()),
            'address' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'working_days' => 'nullable|array',
            'working_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
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
            'type' => 'required|string|in:'.implode(',', AddressType::values()),
            'address' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'working_days' => 'nullable|array',
            'working_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
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
