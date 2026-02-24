@extends('layouts.app')

@section('title', 'Belgeler - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Belgeler</h2>
        <p class="text-secondary mb-0">Tüm belgeleri görüntüleyin ve yönetin</p>
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
    <form method="GET" action="{{ route('admin.documents.index') }}" class="row g-3">
        <div class="col-md-3">
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
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-filter btn-filter-documents w-100 shadow-sm hover:shadow-md transition-all">Filtrele</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Belge Adı</th>
                    <th class="border-0 fw-semibold text-secondary small">Tür</th>
                    <th class="border-0 fw-semibold text-secondary small">Bağlı Olduğu</th>
                    <th class="border-0 fw-semibold text-secondary small">Bitiş Tarihi</th>
                    <th class="border-0 fw-semibold text-secondary small">Durum</th>
                    <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                <tr>
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
                    <td colspan="6" class="text-center py-5">
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
@endsection
