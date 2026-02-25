@extends('layouts.app')

@section('title', 'Teslimat Raporları - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Teslimat Raporları</h2>
        <p class="text-secondary mb-0">Excel ile yüklediğiniz teslimat raporlarını burada görüntüleyebilir; rapor detayından Veri Analiz Raporu ile tarih ve malzeme bazlı özet tabloya geçebilirsiniz.</p>
    </div>
    <a href="{{ route('admin.delivery-imports.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">upload_file</span>
        Rapor Yükle
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="upload_file" color="primary" col="col-md-4" />
    <x-index-stat-card title="Tamamlandı" :value="$stats['completed'] ?? 0" icon="check_circle" color="success" col="col-md-4" />
    <x-index-stat-card title="Hatalı" :value="$stats['failed'] ?? 0" icon="error" color="danger" col="col-md-4" />
</div>

<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.delivery-imports.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="status" class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" id="status" class="form-select">
                <option value="">Tümü</option>
                <option value="pending" @selected(request('status') === 'pending')>Beklemede</option>
                <option value="processing" @selected(request('status') === 'processing')>İşleniyor</option>
                <option value="completed" @selected(request('status') === 'completed')>Tamamlandı</option>
                <option value="failed" @selected(request('status') === 'failed')>Hata</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="date_from" class="form-label small fw-semibold text-dark">Başlangıç</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="date_to" class="form-label small fw-semibold text-dark">Bitiş</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-primary w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-outline-secondary w-100">Temizle</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="delivery-imports-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="delivery-imports-bulk-apply">Uygula</button>
        </div>
        <div class="small text-secondary"><span id="delivery-imports-selected-count">0</span> kayıt seçili</div>
    </div>
    <form id="delivery-imports-bulk-form" action="{{ route('admin.delivery-imports.bulk') }}" method="POST" class="d-none">
        @csrf
        <input type="hidden" name="action" id="delivery-imports-bulk-action-input">
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
                        <input type="checkbox" id="select-all-delivery-imports">
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'file_name' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.delivery-imports.index', array_merge(request()->query(), ['sort' => 'file_name', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Dosya Adı</span>
                            @if($currentSort === 'file_name')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'status' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.delivery-imports.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Durum</span>
                            @if($currentSort === 'status')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">{{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 small text-secondary fw-semibold">Toplam Satır</th>
                    <th class="border-0 small text-secondary fw-semibold">Başarılı / Hatalı</th>
                    <th class="border-0 small text-secondary fw-semibold">Yükleyen</th>
                    <th class="border-0 small text-secondary fw-semibold">
                        @php $direction = $currentSort === 'created_at' && $currentDirection === 'asc' ? 'desc' : 'asc'; @endphp
                        <a href="{{ route('admin.delivery-imports.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $direction])) }}" class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
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
                @forelse($batches as $batch)
                    <tr>
                        <td class="align-middle text-center">
                            <input type="checkbox" class="form-check-input delivery-import-row-check" name="selected[]" value="{{ $batch->id }}" form="delivery-imports-bulk-form">
                        </td>
                        <td class="align-middle">
                            <span class="fw-semibold text-dark">{{ $batch->file_name }}</span>
                        </td>
                        <td class="align-middle">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                ];
                                $statusLabels = [
                                    'pending' => 'Beklemede',
                                    'processing' => 'İşleniyor',
                                    'completed' => 'Tamamlandı',
                                    'failed' => 'Hata',
                                ];
                                $color = $statusColors[$batch->status] ?? 'secondary';
                                $label = $statusLabels[$batch->status] ?? $batch->status;
                            @endphp
                            <span class="badge bg-{{ $color }}-200 text-{{ $color }} rounded-pill px-3 py-2 fw-semibold">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $batch->total_rows ?? 0 }}</small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $batch->successful_rows ?? 0 }} / {{ $batch->failed_rows ?? 0 }}
                            </small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $batch->importer?->name ?? '-' }}
                            </small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                {{ $batch->created_at?->format('d.m.Y H:i') ?? '-' }}
                            </small>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex flex-wrap align-items-center justify-content-end gap-1">
                                <a href="{{ route('admin.delivery-imports.show', $batch) }}" class="btn btn-sm bg-primary-200 text-primary border-0" title="Detay">
                                    Detay
                                </a>
                                @if($batch->report_rows_count > 0)
                                    <a href="{{ route('admin.delivery-imports.export', [$batch, 'format' => 'xlsx']) }}" class="btn btn-sm btn-outline-primary" title="Excel indir">xlsx</a>
                                    <a href="{{ route('admin.delivery-imports.export', [$batch, 'format' => 'csv']) }}" class="btn btn-sm btn-outline-primary" title="CSV indir">csv</a>
                                @endif
                                @if(in_array($batch->status, ['pending', 'failed']))
                                    <form action="{{ route('admin.delivery-imports.reprocess', $batch) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Tekrar işle">
                                            <span class="material-symbols-outlined" style="font-size:1rem">refresh</span>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.delivery-imports.destroy', $batch) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu teslimat raporunu silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Sil">
                                        <span class="material-symbols-outlined" style="font-size:1rem">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">upload_file</span>
                                <p class="text-secondary mb-0">Henüz teslimat raporu bulunmuyor.</p>
                                <a href="{{ route('admin.delivery-imports.create') }}" class="btn btn-primary btn-sm mt-2">İlk Raporu Yükle</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($batches->hasPages())
        <div class="p-4 border-top">
            {{ $batches->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('delivery-imports-bulk-form');
    const actionSelect = document.getElementById('delivery-imports-bulk-action');
    const actionInput = document.getElementById('delivery-imports-bulk-action-input');
    const applyBtn = document.getElementById('delivery-imports-bulk-apply');
    const selectAll = document.getElementById('select-all-delivery-imports');
    const checkboxes = document.querySelectorAll('.delivery-import-row-check');
    const countEl = document.getElementById('delivery-imports-selected-count');
    function updateCount() { const n = document.querySelectorAll('.delivery-import-row-check:checked').length; countEl.textContent = n; }
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
    if (selectAll) { selectAll.addEventListener('change', function () { checkboxes.forEach(cb => { cb.checked = selectAll.checked; }); updateCount(); }); }
    applyBtn.addEventListener('click', function () {
        const action = actionSelect.value;
        if (!action) return;
        const checked = document.querySelectorAll('.delivery-import-row-check:checked');
        if (checked.length === 0) { alert('Lütfen en az bir rapor seçin.'); return; }
        checked.forEach(cb => form.appendChild(cb.cloneNode(true)));
        actionInput.value = action;
        form.submit();
    });
});
</script>
@endpush
@endsection
