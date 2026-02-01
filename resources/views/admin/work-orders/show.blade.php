@extends('layouts.app')

@section('title', 'İş Emri Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">İş Emri Detayı</h2>
        <p class="text-secondary mb-0">İş Emri ID: #{{ $workOrder->id }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.work-orders.edit', $workOrder->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.work-orders.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Genel Bilgiler</h3>
            <dl class="row mb-0">
                <dt class="col-sm-4">Araç</dt>
                <dd class="col-sm-8">
                    @if($workOrder->vehicle)
                        <a href="{{ route('admin.vehicles.show', $workOrder->vehicle->id) }}" class="fw-bold text-primary">
                            {{ $workOrder->vehicle->plate }}
                        </a>
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-4">Servis Sağlayıcı</dt>
                <dd class="col-sm-8">{{ $workOrder->serviceProvider->name ?? '-' }}</dd>

                <dt class="col-sm-4">Tür</dt>
                <dd class="col-sm-8">{{ $workOrder->type ?? '-' }}</dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ match($workOrder->status) { 'completed' => 'success', 'in_progress' => 'primary', default => 'warning' } }}-200 text-{{ match($workOrder->status) { 'completed' => 'success', 'in_progress' => 'primary', default => 'warning' } }} rounded-pill px-3 py-2">
                        {{ ucfirst($workOrder->status) }}
                    </span>
                </dd>

                @if($workOrder->description)
                    <dt class="col-sm-4">Açıklama</dt>
                    <dd class="col-sm-8">{{ $workOrder->description }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
