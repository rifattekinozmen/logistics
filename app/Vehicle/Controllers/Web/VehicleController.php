<?php

namespace App\Vehicle\Controllers\Web;

use App\Core\Services\ExportService;
use App\Http\Controllers\Controller;
use App\Vehicle\Requests\StoreVehicleRequest;
use App\Vehicle\Requests\UpdateVehicleRequest;
use App\Vehicle\Services\VehicleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService,
        protected ExportService $exportService
    ) {}

    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['status', 'branch_id', 'vehicle_type', 'sort', 'direction']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $vehicles = $this->vehicleService->getPaginated($filters);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        $stats = [
            'total' => \App\Models\Vehicle::count(),
            'active' => \App\Models\Vehicle::where('status', 1)->count(),
            'maintenance' => \App\Models\Vehicle::where('status', 2)->count(),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'branches', 'stats'));
    }

    /**
     * Araç listesini CSV veya XML olarak dışa aktar.
     */
    protected function export(array $filters, string $format): StreamedResponse|Response
    {
        $vehicles = $this->vehicleService->getForExport($filters);

        $headers = ['Plaka', 'Marka', 'Seri', 'Model', 'Yıl', 'Tip', 'Araç Tipi (Alt)', 'Kapasite (kg)', 'Kapasite (m³)', 'Şube', 'Durum', 'Oluşturulma'];

        $typeLabels = [
            'car' => 'Otomobil',
            'truck' => 'Arazi, SUV & Pickup',
            'van' => 'Minivan & Panelvan',
            'motorcycle' => 'Motosiklet',
            'bus' => 'Ticari Araçlar',
            'electric' => 'Elektrikli Araçlar',
            'rental' => 'Kiralık Araçlar',
            'marine' => 'Deniz Araçları',
            'damaged' => 'Hasarlı Araçlar',
            'caravan' => 'Karavan',
            'classic' => 'Klasik Araçlar',
            'aircraft' => 'Hava Araçları',
            'atv' => 'ATV',
            'utv' => 'UTV',
            'disabled' => 'Engelli Plakalı Araçlar',
            'other' => 'Diğer',
        ];
        $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'Bakımda'];

        $subtypeLabels = [
            'minibus' => 'Minibüs & Midibüs', 'bus' => 'Otobüs', 'truck' => 'Kamyon & Kamyonet',
            'tractor' => 'Çekici', 'trailer' => 'Dorse', 'caravan' => 'Römork',
            'bodywork' => 'Karoser & Üst Yapı', 'recovery' => 'Oto Kurtarıcı & Taşıyıcı', 'commercial' => 'Ticari Hat & Ticari Plaka',
        ];
        $rows = $vehicles->map(fn ($v) => [
            $v->plate,
            $v->brand ?? '-',
            $v->series ?? '-',
            $v->model ?? '-',
            $v->year !== null ? (string) $v->year : '-',
            $typeLabels[$v->vehicle_type] ?? $v->vehicle_type ?? '-',
            $v->vehicle_subtype ? ($subtypeLabels[$v->vehicle_subtype] ?? $v->vehicle_subtype) : '-',
            $v->capacity_kg !== null ? (string) $v->capacity_kg : '-',
            $v->capacity_m3 !== null ? (string) $v->capacity_m3 : '-',
            $v->branch?->name ?? '-',
            $statusLabels[$v->status] ?? (string) $v->status,
            $v->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'araclar';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'vehicles', 'vehicle')
            : $this->exportService->csv($headers, $rows, $filename);
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create(): View
    {
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        $colors = [
            'Beyaz' => 'Beyaz',
            'Siyah' => 'Siyah',
            'Gri' => 'Gri',
            'Gümüş' => 'Gümüş',
            'Mavi' => 'Mavi',
            'Kırmızı' => 'Kırmızı',
            'Yeşil' => 'Yeşil',
            'Sarı' => 'Sarı',
            'Turuncu' => 'Turuncu',
            'Mor' => 'Mor',
            'Kahverengi' => 'Kahverengi',
            'Bej' => 'Bej',
            'Altın' => 'Altın',
            'Bronz' => 'Bronz',
            'Diğer' => 'Diğer',
        ];

        $hgsBanks = [
            'Akbank' => 'Akbank',
            'Albaraka Türk' => 'Albaraka Türk',
            'Alternatifbank' => 'Alternatifbank',
            'Anadolubank' => 'Anadolubank',
            'Bank of America' => 'Bank of America',
            'Bank of China' => 'Bank of China',
            'BankPozitif' => 'BankPozitif',
            'Birleşik Fon Bankası' => 'Birleşik Fon Bankası',
            'Burgan Bank' => 'Burgan Bank',
            'Citibank' => 'Citibank',
            'Denizbank' => 'Denizbank',
            'Deutsche Bank' => 'Deutsche Bank',
            'Fibabanka' => 'Fibabanka',
            'Garanti BBVA' => 'Garanti BBVA',
            'HSBC' => 'HSBC',
            'ICBC Turkey Bank' => 'ICBC Turkey Bank',
            'ING Bank' => 'ING Bank',
            'İş Bankası' => 'İş Bankası',
            'JPMorgan Chase Bank' => 'JPMorgan Chase Bank',
            'Kuveyt Türk' => 'Kuveyt Türk',
            'Odeabank' => 'Odeabank',
            'QNB Finansbank' => 'QNB Finansbank',
            'Şekerbank' => 'Şekerbank',
            'Türk Ekonomi Bankası (TEB)' => 'Türk Ekonomi Bankası (TEB)',
            'Türkiye Halk Bankası' => 'Türkiye Halk Bankası',
            'Türkiye İhracat Kredi Bankası' => 'Türkiye İhracat Kredi Bankası',
            'Türkiye Kalkınma ve Yatırım Bankası' => 'Türkiye Kalkınma ve Yatırım Bankası',
            'Türkiye Vakıflar Bankası' => 'Türkiye Vakıflar Bankası',
            'Türkiye Ziraat Bankası' => 'Türkiye Ziraat Bankası',
            'Yapı Kredi Bankası' => 'Yapı Kredi Bankası',
            'Ziraat Katılım' => 'Ziraat Katılım',
            'Vakıf Katılım' => 'Vakıf Katılım',
            'Albaraka Katılım' => 'Albaraka Katılım',
            'Kuveyt Türk Katılım' => 'Kuveyt Türk Katılım',
            'Türkiye Finans Katılım' => 'Türkiye Finans Katılım',
            'Ziraat Katılım Bankası' => 'Ziraat Katılım Bankası',
            'Vakıf Katılım Bankası' => 'Vakıf Katılım Bankası',
            'Diğer' => 'Diğer',
        ];

        $brands = \App\Models\Vehicle::select('brand')->distinct()->whereNotNull('brand')->orderBy('brand')->limit(1000)->pluck('brand');
        $models = \App\Models\Vehicle::select('model')->distinct()->whereNotNull('model')->orderBy('model')->limit(1000)->pluck('model');

        return view('admin.vehicles.create', compact('branches', 'colors', 'hgsBanks', 'brands', 'models'));
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (($data['brand'] ?? '') === 'other' && $request->filled('new_brand')) {
            $data['brand'] = $request->input('new_brand');
        }
        unset($data['new_brand']);
        if (($data['model'] ?? '') === 'other' && $request->filled('new_model')) {
            $data['model'] = $request->input('new_model');
        }
        unset($data['new_model']);
        $vehicle = $this->vehicleService->create($data);

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Araç başarıyla oluşturuldu.');
    }

    /**
     * Display the specified vehicle.
     */
    public function show(int $id): View
    {
        $vehicle = \App\Models\Vehicle::with(['branch', 'inspections', 'damages', 'workOrders'])->findOrFail($id);

        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit(int $id): View
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $branches = \App\Models\Branch::where('status', 1)->orderBy('name')->get();

        $colors = [
            'Beyaz' => 'Beyaz',
            'Siyah' => 'Siyah',
            'Gri' => 'Gri',
            'Gümüş' => 'Gümüş',
            'Mavi' => 'Mavi',
            'Kırmızı' => 'Kırmızı',
            'Yeşil' => 'Yeşil',
            'Sarı' => 'Sarı',
            'Turuncu' => 'Turuncu',
            'Mor' => 'Mor',
            'Kahverengi' => 'Kahverengi',
            'Bej' => 'Bej',
            'Altın' => 'Altın',
            'Bronz' => 'Bronz',
            'Diğer' => 'Diğer',
        ];

        $hgsBanks = [
            'Akbank' => 'Akbank',
            'Albaraka Türk' => 'Albaraka Türk',
            'Alternatifbank' => 'Alternatifbank',
            'Anadolubank' => 'Anadolubank',
            'Bank of America' => 'Bank of America',
            'Bank of China' => 'Bank of China',
            'BankPozitif' => 'BankPozitif',
            'Birleşik Fon Bankası' => 'Birleşik Fon Bankası',
            'Burgan Bank' => 'Burgan Bank',
            'Citibank' => 'Citibank',
            'Denizbank' => 'Denizbank',
            'Deutsche Bank' => 'Deutsche Bank',
            'Fibabanka' => 'Fibabanka',
            'Garanti BBVA' => 'Garanti BBVA',
            'HSBC' => 'HSBC',
            'ICBC Turkey Bank' => 'ICBC Turkey Bank',
            'ING Bank' => 'ING Bank',
            'İş Bankası' => 'İş Bankası',
            'JPMorgan Chase Bank' => 'JPMorgan Chase Bank',
            'Kuveyt Türk' => 'Kuveyt Türk',
            'Odeabank' => 'Odeabank',
            'QNB Finansbank' => 'QNB Finansbank',
            'Şekerbank' => 'Şekerbank',
            'Türk Ekonomi Bankası (TEB)' => 'Türk Ekonomi Bankası (TEB)',
            'Türkiye Halk Bankası' => 'Türkiye Halk Bankası',
            'Türkiye İhracat Kredi Bankası' => 'Türkiye İhracat Kredi Bankası',
            'Türkiye Kalkınma ve Yatırım Bankası' => 'Türkiye Kalkınma ve Yatırım Bankası',
            'Türkiye Vakıflar Bankası' => 'Türkiye Vakıflar Bankası',
            'Türkiye Ziraat Bankası' => 'Türkiye Ziraat Bankası',
            'Yapı Kredi Bankası' => 'Yapı Kredi Bankası',
            'Ziraat Katılım' => 'Ziraat Katılım',
            'Vakıf Katılım' => 'Vakıf Katılım',
            'Albaraka Katılım' => 'Albaraka Katılım',
            'Kuveyt Türk Katılım' => 'Kuveyt Türk Katılım',
            'Türkiye Finans Katılım' => 'Türkiye Finans Katılım',
            'Ziraat Katılım Bankası' => 'Ziraat Katılım Bankası',
            'Vakıf Katılım Bankası' => 'Vakıf Katılım Bankası',
            'Diğer' => 'Diğer',
        ];

        $brands = \App\Models\Vehicle::select('brand')->distinct()->whereNotNull('brand')->orderBy('brand')->limit(1000)->pluck('brand');
        $models = \App\Models\Vehicle::select('model')->distinct()->whereNotNull('model')->orderBy('model')->limit(1000)->pluck('model');

        return view('admin.vehicles.edit', compact('vehicle', 'branches', 'colors', 'hgsBanks', 'brands', 'models'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(UpdateVehicleRequest $request, int $id): RedirectResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $data = $request->validated();
        if (($data['brand'] ?? '') === 'other' && $request->filled('new_brand')) {
            $data['brand'] = $request->input('new_brand');
        }
        unset($data['new_brand']);
        if (($data['model'] ?? '') === 'other' && $request->filled('new_model')) {
            $data['model'] = $request->input('new_model');
        }
        unset($data['new_model']);
        $this->vehicleService->update($vehicle, $data);

        return redirect()->route('admin.vehicles.show', $vehicle)
            ->with('success', 'Araç başarıyla güncellendi.');
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(int $id): RedirectResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Araç başarıyla silindi.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:vehicles,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            \App\Models\Vehicle::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Seçili araçlar silindi.');
    }
}
