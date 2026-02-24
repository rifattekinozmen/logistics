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
        $filters = $request->only(['status', 'branch_id', 'vehicle_type']);

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

        $headers = ['Plaka', 'Marka', 'Model', 'Yıl', 'Tip', 'Kapasite (kg)', 'Kapasite (m³)', 'Şube', 'Durum', 'Oluşturulma'];

        $typeLabels = ['truck' => 'Kamyon', 'van' => 'Minibüs', 'car' => 'Araba', 'trailer' => 'Römork'];
        $statusLabels = [0 => 'Pasif', 1 => 'Aktif', 2 => 'Bakımda'];

        $rows = $vehicles->map(fn ($v) => [
            $v->plate,
            $v->brand ?? '-',
            $v->model ?? '-',
            $v->year !== null ? (string) $v->year : '-',
            $typeLabels[$v->vehicle_type] ?? $v->vehicle_type ?? '-',
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

        return view('admin.vehicles.create', compact('branches'));
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $vehicle = $this->vehicleService->create($request->validated());

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

        return view('admin.vehicles.edit', compact('vehicle', 'branches'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(UpdateVehicleRequest $request, int $id): RedirectResponse
    {
        $vehicle = \App\Models\Vehicle::findOrFail($id);

        $this->vehicleService->update($vehicle, $request->validated());

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
}
