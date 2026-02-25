<?php

namespace App\Shipment\Controllers\Web;

use App\Core\Services\ExportService;
use App\DocumentFlow\Services\DocumentFlowService;
use App\Events\ShipmentDelivered;
use App\Http\Controllers\Controller;
use App\Order\Services\OrderStatusTransitionService;
use App\Order\Services\OrderWorkflowGuardService;
use App\Models\ShipmentPlan;
use App\Shipment\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShipmentController extends Controller
{
    public function __construct(
        protected ExportService $exportService,
        protected DocumentFlowService $documentFlowService,
        protected OrderWorkflowGuardService $workflowGuardService,
        protected OrderStatusTransitionService $orderStatusTransitionService,
        protected ShipmentService $shipmentService
    ) {}

    /**
     * Display a listing of shipments.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['status', 'order_id', 'vehicle_id', 'date_from', 'date_to', 'workflow', 'sort', 'direction']);

        if ($request->filled('workflow')) {
            $filters['status'] = match ($request->string('workflow')->toString()) {
                'planned', 'loading' => 'pending',
                'in_transit' => 'in_transit',
                'delivered' => 'delivered',
                default => $filters['status'] ?? null,
            };
        }

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $shipments = $this->buildQuery($filters)
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total' => \App\Models\Shipment::count(),
            'active' => \App\Models\Shipment::whereIn('status', ['pending', 'in_transit'])->count(),
            'delivered' => \App\Models\Shipment::where('status', 'delivered')->count(),
        ];

        return view('admin.shipments.index', compact('shipments', 'stats'));
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
            ->tap(function ($query) use ($filters) {
                $sort = $filters['sort'] ?? null;
                $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

                $sortableColumns = [
                    'id' => 'id',
                    'order_id' => 'order_id',
                    'status' => 'status',
                    'pickup_date' => 'pickup_date',
                    'created_at' => 'created_at',
                ];

                if ($sort !== null && \array_key_exists($sort, $sortableColumns)) {
                    $query->orderBy($sortableColumns[$sort], $direction);
                } else {
                    $query->orderBy('pickup_date', 'desc');
                }
            });
    }

    /**
     * Apply bulk actions to shipments.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array'],
            'selected.*' => ['integer', 'exists:shipments,id'],
            'action' => ['required', 'string', 'in:delete'],
        ]);

        $ids = $validated['selected'];

        if ($validated['action'] === 'delete') {
            \App\Models\Shipment::whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Seçili sevkiyatlar için toplu işlem uygulandı.');
    }

    /**
     * Show the form for creating a new shipment.
     */
    public function create(Request $request): View
    {
        $orderId = $request->integer('order_id');
        $orders = \App\Models\Order::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->get();
        $vehicles = \App\Models\Vehicle::where('status', 1)->orderBy('plate')->get();
        $employees = \App\Models\Employee::where('status', 1)->orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.shipments.create', compact('orders', 'vehicles', 'employees', 'orderId'));
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

        $order = \App\Models\Order::findOrFail($validated['order_id']);
        if (! $this->workflowGuardService->canCreateShipment($order)) {
            abort(403, 'Ödeme onaylanmadan veya uygun sipariş durumuna gelmeden sevkiyat oluşturulamaz.');
        }

        $plan = ShipmentPlan::firstOrCreate(
            ['order_id' => $order->id],
            [
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'] ?? null,
                'planned_pickup_date' => $validated['pickup_date'],
                'planned_delivery_date' => $validated['delivery_date'] ?? null,
                'status' => 'planned',
            ]
        );

        $shipment = $this->shipmentService->startShipment($plan, $validated);

        $this->documentFlowService->recordDeliveryStep($order, $shipment);

        if (in_array($order->status, ['pending', 'planned'], true)
            && $this->orderStatusTransitionService->isValidTransition($order->status, 'assigned')) {
            $this->orderStatusTransitionService->transition($order, 'assigned', $request->user());
        }

        if ($shipment->status === 'delivered') {
            event(new ShipmentDelivered($shipment));
        }

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

        $order = \App\Models\Order::findOrFail($validated['order_id']);
        $targetStatus = $validated['status'];

        if ($targetStatus === 'delivered' && ! $this->workflowGuardService->canMarkDelivered($order, $shipment)) {
            return back()->withErrors([
                'status' => 'Teslim işaretlemek için sevkiyatın önce yolda olması gerekir.',
            ])->withInput();
        }

        $previousStatus = $shipment->status;
        $shipment->update($validated);

        if ($previousStatus !== 'delivered' && $shipment->fresh()->status === 'delivered') {
            $shipment = $this->shipmentService->markDelivered($shipment->fresh(), $validated);
        }

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
