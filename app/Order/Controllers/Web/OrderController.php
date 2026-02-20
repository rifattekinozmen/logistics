<?php

namespace App\Order\Controllers\Web;

use App\Core\Services\ExportService;
use App\Excel\Services\ExcelImportService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Order\Requests\StoreOrderRequest;
use App\Order\Requests\UpdateOrderRequest;
use App\Order\Services\OrderService;
use App\Order\Services\OrderStatusTransitionService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ExportService $exportService,
        protected ExcelImportService $excelImportService,
        protected OrderStatusTransitionService $transitionService
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View|StreamedResponse|Response
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);

        if ($request->has('export')) {
            return $this->export($filters, $request->get('export'));
        }

        $orders = $this->orderService->getPaginated($filters);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Sipariş listesini CSV veya XML olarak dışa aktar.
     */
    public function export(array $filters, string $format): StreamedResponse|Response
    {
        $orders = $this->orderService->getForExport($filters);

        $headers = [
            'Sipariş No',
            'Müşteri',
            'Durum',
            'Alış Adresi',
            'Teslimat Adresi',
            'Planlanan Teslimat',
            'Teslim Tarihi',
            'Ağırlık (kg)',
            'Hacim (m³)',
            'Oluşturulma',
        ];

        $rows = $orders->map(fn ($order) => [
            $order->order_number,
            $order->customer?->name ?? '-',
            $order->status,
            $order->pickup_address ?? '-',
            $order->delivery_address ?? '-',
            $order->planned_delivery_date?->format('d.m.Y H:i') ?? '-',
            $order->delivered_at?->format('d.m.Y H:i') ?? '-',
            $order->total_weight !== null ? (string) $order->total_weight : '-',
            $order->total_volume !== null ? (string) $order->total_volume : '-',
            $order->created_at->format('d.m.Y H:i'),
        ])->all();

        $filename = 'siparisler';

        return $format === 'xml'
            ? $this->exportService->xml($headers, $rows, $filename, 'orders', 'order')
            : $this->exportService->csv($headers, $rows, $filename);
    }

    /**
     * Sipariş import formu.
     */
    public function importForm(): View
    {
        return view('admin.orders.import');
    }

    /**
     * Sipariş import şablonu (CSV) indir.
     */
    public function importTemplate(): StreamedResponse
    {
        $headers = [
            'müşteri_id',
            'alış_adresi',
            'teslimat_adresi',
            'planlanan_teslimat_tarihi',
            'ağırlık_kg',
            'hacim_m3',
            'notlar',
        ];
        $example = [
            '1',
            'Örnek Alış Adresi',
            'Örnek Teslimat Adresi',
            '2025-02-15 10:00',
            '100',
            '2.5',
            'Örnek not',
        ];

        return $this->exportService->csv($headers, [$example], 'siparis_import_sablonu');
    }

    /**
     * Yüklenen CSV/Excel dosyasından sipariş oluştur.
     */
    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:20480',
        ]);

        try {
            $path = $request->file('file')->store('order-imports', 'private');
            $rows = $this->excelImportService->parseFile($path, 'private');
        } catch (Throwable $e) {
            return back()->withErrors(['file' => 'Dosya okunamadı: '.$e->getMessage()]);
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'Dosyada veri satırı bulunamadı.']);
        }

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // 1-based + header
            $normalized = $this->excelImportService->normalizeRow($row);
            $customerId = (int) ($normalized['müşteri_id'] ?? $normalized['customer_id'] ?? 0);
            if ($customerId < 1) {
                $errors[] = "Satır {$rowNumber}: Geçerli müşteri_id gerekli.";
                $failed++;

                continue;
            }
            if (! Customer::where('id', $customerId)->where('status', 1)->exists()) {
                $errors[] = "Satır {$rowNumber}: Müşteri bulunamadı (ID: {$customerId}).";
                $failed++;

                continue;
            }

            $pickup = trim($normalized['alış_adresi'] ?? $normalized['pickup_address'] ?? '');
            $delivery = trim($normalized['teslimat_adresi'] ?? $normalized['delivery_address'] ?? '');
            if ($pickup === '' || $delivery === '') {
                $errors[] = "Satır {$rowNumber}: Alış ve teslimat adresi zorunludur.";
                $failed++;

                continue;
            }

            $plannedDelivery = null;
            $dateStr = trim($normalized['planlanan_teslimat_tarihi'] ?? $normalized['planned_delivery_date'] ?? '');
            if ($dateStr !== '') {
                try {
                    $plannedDelivery = \Carbon\Carbon::parse($dateStr);
                } catch (Throwable) {
                    // Tarih parse edilemezse null bırak
                }
            }

            $weight = isset($normalized['ağırlık_kg']) ? (float) str_replace(',', '.', $normalized['ağırlık_kg']) : null;
            $volume = isset($normalized['hacim_m3']) ? (float) str_replace(',', '.', $normalized['hacim_m3']) : null;
            if ($weight !== null && $weight < 0) {
                $weight = null;
            }
            if ($volume !== null && $volume < 0) {
                $volume = null;
            }
            $notes = trim($normalized['notlar'] ?? $normalized['notes'] ?? '');

            try {
                DB::transaction(function () use ($customerId, $pickup, $delivery, $plannedDelivery, $weight, $volume, $notes) {
                    $this->orderService->create([
                        'customer_id' => $customerId,
                        'pickup_address' => $pickup,
                        'delivery_address' => $delivery,
                        'planned_pickup_date' => null,
                        'planned_delivery_date' => $plannedDelivery,
                        'total_weight' => $weight,
                        'total_volume' => $volume,
                        'notes' => $notes ?: null,
                    ], $request->user());
                });
                $success++;
            } catch (Throwable $e) {
                $errors[] = "Satır {$rowNumber}: ".$e->getMessage();
                $failed++;
            }
        }

        $message = "{$success} sipariş oluşturuldu.";
        if ($failed > 0) {
            $message .= " {$failed} satır atlandı.";
        }
        if (! empty($errors)) {
            return redirect()->route('admin.orders.index')
                ->with('success', $message)
                ->with('import_errors', array_slice($errors, 0, 20));
        }

        return redirect()->route('admin.orders.index')->with('success', $message);
    }

    /**
     * Show the form for creating a new order.
     */
    public function create(): View
    {
        $customers = Customer::where('status', 1)->orderBy('name')->get();

        return view('admin.orders.create', compact('customers'));
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create($request->validated(), $request->user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Sipariş başarıyla oluşturuldu.');
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): View
    {
        $order = \App\Models\Order::with(['customer', 'shipments.vehicle', 'shipments.driver', 'creator'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(int $id): View
    {
        $order = \App\Models\Order::findOrFail($id);

        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, int $id): RedirectResponse
    {
        $order = \App\Models\Order::findOrFail($id);

        $this->orderService->update($order, $request->validated());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Sipariş başarıyla güncellendi.');
    }

    /**
     * Remove the specified order.
     */
    public function destroy(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Sipariş başarıyla silindi.');
    }

    /**
     * SAP uyumlu durum geçişi gerçekleştirir.
     */
    public function transition(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,planned,assigned,loaded,in_transit,delivered,invoiced,cancelled',
        ], [
            'status.required' => 'Yeni durum seçimi zorunludur.',
            'status.in' => 'Geçersiz durum seçimi.',
        ]);

        try {
            $this->transitionService->transition($order, $request->input('status'), $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['transition' => $e->getMessage()]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Sipariş durumu güncellendi: '.$order->fresh()->status_label);
    }
}
