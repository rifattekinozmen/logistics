@extends('layouts.app')

@section('title', 'Belgeler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary-200 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.5rem;">description</span>
        </div>
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Belgeler</h2>
            <p class="text-secondary mb-0">Tüm belgeleri görüntüleyin ve yönetin</p>
        </div>
    </div>
    <a href="{{ route('admin.documents.create') }}" class="btn btn-documents d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Belge
    </a>
</div>

<div class="row g-4 mb-4">
    <x-index-stat-card title="Toplam" :value="$stats['total'] ?? 0" icon="description" color="primary" col="col-md-12" />
</div>

<div class="filter-area filter-area-documents rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('admin.documents.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Tür</label>
            <select name="type" class="form-select">
                <option value="">Tümü</option>
                <option value="license" {{ request('type') === 'license' ? 'selected' : '' }}>Lisans</option>
                <option value="insurance" {{ request('type') === 'insurance' ? 'selected' : '' }}>Sigorta</option>
                <option value="inspection" {{ request('type') === 'inspection' ? 'selected' : '' }}>Muayene</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Durum</label>
            <select name="status" class="form-select">
                <option value="">Tümü</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pasif</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-dark">Bitiş Tarihi Başlangıç</label>
            <input type="date" name="expiry_date_from" value="{{ request('expiry_date_from') }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-documents w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="px-4 pt-3 d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center gap-2">
            <select id="documents-bulk-action" class="form-select form-select-sm w-auto">
                <option value="">Toplu işlem seçin</option>
                <option value="delete">Seçilenleri sil</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-primary" id="documents-bulk-apply">
                Uygula
            </button>
        </div>
        <div class="small text-secondary">
            <span id="documents-selected-count">0</span> kayıt seçili
        </div>
    </div>
    <div class="table-responsive">
        @php
            $currentSort = request('sort');
            $currentDirection = request('direction', 'asc');
        @endphp
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 text-center align-middle" style="width: 40px;">
                        <input type="checkbox" id="select-all-documents">
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.documents.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Belge Adı</span>
                            @if($currentSort === 'name')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'category' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.documents.index', array_merge(request()->query(), ['sort' => 'category', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Tür</span>
                            @if($currentSort === 'category')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">Bağlı Olduğu</th>
                    <th class="border-0 fw-semibold text-secondary small">
                        @php
                            $direction = $currentSort === 'valid_until' && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <a href="{{ route('admin.documents.index', array_merge(request()->query(), ['sort' => 'valid_until', 'direction' => $direction])) }}"
                           class="d-inline-flex align-items-center gap-1 text-secondary text-decoration-none">
                            <span>Bitiş Tarihi</span>
                            @if($currentSort === 'valid_until')
                                <span class="material-symbols-outlined" style="font-size: 1rem;">
                                    {{ $currentDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                </span>
                            @else
                                <span class="material-symbols-outlined opacity-50" style="font-size: 1rem;">unfold_more</span>
                            @endif
                        </a>
                    </th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                <tr>
                    <td class="align-middle text-center">
                        <input type="checkbox" class="documents-row-checkbox" value="{{ $document->id }}">
                    </td>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $document->name }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $document->type }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ class_basename($document->documentable_type) ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">
                            {{ $document->expiry_date ? $document->expiry_date->format('d.m.Y') : '-' }}
                        </small>
                    </td>
                    <td class="align-middle">
                        @if($document->status == 1)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                        @endif
                    </td>
                    <td class="align-middle text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.documents.show', $document->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                            </a>
                            <a href="{{ route('admin.documents.edit', $document->id) }}" class="btn btn-sm bg-primary-200 text-primary border-0 hover:bg-primary hover:text-white transition-all" title="Düzenle">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">edit</span>
                            </a>
                            <form action="{{ route('admin.documents.destroy', $document->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu belgeyi silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm bg-danger-200 text-danger border-0 hover:bg-danger hover:text-white transition-all" title="Sil">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">description</span>
                            <p class="text-secondary mb-0">Henüz belge bulunmuyor.</p>
                            <a href="{{ route('admin.documents.create') }}" class="btn btn-documents btn-sm mt-2">İlk Belgeyi Oluştur</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
    <div class="p-4 border-top">
        {{ $documents->links() }}
    </div>
    @endif
</div>

<form id="documents-bulk-form" method="POST" action="{{ route('admin.documents.bulk') }}" class="d-none">
    @csrf
    <input type="hidden" name="action" id="documents-bulk-action-input">
</form>
@endsection

@push('scripts')
<script>
const docMaster = document.getElementById('select-all-documents');
const docRows = document.querySelectorAll('.documents-row-checkbox');
const docCountEl = document.getElementById('documents-selected-count');
const docApplyBtn = document.getElementById('documents-bulk-apply');
const docActionSelect = document.getElementById('documents-bulk-action');
const docForm = document.getElementById('documents-bulk-form');
const docActionInput = document.getElementById('documents-bulk-action-input');

function updateDocumentsSelectedCount() {
    const selected = Array.from(docRows).filter(cb => cb.checked);
    if (docCountEl) {
        docCountEl.textContent = selected.length.toString();
    }
    if (docMaster) {
        docMaster.checked = selected.length > 0 && selected.length === docRows.length;
        docMaster.indeterminate = selected.length > 0 && selected.length < docRows.length;
    }
}

if (docMaster) {
    docMaster.addEventListener('change', function () {
        const checked = docMaster.checked;
        docRows.forEach(function (cb) {
            cb.checked = checked;
        });
        updateDocumentsSelectedCount();
    });
}

docRows.forEach(function (cb) {
    cb.addEventListener('change', updateDocumentsSelectedCount);
});

if (docApplyBtn) {
    docApplyBtn.addEventListener('click', function () {
        const action = docActionSelect.value;
        const selected = Array.from(docRows).filter(cb => cb.checked);

        if (! action) {
            alert('Lütfen bir toplu işlem seçin.');
            return;
        }

        if (selected.length === 0) {
            alert('Lütfen en az bir kayıt seçin.');
            return;
        }

        if (action === 'delete' && ! confirm('Seçili belgeleri silmek istediğinize emin misiniz?')) {
            return;
        }

        docForm.querySelectorAll('input[name="selected[]"]').forEach(function (input) {
            input.remove();
        });

        selected.forEach(function (cb) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected[]';
            hidden.value = cb.value;
            docForm.appendChild(hidden);
        });

        docActionInput.value = action;
        docForm.submit();
    });
}
</script>
@endpush
