<?php

namespace App\Admin\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyGeneralRequest;
use App\Models\Company;
use App\Models\CompanyDigitalService;
use BadMethodCallException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Company::withTrashed()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            });

        $search = $request->string('search')->toString();
        if ($search !== '') {
            $query->where(function ($companyQuery) use ($search) {
                $companyQuery
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('short_name', 'like', '%'.$search.'%')
                    ->orWhere('tax_number', 'like', '%'.$search.'%');
            });
        }

        $status = $request->string('status')->toString();
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $companies = $query
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.companies.index', [
            'companies' => $companies,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'tax_office' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:50|unique:companies,tax_number',
            'mersis_no' => 'nullable|string|max:20',
            'trade_registry_no' => 'nullable|string|max:50',
            'currency' => 'required|string|size:3',
            'default_vat_rate' => 'required|numeric|min:0|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Logo yükleme
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies/logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Kaşe yükleme
        if ($request->hasFile('stamp')) {
            $stampPath = $request->file('stamp')->store('companies/stamps', 'public');
            $validated['stamp_path'] = $stampPath;
        }

        // Logo ve kaşe alanlarını validated array'inden çıkar (çünkü bunlar file)
        unset($validated['logo'], $validated['stamp']);

        $company = Company::create($validated);

        // Varsayılan dijital mali hizmet kayıtlarını oluştur
        $defaultServiceTypes = [
            CompanyDigitalService::TYPE_E_INVOICE,
            CompanyDigitalService::TYPE_E_ARCHIVE,
            CompanyDigitalService::TYPE_E_WAYBILL,
        ];

        foreach ($defaultServiceTypes as $serviceType) {
            $company->digitalServices()->create([
                'service_type' => $serviceType,
                'is_active' => false,
            ]);
        }

        // Firma oluşturan kullanıcıyı firmaya ekle (admin rolü ile)
        $user = Auth::user();
        $company->users()->attach($user->id, [
            'role' => 'admin',
            'is_default' => ! $user->companies()->exists(), // İlk firma ise default yap
        ]);

        // Eğer kullanıcının aktif firması yoksa, yeni oluşturulan firmayı aktif yap
        if (! session('active_company_id')) {
            session(['active_company_id' => $company->id]);
        }

        return redirect()->route('admin.companies.settings', $company)
            ->with('success', 'Firma başarıyla oluşturuldu.');
    }

    /**
     * Show company selection page.
     * Eğer aktif firma varsa, settings sayfasına yönlendir.
     */
    public function select(): View|RedirectResponse
    {
        $user = Auth::user();

        // Aktif firma kontrolü
        $activeCompany = $user->activeCompany();
        if ($activeCompany) {
            return redirect()->route('admin.companies.settings', $activeCompany);
        }

        $companies = $user->companies()->where(function ($query) {
            if (Schema::hasColumn('companies', 'is_active')) {
                $query->where('is_active', true);
            } else {
                $query->where('status', 1);
            }
        })->get();

        return view('admin.companies.select', compact('companies'));
    }

    /**
     * Switch active company.
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($request->company_id);

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403, 'Bu firmaya erişim yetkiniz yok.');
        }

        // Session güncelle
        session(['active_company_id' => $company->id]);

        // Cache temizle (tags desteklenmiyorsa direkt flush kullan)
        try {
            Cache::tags(["company:{$company->id}"])->flush();
        } catch (BadMethodCallException $e) {
            // Cache store tagging desteklemiyorsa, sadece flush yap
            Cache::flush();
        }

        // Firma değiştirme işleminden sonra:
        // - Eğer select sayfasından geliyorsa -> settings sayfasına yönlendir
        // - Diğer durumlarda (navbar'dan) -> aynı sayfada kal
        $referer = $request->headers->get('referer');
        if ($referer && str_contains($referer, route('admin.companies.select'))) {
            return redirect()->route('admin.companies.settings', $company)->with('success', 'Firma başarıyla seçildi.');
        }

        return redirect()->back()->with('success', 'Firma başarıyla değiştirildi.');
    }

    /**
     * Show company settings page.
     * Sadece aktif firmanın ayarlarını gösterir.
     */
    public function settings(Company $company): View|RedirectResponse
    {
        $user = Auth::user();

        // Aktif firma kontrolü - sadece aktif firmanın ayarlarını gösterebilir
        $activeCompanyId = session('active_company_id');
        if (! $activeCompanyId || $activeCompanyId !== $company->id) {
            // Aktif firma yoksa veya farklı bir firma seçilmişse, aktif firmaya yönlendir
            $activeCompany = $user->activeCompany();
            if ($activeCompany) {
                return redirect()->route('admin.companies.settings', $activeCompany);
            }

            return redirect()->route('admin.companies.select');
        }

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403, 'Bu firmaya erişim yetkiniz bulunmamaktadır.');
        }

        $addresses = $company->addresses()->orderBy('is_default', 'desc')->get();
        $settings = $company->settings()->get()->keyBy('setting_key');

        // Ülke, il ve ilçe verilerini yükle
        $countries = \App\Models\Country::where('is_active', true)->orderBy('name_tr')->get();
        $cities = \App\Models\City::where('is_active', true)
            ->when($company->country_id, function ($query) use ($company) {
                $query->where('country_id', $company->country_id);
            })
            ->orderBy('name_tr')
            ->get();
        $districts = \App\Models\District::where('is_active', true)
            ->when($company->city_id, function ($query) use ($company) {
                $query->where('city_id', $company->city_id);
            })
            ->orderBy('name_tr')
            ->get();

        $digitalServices = $company->digitalServices()
            ->orderBy('service_type')
            ->get();

        return view('admin.companies.settings', compact(
            'company',
            'addresses',
            'settings',
            'countries',
            'cities',
            'districts',
            'digitalServices'
        ));
    }

    /**
     * Toggle digital service active status.
     */
    public function toggleDigitalService(Request $request, Company $company, CompanyDigitalService $service): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasAccessToCompany($company->id) || $service->company_id !== $company->id) {
            abort(403);
        }

        $service->is_active = ! $service->is_active;
        $service->save();

        return back()->with('success', 'Hizmet durumu başarıyla güncellendi.');
    }

    /**
     * Create close request for a digital service.
     */
    public function requestDigitalServiceClose(Request $request, Company $company, CompanyDigitalService $service): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasAccessToCompany($company->id) || $service->company_id !== $company->id) {
            abort(403);
        }

        if ($service->close_request_status === 'none') {
            $service->close_request_status = 'requested';
            $service->close_requested_at = now();
            $service->save();
        }

        return back()->with('success', 'Hizmet kapatma talebi oluşturuldu.');
    }

    /**
     * Update company general information.
     */
    public function updateGeneral(UpdateCompanyGeneralRequest $request, Company $company): RedirectResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403);
        }

        $validated = $request->validated();

        $company->update($validated);

        // Cache temizle
        try {
            Cache::tags(["company:{$company->id}"])->flush();
        } catch (BadMethodCallException $e) {
            Cache::forget("company:{$company->id}");
        }

        return back()->with('success', 'Genel bilgiler başarıyla güncellendi.');
    }

    /**
     * Update company logo.
     */
    public function updateLogo(Request $request, Company $company): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu firmaya erişim yetkiniz yok.',
                ], 403);
            }
            abort(403);
        }

        try {
            $request->validate([
                'logo' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Geçersiz dosya. Lütfen JPG, PNG, GIF veya SVG formatında ve 2MB\'dan küçük bir dosya seçin.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        if ($request->hasFile('logo')) {
            // Eski logoyu sil
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }

            // Yeni logoyu kaydet
            $path = $request->file('logo')->store('companies/logos', 'public');
            $company->logo_path = $path;
            $company->save();

            // Model'i fresh olarak yeniden yükle
            $company->refresh();

            // Cache temizle (tags desteklenmiyorsa direkt flush kullan)
            try {
                Cache::tags(["company:{$company->id}"])->flush();
            } catch (BadMethodCallException $e) {
                // Cache store tagging desteklemiyorsa, sadece ilgili key'leri temizle
                Cache::forget("company:{$company->id}");
            }
        }

        // AJAX isteği ise JSON döndür
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logo başarıyla güncellendi.',
                'logo_url' => $company->logo_url,
            ]);
        }

        return redirect()->back()->with('success', 'Logo başarıyla güncellendi.');
    }

    /**
     * Delete company logo.
     */
    public function deleteLogo(Request $request, Company $company): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu firmaya erişim yetkiniz yok.',
                ], 403);
            }
            abort(403);
        }

        // Logoyu sil
        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->logo_path = null;
        $company->save();

        // Model'i fresh olarak yeniden yükle
        $company->refresh();

        // Cache temizle
        try {
            Cache::tags(["company:{$company->id}"])->flush();
        } catch (BadMethodCallException $e) {
            Cache::forget("company:{$company->id}");
        }

        // AJAX isteği ise JSON döndür
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logo başarıyla silindi.',
            ]);
        }

        return redirect()->back()->with('success', 'Logo başarıyla silindi.');
    }

    /**
     * Update company stamp.
     */
    public function updateStamp(Request $request, Company $company): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403);
        }

        $request->validate([
            'stamp' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('stamp')) {
            // Eski kaşeyi sil
            if ($company->stamp_path && Storage::disk('public')->exists($company->stamp_path)) {
                Storage::disk('public')->delete($company->stamp_path);
            }

            // Yeni kaşeyi kaydet
            $path = $request->file('stamp')->store('companies/stamps', 'public');
            $company->stamp_path = $path;
            $company->save();

            // Model'i fresh olarak yeniden yükle
            $company->refresh();

            // Cache temizle (tags desteklenmiyorsa direkt flush kullan)
            try {
                Cache::tags(["company:{$company->id}"])->flush();
            } catch (BadMethodCallException $e) {
                Cache::forget("company:{$company->id}");
            }
        }

        // AJAX isteği ise JSON döndür
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kaşe başarıyla güncellendi.',
                'stamp_url' => $company->stamp_path ? Storage::disk('public')->url($company->stamp_path) : null,
            ]);
        }

        return redirect()->back()->with('success', 'Kaşe başarıyla güncellendi.');
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Request $request, Company $company): RedirectResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403);
        }

        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            $company->setSetting($key, $value);
        }

        // Cache temizle (tags desteklenmiyorsa direkt forget kullan)
        try {
            Cache::tags(["company:{$company->id}"])->forget('settings');
        } catch (BadMethodCallException $e) {
            // Cache store tagging desteklenmiyorsa, direkt forget yap
            Cache::forget("company:{$company->id}:settings");
        }

        return back()->with('success', 'Ayarlar başarıyla güncellendi.');
    }

    /**
     * Store a new company address.
     */
    public function storeAddress(Request $request, Company $company): RedirectResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        // Eğer varsayılan adres seçildiyse, diğer adreslerin varsayılanını kaldır
        if ($validated['is_default'] ?? false) {
            $company->addresses()->update(['is_default' => false]);
        }

        $company->addresses()->create($validated);

        return back()->with('success', 'Adres başarıyla eklendi.');
    }

    /**
     * Update a company address.
     */
    public function updateAddress(Request $request, Company $company, int $addressId): RedirectResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403);
        }

        $address = $company->addresses()->findOrFail($addressId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);

        // Eğer varsayılan adres seçildiyse, diğer adreslerin varsayılanını kaldır
        if ($validated['is_default'] ?? false) {
            $company->addresses()->where('id', '!=', $addressId)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'Adres başarıyla güncellendi.');
    }

    /**
     * Delete a company address.
     */
    public function deleteAddress(Company $company, int $addressId): RedirectResponse
    {
        $user = Auth::user();

        // Yetki kontrolü
        if (! $user->hasAccessToCompany($company->id)) {
            abort(403);
        }

        $address = $company->addresses()->findOrFail($addressId);
        $address->delete();

        return back()->with('success', 'Adres başarıyla silindi.');
    }
}
