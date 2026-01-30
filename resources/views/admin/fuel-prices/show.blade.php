@extends('layouts.app')

@section('title', 'Motorin Fiyat Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Motorin Fiyat Detayı</h2>
        <p class="text-secondary mb-0">
            Tarih: <span class="fw-semibold">{{ $fuelPrice->price_date->format('d.m.Y') }}</span>
        </p>
    </div>
    <a href="{{ route('admin.fuel-prices.index') }}" class="btn btn-outline-secondary">
        Listeye Dön
    </a>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h5 class="fw-bold mb-3">Fiyat Bilgileri</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Tarih</dt>
                <dd class="col-sm-8">{{ $fuelPrice->price_date->format('d.m.Y') }}</dd>

                <dt class="col-sm-4">Fiyat Türü</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ $fuelPrice->price_type === 'purchase' ? 'info' : 'warning' }}-200 text-{{ $fuelPrice->price_type === 'purchase' ? 'info' : 'warning' }} rounded-pill px-3 py-2">
                        {{ $fuelPrice->price_type === 'purchase' ? 'Satın Alma' : 'İstasyon' }}
                    </span>
                </dd>

                <dt class="col-sm-4">Fiyat</dt>
                <dd class="col-sm-8">
                    <span class="fw-bold text-dark fs-5">{{ number_format($fuelPrice->price, 4) }} ₺/Litre</span>
                </dd>

                <dt class="col-sm-4">Tedarikçi</dt>
                <dd class="col-sm-8">{{ $fuelPrice->supplier_name ?? '-' }}</dd>

                <dt class="col-sm-4">Bölge</dt>
                <dd class="col-sm-8">{{ $fuelPrice->region ?? '-' }}</dd>

                <dt class="col-sm-4">Notlar</dt>
                <dd class="col-sm-8">{{ $fuelPrice->notes ?? '-' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
