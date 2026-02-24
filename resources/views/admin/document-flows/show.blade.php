@extends('layouts.app')

@section('title', 'Doküman Akışı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Doküman Akışı</h2>
        <p class="text-secondary mb-0">Sipariş #{{ $order->order_number }} — {{ $order->customer?->name ?? 'Müşteri' }}</p>
    </div>
    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Siparişe Dön
    </a>
</div>

{{-- Sipariş Özeti --}}
<div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
    <h4 class="h6 fw-bold text-dark mb-3">Sipariş Özeti</h4>
    <div class="row g-3 small">
        <div class="col-md-6">
            <span class="text-secondary">Müşteri:</span>
            <span class="fw-semibold ms-1">{{ $order->customer?->name ?? '-' }}</span>
        </div>
        <div class="col-md-6">
            <span class="text-secondary">Alış:</span>
            <span class="ms-1 text-break">{{ Str::limit($order->pickup_address, 50) }}</span>
        </div>
        <div class="col-md-6">
            <span class="text-secondary">Teslimat:</span>
            <span class="ms-1 text-break">{{ Str::limit($order->delivery_address, 50) }}</span>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">Sipariş Detayı</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    @php
        $stepLabels = [
            'order_created'     => ['label' => 'Sipariş Oluşturuldu', 'icon' => 'add_circle', 'color' => 'success'],
            'delivery_assigned' => ['label' => 'Teslimat Atandı',    'icon' => 'local_shipping', 'color' => 'primary'],
            'invoice_created'   => ['label' => 'Fatura Oluşturuldu', 'icon' => 'receipt',        'color' => 'success'],
        ];
        $modelLabels = [
            \App\Models\Order::class    => 'Sipariş',
            \App\Models\Shipment::class => 'Sevkiyat',
            \App\Models\Payment::class  => 'Ödeme/Fatura',
        ];
    @endphp

    @if($chain->isEmpty())
        <div class="text-center py-5">
            <span class="material-symbols-outlined text-secondary d-block mb-2" style="font-size:3rem">account_tree</span>
            <p class="text-secondary">Bu sipariş için henüz doküman akışı kaydı bulunmuyor.</p>
        </div>
    @else
        <div class="position-relative">
            @foreach($chain as $index => $flow)
                @php $meta = $stepLabels[$flow->step] ?? ['label' => $flow->step, 'icon' => 'arrow_forward', 'color' => 'secondary']; @endphp
                <div class="d-flex gap-3 {{ !$loop->last ? 'mb-4' : '' }}">
                    <div class="d-flex flex-column align-items-center">
                        <div class="rounded-circle bg-{{ $meta['color'] }}-200 d-inline-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:44px;height:44px">
                            <span class="material-symbols-outlined text-{{ $meta['color'] }}" style="font-size:1.3rem">{{ $meta['icon'] }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="bg-secondary-200" style="width:2px;height:32px;margin-top:4px"></div>
                        @endif
                    </div>
                    <div class="flex-grow-1 pb-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="fw-semibold text-dark">{{ $meta['label'] }}</span>
                                <div class="small text-secondary mt-1">
                                    <span class="me-3">
                                        <strong>Kaynak:</strong>
                                        @if($flow->source_type === \App\Models\Order::class)
                                            <a href="{{ route('admin.orders.show', $flow->source_id) }}">{{ $modelLabels[$flow->source_type] ?? 'Sipariş' }} #{{ $order->order_number }}</a>
                                        @else
                                            {{ $modelLabels[$flow->source_type] ?? class_basename($flow->source_type) }} #{{ $flow->source_id }}
                                        @endif
                                    </span>
                                    @if($flow->target_type)
                                    <span>
                                        <strong>Hedef:</strong>
                                        @if($flow->target_type === \App\Models\Shipment::class && isset($shipments[$flow->target_id]))
                                            <a href="{{ route('admin.shipments.show', $flow->target_id) }}">Sevkiyat #{{ $flow->target_id }}</a>
                                        @else
                                            {{ $modelLabels[$flow->target_type] ?? class_basename($flow->target_type) }} #{{ $flow->target_id }}
                                        @endif
                                    </span>
                                    @endif
                                </div>
                                @if($flow->step === 'delivery_assigned' && $flow->target_type === \App\Models\Shipment::class && isset($shipments[$flow->target_id]))
                                @php $shipment = $shipments[$flow->target_id]; @endphp
                                <div class="small mt-2 p-2 rounded bg-secondary-200">
                                    <div><strong>Araç:</strong> {{ $shipment->vehicle?->plate ?? '-' }} {{ $shipment->vehicle ? "({$shipment->vehicle->brand} {$shipment->vehicle->model})" : '' }}</div>
                                    <div><strong>Şoför:</strong> {{ $shipment->driver ? trim($shipment->driver->first_name . ' ' . $shipment->driver->last_name) : '-' }}</div>
                                    @if($shipment->qr_code)
                                    <div><strong>QR:</strong> <code>{{ $shipment->qr_code }}</code></div>
                                    @endif
                                </div>
                                @endif
                                @if($flow->source_sap_doc_number || $flow->target_sap_doc_number)
                                <div class="small mt-1">
                                    @if($flow->source_sap_doc_number)
                                        <span class="badge bg-info-200 text-info font-monospace me-2">SAP: {{ $flow->source_sap_doc_number }}</span>
                                    @endif
                                    @if($flow->target_sap_doc_number)
                                        <span class="badge bg-info-200 text-info font-monospace">SAP Hedef: {{ $flow->target_sap_doc_number }}</span>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="text-end">
                                @if($flow->completed_at)
                                    <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill">Tamamlandı</span>
                                    <div class="small text-secondary mt-1">{{ $flow->completed_at->format('d.m.Y H:i') }}</div>
                                @else
                                    <span class="badge bg-warning-200 text-warning px-3 py-2 rounded-pill">Beklemede</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Özet --}}
        <div class="border-top mt-4 pt-4">
            <div class="row g-3">
                <div class="col-auto">
                    <span class="small text-secondary">Toplam Adım:</span>
                    <span class="fw-bold text-dark ms-1">{{ $chain->count() }}</span>
                </div>
                <div class="col-auto">
                    <span class="small text-secondary">Tamamlanan:</span>
                    <span class="fw-bold text-success ms-1">{{ $chain->whereNotNull('completed_at')->count() }}</span>
                </div>
                @if($chain->whereNotNull('source_sap_doc_number')->isNotEmpty() || $chain->whereNotNull('target_sap_doc_number')->isNotEmpty())
                <div class="col-auto">
                    <span class="small text-secondary">SAP Bağlantısı:</span>
                    <span class="badge bg-info-200 text-info ms-1">Aktif</span>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
