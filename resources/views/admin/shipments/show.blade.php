@extends('layouts.app')

@section('title', 'Sevkiyat Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Sevkiyat Detayı</h2>
        <p class="text-secondary mb-0">Sevkiyat ID: #{{ $shipment->id }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.shipments.edit', $shipment->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.shipments.index') }}" class="btn btn-light">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Genel Bilgiler</h3>
            <dl class="row mb-0">
                <dt class="col-sm-4">Sipariş</dt>
                <dd class="col-sm-8">
                    @if($shipment->order)
                        <a href="{{ route('admin.orders.show', $shipment->order->id) }}" class="fw-bold text-primary">
                            {{ $shipment->order->order_number }}
                        </a>
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-4">Araç</dt>
                <dd class="col-sm-8">{{ $shipment->vehicle->plate ?? '-' }}</dd>

                <dt class="col-sm-4">Şoför</dt>
                <dd class="col-sm-8">
                    @if($shipment->driver)
                        {{ $shipment->driver->first_name }} {{ $shipment->driver->last_name }}
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ match($shipment->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }}-200 text-{{ match($shipment->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                        {{ ucfirst($shipment->status) }}
                    </span>
                </dd>

                <dt class="col-sm-4">Alış Tarihi</dt>
                <dd class="col-sm-8">{{ $shipment->pickup_date?->format('d.m.Y H:i') ?? '-' }}</dd>

                <dt class="col-sm-4">Teslimat Tarihi</dt>
                <dd class="col-sm-8">{{ $shipment->delivery_date?->format('d.m.Y H:i') ?? '-' }}</dd>

                @if($shipment->notes)
                    <dt class="col-sm-4">Notlar</dt>
                    <dd class="col-sm-8">{{ $shipment->notes }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
