@extends('layouts.customer-app')

@section('title', 'Sipariş Şablonlarım - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="material-symbols-outlined text-primary" style="font-size: 1.75rem;">content_copy</span>
            <h2 class="h3 fw-bold text-dark mb-0">Sipariş Şablonlarım</h2>
        </div>
        <p class="text-secondary mb-0">Sık kullandığınız sipariş bilgilerini şablon olarak kaydedin</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
        <span class="material-symbols-outlined" style="font-size: 1.25rem;">add</span>
        Yeni Şablon Ekle
    </button>
</div>

<div class="row g-4">
    @forelse($templates as $template)
        <div class="col-md-6">
            <div class="bg-white rounded-3xl shadow-sm border p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">{{ $template->name }}</h5>
                    <div class="d-flex gap-2">
                        @if(Auth::user() && Auth::user()->hasPermission('customer.portal.orders.create'))
                            <form method="POST" action="{{ route('customer.order-templates.create-order', $template) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary" title="Bu şablondan sipariş oluştur">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">add_shopping_cart</span>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('customer.order-templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('Bu şablonu silmek istediğinizden emin misiniz?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mb-2">
                    <p class="small text-secondary mb-1">
                        <span class="fw-semibold">Alış:</span> {{ Str::limit($template->pickup_address, 60) }}
                    </p>
                    <p class="small text-secondary mb-0">
                        <span class="fw-semibold">Teslimat:</span> {{ Str::limit($template->delivery_address, 60) }}
                    </p>
                </div>
                @if($template->total_weight || $template->total_volume)
                    <div class="small text-secondary mb-2">
                        @if($template->total_weight)
                            <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1 me-1">
                                Ağırlık: {{ number_format($template->total_weight, 2) }} kg
                            </span>
                        @endif
                        @if($template->total_volume)
                            <span class="badge bg-primary-200 text-primary rounded-pill px-2 py-1">
                                Hacim: {{ number_format($template->total_volume, 2) }} m³
                            </span>
                        @endif
                    </div>
                @endif
                @if($template->is_dangerous)
                    <span class="badge bg-danger-200 text-danger rounded-pill px-2 py-1 mb-2">Tehlikeli Madde</span>
                @endif
                @if($template->notes)
                    <p class="small text-secondary mb-0">{{ Str::limit($template->notes, 100) }}</p>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="bg-white rounded-3xl shadow-sm border p-5 text-center">
                <span class="material-symbols-outlined text-secondary mb-2 d-block" style="font-size: 3rem; opacity: 0.3;">content_copy</span>
                <p class="text-secondary mb-3">Henüz sipariş şablonu eklenmemiş.</p>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">add</span>
                    İlk Şablonu Ekle
                </button>
            </div>
        </div>
    @endforelse
</div>

<!-- Add Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('customer.order-templates.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Sipariş Şablonu Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold text-dark">Şablon Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="pickup_address" class="form-label fw-semibold text-dark">Alış Adresi <span class="text-danger">*</span></label>
                        <textarea name="pickup_address" id="pickup_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_address" class="form-label fw-semibold text-dark">Teslimat Adresi <span class="text-danger">*</span></label>
                        <textarea name="delivery_address" id="delivery_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="total_weight" class="form-label fw-semibold text-dark">Ağırlık (kg)</label>
                            <input type="number" step="0.01" min="0" name="total_weight" id="total_weight" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="total_volume" class="form-label fw-semibold text-dark">Hacim (m³)</label>
                            <input type="number" step="0.01" min="0" name="total_volume" id="total_volume" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_dangerous" id="is_dangerous" class="form-check-input" value="1">
                            <label for="is_dangerous" class="form-check-label fw-semibold text-dark">Tehlikeli Madde</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold text-dark">Notlar</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
