@extends('layouts.app')

@section('title', 'Bildirimler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">notifications</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Bildirimler</h2>
            <p class="text-secondary mb-0">Sistem bildirimlerini görüntüleyin ve yönetin</p>
        </div>
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
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="notifications-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
                <option value="mark_read">Okundu işaretle</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="notifications-bulk-apply">Uygula</button>
        </div>
        <div class="small text-secondary"><span id="notifications-selected-count">0</span> kayıt seçili</div>
    </div>
    <form id="notifications-bulk-form" action="{{ route('admin.notifications.bulk') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" id="notifications-bulk-action-input">
    </form>
    <div class="table-responsive">
        @php
            $currentSort = request('sort');
            $currentDirection = request('direction', 'asc');
        @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;">
                        <input type="checkbox" id="select-all-notifications">
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'title' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.notifications.index', array_merge(request()->query(), ['sort' => 'title', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Başlık</span>
                            @if($currentSort === 'title')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">Tür</th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'channel' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.notifications.index', array_merge(request()->query(), ['sort' => 'channel', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Kanal</span>
                            @if($currentSort === 'channel')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.notifications.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Durum</span>
                            @if($currentSort === 'status')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'created_at' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.notifications.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Tarih</span>
                            @if($currentSort === 'created_at')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr class="{{ !$notification->is_read ? 'table-active' : '' }}">
                        <td class="align-middle text-center">
                            <input type="checkbox" class="form-check-input notification-row-check" name="selected[]" value="{{ $notification->id }}" form="notifications-bulk-form">
                        </td>
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
                        <td colspan="7" class="text-center py-5">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('notifications-bulk-form');
    const actionSelect = document.getElementById('notifications-bulk-action');
    const actionInput = document.getElementById('notifications-bulk-action-input');
    const applyBtn = document.getElementById('notifications-bulk-apply');
    const selectAll = document.getElementById('select-all-notifications');
    const checkboxes = document.querySelectorAll('.notification-row-check');
    const countEl = document.getElementById('notifications-selected-count');
    function updateCount() { const n = document.querySelectorAll('.notification-row-check:checked').length; countEl.textContent = n; }
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
    if (selectAll) { selectAll.addEventListener('change', function () { checkboxes.forEach(cb => { cb.checked = selectAll.checked; }); updateCount(); }); }
    applyBtn.addEventListener('click', function () {
        const action = actionSelect.value;
        if (!action) return;
        const checked = document.querySelectorAll('.notification-row-check:checked');
        if (checked.length === 0) { alert('Lütfen en az bir bildirim seçin.'); return; }
        checked.forEach(cb => form.appendChild(cb.cloneNode(true)));
        actionInput.value = action;
        form.submit();
    });
});
</script>
@endpush
@endsection
