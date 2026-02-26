<?php

namespace App\Employee\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonelRequest;
use App\Http\Requests\UpdatePersonelRequest;
use App\Models\Country;
use App\Models\Department;
use App\Models\Personel;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PersonelController extends Controller
{
    /**
     * Form için gerekli referans verileri.
     *
     * @return array<string, mixed>
     */
    private function getFormData(): array
    {
        // Departman / pozisyon ve diğer lookup verilerini her istekte taze çek
        // (cache yerine doğrudan buildFormData kullanıyoruz)
        return $this->buildFormData();
    }

    /**
     * Form referans verilerini oluşturur.
     *
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        $countries = Country::where('is_active', true)->orderBy('name_tr')->get();
        $cities = \App\Models\City::where('is_active', true)->orderBy('name_tr')->get(['id', 'name_tr', 'country_id']);
        $districts = \App\Models\District::where('is_active', true)->orderBy('name_tr')->get(['id', 'name_tr', 'city_id']);

        $departments = [
            'Lojistik Operasyon' => 'Lojistik Operasyon',
            'Nakliye & Sevkiyat' => 'Nakliye & Sevkiyat',
            'Depo & Stok Yönetimi' => 'Depo & Stok Yönetimi',
            'Filo Yönetimi' => 'Filo Yönetimi',
            'Planlama & Rota Optimizasyonu' => 'Planlama & Rota Optimizasyonu',
            'Müşteri İlişkileri & Operasyon Desteği' => 'Müşteri İlişkileri & Operasyon Desteği',
            'Satış & İş Geliştirme' => 'Satış & İş Geliştirme',
            'Finans & Muhasebe' => 'Finans & Muhasebe',
            'İnsan Kaynakları' => 'İnsan Kaynakları',
            'Bilgi Teknolojileri (BT)' => 'Bilgi Teknolojileri (BT)',
        ];

        $positionMap = [
            'Lojistik Operasyon' => [
                'Lojistik Operasyon Uzmanı',
                'Lojistik Operasyon Sorumlusu',
                'Lojistik Operasyon Müdürü',
            ],
            'Nakliye & Sevkiyat' => [
                'Şoför (Tır)',
                'Şoför (Kamyon)',
                'Sevkiyat Şefi',
                'Sevkiyat Planlama Uzmanı',
            ],
            'Depo & Stok Yönetimi' => [
                'Depo Sorumlusu',
                'Depo Şefi',
                'Depo Personeli',
                'Forklift Operatörü',
                'Stok Kontrol Uzmanı',
            ],
            'Filo Yönetimi' => [
                'Filo Yöneticisi',
                'Filo Sorumlusu',
                'Bakım & Onarım Sorumlusu',
                'Araç Takip Uzmanı',
            ],
            'Planlama & Rota Optimizasyonu' => [
                'Rota Planlama Uzmanı',
                'Operasyon Planlama Uzmanı',
                'Planlama ve Optimizasyon Sorumlusu',
            ],
            'Müşteri İlişkileri & Operasyon Desteği' => [
                'Müşteri Temsilcisi',
                'Operasyon Destek Uzmanı',
                'Çağrı Merkezi Temsilcisi',
            ],
            'Satış & İş Geliştirme' => [
                'Satış Temsilcisi',
                'Kurumsal Satış Uzmanı',
                'İş Geliştirme Uzmanı',
                'Satış Müdürü',
            ],
            'Finans & Muhasebe' => [
                'Muhasebe Uzmanı',
                'Finans Uzmanı',
                'Tahsilat Sorumlusu',
                'Muhasebe Sorumlusu',
                'Finans Müdürü',
            ],
            'İnsan Kaynakları' => [
                'İK Uzmanı',
                'İK Sorumlusu',
            ],
            'Bilgi Teknolojileri (BT)' => [
                'Yazılım Geliştirici',
                'Sistem Yöneticisi',
                'Uygulama Destek Uzmanı',
            ],
        ];

        // Düz pozisyon listesi (mevcut select component için)
        $positions = [];
        foreach ($positionMap as $positionList) {
            foreach ($positionList as $name) {
                $positions[$name] = $name;
            }
        }

        // SGK sigorta türleri (kod => ad)
        $sgkInsuranceTypes = [
            '4A' => 'Hizmet Akdiyle Çalışanlar',
            '4B' => 'Bağ-Kur (Kendi Adına Çalışanlar)',
            '4C' => 'Emekli Sandığı',
            '10' => 'Kısmi Süreli / Part-Time Çalışanlar',
            '13' => 'Çağrı Üzerine Çalışanlar',
        ];

        // ÇSGB iş kolları (örnek, lojistik odaklı)
        $csgbBranches = [
            '49.41' => 'Kara Taşımacılığı ve Boru Hattı Taşımacılığı',
            '52.10' => 'Depolama ve Antrepo Faaliyetleri',
            '52.21' => 'Karayolu Taşımacılığını Destekleyici Faaliyetler',
            '52.29' => 'Diğer Taşımacılık Destekleyici Faaliyetleri',
        ];

        // 2821 görevlendirme kodları (örnek)
        $law2821Duties = [
            '101' => 'Genel Müdür',
            '201' => 'Birim / Departman Müdürü',
            '301' => 'Şoför',
            '302' => 'Depo Sorumlusu',
            '303' => 'Operasyon Uzmanı',
            '304' => 'Muhasebe Uzmanı',
            '305' => 'Finans Uzmanı',
        ];

        // Meslek kodu / adı (örnek lojistik meslekleri)
        $professions = [
            '833203' => 'Kamyon Şoförü',
            '833202' => 'Tır Şoförü',
            '432101' => 'Depo Sorumlusu',
            '432102' => 'Stok Kontrol Uzmanı',
            '432103' => 'Lojistik Operasyon Uzmanı',
            '432104' => 'Sevkiyat Planlama Uzmanı',
            '241101' => 'Muhasebe Uzmanı',
            '241301' => 'Finans Analisti',
        ];

        return [
            'countries' => $countries,
            'cities' => $cities,
            'districts' => $districts,
            'departments' => $departments,
            'positions' => $positions,
            'position_map' => $positionMap,
            'sgk_insurance_types' => $sgkInsuranceTypes,
            'csgb_branches' => $csgbBranches,
            'law2821_duties' => $law2821Duties,
            'professions' => $professions,
            'banks' => config('personnel.banks', []),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Personel::query();

        if ($request->filled('aktif')) {
            $query->where('aktif', $request->aktif);
        }
        if ($request->filled('departman')) {
            $query->where('departman', 'like', '%'.$request->departman.'%');
        }
        if ($request->filled('pozisyon')) {
            $query->where('pozisyon', 'like', '%'.$request->pozisyon.'%');
        }

        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'ad_soyad' => 'ad_soyad',
            'email' => 'email',
            'telefon' => 'telefon',
            'departman' => 'departman',
            'pozisyon' => 'pozisyon',
            'ise_baslama_tarihi' => 'ise_baslama_tarihi',
            'maas' => 'maas',
            'aktif' => 'aktif',
            'created_at' => 'created_at',
        ];
        if ($sort !== '' && \array_key_exists($sort, $sortableColumns)) {
            $query->orderBy($sortableColumns[$sort], $direction);
        } else {
            $query->latest();
        }

        $personels = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Personel::count(),
            'active' => Personel::where('aktif', 1)->count(),
        ];

        return view('admin.personnel.index', array_merge(
            $this->buildFormData(),
            compact('personels', 'stats'),
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.personnel.create', $this->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('personnel-photos', 'public');
        }
        unset($data['photo']);

        Personel::create($data);

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Personel $personnel): View
    {
        $personnel->load(['country', 'city', 'district']);
        $personnel->loadCount(['documents', 'personnelAttendances']);

        return view('admin.personnel.show', compact('personnel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personel $personnel): View
    {
        $personnel->load(['country', 'city', 'district']);
        $personnel->loadCount(['documents', 'personnelAttendances']);

        return view('admin.personnel.edit', array_merge(
            $this->getFormData(),
            ['personnel' => $personnel]
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePersonelRequest $request, Personel $personnel): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            if ($personnel->photo_path) {
                Storage::disk('public')->delete($personnel->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('personnel-photos', 'public');
        }
        unset($data['photo']);

        $personnel->update($data);

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personel $personnel): RedirectResponse
    {
        $personnel->delete();

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Personel başarıyla silindi.');
    }

    /**
     * Apply bulk actions to personnel.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:personels,id'],
            'action' => ['required', 'string', 'in:delete,activate,deactivate'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            Personel::whereIn('id', $ids)->delete();
        }
        if ($validated['action'] === 'activate') {
            Personel::whereIn('id', $ids)->update(['aktif' => 1]);
        }
        if ($validated['action'] === 'deactivate') {
            Personel::whereIn('id', $ids)->update(['aktif' => 0]);
        }

        return redirect()->route('admin.personnel.index')
            ->with('success', 'Seçili personel için toplu işlem uygulandı.');
    }
}
