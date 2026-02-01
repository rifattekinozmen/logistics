@extends('layouts.app')

@section('title', 'Teslimat İmport Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Teslimat İmport Detayı</h2>
        <p class="text-secondary mb-0">
            Dosya: <span class="fw-semibold">{{ $batch->file_name }}</span>
        </p>
    </div>
    <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Listeye Dön
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3">
            <div class="small text-secondary mb-1">Durum</div>
            @php
                $statusColors = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'completed' => 'success',
                    'failed' => 'danger',
                ];
                $statusLabels = [
                    'pending' => 'Beklemede',
                    'processing' => 'İşleniyor',
                    'completed' => 'Tamamlandı',
                    'failed' => 'Hata',
                ];
                $color = $statusColors[$batch->status] ?? 'secondary';
                $label = $statusLabels[$batch->status] ?? $batch->status;
            @endphp
            <span class="badge bg-{{ $color }}-200 text-{{ $color }} rounded-pill px-3 py-2 fw-semibold">
                {{ $label }}
            </span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3">
            <div class="small text-secondary mb-1">Toplam Satır</div>
            <div class="fw-bold text-dark">{{ $batch->total_rows ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3">
            <div class="small text-secondary mb-1">Başarılı / Hatalı</div>
            <div class="fw-bold text-dark">
                {{ $batch->successful_rows ?? 0 }} / {{ $batch->failed_rows ?? 0 }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bg-white rounded-3xl shadow-sm border p-3">
            <div class="small text-secondary mb-1">Yükleyen</div>
            <div class="fw-bold text-dark">
                {{ $batch->importer?->name ?? '-' }}
            </div>
            <div class="small text-secondary">
                {{ $batch->created_at?->format('d.m.Y H:i') ?? '-' }}
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Teslimat No</th>
                    <th class="border-0 small text-secondary fw-semibold">Müşteri</th>
                    <th class="border-0 small text-secondary fw-semibold">Telefon</th>
                    <th class="border-0 small text-secondary fw-semibold">Adres</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold">Sipariş</th>
                    <th class="border-0 small text-secondary fw-semibold">Hata</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryNumbers as $deliveryNumber)
                    <tr>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $deliveryNumber->delivery_number }}</span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $deliveryNumber->customer_name }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $deliveryNumber->customer_phone }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ Str::limit($deliveryNumber->delivery_address, 40) }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $deliveryNumber->status }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $deliveryNumber->order?->order_number ?? '-' }}
                            </small>
                        </td>
                        <td class="align-middle">
                            <small class="text-danger">
                                {{ $deliveryNumber->error_message ?? '-' }}
                            </small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <p class="text-secondary mb-0">
                                Bu import için henüz teslimat kaydı oluşturulmamış.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($deliveryNumbers->hasPages())
        <div class="p-4 border-top">
            {{ $deliveryNumbers->links() }}
        </div>
    @endif
</div>
@endsection

