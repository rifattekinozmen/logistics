@extends('layouts.app')

@section('title', 'Müşteri Detayı - Logistics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Müşteri Detayı</h2>
        <p class="text-secondary mb-0">{{ $customer->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">edit</span>
            Düzenle
        </a>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light">
            <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_back</span>
            Geri Dön
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bg-white rounded-3xl shadow-sm border p-4 mb-4" style="border-color: var(--bs-customers-200);">
            <h3 class="h4 fw-bold text-dark mb-4">Müşteri Bilgileri</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Müşteri Adı</label>
                    <p class="fw-bold text-dark mb-0">{{ $customer->name }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Durum</label>
                    <div>
                        @if($customer->status == 1)
                            <span class="badge bg-success-200 text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                        @else
                            <span class="badge bg-danger-200 text-danger px-3 py-2 rounded-pill fw-semibold">Pasif</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">E-posta</label>
                    <p class="text-dark mb-0">{{ $customer->email ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Telefon</label>
                    <p class="text-dark mb-0">{{ $customer->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Vergi Numarası</label>
                    <p class="text-dark mb-0">{{ $customer->tax_number ?? '-' }}</p>
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold text-secondary">Adres</label>
                    <p class="text-dark mb-0">{{ $customer->address ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-info-200);">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="h4 fw-bold text-dark mb-0">Siparişler</h3>
                <span class="badge bg-info-200 text-info px-3 py-2 rounded-pill fw-semibold">{{ $customer->orders->count() }} Sipariş</span>
            </div>
            @if($customer->orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-info-200">
                            <tr>
                                <th class="border-0 fw-semibold text-secondary small">Sipariş No</th>
                                <th class="border-0 fw-semibold text-secondary small">Tarih</th>
                                <th class="border-0 fw-semibold text-secondary small">Durum</th>
                                <th class="border-0 fw-semibold text-secondary small text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->orders as $order)
                            <tr>
                                <td class="align-middle">
                                    <span class="fw-bold text-dark">#{{ $order->id }}</span>
                                </td>
                                <td class="align-middle">
                                    <small class="text-secondary">{{ $order->created_at ? $order->created_at->format('d.m.Y H:i') : '-' }}</small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge bg-primary-200 text-primary px-3 py-2 rounded-pill fw-semibold">Sipariş</span>
                                </td>
                                <td class="align-middle text-end">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm bg-info-200 text-info border-0 hover:bg-info hover:text-white transition-all" title="Görüntüle">
                                        <span class="material-symbols-outlined" style="font-size: 1rem;">visibility</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size: 3rem;">shopping_cart</span>
                        <p class="text-secondary mb-0">Bu müşteriye ait henüz sipariş bulunmuyor.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bg-white rounded-3xl shadow-sm border p-4" style="border-color: var(--bs-customers-200);">
            <h3 class="h4 fw-bold text-dark mb-4">Hızlı İşlemler</h3>
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">edit</span>
                    Düzenle
                </a>
                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Bu müşteriyi silmek istediğinize emin misiniz?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined">delete</span>
                        Sil
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border p-4 mt-4" style="border-color: var(--bs-info-200);">
            <h3 class="h4 fw-bold text-dark mb-4">İstatistikler</h3>
            <div class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">Toplam Sipariş</label>
                    <p class="h5 fw-bold text-dark mb-0">{{ $customer->orders->count() }}</p>
                </div>
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">Kayıt Tarihi</label>
                    <p class="text-dark mb-0">{{ $customer->created_at ? $customer->created_at->format('d.m.Y H:i') : '-' }}</p>
                </div>
                @if($customer->updated_at)
                <div>
                    <label class="form-label small fw-semibold text-secondary mb-1">Son Güncelleme</label>
                    <p class="text-dark mb-0">{{ $customer->updated_at->format('d.m.Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
