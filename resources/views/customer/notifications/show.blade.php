@extends('layouts.customer-app')

@section('title', 'Bildirim Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">notifications</span>
            <h2 class="h3 fw-bold text-dark mb-0">Bildirim Detayı</h2>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if(!$notification->is_read)
            <form method="POST" action="{{ route('customer.notifications.mark-read', $notification) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">done</span>
                    Okundu İşaretle
                </button>
            </form>
        @endif
        <a href="{{ route('customer.notifications.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <span class="material-symbols-outlined">arrow_back</span>
            Listeye Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <div class="d-flex align-items-start gap-3 mb-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 64px; height: 64px;">
                    <span class="material-symbols-outlined" style="font-size: 2rem;">
                        @match($notification->notification_type)
                            @case('order_status')
                                shopping_cart
                                @break
                            @case('payment_reminder')
                                payments
                                @break
                            @case('shipment_update')
                                local_shipping
                                @break
                            @default
                                notifications
                        @endmatch
                    </span>
                </div>
                <div class="grow">
                    <h3 class="h4 fw-bold text-dark mb-2">{{ $notification->title }}</h3>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="badge bg-{{ match($notification->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' } }}-200 text-{{ match($notification->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                            {{ match($notification->status) { 'sent' => 'Gönderildi', 'failed' => 'Başarısız', default => 'Bekliyor' } }}
                        </span>
                        @if($notification->is_read)
                            <span class="badge bg-success-200 text-success rounded-pill px-3 py-2">
                                <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">check_circle</span>
                                Okundu
                            </span>
                        @else
                            <span class="badge bg-primary rounded-pill px-3 py-2">Yeni</span>
                        @endif
                    </div>
                    <p class="text-secondary mb-0 small">
                        <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">schedule</span>
                        {{ $notification->created_at->format('d.m.Y H:i') }}
                        @if($notification->sent_at)
                            • Gönderildi: {{ $notification->sent_at->format('d.m.Y H:i') }}
                        @endif
                        @if($notification->read_at)
                            • Okundu: {{ $notification->read_at->format('d.m.Y H:i') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="border-top pt-4">
                <h5 class="fw-bold text-dark mb-3">İçerik</h5>
                <div class="text-secondary" style="white-space: pre-wrap; line-height: 1.8;">
                    {{ $notification->content }}
                </div>
            </div>

            @if($notification->metadata && is_array($notification->metadata) && count($notification->metadata) > 0)
                <div class="border-top pt-4 mt-4">
                    <h5 class="fw-bold text-dark mb-3">Ek Bilgiler</h5>
                    <dl class="row mb-0">
                        @foreach($notification->metadata as $key => $value)
                            <dt class="col-sm-3">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="col-sm-9">
                                @if(is_array($value))
                                    {{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4">
            <h5 class="fw-bold mb-3">Hızlı İşlemler</h5>
            <div class="d-flex flex-column gap-2">
                @if($notification->related_type && $notification->related_id)
                    @if($notification->related_type === 'App\Models\Order')
                        <a href="{{ route('customer.orders.show', $notification->related_id) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">shopping_cart</span>
                            Siparişi Görüntüle
                        </a>
                    @endif
                    @if($notification->related_type === 'App\Models\Payment')
                        <a href="{{ route('customer.payments.show', $notification->related_id) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 1rem;">payments</span>
                            Ödemeyi Görüntüle
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4">
            <h5 class="fw-bold mb-3">Bildirim Bilgileri</h5>
            <dl class="mb-0">
                <dt class="small text-secondary">Kanal</dt>
                <dd class="mb-2">{{ ucfirst($notification->channel) }}</dd>

                <dt class="small text-secondary">Tür</dt>
                <dd class="mb-2">{{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}</dd>

                <dt class="small text-secondary">Oluşturulma</dt>
                <dd class="mb-2">{{ $notification->created_at->format('d.m.Y H:i') }}</dd>

                @if($notification->sent_at)
                    <dt class="small text-secondary">Gönderilme</dt>
                    <dd class="mb-2">{{ $notification->sent_at->format('d.m.Y H:i') }}</dd>
                @endif

                @if($notification->read_at)
                    <dt class="small text-secondary">Okunma</dt>
                    <dd class="mb-0">{{ $notification->read_at->format('d.m.Y H:i') }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
