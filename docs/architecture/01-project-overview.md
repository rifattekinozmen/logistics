# Logistics Project – Proje Genel Bakış

**Proje Adı:** Logistics ERP + CRM + Fleet Management  
**Versiyon:** 1.0  
**Tarih:** 2026

---

## PROJE TANIMI

Kurumsal ölçekli bir **Logistics ERP + CRM + Fleet Management** projesi. Nakliye sektöründe lojistik hizmetleri sağlayan şirketler için kapsamlı bir yönetim sistemi.

---

## TEKNOLOJİ STACK

### Backend
- **PHP:** 8.2.12
- **Laravel:** v12
- **Veritabanı:** MS SQL Server
- **Queue:** Redis
- **Cache:** Redis

### Frontend
- **Blade:** Template engine
- **Bootstrap:** CSS framework
- **Alpine.js:** Minimal JavaScript
- **Tailwind CSS:** v4
- **Vite:** Build tool

### Araçlar
- **Laravel Pint:** Code formatter
- **Pest:** Testing framework
- **PHPUnit:** v11
- **Git:** Version control
- **Node.js:** Frontend dependencies

---

## TEMEL ÖZELLİKLER

### Export/Import
- PDF export
- Excel export/import
- XML export (E-Fatura/E-Arşiv)

### AI Entegrasyonu
- AI yorum ve tavsiye sistemi
- Operasyon analizi
- Finansal risk uyarıları
- Personel verimlilik önerileri

### İletişim
- WhatsApp entegrasyonu
- Email bildirimleri
- SMS gönderimi
- Otomatik arama (ileri seviye)

### Otomasyon
- Cronjob yönetimi
- Queue sistemi
- Belge hatırlatmaları
- Ödeme hatırlatmaları

---

## MODÜL YAPISI

### 1. Sistem & Yönetim (Core)
- Kullanıcı yönetimi
- Rol yönetimi
- Yetki matrisi
- Şirket yönetimi
- Şube/Depo yönetimi
- Departmanlar
- Audit log

### 2. Dashboard & Anasayfa
- Günlük operasyon özeti
- Aktif sevkiyatlar
- Araç doluluk oranı
- Geciken teslimatlar
- Personel durumları
- Finans özetleri
- AI destekli uyarılar

### 3. Lojistik & Operasyon
- Sipariş yönetimi
- Sepet/Yük takibi
- SLA takibi
- Teslimat performans puanı

### 4. Araç & Filo Yönetimi
- Araç tanımları
- Bakım & muayene takibi
- Yakıt tüketimi
- KM takibi
- Arıza kayıtları
- Araç maliyet analizi
- Ekspertiz ve hasar sistemi

### 5. Personel & İK
- Personel yönetimi
- Kimlik & evrak takibi
- Puantaj tablosu
- İzin yönetimi
- Avans talepleri
- Maaş bordrosu
- SGK takibi
- Vardiya yönetimi

### 6. Müşteri Portalı
- Müşteri girişi
- Sipariş oluşturma
- Sipariş takibi
- Fatura görüntüleme
- Doküman indirme

### 7. Belge & Döküman Yönetimi
- Belge yükleme/görüntüleme/silme
- Belge versiyonlama
- Belge kategorileri
- Belge hatırlatma sistemi

### 8. Ödeme Takvimi & Finans
- Ödeme takvimi
- Ödeme hatırlatmaları
- Finansal raporlama
- Nakit akış analizi

### 9. Raporlama
- Excel export
- PDF export
- XML export
- Özel rapor oluşturucu

### 10. AI Yorum & Tavsiye
- Operasyon analizi
- Finansal risk uyarıları
- Personel verimlilik önerileri
- Araç bakım tahminleri

### 11. Depo & Stok Yönetimi
- Depo içi lokasyon hiyerarşisi
- Barkod ile stok takibi
- Stok transfer fişleri
- Minimum stok seviyesi uyarıları
- Seri/Lot takibi
- Fiziksel sayım

### 12. Motorin Fiyat Takibi
- Günlük fiyat kaydı
- Satın alma vs istasyon karşılaştırması
- Haftalık Excel raporu
- Aylık grafik görselleştirme

### 13. Teslimat Numarası Otomasyonu
- Excel'den toplu yükleme
- Otomatik lokasyon eşleştirme
- Otomatik sipariş oluşturma
- Eşleşmeyen teslimatlar yönetimi

### 14. Mobil Saha Özellikleri
- Sürücü sevkiyat takibi
- POD (Teslimat kanıtı) yükleme
- Mobil barkod okutma
- GPS konum takibi
- Offline çalışma

### 15. İş Emirleri ve Bakım Yönetimi
- Kapsamlı iş emri sistemi
- Bakım kalemleri yönetimi
- Servis sağlayıcıları yönetimi
- Bakım parçaları stok takibi

### 16. Hiyerarşik Lokasyon Yönetimi
- Ülke > Şehir > İlçe > Mahalle hiyerarşisi
- API tabanlı lokasyon servisleri
- Lokasyon arama ve filtreleme

### 17. Aktivite İzleme ve Raporlama
- Kapsamlı aktivite log sistemi
- Model bazlı değişiklik izleme
- Raporlama ve dışa aktarma

### 18. Bildirim Yönetim Paneli
- Web tabanlı bildirim paneli
- Otomatik mail sistemi
- Cronjob yönetimi
- Gerçek zamanlı istatistikler

### 19. Excel Otomasyon Modülü
- Excel dosyası yükleme (XLSX, CSV)
- Haftalık periyot tespiti
- Otomatik tarih hesaplama
- Veri doğrulama ve normalize etme
- Analiz tablosu oluşturma
- Faturalandırma datası üretme
 - ERP entegrasyonu

---

## LOGISTICS B2B ORDER LIFECYCLE

Lojistik projesinin kalbi, siparişten başlayıp faturaya ve cari hareketlere kadar uzanan B2B yaşam döngüsüdür. Yüksek seviye akış:

- **Customer creates order** → sistem `Order` kaydını ve temel yük bilgilerini oluşturur.
- **OrderCreated event** → ilgili `PaymentIntent` kaydı hazırlanır (online/cari/havale için ödeme niyeti).
- **PaymentApproved event** → operasyon için `ShipmentPlan` oluşturulur, araç ve nakliyeci planlanır.
- **ShipmentStarted / ShipmentDelivered events** → gerçek `Shipment` kaydı güncellenir, teslim tarihi ve POD dokümanları bağlanır.
- **InvoiceGenerated event** → sevkiyat bazlı `Invoice` üretilir, satırlar pivot/veri kaynaklarından doldurulur.
- **AccountTransactionCreated event** → müşterinin cari hesabına borç/alacak hareketleri işlenir (ledger mantığı).

Bu akışın görsel karşılığı aşağıdaki diyagramdır:

![Logistics B2B Order Lifecycle](../../.cursor/projects/c-Users-TekinOzmen-Desktop-logistics/assets/c__Users_TekinOzmen_AppData_Roaming_Cursor_User_workspaceStorage_8f265710ef1535467a1e80695d50a57e_images_image-83361f87-da96-44e2-b5c6-f33047cff303.png)

Her renkli blok, sistemdeki bir domain ve event zincirini temsil eder:

- **OrderCreated** → Order domain
- **PaymentApproved** → Payment/Finance domain
- **Create Shipment Plan / ShipmentStarted / ShipmentDelivered** → Shipment domain
- **Generate Invoice / InvoiceGenerated** → Invoice & Account domain

Bu lifecycle, yeni özellik geliştirilirken **Order → PaymentIntent → Payment → ShipmentPlan → Shipment → Invoice → AccountTransaction** sırasının bozulmaması için referans alınmalıdır.

**Durum:** Çekirdek B2B lifecycle production ortamı için tamamlanmış kabul edilir; Analytics & AI tarafındaki ileri seviye metrikler, anomaly detection ve optimizasyon özellikleri Faz 2 / Faz 3 backlog'unda planlanmıştır (detay için `docs/ROADMAP.md`).

---

## MİMARİ YAKLAŞIM

### API-First
- Web ve mobile aynı API'yi kullanır
- Token bazlı authentication
- Versioning (/api/v1)

### Modüler Yapı
- Domain-oriented architecture
- Her modül kendi klasöründe
- Service katmanı (iş kuralları)

### Performans Odaklı
- Eager loading (N+1 önleme)
- Cache stratejisi
- Queue kullanımı
- Database index planlaması

### Mobile Ready
- API-first yaklaşım
- Responsive tasarım
- Touch-friendly UI

---

## LOGISTICS B2B ROLES & FLOW RESPONSIBILITIES

Bu B2B lifecycle içinde rollerin sorumlulukları:

- **Admin:** Tüm domainlerde tam yetkili; ayarlar, kullanıcı ve şirket yönetimi.
- **Operasyon:** `Order`, `ShipmentPlan` ve `Shipment` üzerinde çalışır; araç/şoför atama ve sevkiyat yönetimi.
- **Muhasebe / Finance:** `PaymentIntent`, `Payment`, `Invoice` ve `AccountTransaction` süreçlerinden sorumludur.
- **Müşteri (Customer Portal):** Kendi `Order` kayıtlarını oluşturur ve takip eder; fatura ve dokümanlara erişir.
- **Şoför / Driver (Mobil):** Kendisine atanan `Shipment` kayıtlarını görür, yükleme/teslim durumlarını günceller ve POD yükler.

---

## ROL YÖNETİMİ

### Roller
- **Admin:** Tam yetki
- **Operasyon:** Sipariş ve sevkiyat yönetimi
- **Muhasebe:** Finans ve ödeme yönetimi
- **Şoför:** Sipariş takibi ve güncelleme
- **Müşteri:** Sipariş oluşturma ve takip

### Yetkilendirme
- Sayfa bazlı yetkiler
- Aksiyon bazlı yetkiler
- Cache destekli yetki kontrolü

---

## SECURITY & RBAC ARCHITECTURE

Logistics B2B sisteminde güvenlik mimarisi üç katmandan oluşur:

- **Roles & Permissions:**
  - Roller: `admin`, `operation`, `accounting`, `driver`, `customer` (ve gerektiğinde şirket özel rolleri).
  - Permission kodları: `order.create`, `order.view`, `payment.approve`, `shipment.assign`, `invoice.generate`, `account.view` vb.
  - Blade tarafında menü ve aksiyonlar `@can('permission-code')` ile filtrelenir; böylece sidebar bile RBAC ile korunur.
- **Multi-tenant veri izolasyonu:**
  - `CompanyScope` / benzeri global scope ile kullanıcıya ait `company_id`/`customer_id` otomatik olarak tüm sorgulara eklenir.
  - B2B müşteri tarafında, başka müşterinin `orders` / `documents` / `invoices` kayıtlarına erişim URL manipülasyonu ile mümkün olmayacak şekilde kısıtlanır.
- **ID & public code kullanımı:**
  - Dış dünyaya açık URL'lerde mümkün olduğunca auto-increment ID yerine public code/UUID kullanılır; ID enumeration saldırıları minimize edilir.

---

## API & PAYMENT SECURITY

Ödeme ve API güvenliği için temel prensipler:

- **API Authentication:**
  - Laravel Sanctum ile token bazlı auth; her token belirli bir kullanıcıya ve isteğe bağlı IP aralığına bağlıdır.
  - API grupları `throttle` middleware ile rate limit altına alınır (özellikle login, ödeme ve kritik finans endpoint'leri).
- **Payment Gateway Callback:**
  - Tüm ödeme callback'lerinde HMAC imza kontrolü yapılır (ör. `hash_hmac('sha256', $payload, config('payment.secret'))`).
  - `transaction_id` bazlı duplicate kontrol ile aynı ödeme callback'i ikinci kez işlense bile finansal kayıtlar tekrar oluşturulmaz.
  - Callback sonrası sadece `PaymentService` uygun event'i (`PaymentApproved` / `PaymentFailed`) yayınlar; diğer domain'ler event dinleyicisi olarak tepki verir.
-- **Data Protection:**
  - Kart numarası, CVV vb. hassas bilgiler sistemde tutulmaz; sadece gateway tarafındaki referans/transaction ID saklanır.
  - Vergi numarası gibi hassas alanlar gerekirse Laravel encrypt cast ile şifrelenerek saklanır.

**Regülasyon bağı:** Bu bölüm uygulama tarafındaki teknik güvenlik prensiplerini özetler; TCMB ödeme hizmetleri ve B2B mimarisiyle ilgili yasal/iş gereksinimleri için `docs/compliance/tcmb_pay/` altındaki rehberler (v2, v3, rehber) referans alınmalıdır.

---

## GELİŞTİRME YOL HARİTASI

### Faz 1: Web (Mevcut)
- Tüm modüller web arayüzünde
- Blade + Bootstrap + Alpine.js
- Responsive tasarım

### Faz 2: Mobile (Gelecek)
- Flutter veya React Native
- Aynı API kullanımı
- Push notifications

---

## DOKÜMANTASYON

Bu proje için oluşturulan dokümanlar:

1. **01-project-overview.md** - Genel bakış (bu dosya)
2. **02-database-schema.md** - Veritabanı şeması
3. **03-development-guide.md** - Geliştirme rehberi ve TODO listesi
4. **04-modules-documentation.md** - Modül detayları
5. **05-ux-page-flow.md** - Sayfa akışı ve UX

---

**Not:** Bu doküman projenin genel bakışını içerir. Detaylı bilgiler için ilgili dokümanlara bakınız.
