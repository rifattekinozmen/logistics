# Skill: Blade UI Update

Use when modifying views in `resources/views/`.

---

## View Conventions

- Admin views: `resources/views/admin/{module}/index.blade.php`, `create.blade.php`, `show.blade.php`
- Customer views: `resources/views/customer/`
- Layouts: `resources/views/layouts/admin.blade.php`, `resources/views/layouts/customer.blade.php`
- Bootstrap 5 for structural components (nav, grid, cards, tables, modals, forms)
- Tailwind CSS v4 for utility overrides (spacing, colors, flex/grid)
- Alpine.js for interactivity (`x-data`, `x-show`, `x-bind`, `@click`)

**Activate `tailwindcss-development` skill before any styling change.**

---

## Standard Admin Table Pattern

```blade
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Dosya Adı</th>
                <th>Durum</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $batch)
                <tr>
                    <td>{{ $batch->id }}</td>
                    <td>{{ $batch->file_name }}</td>
                    <td>
                        <span class="badge bg-{{ match($batch->invoice_status) {
                            'created' => 'success',
                            'sent'    => 'primary',
                            default   => 'warning',
                        } }}">{{ ucfirst($batch->invoice_status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.delivery-imports.show', $batch) }}"
                           class="btn btn-sm btn-outline-primary">Görüntüle</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Kayıt bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $batches->links() }}
```

---

## Delete Confirmation (Alpine.js)

```blade
<form method="POST"
      action="{{ route('admin.delivery-imports.destroy', $batch) }}"
      x-data
      @submit.prevent="if(confirm('Bu kaydı silmek istediğinizden emin misiniz?')) $el.submit()">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Sil</button>
</form>
```

---

## Status Badge Helper

```blade
{{-- invoice_status badge --}}
@php
$statusColors = ['pending' => 'warning', 'created' => 'success', 'sent' => 'primary'];
$statusLabels = ['pending' => 'Bekliyor', 'created' => 'Oluşturuldu', 'sent' => 'Gönderildi'];
@endphp
<span class="badge bg-{{ $statusColors[$batch->invoice_status] ?? 'secondary' }}">
    {{ $statusLabels[$batch->invoice_status] ?? $batch->invoice_status }}
</span>
```

---

## Filter Form Pattern

```blade
<form method="GET" action="{{ route('admin.delivery-imports.index') }}" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Ara..." value="{{ request('search') }}">
    </div>
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Tüm Durumlar</option>
            <option value="pending" @selected(request('status') === 'pending')>Bekliyor</option>
            <option value="created" @selected(request('status') === 'created')>Oluşturuldu</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">Filtrele</button>
        <a href="{{ route('admin.delivery-imports.index') }}" class="btn btn-sm btn-secondary">Temizle</a>
    </div>
</form>
```

---

## Pre-Update Checklist

1. Check sibling views in same module for pattern consistency
2. Activate `tailwindcss-development` for any class additions
3. Verify Bootstrap responsive classes (`col-md-*`, `d-none d-md-block`)
4. After JS/CSS changes: notify user to run `npm run build` or `composer run dev`
5. Check both desktop and mobile rendering (Bootstrap breakpoints: sm/md/lg/xl)
