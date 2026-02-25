# Logistics Project – Geliştirme Rehberi

**Amaç:** Laravel modül yapısı, geliştirme best practices, TODO listesi ve teknik detaylar

---

## LARAVEL MODÜL YAPISI (DOMAIN-ORIENTED)

### Genel Yapı
```
app/
├── Core/
│   ├── Auth/
│   ├── Roles/
│   ├── Permissions/
│   ├── Audit/
│   ├── Notifications/
│   └── Helpers/
├── Company/
├── User/
├── Customer/
├── Employee/
├── Vehicle/
├── Order/
├── Finance/
├── Document/
├── AI/
└── Shared/
```

### Controller Ayrımı
- **Web Controllers:** `app/{Module}/Controllers/Web/{Module}Controller.php` → Blade view'ları render eder
- **API Controllers:** `app/{Module}/Controllers/Api/{Module}Controller.php` → JSON response döner

### Service Katmanı
**Prensip:** Controller sadece request alır, tüm iş mantığı Service içindedir.

```php
app/Order/Services/OrderService.php

class OrderService
{
    public function create(array $data): Order
    {
        // İş kuralları burada
    }
    
    public function update(Order $order, array $data): Order
    {
        // İş kuralları burada
    }
}
```

### Job & Queue Kullanımı
**Kullanım Alanları:**
- Mail gönderimi
- SMS / WhatsApp
- PDF oluşturma
- AI analizleri
- Hatırlatma kontrolleri

**Job Yapısı:**
```
app/{Module}/Jobs/{JobName}Job.php
```

### Policy & Yetkilendirme
**Her ana modül için Policy:**
```
app/{Module}/Policies/{Module}Policy.php
```

**Cache Destekli Yetki Doğrulama:**
- Yetkiler Redis'te cache'lenir
- Permission kontrolü hızlı yapılır

---

## ROUTE YAPISI

```
routes/
├── web.php          # Genel web routes
├── api.php          # API routes (v1)
├── admin.php        # Yönetim paneli routes
└── customer.php     # Müşteri portalı routes
```

**Route Grupları:**
- `admin.php` → Yönetim paneli (middleware: auth, role:admin)
- `customer.php` → Müşteri portalı (middleware: auth, role:customer)
- `api.php` → API endpoints (middleware: auth:sanctum)

---

## VIEW (BLADE) ORGANİZASYONU

```
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── admin.blade.php
│   └── customer.blade.php
├── components/
│   ├── alert.blade.php
│   ├── modal.blade.php
│   └── table.blade.php
├── admin/
│   ├── dashboard/
│   ├── orders/
│   ├── vehicles/
│   └── employees/
└── customer/
    ├── dashboard/
    ├── orders/
    └── documents/
```

### Frontend Mimarisi

#### Blade Bileşenleri
- **Layout bileşenleri:** Reusable layout yapıları
- **Reusable UI components:** Slot tabanlı yapı
- **Kullanım:** Hangi bileşenlerin ne zaman ve nerede kullanılacağı (`<x-forms.input>`, `<x-layout.app>`)
- **Prop'lar:** Gerekli/isteğe bağlı prop'ları ve tiplerini belirtin (PHPDoc)
- **Slot'lar:** İsimli slot'ları ve amaçlarını açıklayın

#### JavaScript Modülleri (ESM)
- **Dizin Yapısı:** `resources/js/Modules/`, `resources/js/Components/` gibi ayrım
- **SPA YOK:** Sayfa bazlı modüller, Single Page Application değil
- **ES Modules:** Modern JavaScript modül sistemi kullanılır

#### Tailwind CSS Entegrasyonu
- **Yapılandırma:** `tailwind.config.js` içinde özel renkler, yazı tipleri, eklentiler
- **Özel sınıflar:** Özel kart gölgeleri, kurumsal tipografi
- **Utility-first yaklaşım:** Tailwind utility sınıfları kullanılır
- **Özel renk paleti:** Kurumsal renkler tanımlanır

---

## API & MOBILE UYUMLULUK

### Token Bazlı Auth
- Laravel Sanctum kullanılır
- API token'ları veritabanında saklanır

### Rate Limit
```php
Route::middleware(['throttle:api'])->group(function () {
    // API routes
});
```

### Versioning
```
/api/v1/orders
/api/v1/vehicles
```

---

## PERFORMANS PRENSİPLERİ

### Cache Stratejisi
- **Yetkiler:** Redis cache
- **Sabit veriler:** Cache facade
- **Sayfa cache:** Blade component cache

### Lazy Loading Yasak
- Eager loading kullanılır
- `with()` method'u ile ilişkiler yüklenir

### Heavy İşlemler Job
- PDF oluşturma → Queue
- Email gönderimi → Queue
- AI analizi → Queue

### DB View & Index Kullanımı
- Sık kullanılan sorgular için view
- Kritik kolonlar için index
- Stored procedure (kritik raporlar)

---

## CRONJOB & OTOMASYON

### Zamanlama Stratejisi
- **Cronjob:** Zamanı gelen işleri **TETİKLER**
- **Queue (Job):** Ağır işleri **ÇALIŞTIRIR**
- **Kullanıcı isteği asla beklemez**

### Zamanlama
- **Saatlik:** Kritik kontroller
- **Günlük:** 00:05 (gece yarısı sonrası)
- **Haftalık:** Pazar gecesi
- **Aylık:** Ayın son günü

### Laravel 12 Schedule Sistemi
```php
// routes/console.php

Schedule::command('documents:check-expiry')
    ->dailyAt('00:05')
    ->withoutOverlapping();

Schedule::command('payments:check-due')
    ->dailyAt('00:05')
    ->withoutOverlapping();

Schedule::command('orders:check-delivery')
    ->hourly()
    ->withoutOverlapping();
```

---

## LOGISTICS ORDER–PAYMENT–SHIPMENT FLOW (SERVICE & EVENT)

Logistics B2B sürecinde controller'lar sadece istek alır; asıl iş mantığı service + event zincirindedir:

1. **Order Creation**
   - `OrderController@store` → `OrderService::create()` → `OrderCreated` event.
   - Listener/Action: `CreatePaymentIntentAction` → ilgili `PaymentIntent` kaydı oluşturulur.
2. **Payment Approval**
   - Payment gateway callback veya cari ödeme: `PaymentGatewayCallbackController@handle` → `PaymentService::approve()`.
   - Başarılı ise `PaymentApproved` event yayınlanır → `CreateShipmentPlanAction` çalışır ve `ShipmentPlan` oluşturulur.
3. **Shipment & Delivery**
   - Operasyon `ShipmentService` üzerinden planı gerçek `Shipment` kaydına dönüştürür ve `ShipmentStarted` event tetiklenir.
   - Teslim sonrası `ShipmentService::markDelivered()` → `ShipmentDelivered` event yayınlanır.
4. **Invoice & Accounting**
   - `ShipmentDelivered` listener'ı `GenerateInvoiceAction`'ı çağırır → `InvoiceGenerated` event.
   - `InvoiceGenerated` listener'ı `CreateAccountTransactionAction` ile cari hareket (`AccountTransaction`) üretir.

Bu akışın kuralı:

- Domain'ler (Order, Payment, Shipment, Invoice, Account) **birbirini doğrudan update etmez**, sadece event üzerinden haberleşir.
- Yeni özellik eklerken bu zinciri bozmak yerine yeni listener/action eklemek tercih edilmelidir.

### Job Örnekleri
- `SendEmailJob`
- `SendSmsJob`
- `SendWhatsappJob`
- `GeneratePdfJob`
- `RunAIAnalysisJob`
- `CheckDocumentExpiryJob`
- `CheckPaymentDueJob`

---

## AI MODÜLÜ YAPISI

### Domain Bazlı AI Servisleri
```
AI/
├── Services/
│   ├── AIService.php (Base)
│   ├── AIOperationsService.php
│   ├── AIFinanceService.php
│   ├── AIHRService.php
│   ├── AIFleetService.php
│   └── AIDocumentService.php
└── Jobs/
    └── RunAIAnalysisJob.php
```

### AI Çalışma Modeli
- **Cronjob:** Günlük (09:00)
- **Manuel:** Yönetici isterse (admin panelden)
- **Async & rapor bazlı** çalışır
- Operasyonel akışı **DURDURMAZ**

### AI Sonuçları
- `ai_reports` tablosuna kaydedilir
- Dashboard'a eklenir
- Severity: low, medium, high

---

## MASTER TODO LİSTESİ

> **Not:** Bu liste referans amaçlıdır. Güncel proje durumu ve tamamlanan görevler için [docs/ROADMAP.md](../ROADMAP.md) dosyasına bakın. Proje çekirdek modüller için production ready durumdadır; ileri seviye AI, Analytics ve entegrasyonlar Faz 2/Faz 3 backlog'unda yer alır.

### 1. PROJE TEMEL KURULUMU
- [ ] Laravel 12 kurulumu ve yapılandırması
- [ ] MSSQL bağlantı yapılandırması
- [ ] Git repository kurulumu
- [ ] Environment dosyaları (.env) yapılandırması
- [ ] Composer ve NPM bağımlılıkları kurulumu
- [ ] Vite yapılandırması (Tailwind CSS v4)
- [ ] Laravel Pint yapılandırması
- [ ] Pest test framework kurulumu
- [ ] Redis/Queue yapılandırması

### 2. KULLANICI, ROL VE YETKİ YÖNETİMİ
- [ ] User model ve migration
- [ ] Role model ve migration
- [ ] Permission model ve migration
- [ ] Authentication controller
- [ ] Role middleware
- [ ] Permission middleware
- [ ] Yetki cache mekanizması
- [ ] Audit log sistemi

### 3. ŞİRKET & ORGANİZASYON YAPISI
- [ ] Company model ve migration
- [ ] Branch model ve migration
- [ ] Department model ve migration
- [ ] Position model ve migration
- [ ] Şirket yönetim CRUD

### 4. DASHBOARD & ANASAYFA
- [ ] Dashboard layout tasarımı
- [ ] Günlük operasyon widget'ları
- [ ] AI destekli uyarı kutusu
- [ ] Grafikler (Günlük/Aylık/Yıllık)
- [ ] Real-time güncellemeler

### 5. SİPARİŞ & OPERASYON YÖNETİMİ
- [ ] Order model ve migration
- [ ] Sipariş oluşturma formu
- [ ] Sipariş listesi (pagination)
- [ ] Sipariş durum güncelleme
- [ ] SLA takibi
- [ ] Müşteriye otomatik bildirim

### 6. ARAÇ & FİLO YÖNETİMİ
- [ ] Vehicle model ve migration
- [ ] Araç tanımlama formu
- [ ] Bakım & muayene takibi
- [ ] Yakıt tüketimi kayıtları
- [ ] KM takibi
- [ ] Araç maliyet analizi

### 7. PERSONEL & İK YÖNETİMİ
- [ ] Employee model ve migration
- [ ] Personel kartı oluşturma
- [ ] Kimlik & evrak takibi
- [ ] Puantaj sistemi
- [ ] Vardiya yönetimi
- [ ] İzin yönetimi

### 8. BELGE & DÖKÜMAN YÖNETİMİ
- [ ] Document model ve migration (polymorphic)
- [ ] Belge yükleme (multi-upload)
- [ ] Belge versiyonlama
- [ ] Belge hatırlatma sistemi

### 9. ÖDEME TAKVİMİ & FİNANS
- [ ] Payment model ve migration
- [ ] Ödeme takvimi sayfası
- [ ] Ödeme hatırlatma sistemi
- [ ] Finansal raporlama

### 10. DEPO & STOK YÖNETİMİ
- [ ] Warehouse model ve migration
- [ ] WarehouseLocation model ve migration
- [ ] InventoryItem model ve migration
- [ ] Stok giriş/çıkış işlemleri
- [ ] Stok transfer fişleri
- [ ] Kritik stok uyarıları
- [ ] Barkod sistemi

### 11. MOTORİN FİYAT TAKİP MODÜLÜ
- [ ] FuelPrice model ve migration
- [ ] Günlük fiyat kaydı
- [ ] Fiyat karşılaştırma sistemi
- [ ] Haftalık Excel raporu
- [ ] Aylık grafik görselleştirme

### 12. TESLİMAT NUMARASI OTOMASYONU
- [ ] DeliveryNumber model ve migration
- [ ] Excel'den teslimat numarası yükleme
- [ ] Otomatik lokasyon eşleştirme
- [ ] Otomatik sipariş oluşturma
- [ ] Eşleşmeyen teslimatlar yönetimi

### 13. VARDIYA YÖNETİMİ
- [ ] ShiftTemplate model ve migration
- [ ] ShiftSchedule model ve migration
- [ ] Vardiya şablonları
- [ ] Haftalık vardiya planlama
- [ ] Vardiya-puantaj entegrasyonu

### 14. MOBİL SAHA ÖZELLİKLERİ
- [ ] Sürücü dashboard (mobil)
- [ ] POD (Teslimat kanıtı) yükleme
- [ ] Mobil barkod okutma
- [ ] GPS konum takibi
- [ ] Offline çalışma

### 15. ARAÇ EKSPERTİZ VE HASAR SİSTEMİ
- [ ] VehicleInspection model ve migration
- [ ] VehicleDamage model ve migration
- [ ] Ekspertiz kayıt sistemi
- [ ] Hasar tespiti ve kayıt sistemi
- [ ] Dijital hasar çizimi
- [ ] PDF export

### 16. İŞ EMİRLERİ VE BAKIM YÖNETİMİ
- [ ] WorkOrder model ve migration
- [ ] İş emri oluşturma ve yönetimi
- [ ] Bakım kalemleri yönetimi
- [ ] Servis sağlayıcıları yönetimi
- [ ] Bakım parçaları stok takibi

### 17. HİYERARŞİK LOKASYON YÖNETİMİ
- [ ] Country model ve migration
- [ ] City model ve migration
- [ ] District model ve migration
- [ ] Neighborhood model ve migration
- [ ] Lokasyon API servisleri

### 18. AKTİVİTE İZLEME VE RAPORLAMA
- [ ] ActivityLog model ve migration
- [ ] LogsActivity trait
- [ ] Model bazlı değişiklik izleme
- [ ] Aktivite log raporlama

### 19. BİLDİRİM YÖNETİM PANELİ
- [ ] Notification model ve migration
- [ ] Bildirim yönetim paneli (web)
- [ ] Otomatik mail bildirimleri
- [ ] Cronjob durumu takibi

---

## GÜNCEL BACKLOG (2026-02-25 — Kısa Sprint Listesi)

Bu bölüm, ROADMAP ve `.ai/session.md` ile uyumlu olarak **yakın vadeli geliştirme işleri** için kısa bir referans sağlar.

### Delivery Import & Pivot
- [ ] `DeliveryReportPivotService` için edge-case senaryolarını (farklı rapor tipleri, eksik/bozuk header'lar, null alanlar) kapsayan ek Pest testleri yaz.
- [ ] Fatura kalemi üretiminde (buildInvoiceLines) gruplanmış kalemler için validasyon ve hata raporlama mekanizmasını güçlendir.
- [ ] Pivot ve fatura kalemi çıktılarının Excel/CSV export akışını tamamla ve Delivery rapor detay ekranına entegre et.

### AI & Analytics
- [ ] `AnalyticsDashboardService` içinde fleet ve operations metriklerini genişlet (ör. utilization, on-time delivery, breakdown oranları).
- [ ] Analytics dashboard için mevcut Chart.js grafiklerine ek olarak seçilen 1–2 yeni grafik türü (ör. stacked bar, line+bar kombinasyonu) ekle.
- [ ] `AIFleetService`, `AIFinanceService` ve `AIDocumentService` için en az birer basit anomaly detection kuralı tanımla ve sonuçları `ai_reports` üzerinden raporla.

### PythonBridge & Queue
- [ ] `PythonBridgeService` için minimal bir POC akışı tanımla: hangi veri seti (ör. weekly fuel price + shipments), hangi JSON formatında dış sisteme gönderilecek.
- [ ] AI, Logo, Excel ve notification job'larının queue ve schedule yapılarını gözden geçirerek, “kritik” ve “non-kritik” job'ları ayıran kısa bir plan çıkar.

---

## TESTING

### Pest Test Framework
- Tüm testler Pest ile yazılır
- Feature testler önceliklidir
- Unit testler kritik iş mantığı için

### Test Çalıştırma
```bash
# Tüm testler
php artisan test

# Belirli bir test dosyası
php artisan test tests/Feature/ExampleTest.php

# Filtre ile
php artisan test --filter=testName
```

---

## CODE STYLE

### Laravel Pint
- Kod formatlaması için Laravel Pint kullanılır
- `vendor/bin/pint --dirty` çalıştırılmalı

### PHP Standartları
- PHP 8 constructor property promotion kullanılır
- Explicit return type declarations
- PHPDoc blocks tercih edilir
- Enum keys TitleCase

---

## GÜVENLİK

### Authentication
- Laravel Sanctum (API)
- Laravel Breeze (Web)

### Authorization
- Policy-based authorization
- Role-based access control (RBAC)
- Permission-based fine-grained control

### Veri Güvenliği
- Input validation (Form Requests)
- SQL injection koruması (Eloquent)
- XSS koruması (Blade escaping)
- CSRF koruması

---

## SİSTEM MİMARİSİ KATMANLARI

### Katmanlı Mimari
1. **Presentation Layer (Blade):** View'lar, component'ler, UI
2. **Application Layer (Services):** İş mantığı, business rules
3. **Domain Layer (Business Logic):** Domain modelleri, entity'ler
4. **Infrastructure Layer (DB, Excel, API):** Veritabanı, external API'ler, Excel işleme

### Temel Servisler
- `ExcelImportService` - Excel dosya işleme
- `PeriodCalculationService` - Haftalık/periyodik hesaplamalar
- `AnalysisService` - Veri analizi
- `BillingService` - Faturalandırma
- `IntegrationService` - External entegrasyonlar

---

## AI AGENT KURALLARI (CURSOR IDE)

### Genel Kurallar
- Kod yazmadan önce mevcut mimariyi oku
- Service dışına iş mantığı yazma
- Controller sade kalmalı

### Laravel Kuralları
- Her Excel işlemi Service + Job olmalı
- DB işlemleri Transaction içinde
- Magic number kullanma

### Dokümantasyon
- Her public method PHPDoc içerir
- Blade component prop'ları belgelenir
- Yeni modül eklenirse dokümantasyon güncellenir

### Yasaklar
- ❌ Controller içine SQL yazmak
- ❌ Blade içinde iş mantığı
- ❌ Tek dosyada çoklu sorumluluk

---

## EXCEL İŞLEME BEST PRACTICES

### Excel Import Stratejisi
- **Raw Tables:** Excel'den gelen ham veri `excel_raw_*` tablolarına birebir kaydedilir
- **Processed Tables:** Normalize edilmiş veriler `processed_*` tablolarına işlenir
- **Aggregated Views:** Analiz için view'lar kullanılır

### Excel İşleme Kuralları
- Excel verisi **asla doğrudan silinmez**
- İlk kayıt her zaman **RAW** tablolara alınır
- Hatalı satırlar ayrılır ve raporlanır
- Dosya türü doğrulama (XLSX, CSV)
- Header eşleşme kontrolü
- Satır bazlı validasyon
- Hatalı satır izolasyonu

### Veri Akışı
```
Excel Dosyası
    ↓
Raw Import (hiç dokunulmaz)
    ↓
Normalize Edilmiş Tablolar
    ↓
Haftalık İş Periyodu Hesaplama
    ↓
Analiz & Raporlama
    ↓
Faturalama Datası
```

---

**Not:** Bu rehber proje geliştirme sürecinde referans alınmalıdır. Güncellemeler yapıldıkça bu doküman da güncellenmelidir.
