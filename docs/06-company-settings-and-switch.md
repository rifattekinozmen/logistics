# Firma Ayarları & Firma Değiştir

Bu doküman; **Firma Ayarları**, **Firma Değiştir**, **DB Schema (Migration)**, **UI Akışı** ve **Laravel Teknik Implementasyonunu** tek yerde toplar.

---

## 1️⃣ DB Schema (Migration Yapısı)

### 1.1 companies (Firmalar)

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| `id` | bigint | Primary key |
| `name` | string | Ticari unvan |
| `short_name` | string | Kısa isim |
| `tax_office` | string | Vergi dairesi |
| `tax_number` | string | Vergi numarası |
| `mersis_no` | string | MERSIS numarası |
| `trade_registry_no` | string | Ticaret sicil numarası |
| `currency` | string | Para birimi (TRY, USD, EUR vb.) |
| `default_vat_rate` | decimal | Varsayılan KDV oranı |
| `logo_path` | string, nullable | Logo dosya yolu |
| `stamp_path` | string, nullable | Kaşe/İmza dosya yolu |
| `is_active` | boolean | Aktiflik durumu |
| `created_at` | timestamp | Oluşturulma zamanı |
| `updated_at` | timestamp | Güncellenme zamanı |
| `deleted_at` | timestamp, nullable | Soft delete |

---

### 1.2 company_addresses

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| `id` | bigint | Primary key |
| `company_id` | bigint | Foreign key (companies) |
| `title` | string | Başlık (Merkez, Şube vb.) |
| `address` | text | Adres |
| `city` | string | Şehir |
| `district` | string | İlçe |
| `country` | string | Ülke |
| `is_default` | boolean | Varsayılan adres mi? |
| `created_at` | timestamp | Oluşturulma zamanı |
| `updated_at` | timestamp | Güncellenme zamanı |

---

### 1.3 company_settings

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| `id` | bigint | Primary key |
| `company_id` | bigint | Foreign key (companies) |
| `setting_key` | string | Ayar anahtarı |
| `setting_value` | text | Ayar değeri (JSON veya text) |
| `created_at` | timestamp | Oluşturulma zamanı |
| `updated_at` | timestamp | Güncellenme zamanı |

**Örnek Ayar Anahtarları:**

| Anahtar | Tip | Açıklama |
|---------|-----|----------|
| `work_start_time` | time | İş başlangıç saati (örn: "09:00") |
| `work_end_time` | time | İş bitiş saati (örn: "18:00") |
| `overtime_enabled` | boolean | Mesai izni (true/false) |
| `negative_stock_allowed` | boolean | Negatif stok izni (true/false) |
| `ai_enabled` | boolean | AI özellikleri aktif mi? (true/false) |
| `ai_summary_frequency` | string | AI özet sıklığı (daily, weekly, monthly) |
| `default_warehouse_id` | integer | Varsayılan depo ID |
| `default_branch_id` | integer | Varsayılan şube ID |
| `invoice_prefix` | string | Fatura ön eki (örn: "FTR") |
| `order_prefix` | string | Sipariş ön eki (örn: "SIP") |

---

### 1.4 user_companies

| Kolon | Tip | Açıklama |
|-------|-----|----------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key (users) |
| `company_id` | bigint | Foreign key (companies) |
| `role` | string | Kullanıcının firmadaki rolü (admin, manager, employee vb.) |
| `is_default` | boolean | Varsayılan firma mı? |
| `created_at` | timestamp | Oluşturulma zamanı |
| `updated_at` | timestamp | Güncellenme zamanı |

---

## 2️⃣ UI / UX Akışı (Firma Ayarları)

### 2.1 Firma Ayarları Ana Sayfa (Sekmeli Yapı)

**URL:** `/admin/companies/{company}/settings`

**Sekmeler:**

#### ✅ Mevcut Sekmeler

1. **Genel Bilgiler**
   - Ticari unvan, kısa isim
   - Vergi bilgileri (vergi dairesi, vergi no, MERSIS, ticaret sicil)
   - Para birimi, varsayılan KDV oranı
   - Logo ve kaşe yükleme
   - Aktiflik durumu

2. **İletişim & Adresler**
   - Firma adresleri listesi
   - Varsayılan adres gösterimi
   - Yeni adres ekleme/düzenleme

3. **Sistem Ayarları**
   - İş saatleri (başlangıç/bitiş)
   - Mesai izni toggle
   - Negatif stok izni toggle
   - AI özellikleri aktif toggle

#### ⏳ Yakında Eklenecek Sekmeler

4. **Finansal Ayarlar**
   - Para birimi ayarları
   - Ödeme yöntemleri
   - Fatura ayarları

5. **Belge & Numara Ayarları**
   - Fatura ön eki
   - Sipariş ön eki
   - Belge numaralandırma kuralları

6. **Depo & Operasyon Varsayılanları**
   - Varsayılan depo
   - Varsayılan şube
   - Stok takip yöntemi

7. **Personel & İK**
   - İzin politikaları
   - Çalışma saatleri detayları

8. **Bildirimler**
   - Email bildirimleri
   - SMS bildirimleri
   - Push bildirimleri

9. **Güvenlik**
   - IP kısıtlamaları
   - Oturum yönetimi
   - Erişim logları

---

### 2.2 Firma Değiştir UI

**Konum:** Header sağ üstte dropdown

**Özellikler:**

- Sadece kullanıcının yetkili olduğu firmalar görünür
- Aktif firma badge ile gösterilir
- Firma değişince:
  - Sayfa soft refresh (JavaScript ile)
  - Aktif firma badge güncellenir
  - Session güncellenir

**UX Notları:**

- Animasyon yok (hızlı geçiş)
- Dropdown açıkken aktif firma işaretlenir
- Firma değişiminde loading göstergesi (opsiyonel)

---

## 3️⃣ Laravel Teknik Implementasyon

### 3.1 Aktif Firma Yönetimi

**Session Key:** `active_company_id`

**Akış:**

1. **Kullanıcı Login Olduğunda:**
   - `user_companies` tablosundan `is_default = 1` olan firma bulunur
   - Bulunamazsa ilk firma seçilir
   - Session'a `active_company_id` yazılır

2. **Firma Değiştirme:**
   - Yetki kontrolü yapılır (`user_companies` tablosunda kayıt var mı?)
   - Session güncellenir
   - Cache temizlenir (firma bazlı cache'ler)

**Helper Metod:**

```php
// User modelinde
public function activeCompany(): ?Company
{
    $companyId = session('active_company_id');
    
    if (!$companyId) {
        $defaultCompany = $this->companies()->where('is_default', true)->first();
        $companyId = $defaultCompany?->id ?? $this->companies()->first()?->id;
        
        if ($companyId) {
            session(['active_company_id' => $companyId]);
        }
    }
    
    return $companyId ? Company::find($companyId) : null;
}
```

---

### 3.2 Middleware – ActiveCompany

**Dosya:** `app/Http/Middleware/ActiveCompany.php`

**Görev:**

- Her request'te aktif firma kontrolü
- Session'da `active_company_id` yoksa:
  - Kullanıcının default firması set edilir
  - Veya firma seçim sayfasına yönlendirilir
- Yetkisiz firma erişimi engellenir

**Kullanım:**

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'active.company' => \App\Http\Middleware\ActiveCompany::class,
    ]);
})

// routes/admin.php
Route::middleware(['auth', 'active.company'])->group(function () {
    // Firma bazlı route'lar
});
```

---

### 3.3 Global Scope – CompanyScope

**Dosya:** `app/Core/Scopes/CompanyScope.php`

**Görev:**

- Tüm modellerde `company_id` otomatik filtrelenir
- Query'lerde `where('company_id', active_company_id)` otomatik eklenir

**Kullanım:**

```php
// Model'de
use App\Core\Scopes\CompanyScope;

protected static function booted(): void
{
    static::addGlobalScope(new CompanyScope);
}
```

**İstisnalar:**

- `Company` modeli scope'tan muaf
- `User` modeli scope'tan muaf
- Admin işlemleri için `withoutGlobalScope(CompanyScope::class)` kullanılabilir

**Örnek:**

```php
// Normal kullanım - otomatik company_id filtrelenir
Order::all(); // Sadece aktif firmaya ait siparişler

// Scope'u devre dışı bırakma
Order::withoutGlobalScope(CompanyScope::class)->all(); // Tüm siparişler
```

---

### 3.4 Firma Değiştir Akışı

**Route:** `POST /admin/companies/switch`

**Controller Method:** `CompanyController@switch`

**Akış:**

1. Kullanıcı firma seçer
2. Yetki kontrol edilir (`user_companies` tablosunda kayıt var mı?)
3. Session güncellenir (`active_company_id`)
4. Cache temizlenir (firma bazlı cache'ler)
5. Kullanıcı aynı sayfada kalır (redirect back)

**Controller Örneği:**

```php
public function switch(Request $request): RedirectResponse
{
    $request->validate([
        'company_id' => 'required|exists:companies,id',
    ]);
    
    $user = auth()->user();
    $company = Company::findOrFail($request->company_id);
    
    // Yetki kontrolü
    if (!$user->companies()->where('company_id', $company->id)->exists()) {
        abort(403, 'Bu firmaya erişim yetkiniz yok.');
    }
    
    // Session güncelle
    session(['active_company_id' => $company->id]);
    
    // Cache temizle
    Cache::tags(["company:{$company->id}"])->flush();
    
    return redirect()->back()->with('success', 'Firma başarıyla değiştirildi.');
}
```

**API Response (JSON):**

```json
{
    "success": true,
    "message": "Firma başarıyla değiştirildi",
    "company": {
        "id": 1,
        "name": "Örnek Firma A.Ş.",
        "short_name": "Örnek Firma"
    }
}
```

---

## 4️⃣ Performans & Güvenlik Notları

### 4.1 Cache Stratejisi

**Firma Bazlı Cache Key'leri:**

```php
// Örnek cache key'leri
$settingsKey = "company:{$companyId}:settings";
$usersKey = "company:{$companyId}:users";
$permissionsKey = "company:{$companyId}:permissions";
```

**Cache Tag'leri ile Yönetim:**

```php
// Cache'e yazma
Cache::tags(['company', "company:{$companyId}"])
    ->put("company:{$companyId}:settings", $settings, 3600);

// Firma değişiminde sadece ilgili cache temizleme
Cache::tags(["company:{$companyId}"])->flush();
```

**Öneriler:**

- Firma bazlı cache'ler tag'lerle yönetilmeli
- Firma değişiminde sadece ilgili cache temizlenir
- Global cache'ler (ör: ülke listesi) tag'lenmemeli

---

### 4.2 Rate Limiting

**Firma Bazlı Rate Limit:**

```php
// routes/admin.php
Route::middleware(['throttle:company'])->group(function () {
    // Firma bazlı rate limit uygulanır
});

// app/Providers/AppServiceProvider.php
RateLimiter::for('company', function (Request $request) {
    $companyId = session('active_company_id');
    $limit = CompanySetting::get($companyId, 'rate_limit', 60);
    
    return Limit::perMinute($limit)->by($companyId);
});
```

**Öneriler:**

- Her firma için ayrı limit tanımlanabilir
- Rate limit logları `company_id` ile tutulmalı

---

### 4.3 Loglama

**Audit Log Örneği:**

```php
// Tüm loglar company_id ile tutulur
Log::channel('audit')->info('Order created', [
    'company_id' => session('active_company_id'),
    'user_id' => auth()->id(),
    'order_id' => $order->id,
]);
```

**Öneriler:**

- Tüm loglar `company_id` ile tutulur
- Audit log'larda firma bilgisi mutlaka yer alır
- Log rotation firma bazlı yapılabilir

---

### 4.4 Güvenlik

**CSRF Koruması:**

```php
// Blade template'te
@csrf

// API'de (JWT kullanılıyorsa)
// JWT içinde company_id taşınır
```

**Yetki Kontrolü:**

```php
// Her firma değişiminde yetki kontrolü
if (!$user->companies()->where('company_id', $companyId)->exists()) {
    abort(403, 'Bu firmaya erişim yetkiniz yok.');
}
```

**SQL Injection Koruması:**

- Eloquent ORM kullanımı (parametreli sorgular)
- Raw query'lerde mutlaka binding kullanılmalı

**Öneriler:**

- Firma değişiminde CSRF koruması zorunlu
- Yetki kontrolü her zaman yapılır
- SQL injection koruması (Eloquent kullanımı)
- XSS koruması (Blade escaping)

---

## 5️⃣ İleri Seviye (Opsiyonel)

### 5.1 Firma Bazlı Tema

**Özellikler:**

- Her firma için özel renk şeması
- Logo ve favicon dinamik yükleme
- CSS değişkenleri ile tema yönetimi

**Implementasyon:**

```php
// Company modelinde
public function getThemeAttribute(): array
{
    return [
        'primary_color' => $this->settings()->where('setting_key', 'primary_color')->value('setting_value') ?? '#3B82F6',
        'secondary_color' => $this->settings()->where('setting_key', 'secondary_color')->value('setting_value') ?? '#10B981',
        'logo_path' => $this->logo_path,
    ];
}
```

```blade
{{-- Blade template'te --}}
<style>
    :root {
        --primary-color: {{ $company->theme['primary_color'] }};
        --secondary-color: {{ $company->theme['secondary_color'] }};
    }
</style>
```

---

### 5.2 Firma Bazlı PDF Şablonları

**Özellikler:**

- Fatura şablonları
- Sipariş şablonları
- Rapor şablonları

**Implementasyon:**

```php
// Company modelinde
public function getPdfTemplate(string $type): string
{
    $template = $this->settings()
        ->where('setting_key', "pdf_template_{$type}")
        ->value('setting_value');
    
    return $template ?? "default.{$type}";
}
```

---

### 5.3 Firma Bazlı AI Eşik Değerleri

**Özellikler:**

- Her firma için farklı AI eşik değerleri
- Firma bazlı AI model seçimi
- Firma bazlı AI özet sıklığı

**Implementasyon:**

```php
// CompanySetting helper
public static function getAiThreshold(int $companyId, string $key, $default = null)
{
    return self::where('company_id', $companyId)
        ->where('setting_key', "ai_threshold_{$key}")
        ->value('setting_value') ?? $default;
}

// Kullanım
$threshold = CompanySetting::getAiThreshold($companyId, 'order_priority', 0.7);
```

---

## 🔑 Sonuç

Bu yapı sayesinde:

✅ **Multi-firma sistem kırılmaz** - Global scope ve middleware ile veri izolasyonu garanti edilir

✅ **Web → Mobile geçiş problemsiz olur** - API'lerde aynı mantık kullanılır (JWT içinde `company_id`)

✅ **Yetki & veri izolasyonu garanti edilir** - Her kullanıcı sadece yetkili olduğu firmaları görebilir

✅ **Sistem performansı korunur** - Cache stratejisi ve optimize query'ler ile

> **💡 Firma Ayarları düzgün kurulursa, sistem ölçeklenir.**
>
> Bu doküman, multi-company mimarinin çekirdeğidir. Mobile geçerken hiç değişmeyecek, ERP entegrasyonuna hazır ve AI modüllerinin doğru çalışması için zorunludur.
>
> Yani bu sadece "ayarlar" değil, **👉 sistemin omurgası.**

---

## 📋 Implementasyon Checklist

- [x] Migration dosyaları oluşturuldu
  - ✅ `2026_01_26_120005_create_companies_table.php` (güncellendi)
  - ✅ `2026_01_26_120043_create_company_addresses_table.php`
  - ✅ `2026_01_26_120044_create_company_settings_table.php`
  - ✅ `2026_01_26_120045_create_user_companies_table.php`
  - ✅ `2026_01_26_120046_update_companies_table_add_new_columns.php`

- [x] Model dosyaları güncellendi/oluşturuldu
  - ✅ `Company` modeli güncellendi (ilişkiler, helper metodlar)
  - ✅ `CompanyAddress` modeli oluşturuldu
  - ✅ `CompanySetting` modeli oluşturuldu
  - ✅ `User` modeli güncellendi (company ilişkileri, activeCompany metodu)

- [x] Global Scope eklendi
  - ✅ `app/Core/Scopes/CompanyScope.php` oluşturuldu

- [x] Middleware eklendi
  - ✅ `app/Http/Middleware/ActiveCompany.php` oluşturuldu
  - ✅ `bootstrap/app.php`'ye middleware alias eklendi

- [x] Controller ve route'lar oluşturuldu
  - ✅ `app/Http/Controllers/Admin/CompanyController.php` oluşturuldu
  - ✅ `routes/admin.php`'ye company route'ları eklendi
  - ✅ Route model binding ile soft delete desteği eklendi

- [x] View dosyaları oluşturuldu
  - ✅ `resources/views/admin/companies/select.blade.php` - Firma seçim sayfası
  - ✅ `resources/views/admin/companies/settings.blade.php` - Firma ayarları (sekme bazlı)
  - ✅ Navbar'a firma değiştir dropdown'ı eklendi
  - ✅ Sidebar'a "Firmalar" linki eklendi

- [x] Firma değiştir fonksiyonu çalışıyor
  - ✅ Navbar dropdown'ında firma değiştirme
  - ✅ Session yönetimi
  - ✅ Cache temizleme
  - ✅ Yetki kontrolü

- [ ] Test dosyaları yazıldı (Yakında eklenecek)

## 📁 Oluşturulan/Güncellenen Dosyalar

### Migration Dosyaları
- `database/migrations/2026_01_26_120005_create_companies_table.php` (güncellendi)
- `database/migrations/2026_01_26_120043_create_company_addresses_table.php`
- `database/migrations/2026_01_26_120044_create_company_settings_table.php`
- `database/migrations/2026_01_26_120045_create_user_companies_table.php`
- `database/migrations/2026_01_26_120046_update_companies_table_add_new_columns.php`

### Model Dosyaları
- `app/Models/Company.php` (güncellendi)
- `app/Models/CompanyAddress.php` (yeni)
- `app/Models/CompanySetting.php` (yeni)
- `app/Models/User.php` (güncellendi)

### Controller & Middleware
- `app/Http/Controllers/Admin/CompanyController.php` (yeni)
- `app/Http/Middleware/ActiveCompany.php` (yeni)
- `app/Core/Scopes/CompanyScope.php` (yeni)

### View Dosyaları
- `resources/views/admin/companies/select.blade.php` (yeni)
- `resources/views/admin/companies/settings.blade.php` (yeni)
- `resources/views/layouts/navbar.blade.php` (güncellendi - firma dropdown)
- `resources/views/layouts/sidebar.blade.php` (güncellendi - firmalar linki)

### Route Dosyaları
- `routes/admin.php` (güncellendi - company route'ları eklendi)
- `bootstrap/app.php` (güncellendi - middleware alias eklendi)
