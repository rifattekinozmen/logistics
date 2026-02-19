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
- **Şoför Mobil API** — Gerçek zamanlı gönderi takibi, POD yükleme, konum güncellemesi
- **Finans** — Ödeme takibi, bordro yönetimi, dashboard analitik
- **AI Entegrasyonu** — Finans ve operasyon analiz raporları
- **Dış Entegrasyonlar** — LOGO ve Python backend entegrasyonu
- **Denetim İzi** — Tüm değişiklikler için tam audit log
- **Bildirim Paneli** — Sistem geneli bildirim yönetimi

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
├── AI/            # Yapay zeka analiz servisleri
├── Customer/      # Müşteri yönetimi ve portal
├── Delivery/      # Teslimat ve Excel import
├── Document/      # Belge yönetimi
├── Driver/        # Şoför mobil API
├── Employee/      # İK: personel, izin, avans, bordro
├── Excel/         # Excel işleme servisleri
├── Finance/       # Finans ve ödeme
├── FuelPrice/     # Yakıt fiyat takibi
├── Integration/   # LOGO ve Python entegrasyonu
├── Notification/  # Bildirim sistemi
├── Order/         # Sipariş yönetimi
├── Shipment/      # Gönderi takibi
├── Shift/         # Vardiya planlama
├── Vehicle/       # Araç ve filo yönetimi
├── Warehouse/     # Depo ve stok
├── WorkOrder/     # İş emirleri
├── Core/          # Paylaşılan altyapı (middleware, scope, servisler)
└── Http/          # HTTP katmanı (Admin ve Auth controller'lar)
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
