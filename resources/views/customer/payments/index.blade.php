@extends('layouts.customer-app')

@section('title', 'Ödemelerim - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">payments</span>
            <h2 class="h3 fw-bold text-dark mb-0">Ödemelerim</h2>
        </div>
        <p class="text-secondary mb-0">Ödeme geçmişinizi görüntüleyin ve takip edin</p>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-warning rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">schedule</span>
                </div>
            </div>
            <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['total_pending'], 2) }} ₺</h3>
            <p class="small fw-semibold text-secondary mb-0">Bekleyen Ödeme</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-success rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">check_circle</span>
                </div>
            </div>
            <h3 class="h1 fw-bold text-dark mb-1">{{ number_format($stats['total_paid'], 2) }} ₺</h3>
            <p class="small fw-semibold text-secondary mb-0">Ödenen Toplam</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="bg-danger rounded-2xl d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <span class="material-symbols-outlined text-white" style="font-size: 1.75rem;">warning</span>
                </div>
            </div>
            <h3 class="h1 fw-bold text-dark mb-1">{{ $stats['overdue_count'] }}</h3>
            <p class="small fw-semibold text-secondary mb-0">Geciken Ödeme</p>
        </div>
    </div>
</div>

<!-- Filtreleme -->
<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('customer.payments.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Bekliyor</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ödendi</option>
                <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Gecikti</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-dark">Ödeme Türü</label>
            <select name="payment_type" class="form-select">
                <option value="">Tümü</option>
                <option value="customer" {{ request('payment_type') === 'customer' ? 'selected' : '' }}>Müşteri Ödemesi</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-dark">Vade Başlangıç</label>
            <input type="date" name="due_date_from" value="{{ request('due_date_from') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-dark">Vade Bitiş</label>
            <input type="date" name="due_date_to" value="{{ request('due_date_to') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<!-- Ödeme Listesi -->
<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Ödeme No</th>
                    <th class="border-0 small text-secondary fw-semibold">Tutar</th>
                    <th class="border-0 small text-secondary fw-semibold">Vade Tarihi</th>
                    <th class="border-0 small text-secondary fw-semibold">Ödeme Tarihi</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">#{{ $payment->id }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="fw-bold text-dark">{{ number_format($payment->amount, 2) }} ₺</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $payment->due_date->format('d.m.Y') }}
                                @if($payment->due_date->isPast() && $payment->status == 0)
                                    <span class="badge bg-danger-200 text-danger rounded-pill px-2 py-1 ms-2">Gecikmiş</span>
                                @endif
                            </small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $payment->paid_date?->format('d.m.Y') ?? '-' }}
                            </small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($payment->status) { 1 => 'success', 2 => 'danger', default => 'warning' } }}-200 text-{{ match($payment->status) { 1 => 'success', 2 => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($payment->status) { 1 => 'Ödendi', 2 => 'Gecikti', default => 'Bekliyor' } }}
                            </span>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('customer.payments.show', $payment) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">payments</span>
                            <p class="text-secondary mb-0">Henüz ödeme kaydı bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="p-4 border-top">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
