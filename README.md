## Session State

**Status:** Active — Delivery import pipeline (pivot, invoice status, petrokok route)
**Last work:** Invoice status tracking + petrokok route preference on DeliveryImportBatch
**Session tracker:** [`.ai/session.md`](.ai/session.md) | **Architecture map:** [`.ai/project-map.md`](.ai/project-map.md)

---

# Logistics ERP + CRM + Filo Yönetim Sistemi

PHP 8.2, Laravel 12, Bootstrap 5 ve Pest ile geliştirilmiş kurumsal lojistik yönetim platformu.

## Özellikler

- **Çok Şirketli (Multi-Tenant)** — Şirket bazlı veri izolasyonu ve aktif şirket geçişi
- **Sipariş Yönetimi** — Sipariş oluşturma, şablonlar, Excel import/export
- **Teslimat Otomasyonu** — Excel'den teslimat aktarımı, pivot raporlar, fatura satırları
- **Filo Yönetimi** — Araç takibi, muayene, hasar raporları, yakıt fiyat takibi
- **İnsan Kaynakları** — Personel, izin, avans, bordro, puantaj
- **Depo Yönetimi** — Barkod okuma, stok giriş/çıkış, envanter
- **Müşteri Portalı** — Self-servis sipariş, belge ve ödeme takibi
- **Şoför Mobil API v1 & v2** — Gerçek zamanlı gönderi takibi, POD yükleme, konum güncellemesi, dashboard
- **Finans** — Ödeme takibi, bordro yönetimi, dashboard analitik
- **Advanced Analytics Dashboard** — Finansal metrikler, operasyonel KPI'lar, filo performansı, Chart.js görselleştirme
- **AI Entegrasyonu** — Finans ve operasyon analiz raporları, HR performans analizi, filo bakım tahmini
- **SAP Integration** — OData servis entegrasyonu, CDS View consumption, Event Mesh, document flow tracking
- **E-Fatura/E-Arşiv** — UBL-TR XML generation, GIB entegrasyonu, otomatik fatura gönderimi
- **Real-Time Features** — WebSocket (laravel-websockets), push notifications, live dashboard updates
- **LOGO ERP Integration** — Fatura export, müşteri senkronizasyonu, muhasebe verileri
- **Denetim İzi** — Tüm değişiklikler için tam audit log
- **Bildirim Paneli** — Sistem geneli bildirim yönetimi, push notifications
- **Security & Compliance** — KVKK/GDPR uyumluluğu, veri şifreleme, güvenlik header'ları

## Teknoloji Yığını

| Katman | Teknoloji |
|--------|-----------|
| Backend | PHP 8.2.12, Laravel 12 |
| Frontend | Bootstrap 5.3, Tailwind CSS v4 (utility), Vite |
| Test | Pest v3, PHPUnit v11 |
| Kod Kalitesi | Laravel Pint v1, Rector |
| Veritabanı | MS SQL Server |
| Kuyruk | Laravel Queue (Redis) |
| Excel | PHPOffice/PhpSpreadsheet |
| Audit | Spatie Laravel Activity Log |
| AI Geliştirme | Laravel Boost (MCP) |

## Kurulum

```bash
# Bağımlılıkları yükle
composer install
npm install

# Ortam dosyasını oluştur
cp .env.example .env
php artisan key:generate

# Veritabanını hazırla
php artisan migrate --seed

# Frontend asset'leri derle
npm run build

# Sunucuyu başlat
composer run dev

# WebSocket sunucusu (opsiyonel, ayrı terminal)
php artisan websockets:serve
```

### Entegrasyon Ayarları

#### SAP OData Entegrasyonu

`.env` dosyasında SAP bağlantı bilgilerini yapılandırın:

```env
SAP_ODATA_URL=https://your-sap-system:port/sap/opu/odata/sap
SAP_USERNAME=your_username
SAP_PASSWORD=your_password
SAP_SYNC_ENABLED=true
SAP_AUTO_SYNC=false
```

#### E-Fatura/GIB Entegrasyonu

`.env` dosyasında GIB bağlantı bilgilerini yapılandırın:

```env
EINVOICE_ENABLED=true
EINVOICE_GIB_URL=https://efaturatest.gbonline.com.tr/services
EINVOICE_GIB_USERNAME=your_username
EINVOICE_GIB_PASSWORD=your_password
EINVOICE_AUTO_GENERATE=false
EINVOICE_AUTO_SEND=false
```

#### WebSocket & Real-Time Features

`.env` dosyasında broadcasting ayarlarını yapılandırın:

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=logistics-app
PUSHER_APP_KEY=logistics-app-key
PUSHER_APP_SECRET=logistics-app-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
```

#### LOGO ERP Entegrasyonu

`.env` dosyasında LOGO API bilgilerini yapılandırın:

```env
LOGO_API_URL=https://your-logo-api-url
LOGO_API_TOKEN=your_api_token
LOGO_AUTO_SYNC=false
```

## Geliştirme

```bash
# Dev sunucusu (PHP + Vite)
composer run dev

# Testleri çalıştır
php artisan test --compact

# Belirli bir testi filtrele
php artisan test --compact --filter=OrderTest

# Kod formatını düzelt
vendor/bin/pint --dirty
```

## Uygulama Yapısı

Uygulama domain-driven, modüler bir mimari kullanır:

```
app/
├── AI/              # Yapay zeka analiz servisleri (HR, Fleet, Document)
├── Analytics/       # Advanced analytics dashboard servisleri
├── BusinessPartner/ # SAP Business Partner yönetimi
├── Customer/        # Müşteri yönetimi ve portal
├── Delivery/        # Teslimat ve Excel import
├── Document/        # Belge yönetimi
├── DocumentFlow/    # SAP document flow tracking
├── Driver/          # Şoför mobil API (v1 & v2)
├── EInvoice/        # E-Fatura/E-Arşiv modülü (XML, GIB)
├── Employee/        # İK: personel, izin, avans, bordro
├── Events/          # Broadcast event'leri (WebSocket)
├── Excel/           # Excel işleme servisleri
├── Finance/         # Finans ve ödeme
├── FuelPrice/       # Yakıt fiyat takibi
├── Integration/     # LOGO ve Python entegrasyonu
├── Jobs/            # Queue job'ları (SAP, E-Invoice)
├── Notification/    # Bildirim sistemi
├── Notifications/   # Notification class'ları (Push)
├── Order/           # Sipariş yönetimi
├── Pricing/         # Dinamik fiyatlandırma
├── Sap/             # SAP OData, CDS, Event Mesh entegrasyonu
├── Shipment/        # Gönderi takibi
├── Shift/           # Vardiya planlama
├── Vehicle/         # Araç ve filo yönetimi
├── Warehouse/       # Depo ve stok
├── WorkOrder/       # İş emirleri
├── Core/            # Paylaşılan altyapı (middleware, scope, servisler)
└── Http/            # HTTP katmanı (Admin ve Auth controller'lar)
```

## Route Grupları

| Dosya | Prefix | Açıklama |
|-------|--------|----------|
| `routes/web.php` | `/` | Genel ve auth rotaları |
| `routes/admin.php` | `/admin` | Yönetim paneli |
| `routes/api.php` | `/api/v1` | REST API (Sanctum) |
| `routes/customer.php` | `/customer` | Müşteri portalı |
| `routes/console.php` | — | Artisan zamanlayıcılar |

## Roller

| Rol | Erişim |
|-----|--------|
| Admin | Tam yönetim paneli erişimi |
| Operasyon | Sipariş, teslimat, araç, depo |
| Muhasebe | Finans, bordro, ödemeler |
| Şoför | Mobil API — gönderi ve konum |
| Müşteri | Self-servis portal |

## Dokümantasyon

Detaylı teknik dokümantasyon `docs/` klasöründe bulunur:

- [Proje Genel Bakış](docs/01-project-overview.md)
- [Veritabanı Şeması](docs/02-database-schema.md)
- [Geliştirme Rehberi](docs/03-development-guide.md)
- [Modül Dokümantasyonu](docs/04-modules-documentation.md)
- [UX ve Sayfa Akışı](docs/05-ux-page-flow.md)
- [Şirket Ayarları ve Geçiş](docs/06-company-settings-and-switch.md)
- [Hizmet Sözleşmesi](docs/07-service-agreement.md)

## Lisans

Bu proje özel yazılımdır. Tüm haklar saklıdır.
