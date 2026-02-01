@extends('layouts.customer-app')

@section('title', 'Ödeme Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">payments</span>
            <h2 class="h3 fw-bold text-dark mb-0">Ödeme Detayı</h2>
        </div>
        <p class="text-secondary mb-0">Ödeme No: <span class="fw-semibold">#{{ $payment->id }}</span></p>
    </div>
    <a href="{{ route('customer.payments.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Listeye Dön
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h5 class="fw-bold mb-3">Ödeme Bilgileri</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Ödeme No</dt>
                <dd class="col-sm-8"><span class="fw-bold">#{{ $payment->id }}</span></dd>

                <dt class="col-sm-4">Tutar</dt>
                <dd class="col-sm-8">
                    <span class="fw-bold text-dark fs-5">{{ number_format($payment->amount, 2) }} ₺</span>
                </dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ match($payment->status) { 1 => 'success', 2 => 'danger', default => 'warning' } }}-200 text-{{ match($payment->status) { 1 => 'success', 2 => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                        {{ match($payment->status) { 1 => 'Ödendi', 2 => 'Gecikti', default => 'Bekliyor' } }}
                    </span>
                </dd>

                <dt class="col-sm-4">Vade Tarihi</dt>
                <dd class="col-sm-8">
                    {{ $payment->due_date->format('d.m.Y') }}
                    @if($payment->due_date->isPast() && $payment->status == 0)
                        <span class="badge bg-danger-200 text-danger rounded-pill px-2 py-1 ms-2">Gecikmiş</span>
                    @endif
                </dd>

                @if($payment->paid_date)
                    <dt class="col-sm-4">Ödeme Tarihi</dt>
                    <dd class="col-sm-8">{{ $payment->paid_date->format('d.m.Y') }}</dd>
                @endif

                @if($payment->payment_method)
                    <dt class="col-sm-4">Ödeme Yöntemi</dt>
                    <dd class="col-sm-8">{{ $payment->payment_method }}</dd>
                @endif

                @if($payment->reference_number)
                    <dt class="col-sm-4">Referans No</dt>
                    <dd class="col-sm-8">{{ $payment->reference_number }}</dd>
                @endif

                @if($payment->notes)
                    <dt class="col-sm-4">Notlar</dt>
                    <dd class="col-sm-8">{{ $payment->notes }}</dd>
                @endif

                <dt class="col-sm-4">Oluşturulma Tarihi</dt>
                <dd class="col-sm-8">{{ $payment->created_at->format('d.m.Y H:i') }}</dd>
            </dl>
        </div>
    </div>

    <div class="col-md-4">
        @if($payment->status == 0)
            <div class="bg-warning-200 rounded-3xl shadow-sm border border-warning p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-warning" style="font-size: 1.5rem;">schedule</span>
                    <h5 class="fw-bold mb-0">Bekleyen Ödeme</h5>
                </div>
                <p class="small text-secondary mb-0">
                    Bu ödeme henüz ödenmemiş. Vade tarihi: <strong>{{ $payment->due_date->format('d.m.Y') }}</strong>
                </p>
            </div>
        @elseif($payment->status == 1)
            <div class="bg-success-200 rounded-3xl shadow-sm border border-success p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-success" style="font-size: 1.5rem;">check_circle</span>
                    <h5 class="fw-bold mb-0">Ödeme Tamamlandı</h5>
                </div>
                <p class="small text-secondary mb-0">
                    Bu ödeme <strong>{{ $payment->paid_date->format('d.m.Y') }}</strong> tarihinde ödenmiştir.
                </p>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h5 class="fw-bold mb-3">Hızlı İşlemler</h5>
            <div class="d-flex flex-column gap-2">
                @if(Auth::user() && Auth::user()->hasPermission('customer.portal.invoices.view'))
                    <a href="{{ route('customer.invoices.index', ['payment_id' => $payment->id]) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">receipt_long</span>
                        İlgili Faturalar
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
