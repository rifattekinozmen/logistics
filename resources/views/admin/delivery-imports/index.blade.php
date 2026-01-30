@extends('layouts.app')

@section('title', 'Teslimat İmportları - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Teslimat İmportları</h2>
        <p class="text-secondary mb-0">Excel ile yüklenmiş teslimat numarası batch’lerini görüntüleyin</p>
    </div>
    <a href="{{ route('admin.delivery-imports.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">upload_file</span>
        Yeni İmport
    </a>
</div>

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
                            <a href="{{ route('admin.delivery-imports.show', $batch) }}" class="btn btn-sm bg-primary-200 text-primary border-0">
                                Detay
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">upload_file</span>
                                <p class="text-secondary mb-0">Henüz teslimat importu bulunmuyor.</p>
                                <a href="{{ route('admin.delivery-imports.create') }}" class="btn btn-primary btn-sm mt-2">İlk İmportu Yap</a>
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

