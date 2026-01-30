@extends('layouts.app')

@section('title', 'Depo Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Depo Detayı</h2>
        <p class="text-secondary mb-0">{{ $warehouse->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-light">
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
                <dt class="col-sm-4">Depo Adı</dt>
                <dd class="col-sm-8"><span class="fw-bold">{{ $warehouse->name }}</span></dd>

                <dt class="col-sm-4">Kod</dt>
                <dd class="col-sm-8">{{ $warehouse->code ?? '-' }}</dd>

                <dt class="col-sm-4">Şube</dt>
                <dd class="col-sm-8">{{ $warehouse->branch->name ?? '-' }}</dd>

                <dt class="col-sm-4">Adres</dt>
                <dd class="col-sm-8">{{ $warehouse->address ?? '-' }}</dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ $warehouse->status == 1 ? 'success' : 'secondary' }}-200 text-{{ $warehouse->status == 1 ? 'success' : 'secondary' }} rounded-pill px-3 py-2">
                        {{ $warehouse->status == 1 ? 'Aktif' : 'Pasif' }}
                    </span>
                </dd>
            </dl>
        </div>
    </div>
</div>
@endsection
