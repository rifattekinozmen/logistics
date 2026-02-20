@extends('layouts.customer-app')

@section('title', 'Bildirimlerim - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">notifications</span>
            <h2 class="h3 fw-bold text-dark mb-0">Bildirimlerim</h2>
            @if($unreadCount > 0)
                <span class="badge bg-danger rounded-pill px-3 py-2">{{ $unreadCount }} Okunmamış</span>
            @endif
        </div>
        <p class="text-secondary mb-0">Sipariş durumu, ödeme hatırlatmaları ve diğer bildirimleriniz</p>
    </div>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('customer.notifications.mark-all-read') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">done_all</span>
                Tümünü Okundu İşaretle
            </button>
        </form>
    @endif
</div>

<!-- Filtreleme -->
<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('customer.notifications.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="is_read" class="form-select">
                <option value="">Tümü</option>
                <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Okunmamış</option>
                <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Okunmuş</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Bildirim Türü</label>
            <select name="notification_type" class="form-select">
                <option value="">Tümü</option>
                <option value="order_status" {{ request('notification_type') === 'order_status' ? 'selected' : '' }}>Sipariş Durumu</option>
                <option value="payment_reminder" {{ request('notification_type') === 'payment_reminder' ? 'selected' : '' }}>Ödeme Hatırlatması</option>
                <option value="shipment_update" {{ request('notification_type') === 'shipment_update' ? 'selected' : '' }}>Sevkiyat Güncellemesi</option>
                <option value="general" {{ request('notification_type') === 'general' ? 'selected' : '' }}>Genel</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100">Filtrele</button>
        </div>
    </form>
</div>

<!-- Bildirim Listesi -->
<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    @forelse($notifications as $notification)
        <a href="{{ route('customer.notifications.show', $notification) }}" class="text-decoration-none">
            <div class="p-4 border-bottom {{ !$notification->is_read ? 'bg-primary-50' : '' }} transition-all hover:bg-primary-100" style="cursor: pointer;">
                <div class="d-flex align-items-start gap-3">
                    <div class="shrink-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center {{ !$notification->is_read ? 'bg-primary text-white' : 'bg-secondary-200 text-secondary' }}" style="width: 48px; height: 48px;">
                            <span class="material-symbols-outlined">
                                @switch($notification->notification_type)
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
                                @endswitch
                            </span>
                    </div>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                            <h5 class="fw-bold text-dark mb-0 {{ !$notification->is_read ? '' : 'text-secondary' }}">
                                {{ $notification->title }}
                            </h5>
                            @if(!$notification->is_read)
                                <span class="badge bg-primary rounded-pill px-2 py-1">Yeni</span>
                            @endif
                        </div>
                        <p class="text-secondary mb-2 small">{{ Str::limit($notification->content, 150) }}</p>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-secondary">
                                <span class="material-symbols-outlined align-middle" style="font-size: 0.875rem;">schedule</span>
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                            @if($notification->sent_at)
                                <small class="text-secondary">
                                    Gönderildi: {{ $notification->sent_at->format('d.m.Y H:i') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="text-center py-5">
            <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">notifications_off</span>
            <p class="text-secondary mb-0">Henüz bildirim bulunmuyor.</p>
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endif
@endsection
