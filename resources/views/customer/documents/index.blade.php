@extends('layouts.customer-app')

@section('title', 'Belgelerim - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">description</span>
            <h2 class="h3 fw-bold text-dark mb-0">Belgelerim</h2>
        </div>
        <p class="text-secondary mb-0">Siparişlerinize ait belgeleri görüntüleyin ve indirin</p>
    </div>
    @if(Auth::user() && Auth::user()->hasPermission('customer.portal.documents.download'))
        <form method="POST" action="{{ route('customer.documents.index') }}" id="bulkDownloadForm" class="d-none">
            @csrf
            <input type="hidden" name="download_selected" value="1">
            <input type="hidden" name="selected_documents" id="selectedDocumentsInput">
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">download</span>
                Seçilenleri İndir (<span id="selectedCount">0</span>)
            </button>
        </form>
    @endif
</div>

<!-- Filtreleme ve Arama -->
<div class="filter-area filter-area-primary rounded-3xl shadow-sm border p-4 mb-4">
    <form method="GET" action="{{ route('customer.documents.index') }}" class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Kategori</label>
            <select name="category" class="form-select">
                <option value="">Tüm Kategoriler</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                        {{ ucfirst($category) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Sipariş No</label>
            <select name="order_id" class="form-select">
                <option value="">Tüm Siparişler</option>
                @foreach($orders as $order)
                    <option value="{{ $order->id }}" {{ request('order_id') == $order->id ? 'selected' : '' }}>
                        {{ $order->order_number }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-dark">Arama</label>
            <div class="input-group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Belge adı veya kategori ara..." class="form-control">
                <button type="submit" class="btn btn-filter btn-filter-primary">Ara</button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    @if(Auth::user() && Auth::user()->hasPermission('customer.portal.documents.download'))
                        <th class="border-0 small text-secondary fw-semibold" style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                    @endif
                    <th class="border-0 small text-secondary fw-semibold">Belge Adı</th>
                    <th class="border-0 small text-secondary fw-semibold">Kategori</th>
                    <th class="border-0 small text-secondary fw-semibold">Sipariş No</th>
                    <th class="border-0 small text-secondary fw-semibold">Dosya Boyutu</th>
                    <th class="border-0 small text-secondary fw-semibold">Tarih</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    @php
                        $order = \App\Models\Order::find($document->documentable_id);
                    @endphp
                    <tr>
                        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.documents.download'))
                            <td class="align-middle">
                                <input type="checkbox" class="form-check-input document-checkbox" value="{{ $document->id }}">
                            </td>
                        @endif
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-primary" style="font-size: 1.25rem;">description</span>
                                <span class="fw-semibold text-dark">{{ $document->name }}</span>
                            </div>
                        </td>
                        <td class="align-middle">
                            <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1">
                                {{ $document->category ?? 'Genel' }}
                            </span>
                        </td>
                        <td class="align-middle">
                            @if($order)
                                <a href="{{ route('customer.orders.show', $order) }}" class="text-primary text-decoration-none">
                                    {{ $order->order_number }}
                                </a>
                            @else
                                <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">
                                @if($document->file_size)
                                    {{ number_format($document->file_size / 1024, 2) }} KB
                                @else
                                    -
                                @endif
                            </small>
                        </td>
                        <td class="align-middle">
                            <small class="text-secondary">{{ $document->created_at->format('d.m.Y H:i') }}</small>
                        </td>
                        <td class="align-middle text-end">
                            @if(Auth::user() && Auth::user()->hasPermission('customer.portal.documents.download'))
                                <a href="{{ route('customer.documents.download', $document) }}" class="btn btn-sm bg-primary-200 text-primary border-0 d-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">download</span>
                                    İndir
                                </a>
                            @else
                                <span class="text-secondary small">Yetki yok</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ Auth::user() && Auth::user()->hasPermission('customer.portal.documents.download') ? '7' : '6' }}" class="text-center py-5">
                            <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">description</span>
                            <p class="text-secondary mb-0">Henüz belge bulunmuyor.</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.document-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const bulkDownloadForm = document.getElementById('bulkDownloadForm');
    const selectedDocumentsInput = document.getElementById('selectedDocumentsInput');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                // Tümü seçili mi kontrol et
                selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
            });
        });

        function updateSelectedCount() {
            const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (selectedCount) {
                selectedCount.textContent = selected.length;
            }
            
            // Toplu indirme butonunu göster/gizle
            if (bulkDownloadForm) {
                if (selected.length > 0) {
                    bulkDownloadForm.classList.remove('d-none');
                    selectedDocumentsInput.value = JSON.stringify(selected);
                } else {
                    bulkDownloadForm.classList.add('d-none');
                }
            }
        }

        // Form submit
        if (bulkDownloadForm) {
            bulkDownloadForm.addEventListener('submit', function(e) {
                const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
                if (selected.length === 0) {
                    e.preventDefault();
                    alert('Lütfen en az bir belge seçin.');
                    return false;
                }
                selectedDocumentsInput.value = JSON.stringify(selected);
            });
        }
    }
});
</script>
@endpush
@endsection
