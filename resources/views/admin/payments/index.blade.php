@extends('layouts.app')

@section('title', 'Finans - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">payments</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Ödemeler</h2>
            <p class="text-secondary mb-0">Tüm ödemeleri görüntüleyin ve yönetin</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                Dışa Aktar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.payments.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">table_chart</span>
                        CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.payments.index', array_merge(request()->query(), ['export' => 'xml'])) }}">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">code</span>
                        XML
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-payments d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
            Yeni Ödeme
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam Tutar (₺)" :value="number_format($stats['total_amount'] ?? 0, 2, ',', '.')" icon="payments" color="primary" />
    <x-index-stat-card title="Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" />
    <x-index-stat-card title="Ödendi" :value="$stats['paid'] ?? 0" icon="check_circle" color="success" />
    <x-index-stat-card title="Gecikmiş" :value="$stats['overdue'] ?? 0" icon="warning" color="danger" />
</div>

<div class="filter-area filter-area-payments rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Tür</label>
            <select name="type" class="form-select">
                <option value="">Tümü</option>
                <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Gelir</option>
                <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Gider</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Ödendi</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Gecikmiş</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Vade Tarihi Başlangıç</label>
            <input type="date" name="due_date_from" value="{{ request('due_date_from') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-payments w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Firma</th>
                    <th class="border-0 fw-semibold text-secondary small">Tür</th>
                    <th class="border-0 fw-semibold text-secondary small">Tutar</th>
                    <th class="border-0 fw-semibold text-secondary small">Vade Tarihi</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $payment->company?->name ?? '-' }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $payment->payment_type ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ number_format($payment->amount, 2) }} ₺</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $payment->due_date ? $payment->due_date->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'paid' => 'success',
                                'overdue' => 'danger',
                            ];
                            $softColors = [
                                'warning' => 'warning-200',
                                'success' => 'success-200',
                                'danger' => 'danger-200',
                            ];
                            $statusLabels = [
                                'pending' => 'Beklemede',
                                'paid' => 'Ödendi',
                                'overdue' => 'Gecikmiş',
                            ];
                            $color = $statusColors[$payment->status] ?? 'secondary';
                            $softColor = $softColors[$color] ?? 'secondary-200';
                            $label = $statusLabels[$payment->status] ?? $payment->status;
                        @endphp
                        <span class="badge bg-{{ $softColor }} text-{{ $color }} px-3 py-2 rounded-pill fw-semibold">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu ödemeyi silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0 hover:bg-danger hover:text-white transition-all" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">payments</span>
                            <p class="text-secondary mb-0">Henüz ödeme bulunmuyor.</p>
                            <a href="{{ route('admin.payments.create') }}" class="btn btn-payments btn-sm mt-2">İlk Ödemeyi Oluştur</a>
                        </div>
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
