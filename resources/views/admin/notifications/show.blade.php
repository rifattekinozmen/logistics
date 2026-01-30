@extends('layouts.app')

@section('title', 'Bildirim Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Bildirim Detayı</h2>
        <p class="text-secondary mb-0">{{ $notification->title }}</p>
    </div>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
        Listeye Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border p-4">
    <dl class="row mb-0">
        <dt class="col-sm-3">Başlık</dt>
        <dd class="col-sm-9">
            <span class="fw-bold text-dark">{{ $notification->title }}</span>
        </dd>

        <dt class="col-sm-3">İçerik</dt>
        <dd class="col-sm-9">
            <p class="mb-0">{{ $notification->content }}</p>
        </dd>

        <dt class="col-sm-3">Tür</dt>
        <dd class="col-sm-9">
            <span class="badge bg-secondary rounded-pill px-3 py-2">
                {{ $notification->notification_type }}
            </span>
        </dd>

        <dt class="col-sm-3">Kanal</dt>
        <dd class="col-sm-9">
            <span class="badge bg-info rounded-pill px-3 py-2">
                {{ $notification->channel }}
            </span>
        </dd>

        <dt class="col-sm-3">Durum</dt>
        <dd class="col-sm-9">
            <span class="badge bg-{{ match($notification->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' } }}-200 text-{{ match($notification->status) { 'sent' => 'success', 'failed' => 'danger', default => 'warning' } }} rounded-pill px-3 py-2">
                {{ match($notification->status) { 'sent' => 'Gönderildi', 'failed' => 'Başarısız', default => 'Bekleyen' } }}
            </span>
        </dd>

        <dt class="col-sm-3">Oluşturulma</dt>
        <dd class="col-sm-9">
            <small class="text-secondary">{{ $notification->created_at->format('d.m.Y H:i:s') }}</small>
        </dd>

        @if($notification->sent_at)
            <dt class="col-sm-3">Gönderilme</dt>
            <dd class="col-sm-9">
                <small class="text-secondary">{{ $notification->sent_at->format('d.m.Y H:i:s') }}</small>
            </dd>
        @endif

        @if($notification->is_read)
            <dt class="col-sm-3">Okunma</dt>
            <dd class="col-sm-9">
                <small class="text-secondary">{{ $notification->read_at?->format('d.m.Y H:i:s') ?? '-' }}</small>
            </dd>
        @endif

        @if($notification->metadata)
            <dt class="col-sm-3">Ek Bilgiler</dt>
            <dd class="col-sm-9">
                <pre class="bg-light p-3 rounded">{{ json_encode($notification->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </dd>
        @endif
    </dl>
</div>
@endsection
