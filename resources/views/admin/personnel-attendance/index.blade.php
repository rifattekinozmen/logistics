@extends('layouts.app')

@section('title', 'Puantaj - Logistics')

@section('content')
@php
    $statusList = $attendanceStatuses ?? \App\Enums\AttendanceStatus::forFrontend();
    $nextStatusMap = $nextStatus ?? \App\Enums\AttendanceStatus::cycleOrderMap();
@endphp
<div class="attendance-page-wrapper" x-data="puantajTable()" x-init="init()" x-cloak>
    {{-- Başlık: Sevkiyatlar ile aynı mobil uyumlu yapı --}}
    <div class="attendance-page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="attendance-page-header-title">
            <h2 class="h3 fw-bold text-dark mb-1">Puantaj</h2>
            <p class="text-secondary mb-0">Personel yoklama durumları</p>
        </div>
        <div class="attendance-page-header-actions d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-shifts d-flex align-items-center gap-2" :class="{ 'active': showQuickFill }" @click="toggleQuickFill()" title="Toplu Doldur">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">format_paint</span>
                Toplu Doldur
            </button>
            <button type="button" class="btn btn-shifts d-flex align-items-center gap-2" :class="{ 'active': showStats }" @click="toggleStats()" title="İstatistikler">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">bar_chart</span>
                İstatistikler
            </button>
            <button type="button" class="btn btn-shifts d-flex align-items-center gap-2" @click="exportExcel()" title="Excel">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">table_chart</span>
                Excel
            </button>
            <button type="button" class="btn btn-shifts d-flex align-items-center gap-2" @click="printTable()" title="Yazdır">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">print</span>
                Yazdır
            </button>
            <div class="header-actions-dropdown position-relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" class="btn btn-shifts d-flex align-items-center gap-2" @click="open = !open">
                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">settings</span>
                    İşlemler
                    <span class="material-symbols-outlined" style="font-size: 1rem; transition: transform 0.2s;" :class="{ 'rotate-180': open }">expand_more</span>
                </button>
                <div x-show="open" x-transition class="header-dropdown-menu shadow border rounded-3xl p-2 min-w-200 bg-white position-absolute top-100 end-0 mt-2">
                    <h6 class="px-2 py-1 small fw-semibold text-dark mb-1">Görünüm</h6>
                    <button type="button" class="view-option d-block w-100 text-start border-0 rounded-2xl px-3 py-2 small" :class="{ 'bg-primary-200 text-primary': !compactMode }" @click="compactMode = false; localStorage.setItem('attendance-compact-mode', 'false'); open = false">Normal</button>
                    <button type="button" class="view-option d-block w-100 text-start border-0 rounded-2xl px-3 py-2 small" :class="{ 'bg-primary-200 text-primary': compactMode }" @click="compactMode = true; localStorage.setItem('attendance-compact-mode', 'true'); open = false">Kompakt</button>
                    <button type="button" class="view-option d-block w-100 text-start border-0 rounded-2xl px-3 py-2 small bg-transparent" @click="toggleFullscreen(); open = false">Tam Ekran</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtre alanı: Sevkiyatlar ile aynı mobil uyumlu yapı (row + col-md-*) --}}
    <div class="filter-area filter-area-attendance rounded-3xl shadow-sm border p-4 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-dark">Ay</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="month" x-model="month" @change="fetchTableAjax()" class="form-control" id="attendance-month">
                    <div x-show="loading" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none;"></div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-dark">Personel Ara</label>
                <input type="text" x-model="search" placeholder="Personel ara..." class="form-control">
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-filter btn-filter-attendance w-100 shadow-sm hover:shadow-md transition-all" disabled>Ay / arama yukarıdan</button>
            </div>
        </div>
    </div>

    {{-- Toplu Doldur paneli - İzin yapısı backend'den ($statusList) --}}
    <div x-show="showQuickFill" x-transition class="modern-panel quick-fill-panel">
        <div class="panel-card panel-card-bulk">
            <div class="card-header">
                <h3 class="card-title"><span class="material-symbols-outlined" aria-hidden="true">format_paint</span><span>Toplu Atama</span></h3>
                <button type="button" @click="showQuickFill = false" class="close-btn" aria-label="Kapat"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="card-content">
                <div class="bulk-fill-grid">
                    <div class="input-group">
                        <label class="input-label" for="bulk-person">Personel</label>
                        <select x-model="quickFillPerson" class="modern-select" id="bulk-person">
                            <option value="">Tüm Personel</option>
                            <template x-for="p in personnel" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="input-group">
                        <label class="input-label" for="bulk-status">Durum</label>
                        <select x-model="quickFillStatus" class="modern-select" id="bulk-status">
                            @foreach($statusList as $key => $item)
                                <option value="{{ $key }}">{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group">
                        <label class="input-label" for="bulk-start">Başlangıç günü</label>
                        <input type="number" x-model="quickFillStartDay" id="bulk-start" placeholder="1" min="1" :max="daysInMonth" class="modern-input-field">
                    </div>
                    <div class="input-group">
                        <label class="input-label" for="bulk-end">Bitiş günü</label>
                        <input type="number" x-model="quickFillEndDay" id="bulk-end" placeholder="31" min="1" :max="daysInMonth" class="modern-input-field">
                    </div>
                    <div class="input-group apply-group">
                        <label class="input-label input-label-invisible">İşlem</label>
                        <button type="button" @click="applyQuickFill" class="apply-btn"><span class="material-symbols-outlined">check</span><span>Uygula</span></button>
                    </div>
                </div>
                <div class="filter-options">
                    <span class="filter-options-title">Gün filtresi</span>
                    <div class="option-item">
                        <input class="modern-checkbox" type="checkbox" id="weekdays" x-model="quickFillWeekdays">
                        <label for="weekdays" class="checkbox-label">Sadece hafta içi (Pzt–Cum)</label>
                    </div>
                    <div class="option-item">
                        <input class="modern-checkbox" type="checkbox" id="weekends" x-model="quickFillWeekends">
                        <label for="weekends" class="checkbox-label">Sadece hafta sonu (Cmt–Paz)</label>
                    </div>
                    <div class="option-item">
                        <input class="modern-checkbox" type="checkbox" id="monSat" x-model="quickFillMonSat">
                        <label for="monSat" class="checkbox-label">Pzt–Cmt (Pazar devamsızlık)</label>
                    </div>
                </div>
                <p class="filter-hint">Hafta içi + hafta sonu birlikte işaretlenirse tüm günlere uygulanır. Hiçbiri işaretli değilse aralıktaki tüm günlere uygulanır.</p>
            </div>
        </div>
    </div>

    {{-- Aylık İstatistikler paneli (görseldeki gibi 5 kart) --}}
    <div x-show="showStats" x-transition class="stats-panel-card">
        <div class="stats-panel-header">
            <h3 class="stats-panel-title"><span class="material-symbols-outlined me-2">bar_chart</span> Aylık İstatistikler</h3>
            <button type="button" @click="showStats = false" class="stats-panel-close" aria-label="Kapat"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="stats-panel-body">
            <div class="stats-cards-row">
                @foreach($statusList as $key => $item)
                    @php
                        $cardClass = match($key) { 'full' => 'stat-success', 'half' => 'stat-warning', 'izin' => 'stat-info', 'yillik' => 'stat-primary', 'rapor' => 'stat-danger', 'none' => 'stat-secondary', default => 'stat-secondary' };
                        $materialIcon = match($key) { 'full' => 'check_circle', 'half' => 'schedule', 'izin' => 'event_busy', 'yillik' => 'event', 'rapor' => 'local_hospital', 'none' => 'cancel', default => 'circle' };
                    @endphp
                    <div class="stat-card {{ $cardClass }}">
                        <div class="stat-card-bar"></div>
                        <div class="stat-card-content">
                            <div class="stat-card-icon"><span class="material-symbols-outlined" aria-hidden="true">{{ $materialIcon }}</span></div>
                            <div class="stat-card-body">
                                <div class="stat-card-value" x-text="totalStats['{{ $key }}'] ?? 0">0</div>
                                <div class="stat-card-label">{{ $item['label'] }}</div>
                                <div class="stat-card-pct"><span x-text="getStatPercent('{{ $key }}')">0</span>%</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div x-show="longLoading" class="loading-overlay" x-transition>
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-3"><h5>Yükleniyor</h5><p class="text-muted">Lütfen bekleyin.</p></div>
        </div>
    </div>

    {{-- Tablo kartı: Sevkiyatlar gibi mobilde table-responsive (yatay kaydırma) --}}
    <div class="attendance-table-card bg-white rounded-3xl shadow-sm border overflow-hidden mb-0" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive table-container" x-show="!loading" :class="{ 'compact-mode': compactMode }">
        <table class="attendance-table" :class="{ 'table-compact': compactMode }">
            <thead>
                <tr>
                    <th class="personnel-header">
                        <div class="d-flex align-items-center">
                            <span>Personel</span>
                            <span class="badge bg-secondary ms-2" x-text="filteredPersonnel().length"></span>
                        </div>
                    </th>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $date = \Carbon\Carbon::parse($month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT));
                            $dayName = $date->locale('tr')->dayName;
                            $isWeekend = $date->isWeekend();
                        @endphp
                        <th class="day-header {{ $isWeekend ? 'weekend' : '' }}" :class="{ 'today': isToday({{ $d }}) }">
                            <div class="day-content">
                                <div class="day-number">{{ $d }}</div>
                                <div class="day-name" x-show="!compactMode">{{ mb_substr($dayName, 0, 2) }}</div>
                            </div>
                        </th>
                    @endfor
                    @foreach($statusList as $key => $item)
                        <th class="summary-header total-col summary-col summary-col-{{ $key }}">{{ $item['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <template x-for="(p, index) in filteredPersonnel()" :key="p.id">
                    <tr :class="{ 'selected-row': selectedPersonnel === p.id }" @click="selectedPersonnel = p.id">
                        <td class="personnel-cell">
                            <div class="personnel-info" :class="{ 'compact': compactMode }">
                                <div class="personnel-avatar" :class="{ 'avatar-sm': compactMode }"><span x-text="index + 1"></span></div>
                                <span class="personnel-name" x-text="p.name || 'Bilinmeyen'"></span>
                            </div>
                        </td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php $date = \Carbon\Carbon::parse($month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT)); $isWeekend = $date->isWeekend(); @endphp
                            <td class="day-cell {{ $isWeekend ? 'weekend' : '' }}" :class="{ 'today': isToday({{ $d }}) }">
                                <button
                                    @click="cycleStatus(p.id, {{ $d }}, $event)"
                                    @contextmenu.prevent="showContextMenu($event, p.id, {{ $d }})"
                                    :class="'status-btn status-' + (p.attendance[{{ $d }}] || 'none') + (compactMode ? ' btn-compact' : '')"
                                    :disabled="saving"
                                    :title="getStatusTooltip(p.attendance[{{ $d }}] || 'none', {{ $d }}, p.name)"
                                    :data-bs-toggle="'tooltip'" :data-bs-placement="'top'">
                                    <div class="status-icon" :class="getStatusIconClass(p.attendance[{{ $d }}] || 'none')"
                                         x-text="getStatusSymbol(p.attendance[{{ $d }}] || 'none')"></div>
                                </button>
                            </td>
                        @endfor
                        @foreach($statusList as $key => $item)
                            <td class="summary-cell summary-col summary-col-{{ $key }}">
                                <span class="badge summary-badge summary-badge-{{ $key }}" :title="'{{ $item['label'] }}'" x-text="countStatus(p, '{{ $key }}')"></span>
                            </td>
                        @endforeach
                    </tr>
                </template>
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td class="text-end fw-bold total-label">Toplam:</td>
                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php $date = \Carbon\Carbon::parse($month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT)); $isWeekend = $date->isWeekend(); @endphp
                        <td class="text-center {{ $isWeekend ? 'weekend' : '' }}"><small x-text="getDayTotal({{ $d }})"></small></td>
                    @endfor
                    <td colspan="{{ count($statusList) }}"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    </div>

    {{-- Context menu - İzin yapısı backend'den --}}
    <div x-show="contextMenu.show" @click.away="contextMenu.show = false"
         :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }"
         class="context-menu" x-transition>
        @foreach($statusList as $key => $item)
        <div class="context-menu-item" @click="setStatusFromContext('{{ $key }}')">
            <div class="status-icon {{ $key }}-icon">{{ $item['symbol'] }}</div> {{ $item['label'] }}
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
window.__ATTENDANCE_CONFIG__ = {!! $configJson ?? '{}' !!};
</script>
<script src="{{ asset('js/attendance.js') }}"></script>
<script defer src="https://unpkg.com/alpinejs@3/dist/cdn.min.js"></script>
@endpush

@push('styles')
<style>
[x-cloak] { display: none !important; }

/* Sayfa konteyneri - Sevkiyatlar gibi tam genişlik, mobil uyumlu */
.attendance-page-wrapper {
    width: 100%;
    max-width: 100%;
    min-width: 0; /* flex içinde daralabilmesi için */
    box-sizing: border-box;
}

/* Tablo kartı: masaüstü 1525×200, mobilde tam genişlik */
.attendance-table-card {
    width: 1525px;
    max-width: 100%;
    min-height: 200px;
    box-sizing: border-box;
}

/* İçerik kartı: diyagrama göre padding 32px, içerik alanı 1475×355.141px (margin 0, border 1px) */
.attendance-page-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 32px;
    box-sizing: border-box;
    /* Toplam genişlik/yükseklik = içerik + 2×padding + 2×border → içerik 1475×355.141 */
    width: 1539px;   /* 1475 + 32*2 + 1*2 */
    min-height: 419.141px;   /* 355.141 + 32*2 + 1*2 */
}

.attendance-page-vars { --primary: #3b82f6; --primary-light: #dbeafe; --success: #10b981; --warning: #f59e0b; --info: #06b6d4; --danger: #ef4444; --text-dark: #1f2937; --text-muted: #6b7280; }

/* Header (Personnel Attendance Tracking) */
.attendance-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.attendance-header-left { flex: 1; min-width: 0; }
.attendance-title-block { display: flex; align-items: flex-start; gap: 1rem; }
.attendance-title-icon { width: 48px; height: 48px; min-width: 48px; background: var(--primary-light); color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.attendance-title { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0 0 0.25rem 0; }
.attendance-subtitle { font-size: 0.875rem; color: var(--text-muted); margin: 0; display: flex; align-items: center; }
.attendance-header-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; shrink: 0; }

.header-action-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid transparent; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: background 0.2s, color 0.2s; }
.header-action-btn.primary { background: #3b82f6; color: #fff; }
.header-action-btn.primary:hover, .header-action-btn.primary.active { background: #2563eb; color: #fff; }
.header-action-btn.info { background: #06b6d4; color: #fff; }
.header-action-btn.info:hover, .header-action-btn.info.active { background: #0891b2; color: #fff; }
.header-action-btn.success { background: #10b981; color: #fff; }
.header-action-btn.success:hover { background: #059669; color: #fff; }
.header-action-btn.secondary { background: #6b7280; color: #fff; }
.header-action-btn.secondary:hover { background: #4b5563; color: #fff; }

/* Controls card (Month Selection, Search Personnel) */
.controls-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.controls-inner { display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-end; }
.control-group { display: flex; flex-direction: column; gap: 0.5rem; min-width: 140px; }
.control-group.flex-grow-1 { flex: 1; min-width: 200px; }
.control-label { font-size: 0.875rem; font-weight: 500; color: var(--text-muted); margin: 0; display: flex; align-items: center; }
.control-input-wrap { display: flex; align-items: center; gap: 0.5rem; position: relative; }
.control-input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; color: var(--text-dark); width: 100%; max-width: 200px; }
.control-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.spinner-wrap { display: flex; align-items: center; }

/* Stats panel (Monthly Statistics - 5 cards) */
.stats-panel-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.stats-panel-header { background: #f8fafc; padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.stats-panel-title { margin: 0; font-size: 1.125rem; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; }
.stats-panel-close { background: none; border: none; color: var(--text-muted); padding: 0.5rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.stats-panel-close:hover { background: #f1f5f9; color: var(--text-dark); }
.stats-panel-body { padding: 0.75rem 1rem; }
.stats-cards-row { display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.5rem; }
.stat-card { position: relative; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 0.5rem 0.6rem; overflow: hidden; }
.stat-card-bar { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; }
.stat-card.stat-success .stat-card-bar { background: #10b981; }
.stat-card.stat-warning .stat-card-bar { background: #f59e0b; }
.stat-card.stat-info .stat-card-bar { background: #06b6d4; }
.stat-card.stat-primary .stat-card-bar { background: #3b82f6; }
.stat-card.stat-danger .stat-card-bar { background: #ef4444; }
.stat-card.stat-secondary .stat-card-bar { background: #6b7280; }
.stat-card-content { display: flex; align-items: center; gap: 0.5rem; }
.stat-card-icon { width: 28px; height: 28px; min-width: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.875rem; }
.stat-card-icon .material-symbols-outlined { font-size: 1.125rem; font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 20; }
.stat-card.stat-success .stat-card-icon { background: #10b981; }
.stat-card.stat-warning .stat-card-icon { background: #f59e0b; }
.stat-card.stat-info .stat-card-icon { background: #06b6d4; }
.stat-card.stat-primary .stat-card-icon { background: #3b82f6; }
.stat-card.stat-danger .stat-card-icon { background: #ef4444; }
.stat-card.stat-secondary .stat-card-icon { background: #6b7280; }
.stat-card-body { flex: 1; min-width: 0; }
.stat-card-value { font-size: 1.125rem; font-weight: 700; color: var(--text-dark); line-height: 1.2; margin-bottom: 0.125rem; }
.stat-card-label { font-size: 0.6875rem; font-weight: 500; color: var(--text-muted); margin-bottom: 0.25rem; }
.stat-card-pct { font-size: 0.75rem; font-weight: 600; color: var(--text-dark); }

/* Panel card (Bulk Fill) */
.panel-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
/* Toplu Atama kartı: masaüstü 1525×200, mobilde tam genişlik (Sevkiyatlar gibi) */
.panel-card-bulk {
    width: 1525px;
    max-width: 100%;
    min-height: 200px;
    box-sizing: border-box;
}
.card-header { background: #f8fafc; padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; }
.card-title { margin: 0; font-size: 1.125rem; font-weight: 600; color: #1f2937; display: flex; align-items: center; gap: 0.5rem; }
.card-title i { flex-shrink: 0; }
.card-title span { min-width: 0; }
.close-btn { background: none; border: none; color: #6b7280; padding: 0.5rem; border-radius: 6px; cursor: pointer; flex-shrink: 0; }
.close-btn:hover { background: #fee2e2; color: #ef4444; }
.card-content { padding: 1.25rem; }
.bulk-fill-grid { display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr auto; gap: 1rem; align-items: end; margin-bottom: 1.25rem; }
.input-group { display: flex; flex-direction: column; gap: 0.375rem; min-width: 0; }
.input-label { font-size: 0.8125rem; font-weight: 600; color: #374151; display: block; }
.input-label-invisible { visibility: hidden; height: 0; overflow: hidden; margin: 0; padding: 0; gap: 0; }
.modern-select, .modern-input-field { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; width: 100%; box-sizing: border-box; }
.apply-group .apply-btn { width: 100%; min-width: 100px; }
.apply-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: #3b82f6; color: #fff; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; }
.apply-btn:hover { background: #2563eb; }
.filter-options { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.25rem; padding-top: 1rem; padding-bottom: 0.5rem; border-top: 1px solid #e5e7eb; }
.filter-options-title { font-size: 0.8125rem; font-weight: 600; color: #374151; width: 100%; margin-bottom: 0.25rem; }
.option-item { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.option-item input { flex-shrink: 0; margin: 0; }
.checkbox-label { font-size: 0.875rem; color: #374151; cursor: pointer; margin: 0; line-height: 1.4; }
.filter-hint { font-size: 0.8125rem; color: #6b7280; line-height: 1.45; margin: 0; padding-top: 0.75rem; border-top: 1px solid #f3f4f6; }

/* Table - Sevkiyatlar gibi mobilde yatay kaydırma */
.table-container { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: auto; max-height: min(calc(100vh - 280px), 720px); box-shadow: 0 1px 3px rgba(0,0,0,0.05); -webkit-overflow-scrolling: touch; }
.attendance-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.875rem; }
.attendance-table thead { background: var(--bs-primary); color: #fff; position: sticky; top: 0; z-index: 20; }
.attendance-table thead th.weekend { background: #fef9c3; color: #1f2937; }
/* z-index: 100 — sidebar (1000) altında kalsın, mobilde üstte görünmesin */
.personnel-header { padding: 0.75rem 1rem; min-width: 200px; position: sticky; left: 0; z-index: 100; background: var(--bs-primary); color: #fff; }
.personnel-header .badge { background: rgba(255,255,255,0.3); color: #fff; }
.day-header { padding: 0.5rem 0.35rem; text-align: center; min-width: 52px; }
.day-content { display: flex; flex-direction: column; align-items: center; gap: 0.15rem; }
.day-number { font-weight: 600; font-size: 0.875rem; }
.day-name { font-size: 0.65rem; opacity: 0.9; }
.personnel-cell { padding: 0.5rem 1rem; position: sticky; left: 0; background: #fff; z-index: 10; border-right: 1px solid #f3f4f6; }
.personnel-info { display: flex; align-items: center; gap: 0.75rem; }
.personnel-avatar { width: 32px; height: 32px; min-width: 32px; border-radius: 50%; background: var(--bs-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; }
.personnel-avatar.avatar-sm { width: 26px; height: 26px; min-width: 26px; font-size: 0.75rem; }
.personnel-name { font-weight: 500; color: var(--text-dark); }
.day-cell { padding: 0.35rem; text-align: center; }
.day-cell.weekend { background: #fefce8; }
.status-btn { width: 44px; height: 44px; border: none; border-radius: 50%; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; padding: 0; background: transparent; }
.status-btn.btn-compact { width: 34px; height: 34px; }
.status-icon { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; font-size: 17px; font-weight: bold; line-height: 1; -webkit-font-smoothing: antialiased; }
.status-btn.btn-compact .status-icon { width: 24px; height: 24px; font-size: 14px; }
.full-icon { background: #10b981; color: #fff; }
.half-icon { background: #f59e0b; color: #fff; }
.leave-icon, .izin-icon { background: #06b6d4; color: #fff; }
.annual-icon, .yillik-icon { background: #3b82f6; color: #fff; }
.sick-icon, .rapor-icon { background: #ef4444; color: #fff; }
.absent-icon, .none-icon { background: #9ca3af; color: #fff; font-weight: 900; -webkit-text-stroke: 1.5px #fff; paint-order: stroke fill; }
.summary-header, .summary-cell { padding: 0.5rem; text-align: center; }
.summary-header.summary-col-full { background: var(--bs-success-200); color: #0f5132; }
.summary-header.summary-col-half { background: var(--bs-warning-200); color: #664d03; }
.summary-header.summary-col-izin { background: var(--bs-info-200); color: #055160; }
.summary-header.summary-col-yillik { background: var(--bs-primary-200); color: var(--bs-primary); }
.summary-header.summary-col-rapor { background: var(--bs-danger-200); color: #842029; }
.summary-header.summary-col-none { background: var(--bs-secondary-200); color: #495057; }
.summary-cell.summary-col-full { background: var(--bs-success-200); }
.summary-cell.summary-col-half { background: var(--bs-warning-200); }
.summary-cell.summary-col-izin { background: var(--bs-info-200); }
.summary-cell.summary-col-yillik { background: var(--bs-primary-200); }
.summary-cell.summary-col-rapor { background: var(--bs-danger-200); }
.summary-cell.summary-col-none { background: var(--bs-secondary-200); }
.summary-badge.summary-badge-full { background: var(--bs-success-200); color: #0f5132; border: 1px solid rgba(15,81,50,0.2); }
.summary-badge.summary-badge-half { background: var(--bs-warning-200); color: #664d03; border: 1px solid rgba(102,77,3,0.2); }
.summary-badge.summary-badge-izin { background: var(--bs-info-200); color: #055160; border: 1px solid rgba(5,81,96,0.2); }
.summary-badge.summary-badge-yillik { background: var(--bs-primary-200); color: var(--bs-primary); border: 1px solid rgba(61,105,206,0.2); }
.summary-badge.summary-badge-rapor { background: var(--bs-danger-200); color: #842029; border: 1px solid rgba(132,32,41,0.2); }
.summary-badge.summary-badge-none { background: var(--bs-secondary-200); color: #495057; border: 1px solid rgba(73,80,87,0.2); }
.totals-row td { background: var(--bs-primary); color: #fff; position: relative; z-index: 15; }
.totals-row td.total-label { position: sticky; left: 0; z-index: 100; min-width: 200px; }
.totals-row td.weekend { background: #fef9c3; color: #1f2937; }

.context-menu { position: absolute; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); padding: 0.5rem 0; z-index: 1000; min-width: 160px; border: 1px solid #e5e7eb; }
.context-menu-item { padding: 0.75rem 1rem; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; }
.context-menu-item:hover { background: #f8fafc; }
/* Toast bildirimi – modern ve net */
.toast-notification { position: fixed; top: 24px; right: 24px; padding: 0.875rem 1.25rem 0.875rem 1rem; border-radius: 12px; display: flex; align-items: center; gap: 0.75rem; z-index: 9999; opacity: 0; transform: translateX(calc(100% + 32px)); transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 10px 25px -5px rgba(0,0,0,0.12); font-size: 0.9375rem; font-weight: 500; color: #1f2937; min-width: 200px; }
.toast-notification.show { opacity: 1; transform: translateX(0); }
.toast-notification .toast-icon-wrap { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.25rem; font-weight: bold; line-height: 1; }
.toast-notification.toast-success .toast-icon-wrap { background: var(--bs-success-200); color: #0f5132; }
.toast-notification.toast-error .toast-icon-wrap { background: var(--bs-danger-200); color: #842029; font-size: 1.375rem; }
.toast-notification .toast-message { flex: 1; line-height: 1.4; }
.toast-notification.toast-success { border-left: 4px solid #10b981; }
.toast-notification.toast-error { border-left: 4px solid #ef4444; }
.loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.header-actions-dropdown { position: relative; }
.header-actions-btn { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; cursor: pointer; font-size: 0.875rem; }
.header-actions-btn:hover { border-color: var(--primary); color: var(--primary); }
.header-dropdown-menu { position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.5rem; min-width: 200px; z-index: 1000; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
.view-options { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
.view-option { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; padding: 0.75rem 0.5rem; border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 0.75rem; }
.view-option.active { background: #eff6ff; border-color: var(--primary); color: var(--primary); }

/* Mobil uyum: Sevkiyatlar sayfası ile aynı alan davranışları */
@media (max-width: 992px) {
    .attendance-table-card,
    .panel-card-bulk {
        width: 100% !important;
        max-width: 100%;
    }
}

@media (max-width: 768px) {
    .attendance-page-header {
        flex-direction: column;
        align-items: stretch;
    }
    .attendance-page-header-title {
        flex: none;
    }
    .attendance-page-header-actions {
        width: 100%;
    }
    .attendance-page-header-actions .btn {
        flex: 1;
        min-width: fit-content;
        justify-content: center;
    }
    .attendance-page-header-actions .btn .material-symbols-outlined:first-child {
        margin-right: 0.25rem;
    }
    .bulk-fill-grid {
        grid-template-columns: 1fr 1fr;
    }
    .bulk-fill-grid .apply-group {
        grid-column: 1 / -1;
    }
    .filter-options {
        flex-direction: column;
        align-items: flex-start;
    }
    .filter-options-title {
        margin-bottom: 0;
    }
    .stats-cards-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .stats-panel-body {
        padding: 0.5rem 0.75rem;
    }
    .card-header,
    .card-content {
        padding: 0.75rem 1rem;
    }
    .table-container {
        max-height: min(calc(100vh - 240px), 480px);
    }
    .personnel-header {
        min-width: 140px;
    }
    .personnel-cell {
        padding: 0.4rem 0.6rem;
    }
    .day-header {
        min-width: 44px;
        padding: 0.4rem 0.25rem;
    }
    .day-cell {
        padding: 0.25rem;
    }
    .status-btn {
        width: 36px;
        height: 36px;
    }
    .status-btn.btn-compact {
        width: 30px;
        height: 30px;
    }
    .status-btn .status-icon {
        width: 24px;
        height: 24px;
        font-size: 14px;
    }
    .status-btn.btn-compact .status-icon {
        width: 20px;
        height: 20px;
        font-size: 12px;
    }
    .totals-row td.total-label {
        min-width: 140px;
    }
}

@media (max-width: 576px) {
    .stats-cards-row {
        grid-template-columns: 1fr;
    }
    .bulk-fill-grid {
        grid-template-columns: 1fr;
    }
    .bulk-fill-grid .apply-group {
        grid-column: 1;
    }
}
</style>
@endpush
@endsection
