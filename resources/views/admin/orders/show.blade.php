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

{{-- Lineer Sipariş Akışı --}}
<div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
    <h3 class="h5 fw-bold text-dark mb-4">Sipariş Yaşam Döngüsü</h3>
    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
        @foreach(($timelineSteps ?? []) as $index => $step)
            @php
                $colorClass = 'bg-secondary-200 text-secondary';
                if ($step['problem']) {
                    $colorClass = 'bg-danger-200 text-danger';
                } elseif ($step['done']) {
                    $colorClass = 'bg-success-200 text-success';
                } elseif ($step['active']) {
                    $colorClass = 'bg-primary-200 text-primary';
                }
            @endphp
            <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 140px;">
                <div class="d-flex flex-column align-items-center text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $colorClass }}" style="width:40px;height:40px;">
                        <span class="material-symbols-outlined" style="font-size:1.1rem;">
                            {{ $step['done'] ? 'check' : ($step['problem'] ? 'warning' : 'schedule') }}
                        </span>
                    </div>
                    <div class="small fw-semibold mt-2">{{ $step['label'] }}</div>
                </div>
                @if(!$loop->last)
                    <div class="flex-grow-1 border-top {{ $step['done'] ? 'border-success' : 'border-secondary' }}"></div>
                @endif
            </div>
        @endforeach
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
                    <label class="form-label small fw-semibold text-secondary">Ödeme Durumu</label>
                    <p class="fw-bold mb-0">
                        @if($paymentConfirmed ?? false)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill">Ödeme Onaylandı</span>
                        @else
                            <a href="{{ route('admin.payments.create', ['customer_id' => $order->customer_id, 'from_order' => $order->id]) }}" class="badge bg-warning-200 text-warning px-3 py-2 rounded-pill text-decoration-none d-inline-flex align-items-center gap-1">
                                Ödeme Bekleniyor — Ödeme Ekle
                                <span class="material-symbols-outlined" style="font-size:1rem">arrow_forward</span>
                            </a>
                        @endif
                    </p>
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

        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Sevkiyatlar</h3>
            @if($order->shipments->isEmpty())
                <p class="text-secondary mb-0">Bu sipariş için henüz sevkiyat bulunmuyor.</p>
                @if(($paymentConfirmed ?? false) && !in_array($order->status, ['cancelled', 'delivered', 'invoiced'], true))
                <a href="{{ route('admin.shipments.create', ['order_id' => $order->id]) }}" class="btn btn-sm btn-primary mt-2 d-inline-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:1rem">add</span>
                    Sevkiyat Oluştur
                </a>
                @endif
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th class="text-secondary small">Araç</th>
                                <th class="text-secondary small">Şoför</th>
                                <th class="text-secondary small">Durum</th>
                                <th class="text-secondary small">Alış</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->shipments as $s)
                            <tr>
                                <td>{{ $s->vehicle?->plate ?? '-' }} {{ $s->vehicle ? "({$s->vehicle->brand} {$s->vehicle->model})" : '' }}</td>
                                <td>{{ $s->driver ? trim($s->driver->first_name . ' ' . $s->driver->last_name) : '-' }}</td>
                                <td>
                                    @php
                                        $sColors = ['pending' => 'warning', 'in_transit' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
                                        $sc = $sColors[$s->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $sc }} bg-opacity-10 text-{{ $sc }}">{{ $s->status_label }}</span>
                                </td>
                                <td class="small">{{ $s->pickup_date?->format('d.m.Y H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.shipments.show', $s->id) }}" class="btn btn-sm btn-outline-secondary">Detay</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($order->notes)
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h3 class="h4 fw-bold text-dark mb-3">Notlar</h3>
            <p class="text-dark mb-0">{{ $order->notes }}</p>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        @php
            $primaryAction = null;

            if (!($paymentConfirmed ?? false) && $order->customer_id) {
                // Adım: Ödeme Bekleniyor
                $primaryAction = [
                    'route' => route('admin.payments.create', ['customer_id' => $order->customer_id, 'from_order' => $order->id]),
                    'icon' => 'payments',
                    'label' => 'Ödeme Ekle / Onayla',
                    'variant' => 'warning',
                ];
            } elseif (($paymentConfirmed ?? false) && in_array($order->status, ['pending', 'planned', 'assigned'], true)) {
                // Adım: Hazırlık / Sevkiyata Hazır
                $primaryAction = [
                    'route' => route('admin.shipments.create', ['order_id' => $order->id]),
                    'icon' => 'local_shipping',
                    'label' => $order->shipments->isEmpty() ? 'Sevkiyat Oluştur' : 'Yeni Sevkiyat Oluştur',
                    'variant' => 'primary',
                ];
            } elseif (($paymentConfirmed ?? false) && in_array($order->status, ['loaded', 'in_transit', 'delivered', 'invoiced'], true) && $order->shipments->isNotEmpty()) {
                // Adım: Sevkiyat Aşamasında / Teslim
                $primaryAction = [
                    'route' => route('admin.shipments.index', ['order_id' => $order->id]),
                    'icon' => 'local_shipping',
                    'label' => 'Sevkiyatları Görüntüle',
                    'variant' => 'primary',
                ];
            }
        @endphp

        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h3 class="h4 fw-bold text-dark mb-4">Hızlı İşlemler</h3>
            <div class="d-flex flex-column gap-2">
                @if($primaryAction)
                    <a href="{{ $primaryAction['route'] }}" class="btn btn-{{ $primaryAction['variant'] }} w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">{{ $primaryAction['icon'] }}</span>
                        {{ $primaryAction['label'] }}
                    </a>
                @endif

                {{-- İkincil işlemler --}}
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

            // Ödeme onaylanmadan, sadece iptal edilebilsin (planla/ata/yükle vb. görünmesin)
            if (!($paymentConfirmed ?? false) && $order->status === 'pending') {
                $nextStatuses = array_values(array_intersect($nextStatuses, ['cancelled']));
            }
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

        <div class="bg-white rounded-3xl shadow-sm border p-4 mt-4">
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
    </div>
</div>
@endsection
