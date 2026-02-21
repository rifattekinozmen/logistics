@extends('layouts.app')

@push('styles')
    @vite(['resources/css/calendar.css', 'resources/js/calendar.js'])
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Takvim</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="bi bi-plus-lg me-2"></i>Etkinlik Ekle
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <ul class="nav nav-tabs mb-4" id="eventFilterTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#" data-filter="all">Tümü</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="document">Belgeler</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="payment">Ödemeler</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="maintenance">Bakımlar</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-filter="inspection">Muayeneler</a>
        </li>
    </ul>

    {{-- Calendar Container --}}
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

{{-- Add Event Modal --}}
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Etkinlik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addEventForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Başlık *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Etkinlik Tipi *</label>
                        <select name="event_type" class="form-select" required>
                            <option value="meeting">Toplantı</option>
                            <option value="document">Belge</option>
                            <option value="payment">Ödeme</option>
                            <option value="maintenance">Bakım</option>
                            <option value="inspection">Muayene</option>
                            <option value="leave">İzin</option>
                            <option value="delivery">Teslimat</option>
                            <option value="other">Diğer</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Başlangıç Tarihi *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Öncelik *</label>
                        <select name="priority" class="form-select" required>
                            <option value="low">Düşük</option>
                            <option value="medium" selected>Orta</option>
                            <option value="high">Yüksek</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_all_day" id="isAllDay" checked>
                        <label class="form-check-label" for="isAllDay">
                            Tüm Gün
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
