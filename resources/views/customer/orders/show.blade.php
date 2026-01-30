@extends('layouts.customer-app')

@section('title', 'Sipariş Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">receipt_long</span>
            <h2 class="h3 fw-bold text-dark mb-0">Sipariş Detayı</h2>
        </div>
        <p class="text-secondary mb-0">Sipariş No: <span class="fw-semibold">{{ $order->order_number }}</span></p>
    </div>
    <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
        Listeye Dön
    </a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h5 class="fw-bold mb-3">Sipariş Bilgileri</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Sipariş No</dt>
                <dd class="col-sm-8"><span class="fw-bold">{{ $order->order_number }}</span></dd>

                <dt class="col-sm-4">Durum</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ match($order->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }}-200 text-{{ match($order->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }} rounded-pill px-3 py-2">
                        {{ ucfirst($order->status) }}
                    </span>
                </dd>

                <dt class="col-sm-4">Alış Adresi</dt>
                <dd class="col-sm-8">{{ $order->pickup_address }}</dd>

                <dt class="col-sm-4">Teslimat Adresi</dt>
                <dd class="col-sm-8">{{ $order->delivery_address }}</dd>

                <dt class="col-sm-4">Planlanan Teslimat</dt>
                <dd class="col-sm-8">{{ $order->planned_delivery_date?->format('d.m.Y H:i') ?? '-' }}</dd>

                @if($order->delivered_at)
                    <dt class="col-sm-4">Teslim Tarihi</dt>
                    <dd class="col-sm-8">{{ $order->delivered_at->format('d.m.Y H:i') }}</dd>
                @endif

                @if($order->notes)
                    <dt class="col-sm-4">Notlar</dt>
                    <dd class="col-sm-8">{{ $order->notes }}</dd>
                @endif
            </dl>
        </div>

        <!-- Sipariş Zaman Çizelgesi -->
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">timeline</span>
                <h5 class="fw-bold mb-0">Sipariş Takibi</h5>
            </div>
            <div class="position-relative">
                @php
                    $timelineSteps = [
                        ['status' => 'pending', 'label' => 'Sipariş Alındı', 'icon' => 'shopping_cart', 'date' => $order->created_at],
                        ['status' => 'assigned', 'label' => 'Sevkiyata Atandı', 'icon' => 'assignment', 'date' => $order->shipments->first()?->created_at],
                        ['status' => 'in_transit', 'label' => 'Yola Çıktı', 'icon' => 'local_shipping', 'date' => $order->shipments->first()?->pickup_date],
                        ['status' => 'delivered', 'label' => 'Teslim Edildi', 'icon' => 'check_circle', 'date' => $order->delivered_at],
                    ];
                    
                    $currentStepIndex = match($order->status) {
                        'delivered' => 3,
                        'in_transit' => 2,
                        'assigned' => 1,
                        default => 0
                    };
                @endphp
                
                @foreach($timelineSteps as $index => $step)
                    <div class="d-flex align-items-start mb-4 position-relative">
                        <div class="d-flex flex-column align-items-center me-3" style="min-width: 50px;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center {{ $index <= $currentStepIndex ? 'bg-primary text-white' : 'bg-secondary-200 text-secondary' }}" style="width: 40px; height: 40px; z-index: 2;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">{{ $step['icon'] }}</span>
                            </div>
                            @if($index < count($timelineSteps) - 1)
                                <div class="position-absolute {{ $index < $currentStepIndex ? 'bg-primary' : 'bg-secondary-200' }}" style="width: 2px; height: calc(100% + 1rem); top: 40px; left: 50%; transform: translateX(-50%); z-index: 1;"></div>
                            @endif
                        </div>
                        <div class="grow">
                            <p class="fw-bold text-dark mb-1 {{ $index <= $currentStepIndex ? '' : 'text-secondary' }}">
                                {{ $step['label'] }}
                            </p>
                            @if($step['date'] && $index <= $currentStepIndex)
                                <p class="small text-secondary mb-0">
                                    {{ $step['date']->format('d.m.Y H:i') }}
                                </p>
                            @else
                                <p class="small text-secondary mb-0">Bekleniyor</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($order->shipments->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">local_shipping</span>
                    <h5 class="fw-bold mb-0">Sevkiyat Bilgileri</h5>
                </div>
                @foreach($order->shipments as $shipment)
                    <div class="p-3 rounded-2xl border mb-2">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <p class="fw-bold mb-0">Sevkiyat #{{ $shipment->id }}</p>
                            <span class="badge bg-{{ match($shipment->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }}-200 text-{{ match($shipment->status) { 'delivered' => 'success', 'in_transit' => 'primary', 'assigned' => 'info', default => 'warning' } }} rounded-pill px-3 py-1">
                                {{ ucfirst($shipment->status) }}
                            </span>
                        </div>
                        @if($shipment->vehicle)
                            <p class="small text-secondary mb-1">
                                <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">directions_car</span>
                                Araç: {{ $shipment->vehicle->plate }}
                            </p>
                        @endif
                        @if($shipment->driver)
                            <p class="small text-secondary mb-1">
                                <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">person</span>
                                Şoför: {{ $shipment->driver->first_name }} {{ $shipment->driver->last_name }}
                            </p>
                        @endif
                        @if($shipment->qr_code)
                            <div class="mt-2 p-2 bg-primary-200 rounded-2xl">
                                <p class="small fw-semibold text-dark mb-1">QR Kod:</p>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small text-primary">{{ $shipment->qr_code }}</code>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $shipment->qr_code }}')">
                                        <span class="material-symbols-outlined" style="font-size: 1rem;">content_copy</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h5 class="fw-bold mb-3">Hızlı İşlemler</h5>
            <div class="d-flex flex-column gap-2">
                @if(Auth::user() && Auth::user()->hasPermission('customer.portal.documents.view'))
                    <a href="{{ route('customer.documents.index') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 1rem;">description</span>
                        Belgeleri Görüntüle
                    </a>
                @endif
            </div>
        </div>
        
        @if($order->status !== 'delivered' && $order->status !== 'cancelled')
            <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-warning" style="font-size: 1.5rem;">schedule</span>
                    <h5 class="fw-bold mb-0">Sipariş Durumu</h5>
                </div>
                <p class="small text-secondary mb-0">
                    Siparişiniz şu anda <strong>{{ match($order->status) { 'pending' => 'beklemede', 'assigned' => 'atandı', 'in_transit' => 'yolda', default => $order->status } }}</strong> durumunda.
                </p>
            </div>
            
            @if(in_array($order->status, ['pending', 'assigned']) && Auth::user() && Auth::user()->hasPermission('customer.portal.orders.cancel'))
                <div class="bg-white rounded-3xl shadow-sm border p-4">
                    <h5 class="fw-bold mb-3">Sipariş İptali</h5>
                    <form method="POST" action="{{ route('customer.orders.cancel', $order) }}" onsubmit="return confirm('Bu siparişi iptal etmek istediğinizden emin misiniz?');">
                        @csrf
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label small fw-semibold text-dark">İptal Nedeni (Opsiyonel)</label>
                            <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="3" placeholder="İptal nedeninizi belirtin..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">cancel</span>
                            Siparişi İptal Et
                        </button>
                    </form>
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('QR kod kopyalandı: ' + text);
    }, function(err) {
        console.error('Kopyalama hatası:', err);
    });
}
</script>
@endpush
@endsection
