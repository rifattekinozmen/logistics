# 🧠 LOJİSTİK ERP — AI AUTOPILOT KONTROL DOSYASI

> **Konum:** `docs/ai/SESSION_CONTROL.md` (taşındı: eski `docs/onereadme.md`)
> **Kullanım:** Bu dosyayı Cursor Chat'e sürükle → `/session` yaz → AI kaldığı yerden devam eder.
> **Güncelleme:** Her önemli özellik sonrası bu dosyanın `SESSION BELLEĞİ` bölümünü güncelle.

---

## ═══ 1. AI KONTROL PANELİ ═══

```
Proje Modu    : AKTIF_GELİŞTİRME
AI Yetkisi    : TAM OTONOM UYGULAMA
İnsan Rolü    : Yön Verme & Onaylama

Şu Anki Odak:
  ⚡ Docs & backlog alignment — dokümantasyon ile gerçek proje durumunu hibrit modele çekme
  ⚡ Analytics Dashboard — Chart.js entegrasyonu ve ek metrikler (finance/fleet/ops)
  ⚡ Delivery Import Pipeline — pivot & fatura kalemleri hattının sertleştirilmesi

Stabil (son milestone): ✔ Order  ✔ Customer  ✔ Vehicle  ✔ Auth  ✔ FuelPrice  ✔ Calendar  ✔ EInvoice
Aktif Geliştirme (ileri seviye): ⚠ Analytics (advanced metrics)  ⚠ Delivery (pivot/invoice tuning)  ⚠ Finance (anomaly detection)  ⚠ AI (AIFleet/AIDocument advanced)
Beklemede:              ❌ Tahminsel Bakım AI'nin derinleştirilmesi  ❌ Mobile App
```

---

## ═══ 2. PROJE ZEKA HARİTASI ═══

### Mimari
```
DDD + Modular Monolith | Laravel 12 | PHP 8.2.12 | MSSQL
Multi-tenant: CompanyScope (global scope) + ActiveCompany middleware
API: Laravel Sanctum | Queue: Redis | Test: Pest v3
Frontend: Bootstrap 5 + Tailwind CSS v4 + Vite + Alpine.js
```

### Temel Veri Akışı
```
Sipariş → Sevkiyat → Teslimat → Fatura → Finans → Analitik
Order   → Shipment → Delivery → Invoice → Finance → Analytics
```

### Tech Stack
| Katman      | Teknoloji                              |
|-------------|----------------------------------------|
| Backend     | PHP 8.2, Laravel 12                    |
| Veritabanı  | MS SQL Server (MSSQL)                  |
| Frontend    | Bootstrap 5 + Tailwind v4 + Vite       |
| Test        | Pest v3 + PHPUnit v11                  |
| Queue       | Redis (Laravel Queue)                  |
| Excel       | PHPOffice/PhpSpreadsheet               |
| Auth API    | Laravel Sanctum                        |
| Kod Kalite  | Laravel Pint v1                        |

### Route Grupları
| Dosya               | Prefix      | Amaç                        |
|---------------------|-------------|-----------------------------|
| `routes/web.php`    | `/`         | Auth + genel                |
| `routes/admin.php`  | `/admin`    | Yönetim paneli              |
| `routes/api.php`    | `/api/v1`   | REST API (Sanctum)          |
| `routes/customer.php`| `/customer`| Müşteri self-servis portalı |
| `routes/console.php`| —          | Artisan zamanlayıcılar      |

---

## ═══ 3. MODÜL OLGUNLUK MATRİSİ ═══

| Modül           | Durum                        | Test   | Refactor | AI Önceliği |
|-----------------|------------------------------|--------|----------|-------------|
| Order           | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| Customer        | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| Vehicle         | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| Auth/RBAC       | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| FuelPrice       | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| Calendar        | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| Location        | Stabil (production)          | ✅ Var | Hayır    | Düşük       |
| **Analytics**   | Çekirdek tamam, advanced dev | ⚠ Yeni | Evet     | **YÜKSEK**  |
| **Delivery**    | Çekirdek tamam, tuning aşaması | ⚠ Kısmi| Evet   | **YÜKSEK**  |
| **EInvoice**    | Stabil (production)          | ✅ Var | Hayır    | Orta        |
| **Finance**     | Çekirdek tamam, advanced dev | ⚠ Kısmi| Evet     | **YÜKSEK**  |
| Employee        | Kısmi      | ❌ Yok | Evet     | Orta        |
| Warehouse       | Kısmi      | ✅ Var | Evet     | Orta        |
| Shipment        | Kısmi      | ✅ Var | Hayır    | Orta        |
| Document        | Kısmi      | ✅ Var | Hayır    | Orta        |
| WorkOrder       | Kısmi      | ❌ Yok | Evet     | Orta        |
| Shift           | Kısmi      | ❌ Yok | Hayır    | Orta        |
| Notification    | Kısmi      | ✅ Var | Hayır    | Orta        |
| SAP             | Deneysel   | ✅ Var | Evet     | Bekle       |
| BusinessPartner | Deneysel   | ✅ Var | Evet     | Bekle       |
| Pricing         | Deneysel   | ✅ Var | Hayır    | Bekle       |
| DocumentFlow    | Deneysel   | ✅ Var | Hayır    | Bekle       |
| Driver (API)    | Deneysel   | ❌ Yok | Hayır    | Bekle       |

> **AI KURALI:** Önce YÜKSEK öncelikli modülleri tamamla. Deneysel modüllere insan onayı olmadan dokunma.

---

## ═══ 4. SESSION BELLEĞİ ═══

> **Son Güncelleme:** 2026-02-22

**Aktif Entity:** `AnalyticsDashboardService` + Analytics Dashboard

**Son Değişiklikler (git status):**
- `app/Analytics/Services/AnalyticsDashboardService.php` — finansal metrik metodları refactor
- `resources/js/charts.js` — mevcut chart yapısı yeniden düzenlendi
- `resources/js/analytics-charts.js` *(yeni)* — modüler chart başlatma
- `resources/views/admin/analytics/finance.blade.php` — Chart.js entegrasyonu
- `resources/views/admin/analytics/fleet.blade.php` — Chart.js entegrasyonu
- `resources/views/admin/analytics/operations.blade.php` — Chart.js entegrasyonu
- `resources/views/layouts/app.blade.php` — Vite asset güncelleme
- `vite.config.js` — analytics-charts.js entry point eklendi
- `tests/Feature/AnalyticsTest.php` *(yeni)* — test coverage başlatıldı

**Sonraki Beklenen Adımlar:**
1. `AnalyticsTest.php` test case'lerini tamamla
2. `AnalyticsDashboardService` — fleet metrics metodunu doğrula
3. `analytics-charts.js` — hata yönetimi ekle (empty data fallback)
4. Finance analytics — aylık karşılaştırma grafiği ekle

---

## ═══ 5. OTONOM GÖREV MOTORU ═══

Session başladığında AI şu adımları izler:

```
1. SESSION BELLEĞİ'ni oku → aktif entity'yi tespit et
2. SADECE ilgili domain klasörünü tara (tüm projeyi tarama!)
3. Kontrol et:
   a. Eksik/yetersiz Pest testleri
   b. N+1 query riski (eager loading eksikliği)
   c. 200+ satır controller (service'e taşı)
   d. Tipsiz method dönüşleri
   e. Büyük service metodları (50+ satır)
4. Sonraki mantıklı implementasyon adımını üret
5. Rewrite yerine refactor tercih et
6. Mevcut service/component varsa yenisini oluşturma
```

---

## ═══ 6. TOKEN OPTİMİZASYON KURALLARI ═══

```
✅ YAPILACAKLAR:
  - Sadece aktif domain klasörünü oku
  - Session memory'yi referans olarak kullan
  - Mevcut servisleri önce kontrol et, sonra yaz
  - Özet çıkar, tam dosyayı tekrar analiz etme
  - Incremental development — küçük, odaklı değişiklikler

❌ YAPILMAYACAKLAR:
  - Tüm app/ klasörünü tarama
  - Her session'da mimariyi yeniden analiz etme
  - Yinelenen iş mantığı oluşturma
  - Olanı baştan yazma
  - AGENTS.md / CLAUDE.md / .ai/ dosyalarını yeniden okuma
```

---

## ═══ 7. GELİŞTİRME KONTRATI ═══

### Laravel Kuralları (Zorunlu)
```php
// ✅ DOĞRU — FormRequest zorunlu
public function store(StoreOrderRequest $request): RedirectResponse { }

// ❌ YANLIŞ — inline validation yasak
$request->validate([...]);

// ✅ DOĞRU — Eloquent ilişki + eager loading
Order::with(['customer', 'shipments'])->paginate(25);

// ❌ YANLIŞ — DB:: facade yasak
DB::table('orders')->get();

// ✅ DOĞRU — Constructor property promotion
public function __construct(private readonly OrderService $orderService) { }

// ✅ DOĞRU — Explicit return types zorunlu
public function getActiveOrders(): Collection { }
```

### Kalite Kontrol
```bash
# Her PHP değişikliği sonrası ZORUNLU:
vendor/bin/pint --dirty

# Her değişiklik için Pest test:
php artisan test --compact --filter=ModulAdı
```

---

## ═══ 8. GIT COMMIT KURALLARI ═══

### Format (Zorunlu)
```
<prefix>: <kısa açıklama — max 72 karakter, imperative>

- <değişiklik 1 — ne yapıldı, neden>
- <değişiklik 2>
- <değişiklik 3>

Test: php artisan test --compact --filter=<TestClass>

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

### Prefix'ler
| Prefix       | Kullanım                                      |
|--------------|-----------------------------------------------|
| `feat:`      | Yeni özellik                                  |
| `fix:`       | Bug düzeltme                                  |
| `refactor:`  | Davranış değiştirmeden kod iyileştirme        |
| `test:`      | Test ekleme / güncelleme                      |
| `docs:`      | Sadece dokümantasyon değişikliği              |
| `perf:`      | Performans iyileştirmesi                      |
| `chore:`     | Build, config, bağımlılık güncellemesi        |

### Örnek Commit
```
feat: Analytics dashboard Chart.js entegrasyonu tamamlandı

- AnalyticsDashboardService finansal metrik metodları refactor edildi
- analytics-charts.js modüler yapıda oluşturuldu, charts.js güncellendi
- finance/fleet/operations blade view'larına Chart.js entegre edildi
- AnalyticsTest feature testi eklendi (8 test case)
- Vite config'e analytics-charts.js entry point eklendi

Test: php artisan test --compact --filter=AnalyticsTest

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

### Commit Şablonu Kurulumu
```bash
# Tek seferlik kurulum (proje kökünde çalıştır):
git config commit.template .gitmessage
```

> `.gitmessage` dosyası proje kökünde mevcut — `git commit` açıldığında otomatik yüklenir.

---

## ═══ 9. KORUNAN ALANLAR ═══

```
🚫 İNSAN ONAYI OLMADAN DEĞİŞTİRİLEMEZ:
  - app/Core/                        (MultiTenant Scope, CompanyScope)
  - app/Http/Middleware/ActiveCompany.php
  - SAP Authentication katmanı
  - AGENTS.md, CLAUDE.md
  - .ai/boost/, .ai/boost-main/
  - bootstrap/app.php (middleware konfigürasyonu)
  - Company Switch Logic (CompanyController@switch)

⚠️  DİKKATLİ OL:
  - database/migrations/             (mevcut migration'lara dokunma)
  - routes/admin.php                 (middleware gruplarını değiştirme)
  - app/Models/                      (mevcut cast/scope'ları koru)
```

---

## ═══ 10. AI KARAR MODELİ ═══

```
DURUM                        → EYLEM
─────────────────────────────────────────────────────
Modül == AKTİF               → Özelliği tamamla, test yaz
Test eksik (YÜKSEK öncelik)  → Pest feature test oluştur
N+1 tespit edildi            → with() ekle, eager loading
Controller > 200 satır       → Service'e taşı
Servis > 50 satır metod      → Private metodlara böl
Duplicate logic              → Refactor (rewrite değil)
Tip bildirimi eksik          → Ekle (return type + param)
Belirsiz requirement         → Minimum 1 soru sor, devam
─────────────────────────────────────────────────────
```

---

## ═══ 11. UZUN VADELİ HEDEF ═══

```
Logistics Intelligence Platform:

✔ Analytics Dashboard        → Chart.js + gerçek zamanlı KPI'lar
✔ Otonom faturalama          → EInvoice + Logo ERP + GIB
⏳ Tahminsel filo bakımı      → AIFleetService completion
⏳ Finansal anomali tespiti   → AIFinanceService advanced
⏳ Lojistik optimizasyon AI   → Route + load optimization
⏳ Mobile App                 → Flutter/RN (API hazır)
⏳ Real-time GPS              → WebSocket + pusher
```

---

## ═══ 12. SESSION KOMUTU ═══

> Cursor Chat'e bu dosyayı sürükle, ardından yaz:

```
/session
```

**AI şunları yapacak:**
1. SESSION BELLEĞİ'ni okur → aktif entity'yi belirler
2. İlgili domain klasörünü tarar (minimal)
3. Sonraki görevi üretir
4. Açıklama tekrar etmeden implementasyona geçer

---

*Bu dosyayı her önemli özellik tamamlandığında güncelle — özellikle bölüm 4 (SESSION BELLEĞİ) ve bölüm 3 (Modül Matrisi).*
