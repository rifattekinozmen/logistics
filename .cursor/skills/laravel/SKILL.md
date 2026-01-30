---
name: laravel
description: Laravel 12 geliştirme - controller, model, migration, Form Request, Pest testleri, Blade. Controller/model/route eklerken veya değiştirirken, Eloquent/Blade/artisan/test konuşulduğunda kullanılır.
---

# Laravel Skill

Bu projede Laravel 12, Pest, Pint ve Tailwind v4 kullanılıyor. Tam kurallar için `.cursor/rules/laravel-boost.mdc` geçerlidir; bu skill hızlı akış ve proje yapısı için kısa referans sağlar.

## Ne Zaman Uygulanır

- Yeni veya mevcut controller, model, migration, route eklenirken/güncellenirken
- Form Request, policy veya test yazılırken
- Eloquent ilişkileri, Blade view veya Artisan komutu konuşulduğunda
- Laravel, artisan, Blade, Pest veya migration denildiğinde

## Hızlı Akış

1. **Dosya oluşturma:** `php artisan make:` kullan; `--no-interaction` ve gerekli seçenekleri ver. Generic sınıf için `php artisan make:class`.
2. **Validasyon:** Controller içinde inline validasyon yok; her zaman Form Request sınıfı kullan. Mevcut Request sınıflarına bakarak array/string kural tercihini koru.
3. **URL:** `route('route.name')` ile named route kullan; config için `config()` kullan, `env()` sadece config dosyalarında.
4. **Veritabanı:** `Model::query()` ve ilişki metodları; N+1 için eager loading. `DB::` yerine Eloquent tercih et.
5. **Test:** Pest kullan; `php artisan make:test --pest {Name}`. Assertion için `assertSuccessful`, `assertNotFound`, `assertForbidden` gibi spesifik metodlar.
6. **Bitirirken:** `vendor/bin/pint --dirty` çalıştır; ilgili testleri `php artisan test --filter=...` ile çalıştır.

## Pest Testleri

### Oluşturma ve konum
- Feature test: `php artisan make:test --pest tests/Feature/ModulAdiTest.php`
- Unit test: `php artisan make:test --pest --unit tests/Unit/ModulAdiTest.php`
- Testler: `tests/Feature/`, `tests/Unit/`; Feature testler Laravel TestCase ve `$this->get()` vb. kullanır.

### Yazım stili
- `it('açıklama', function () { ... })` veya `test('açıklama', function () { ... })`.
- Factory kullan: `User::factory()->create()`. Mevcut factory state'lerini (örn. `unverified()`) kullan.
- Faker: Projede `$this->faker` veya `fake()` kullanımına bak; aynı stili koru.

### HTTP assertion (status yerine spesifik metod)
- `$response->assertSuccessful()` (200–299)
- `$response->assertNotFound()`, `$response->assertForbidden()`, `$response->assertRedirect()`
- `assertStatus(403)` yerine `assertForbidden()` tercih et.

### Pest Laravel helper'ları (isteğe bağlı)
- `use function Pest\Laravel\actingAs;` → `actingAs($user);`
- `use function Pest\Laravel\get;` → `get(route('admin.leaves.index'));`
- `use function Pest\Laravel\post;`, `assertDatabaseHas`, `assertDatabaseMissing` vb.

### Dataset (tekrarlı veri / validasyon)
- Aynı testi farklı girdilerle çalıştırmak için `->with([...])` kullan:

```php
it('accepts valid emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'one' => 'a@b.com',
    'two' => 'user@example.com',
]);
```

### Mocking
- `use function Pest\Laravel\mock;` ile mock; veya mevcut testlerde `$this->mock()` kullanılıyorsa onu tercih et.

### Çalıştırma
- Tümü: `php artisan test`
- Dosya: `php artisan test tests/Feature/LeaveTest.php`
- Filtre: `php artisan test --filter="kullanıcı izin"`

### Checklist (yeni test)
- [ ] Factory ile model oluştur; seed’e bağlama.
- [ ] Status için `assertSuccessful` / `assertNotFound` / `assertForbidden` kullan.
- [ ] Gerekirse `actingAs($user)` ile giriş yap.
- [ ] Validasyon testlerinde dataset düşün.

## Proje Yapısı

- **Modüller:** `app/{Domain}/` (örn. `Order`, `Vehicle`, `Employee`) — içinde `Controllers/`, `Requests/`, `Policies/`, `Services/` olabilir.
- **Admin/Auth:** `app/Http/Controllers/Admin/`, `app/Http/Controllers/Auth/`.
- **Route dosyaları:** `routes/web.php`, `routes/admin.php`, `routes/api.php`, `routes/customer.php`, `routes/console.php`.
- **Middleware:** `bootstrap/app.php`; `app/Http/Middleware/` yok (Laravel 12).
- **View:** `resources/views/` — `admin/`, `customer/`, `layouts/` altında Blade.

Yeni özellik eklerken mevcut modül yapısına ve route gruplarına uy; yeni ana klasör açmadan önce uygun modül veya `Http` altını kullan.

## PHP / Laravel Kuralları (Özet)

- Constructor property promotion; açık return type ve parametre type hint.
- Enum key'leri TitleCase.
- Model: ilişki metodları return type ile; cast'lar `casts()` metodu ile (mevcut modellere bak).
- Migration'da sütun değiştirirken önceki tüm attribute'ları koru.

## Ek Kaynak

Detaylı kurallar, Pest örnekleri, Tailwind v4 ve Boost araçları için `.cursor/rules/laravel-boost.mdc` dosyasına bak.
