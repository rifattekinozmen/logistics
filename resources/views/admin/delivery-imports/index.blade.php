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

<form method="GET" action="{{ route('admin.delivery-imports.index') }}" class="mb-4">
    <div class="bg-white rounded-3xl shadow-sm border p-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label for="status" class="form-label small text-secondary mb-0">Durum</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">Tümü</option>
                    <option value="pending" @selected(request('status') === 'pending')>Beklemede</option>
                    <option value="processing" @selected(request('status') === 'processing')>İşleniyor</option>
                    <option value="completed" @selected(request('status') === 'completed')>Tamamlandı</option>
                    <option value="failed" @selected(request('status') === 'failed')>Hata</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-secondary mb-0">Başlangıç</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label small text-secondary mb-0">Bitiş</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filtrele</button>
                <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-sm btn-outline-secondary">Temizle</a>
            </div>
        </div>
    </div>
</form>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 small text-secondary fw-semibold">Dosya Adı</th>
                    <th class="border-0 small text-secondary fw-semibold">Durum</th>
                    <th class="border-0 small text-secondary fw-semibold">Toplam Satır</th>
                    <th class="border-0 small text-secondary fw-semibold">Başarılı / Hatalı</th>
                    <th class="border-0 small text-secondary fw-semibold">Yükleyen</th>
                    <th class="border-0 small text-secondary fw-semibold">Tarih</th>
                    <th class="border-0 small text-secondary fw-semibold text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    <tr>
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
                        <td colspan="7" class="text-center py-5">
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
@endsection
