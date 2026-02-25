<?php

namespace App\FuelPrice\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuelPriceController extends Controller
{
    /**
     * Motorin fiyat listesi.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $company = $user->activeCompany();

        if (! $company) {
            abort(403, 'Aktif bir firma seçmeden motorin fiyatlarını görüntüleyemezsiniz.');
        }

        $query = FuelPrice::where('company_id', $company->id);

        // Filtreler
        if ($request->filled('date_from')) {
            $query->where('price_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('price_date', '<=', $request->date_to);
        }

        if ($request->filled('price_type')) {
            $query->where('price_type', $request->price_type);
        }

        $sort = $request->string('sort')->toString();
        $directionInput = $request->string('direction')->toString();
        $direction = $directionInput === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'price_date' => 'price_date',
            'price_type' => 'price_type',
            'price' => 'price',
            'supplier_name' => 'supplier_name',
            'region' => 'region',
            'notes' => 'notes',
            'created_at' => 'created_at',
        ];

        if ($sort !== '' && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest('price_date');
        }

        $prices = $query->paginate(30)->withQueryString();

        $stats = [
            'total' => FuelPrice::where('company_id', $company->id)->count(),
        ];

        return view('admin.fuel-prices.index', [
            'prices' => $prices,
            'filters' => $request->only(['date_from', 'date_to', 'price_type']),
            'stats' => $stats,
        ]);
    }

    /**
     * Yeni fiyat kaydı formu.
     */
    public function create(): View
    {
        return view('admin.fuel-prices.create');
    }

    /**
     * Fiyat kaydı oluştur.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->activeCompany();

        if (! $company) {
            return back()->withErrors(['company' => 'Aktif bir firma seçmeden fiyat kaydedemezsiniz.']);
        }

        $validated = $request->validate([
            'price_date' => 'required|date',
            'price_type' => 'required|in:purchase,station',
            'price' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        FuelPrice::create([
            ...$validated,
            'company_id' => $company->id,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('admin.fuel-prices.index')
            ->with('success', 'Motorin fiyatı başarıyla kaydedildi.');
    }

    /**
     * Fiyat detayı.
     */
    public function show(FuelPrice $fuelPrice): View
    {
        return view('admin.fuel-prices.show', compact('fuelPrice'));
    }

    /**
     * Motorin fiyatları için toplu işlem.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->activeCompany();

        if (! $company) {
            abort(403, 'Aktif bir firma seçmeden motorin fiyatlarını yönetemezsiniz.');
        }

        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:fuel_prices,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            FuelPrice::where('company_id', $company->id)
                ->whereIn('id', $ids)
                ->delete();
        }

        return redirect()
            ->route('admin.fuel-prices.index')
            ->with('success', 'Seçili motorin fiyatları için toplu işlem uygulandı.');
    }
}
