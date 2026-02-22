<?php

namespace App\Customer\Controllers\Web;

use App\Core\Services\GeocodingService;
use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\FavoriteAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteAddressController extends Controller
{
    use ResolvesCustomerFromUser;

    public function __construct(
        protected GeocodingService $geocodingService
    ) {}

    public function favoriteAddresses(): View
    {
        $this->authorizeCustomerPermission('customer.portal.favorite-addresses.manage');
        $customer = $this->resolveCustomer();

        $addresses = FavoriteAddress::where('customer_id', $customer->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('customer.favorite-addresses.index', compact('addresses'));
    }

    public function storeFavoriteAddress(Request $request): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.favorite-addresses.manage');
        $customer = $this->resolveCustomer();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:pickup,delivery,both',
            'address' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        FavoriteAddress::create(array_merge($validated, ['customer_id' => $customer->id]));

        return back()->with('success', 'Favori adres başarıyla eklendi.');
    }

    public function deleteFavoriteAddress(FavoriteAddress $favoriteAddress): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.favorite-addresses.manage');
        $customer = $this->resolveCustomer();

        if ($favoriteAddress->customer_id !== $customer->id) {
            abort(403, 'Bu adrese erişim yetkiniz yok.');
        }

        $favoriteAddress->delete();

        return back()->with('success', 'Favori adres başarıyla silindi.');
    }

    public function geocodeAddress(Request $request): JsonResponse
    {
        $this->authorizeCustomerPermission('customer.portal.favorite-addresses.manage');

        $address = $request->input('address', '');
        $address = is_string($address) ? trim($address) : '';
        if ($address === '') {
            return response()->json(['message' => 'Adres boş olamaz.'], 422);
        }

        $result = $this->geocodingService->geocode($address);
        if ($result === null) {
            return response()->json(['message' => 'Bu adres için konum bulunamadı.'], 404);
        }

        return response()->json($result);
    }
}
