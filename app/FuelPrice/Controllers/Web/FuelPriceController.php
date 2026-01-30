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

        if (!$company) {
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

        $prices = $query->latest('price_date')->paginate(30);

        return view('admin.fuel-prices.index', [
            'prices' => $prices,
            'filters' => $request->only(['date_from', 'date_to', 'price_type']),
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

        if (!$company) {
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
}
