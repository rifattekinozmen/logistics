@extends('layouts.app')

@section('title', 'İş Ortağı Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">{{ $partner->name }}</h2>
        <p class="text-secondary mb-0 font-monospace">{{ $partner->partner_number }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.business-partners.edit', $partner->id) }}" class="btn btn-primary">Düzenle</a>
        <a href="{{ route('admin.business-partners.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h5 fw-bold text-dark mb-4">Genel Bilgiler</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">BP No</label>
                    <p class="fw-bold font-monospace text-dark mb-0">{{ $partner->partner_number }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Tür</label>
                    @php $typeLabels = ['customer' => 'Müşteri', 'vendor' => 'Tedarikçi', 'carrier' => 'Taşıyıcı', 'both' => 'Müşteri & Tedarikçi']; @endphp
                    <p class="mb-0"><span class="badge bg-primary-200 text-primary px-3 py-2 rounded-pill">{{ $typeLabels[$partner->partner_type] ?? $partner->partner_type }}</span></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Vergi No</label>
                    <p class="text-dark mb-0">{{ $partner->tax_number ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Vergi Dairesi</label>
                    <p class="text-dark mb-0">{{ $partner->tax_office ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Telefon</label>
                    <p class="text-dark mb-0">{{ $partner->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">E-posta</label>
                    <p class="text-dark mb-0">{{ $partner->email ?? '-' }}</p>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold text-secondary">Adres</label>
                    <p class="text-dark mb-0">{{ $partner->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        @if($partner->customers->isNotEmpty())
        <div class="bg-white rounded-3xl shadow-sm border p-4 mt-4">
            <h3 class="h5 fw-bold text-dark mb-3">Bağlı Müşteriler</h3>
            <ul class="list-group list-group-flush">
                @foreach($partner->customers as $customer)
                <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                    <span>{{ $customer->name }}</span>
                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h5 fw-bold text-dark mb-4">Ticari Koşullar</h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-secondary">Para Birimi</label>
                    <p class="fw-bold text-dark mb-0">{{ $partner->currency }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Ödeme Koşulu</label>
                    <p class="fw-bold text-dark mb-0">{{ $partner->payment_terms ?? '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Kredi Limiti</label>
                    <p class="fw-bold text-dark mb-0">{{ $partner->credit_limit ? number_format($partner->credit_limit, 2).' '.$partner->currency : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <p class="mb-0">
                        <span class="badge bg-{{ $partner->status ? 'success' : 'danger' }}-200 text-{{ $partner->status ? 'success' : 'danger' }} px-3 py-2 rounded-pill">
                            {{ $partner->status ? 'Aktif' : 'Pasif' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
