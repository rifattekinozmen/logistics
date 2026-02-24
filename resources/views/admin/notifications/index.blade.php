@extends('layouts.app')

@section('title', 'Bildirimler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Bildirimler</h2>
        <p class="text-secondary mb-0">Sistem bildirimlerini görüntüleyin ve yönetin</p>
    </div>
    <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.1rem;">done_all</span>
            Tümünü Okundu İşaretle
        </button>
    </form>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="notifications" color="primary" />
    <x-index-stat-card title="Bekleyen" :value="$stats['pending'] ?? 0" icon="schedule" color="warning" />
    <x-index-stat-card title="Gönderildi" :value="$stats['sent'] ?? 0" icon="send" color="success" />
    <x-index-stat-card title="Okunmamış" :value="$stats['unread'] ?? 0" icon="mark_email_unread" color="info" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Bekleyen</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Gönderildi</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Başarısız</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Kanal</label>
            <select name="channel" class="form-select">
                <option value="">Tümü</option>
                <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                <option value="dashboard" {{ request('channel') === 'dashboard' ? 'selected' : '' }}>Dashboard</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Okunma Durumu</label>
            <select name="is_read" class="form-select">
                <option value="">Tümü</option>
                <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Okunmamış</option>
                <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Okunmuş</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Başlık</th>
                    <th class="border-0 small text-secondary fw-semibold">Tür</th>
                    <th class="border-0 small text-secondary fw-semibold">Kanal</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold">Tarih</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr class="{{ !$notification->is_read ? 'table-active' : '' }}">
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $notification->title }}</span>
                            @if(!$notification->is_read)
                                <span class="badge bg-primary rounded-pill ms-2">Yeni</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $notification->notification_type }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $notification->channel }}</small>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-{{ match($notification->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' } }}-200 text-{{ match($notification->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                                {{ match($notification->status) { 'sent' => 'Gönderildi', 'failed' => 'Başarısız', default => 'Bekleyen' } }}
                            </span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $notification->created_at->format('d.m.Y H:i') }}</small>
                        </td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.notifications.show', $notification) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <p class="text-secondary mb-0">Henüz bildirim bulunmuyor.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notifications->hasPages())
        <div class="p-4 border-top">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
