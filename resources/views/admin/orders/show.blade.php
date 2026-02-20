@extends('layouts.app')

@section('title', 'Sipariş Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Sipariş Detayı</h2>
        <p class="text-secondary mb-0">Sipariş #{{ $order->order_number }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Genel Bilgiler</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Sipariş No</label>
                    <p class="fw-bold text-dark mb-0">{{ $order->order_number }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <div>
                        @php
                            $statusColors = [
                                'pending'    => 'warning',
                                'planned'    => 'info',
                                'assigned'   => 'info',
                                'loaded'     => 'primary',
                                'in_transit' => 'primary',
                                'delivered'  => 'success',
                                'invoiced'   => 'success',
                                'cancelled'  => 'danger',
                            ];
                            $color = $statusColors[$order->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2 rounded-pill">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
                @if($order->sap_order_number)
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">SAP Sipariş No</label>
                    <p class="fw-bold text-dark mb-0 font-monospace">{{ $order->sap_order_number }}</p>
                </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Müşteri</label>
                    <p class="fw-bold text-dark mb-0">{{ $order->customer->name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Oluşturulma Tarihi</label>
                    <p class="text-dark mb-0">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Adres Bilgileri</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Alış Adresi</label>
                    <p class="text-dark mb-0">{{ $order->pickup_address }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Teslimat Adresi</label>
                    <p class="text-dark mb-0">{{ $order->delivery_address }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Planlanan Alış Tarihi</label>
                    <p class="text-dark mb-0">{{ $order->planned_pickup_date ? $order->planned_pickup_date->format('d.m.Y H:i') : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Planlanan Teslimat Tarihi</label>
                    <p class="text-dark mb-0">{{ $order->planned_delivery_date ? $order->planned_delivery_date->format('d.m.Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        @if($order->notes)
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-3">Notlar</h3>
            <p class="text-dark mb-0">{{ $order->notes }}</p>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Sipariş Detayları</h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-secondary">Toplam Ağırlık</label>
                    <p class="fw-bold text-dark mb-0">{{ $order->total_weight ? number_format($order->total_weight, 2).' kg' : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Toplam Hacim</label>
                    <p class="fw-bold text-dark mb-0">{{ $order->total_volume ? number_format($order->total_volume, 2).' m³' : '-' }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary">Tehlikeli Madde</label>
                    <p class="fw-bold text-dark mb-0">
                        <span class="badge bg-{{ $order->is_dangerous ? 'danger' : 'success' }} bg-opacity-10 text-{{ $order->is_dangerous ? 'danger' : 'success' }} px-3 py-2 rounded-pill">
                            {{ $order->is_dangerous ? 'Evet' : 'Hayır' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Hızlı İşlemler</h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </a>
                <a href="{{ route('admin.orders.document-flow', $order->id) }}" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">account_tree</span>
                    Doküman Akışı
                </a>
                <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Bu siparişi silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>

        {{-- SAP Durum Geçişi --}}
        @php
            $transitionService = app(\App\Order\Services\OrderStatusTransitionService::class);
            $nextStatuses = $transitionService->allowedNextStatuses($order->status);
            $nextStatusLabels = [
                'planned'    => ['label' => 'Planla', 'icon' => 'schedule', 'color' => 'info'],
                'assigned'   => ['label' => 'Ata',    'icon' => 'local_shipping', 'color' => 'primary'],
                'loaded'     => ['label' => 'Yükle',  'icon' => 'inventory_2', 'color' => 'primary'],
                'in_transit' => ['label' => 'Yola Çıkar', 'icon' => 'directions_car', 'color' => 'primary'],
                'delivered'  => ['label' => 'Teslim Et', 'icon' => 'check_circle', 'color' => 'success'],
                'invoiced'   => ['label' => 'Faturalandır', 'icon' => 'receipt', 'color' => 'success'],
                'cancelled'  => ['label' => 'İptal Et', 'icon' => 'cancel', 'color' => 'danger'],
            ];
        @endphp
        @if(count($nextStatuses) > 0)
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h6 fw-bold text-dark mb-3">Durum Geçişi</h3>
            @error('transition')
                <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
            @enderror
            <div class="d-flex flex-column gap-2">
                @foreach($nextStatuses as $nextStatus)
                    @php $meta = $nextStatusLabels[$nextStatus] ?? ['label' => $nextStatus, 'icon' => 'arrow_forward', 'color' => 'secondary']; @endphp
                    <form action="{{ route('admin.orders.transition', $order->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <button type="submit" class="btn btn-outline-{{ $meta['color'] }} w-100 d-flex align-items-center justify-content-center gap-2"
                            onclick="return confirm('{{ $meta['label'] }} işlemini onaylıyor musunuz?')">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $meta['icon'] }}</span>
                            {{ $meta['label'] }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- SAP Zaman Çizelgesi --}}
@if($order->planned_at || $order->invoiced_at)
<div class="bg-white rounded-3xl shadow-sm border p-4 mt-4">
    <h3 class="h5 fw-bold text-dark mb-4">SAP Durum Zaman Çizelgesi</h3>
    <div class="d-flex gap-4 flex-wrap">
        <div class="text-center">
            <div class="rounded-circle bg-success-200 d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px">
                <span class="material-symbols-outlined text-success" style="font-size:1.2rem">add_circle</span>
            </div>
            <div class="small fw-semibold">Oluşturuldu</div>
            <div class="text-secondary" style="font-size:0.75rem">{{ $order->created_at->format('d.m.Y') }}</div>
        </div>
        @if($order->planned_at)
        <div class="text-center">
            <div class="rounded-circle bg-info-200 d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px">
                <span class="material-symbols-outlined text-info" style="font-size:1.2rem">schedule</span>
            </div>
            <div class="small fw-semibold">Planlandı</div>
            <div class="text-secondary" style="font-size:0.75rem">{{ $order->planned_at->format('d.m.Y') }}</div>
        </div>
        @endif
        @if($order->delivered_at)
        <div class="text-center">
            <div class="rounded-circle bg-success-200 d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px">
                <span class="material-symbols-outlined text-success" style="font-size:1.2rem">check_circle</span>
            </div>
            <div class="small fw-semibold">Teslim Edildi</div>
            <div class="text-secondary" style="font-size:0.75rem">{{ $order->delivered_at->format('d.m.Y') }}</div>
        </div>
        @endif
        @if($order->invoiced_at)
        <div class="text-center">
            <div class="rounded-circle bg-success-200 d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px">
                <span class="material-symbols-outlined text-success" style="font-size:1.2rem">receipt</span>
            </div>
            <div class="small fw-semibold">Faturalandı</div>
            <div class="text-secondary" style="font-size:0.75rem">{{ $order->invoiced_at->format('d.m.Y') }}</div>
        </div>
        @endif
    </div>
</div>
@endif
@endsection
