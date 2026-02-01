@extends('layouts.app')

@section('title', 'Ödeme Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Ödeme Detayı</h2>
        <p class="text-secondary mb-0">Ödeme ID: #{{ $payment->id }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
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
                <dt class="col-sm-4">Ödeme Türü</dt>
                <dd class="col-sm-8"><span class="fw-bold">{{ ucfirst($payment->payment_type ?? '-') }}</span></dd>

                <dt class="col-sm-4">Tutar</dt>
                <dd class="col-sm-8"><span class="fw-bold text-dark fs-5">{{ number_format($payment->amount, 2) }} ₺</span></dd>

                <dt class="col-sm-4">Vade Tarihi</dt>
                <dd class="col-sm-8">{{ $payment->due_date?->format('d.m.Y') ?? '-' }}</dd>

                <dt class="col-sm-4">Ödeme Tarihi</dt>
                <dd class="col-sm-8">{{ $payment->paid_date?->format('d.m.Y') ?? '-' }}</dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ $payment->status == 1 ? 'success' : 'warning' }}-200 text-{{ $payment->status == 1 ? 'success' : 'warning' }} rounded-pill px-3 py-2">
                        {{ $payment->status == 1 ? 'Ödendi' : 'Bekliyor' }}
                    </span>
                </dd>

                <dt class="col-sm-4">Ödeme Yöntemi</dt>
                <dd class="col-sm-8">{{ $payment->payment_method ?? '-' }}</dd>

                <dt class="col-sm-4">Referans No</dt>
                <dd class="col-sm-8">{{ $payment->reference_number ?? '-' }}</dd>

                @if($payment->notes)
                    <dt class="col-sm-4">Notlar</dt>
                    <dd class="col-sm-8">{{ $payment->notes }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
