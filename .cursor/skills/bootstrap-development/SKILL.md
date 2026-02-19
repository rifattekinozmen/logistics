---
name: bootstrap-development
description: Bootstrap 5 ve Tailwind CSS v4 utility ile frontend geliştirme. Yeni sayfa/bileşen oluştururken, mevcut UI düzenlenirken, tablo/form/kart/modal eklenirken kullanılır. "Bootstrap", "CSS", "stil", "tasarım", "UI", "kart", "tablo", "form", "modal", "buton" denildiğinde aktive olur.
---

# Bootstrap + Tailwind Development Skill

Bu projede **Bootstrap 5.3** layout/component çatısı, **Tailwind CSS v4** ise ek utility class'ları için kullanılıyor. Blade view'ları `resources/views/` altında `admin/`, `customer/`, `layouts/` olarak organize edilmiş.

## Ne Zaman Uygulanır

- Yeni sayfa veya Blade component yazılırken
- Tablo, form, kart, modal, buton ekleme/güncelleme
- Responsive düzenleme, renk/spacing değişikliği
- "Bootstrap", "tablo", "form", "kart", "modal", "stil", "UI" denildiğinde

## Temel Kural

**Önce Bootstrap bileşenlerini kullan.** Tailwind'i Bootstrap'ın kapsamadığı küçük ayarlamalar (padding, margin ince düzenleme, renk utility vb.) için ekle.

```html
{{-- Bootstrap layout + Tailwind utility birlikte --}}
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <h5 class="mb-0 fw-semibold">Sipariş Listesi</h5>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
            Yeni Sipariş
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                ...
            </table>
        </div>
    </div>
</div>
```

## Sık Kullanılan Bootstrap Bileşenleri

### Tablo
```html
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Müşteri</th>
                <th>Durum</th>
                <th class="text-end">İşlem</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->customer->name }}</td>
                <td>
                    <span class="badge bg-success">Aktif</span>
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                        Görüntüle
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

### Form
```html
<form action="{{ route('admin.orders.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="customer_id" class="form-label">Müşteri</label>
        <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
            <option value="">Seçiniz...</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">Kaydet</button>
</form>
```

### Modal
```html
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Onay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bu işlemi onaylıyor musunuz?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger">Evet, Sil</button>
            </div>
        </div>
    </div>
</div>
```

### Alert / Badge
```html
{{-- Alert --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Badge --}}
<span class="badge bg-primary">Yeni</span>
<span class="badge bg-success">Aktif</span>
<span class="badge bg-warning text-dark">Bekliyor</span>
<span class="badge bg-danger">İptal</span>
```

## Tailwind v4 Utility — Ne Zaman Ekle

Bootstrap'ın karşılamadığı ince ayarlar için Tailwind kullan:

```html
{{-- Bootstrap grid + Tailwind gap --}}
<div class="row g-3">
    <div class="col-md-6">...</div>
</div>

{{-- Bootstrap card + Tailwind truncate --}}
<p class="card-text truncate">{{ $longText }}</p>

{{-- Bootstrap btn + Tailwind transition --}}
<button class="btn btn-primary transition-all duration-200">Gönder</button>
```

### Tailwind v4 Kritik Notlar
- Import: `@import "tailwindcss"` (`@tailwind base/components/utilities` değil)
- Opacity: `bg-black/50` (`bg-opacity-50` değil)
- Shrink: `shrink-0` (`flex-shrink-0` değil)

## Mevcut Blade Yapısı

```
resources/views/
├── admin/          # Yönetim paneli view'ları
│   ├── orders/     # index.blade.php, create.blade.php, show.blade.php
│   ├── vehicles/
│   ├── employees/
│   └── ...
├── customer/       # Müşteri portalı view'ları
├── layouts/        # Ana layout (app.blade.php, admin.blade.php vb.)
└── components/     # Paylaşılan Blade component'leri
```

Yeni view eklerken ilgili modülün mevcut view'larını referans al.

## Checklist (yeni sayfa/bileşen)

- [ ] Aynı modülün mevcut view'larına bak, aynı pattern'i kullan
- [ ] Bootstrap bileşenini kullan; Tailwind'i sadece ince ayar için ekle
- [ ] Form'larda `@error` ve `is-invalid` class'ını kullan
- [ ] Tablo'larda `table-responsive` sarmalayıcısı ekle
- [ ] `npm run build` veya `npm run dev` çalıştır
