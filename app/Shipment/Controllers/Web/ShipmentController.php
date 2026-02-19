<?php

namespace App\Shipment\Controllers\Web;

use App\Core\Services\ExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShipmentController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    /**
     * Display a listing of shipments.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['status', 'order_id', 'vehicle_id', 'date_from', 'date_to']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $shipments = $this->buildQuery($filters)->paginate(25);

        return view('admin.shipments.index', compact('shipments'));
    }

    /**
     * Sevkiyat listesini CSV veya XML olarak dışa aktar.
     */
    protected function export(array $filters, string $format): StreamedResponse|Response
    {
        $shipments = $this->buildQuery($filters)->get();

        $headers = [
            'Sevkiyat No',
            'Sipariş No',
            'Araç',
            'Sürücü',
            'Durum',
            'Alış Tarihi',
            'Teslim Tarihi',
            'Oluşturulma',
        ];

        $rows = $shipments->map(fn ($s) => [
            (string) $s->id,
            $s->order?->order_number ?? '-',
            $s->vehicle?->plate ?? '-',
            $s->driver?->name ?? '-',
            $s->status,
            $s->pickup_date?->format('d.m.Y H:i') ?? '-',
            $s->delivery_date?->format('d.m.Y H:i') ?? '-',
            $s->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'sevkiyatlar';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'shipments', 'shipment')
            : $this->exportService->csv($headers, $rows, $filename);
    }

    protected function buildQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        return \App\Models\Shipment::query()
            ->with(['order', 'vehicle', 'driver'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['order_id'] ?? null, fn ($q, $orderId) => $q->where('order_id', $orderId))
            ->when($filters['vehicle_id'] ?? null, fn ($q, $vehicleId) => $q->where('vehicle_id', $vehicleId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('pickup_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('pickup_date', '<=', $date))
            ->orderBy('pickup_date', 'desc');
    }

    /**
     * Show the form for creating a new shipment.
     */
    public function create(): View
    {
        $orders = \App\Models\Order::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->get();
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.shipments.create', compact('orders', 'vehicles', 'employees'));
    }

    /**
     * Store a newly created shipment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:employees,id',
            'pickup_date' => 'required|date',
            'delivery_date' => 'nullable|date|after:pickup_date',
            'status' => 'required|string|max:50',
        ]);

        $shipment = \App\Models\Shipment::create($validated);

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('success', 'Sevkiyat başarıyla oluşturuldu.');
    }

    /**
     * Display the specified shipment.
     */
    public function show(int $id): View
    {
        $shipment = \App\Models\Shipment::with(['order', 'vehicle', 'driver'])->findOrFail($id);

        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Show the form for editing the specified shipment.
     */
    public function edit(int $id): View
    {
        $shipment = \App\Models\Shipment::findOrFail($id);
        $orders = \App\Models\Order::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->get();
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.shipments.edit', compact('shipment', 'orders', 'vehicles', 'employees'));
    }

    /**
     * Update the specified shipment.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $shipment = \App\Models\Shipment::findOrFail($id);

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:employees,id',
            'pickup_date' => 'required|date',
            'delivery_date' => 'nullable|date|after:pickup_date',
            'status' => 'required|string|max:50',
        ]);

        $shipment->update($validated);

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('success', 'Sevkiyat başarıyla güncellendi.');
    }

    /**
     * Remove the specified shipment.
     */
    public function destroy(int $id): RedirectResponse
    {
        $shipment = \App\Models\Shipment::findOrFail($id);
        $shipment->delete();

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Sevkiyat başarıyla silindi.');
    }
}
