@extends('layouts.app')

@section('title', 'Vardiya Şablonları - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Vardiya Şablonları</h2>
        <p class="text-secondary mb-0">Vardiya şablonlarını görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-light d-inline-flex align-items-center gap-2">
        <span class="material-symbols-outlined">arrow_back</span>
        Geri Dön
    </a>
</div>

<div class="bg-white rounded-3xl shadow-sm border overflow-hidden" style="border-color: var(--bs-primary-200);">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-primary-200">
                <tr>
                    <th class="border-0 fw-semibold text-secondary small">Şablon Adı</th>
                    <th class="border-0 fw-semibold text-secondary small">Başlangıç Saati</th>
                    <th class="border-0 fw-semibold text-secondary small">Bitiş Saati</th>
                    <th class="border-0 fw-semibold text-secondary small">Süre</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                <tr>
                    <td class="align-middle">
                        <span class="fw-bold text-dark">{{ $template->name }}</span>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $template->start_time ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $template->end_time ?? '-' }}</small>
                    </td>
                    <td class="align-middle">
                        <small class="text-secondary">{{ $template->duration ?? '-' }} saat</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">schedule</span>
                            <p class="text-secondary mb-0">Henüz vardiya şablonu bulunmuyor.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($templates->hasPages())
    <div class="p-4 border-top">
        {{ $templates->links() }}
    </div>
    @endif
</div>
@endsection
