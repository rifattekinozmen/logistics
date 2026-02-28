---
name: logistics-domain
description: Logistics domain odaklı Laravel modüllerinde (Order, Shipment, Delivery, Warehouse, Vehicle, Employee, Customer vb.) geliştirme yaparken modül yapısı, servis katmanı ve test konumlandırmasını standartlaştırır. Yeni domain özelliği eklerken veya mevcut akışları (sipariş, sevkiyat, teslimat, depo, fiyatlandırma) geliştirirken kullanılır.
---

# Logistics Domain Skill

Bu skill, projenin logistics domain yapısını (Order, Shipment, Delivery, Warehouse vb.) referans alarak **mevcut mimariyi bozmadan** yeni özellik eklemeyi kolaylaştırır. Genel Laravel kuralları için `laravel` ve test detayları için `pest-testing` skill'leri geçerlidir; bu skill yalnızca **domain odaklı klasör yapısı ve akış** rehberi sunar.

## Ne Zaman Uygulanır

- `app/Order`, `app/Shipment`, `app/Delivery`, `app/Warehouse`, `app/Vehicle`, `app/Employee`, `app/Customer`, `app/Finance`, `app/Analytics`, `app/AI`, `app/EInvoice`, `app/Sap` gibi domain klasörlerinde çalışırken
- Sipariş → sevkiyat → teslimat gibi uçtan uca logistics akışlarına yeni adım eklerken
- Depo (warehouse), stok/transfer, fiyatlandırma veya ödeme ile ilgili yeni servis/metod/endpoint yazarken
- Domain'e özel Feature test veya Blade ekranı eklerken

## Klasör Yapısı (Özet Görünüm)

Projede domain odaklı bir klasör yapısı kullanılıyor:

```text
app/
├── Order/           # Sipariş oluşturma ve yönetimi ile ilgili servisler, controller'lar
├── Shipment/        # Sevkiyat planlama, durum geçişleri
├── Delivery/        # Teslimat raporları, pivot/export/import servisleri
├── Warehouse/       # Depo, stok, barkod vs.
├── Vehicle/         # Araçlar, GPS, policy ve API controller'ları
├── Employee/        # Personel, vardiya, izin vb.
├── Customer/        # Müşteri portalı, sipariş şablonları, dokümanlar
├── Finance/         # Ödemeler, muhasebe ile ilgili servisler
├── Analytics/       # Dashboard ve raporlama servisleri
├── AI/              # AI ile ilgili job ve servisler
├── EInvoice/        # E-fatura modelleri ve servisleri
├── Sap/             # SAP entegrasyon modelleri ve job'ları
├── Core/            # Ortak servisler (örn. Geocoding, Export)
└── ...
```

Route ve view tarafı da domain'e göre ayrılmıştır:

- `routes/web.php`, `routes/admin.php`, `routes/api.php`, `routes/customer.php`
- `resources/views/admin/...` (admin paneli)
- `resources/views/customer/...` (müşteri portalı)

Yeni bir domain özelliği eklerken **önce ilgili klasördeki mevcut dosyaları incele**, aynı deseni takip et.

## Yeni Domain Özelliği Ekleme Akışı

1. **Doğru domain klasörünü seç**
   - Siparişle ilgili ise `app/Order/`
   - Sevkiyat/takip ise `app/Shipment/`
   - Teslimat raporu/ithalat-ihracat ise `app/Delivery/`
   - Depo/barkod/stok ise `app/Warehouse/`
   - Araç, GPS, filo ise `app/Vehicle/`
   - Personel/izin/vardiya ise `app/Employee/`
   - Müşteri portalı/şablon/doküman ise `app/Customer/`

2. **Mevcut desenleri kopyala**
   - Aynı klasördeki `Services/`, `Controllers/Api`, `Controllers/Web`, `Requests/`, `Policies/` ve `Enums` dosyalarına bak.
   - İsimlendirmeyi koru: `SomethingService`, `SomethingController`, `StoreSomethingRequest`, `SomethingPolicy`, `SomethingLifecycleState` gibi.

3. **Servis katmanını kullan**
   - İş kurallarını ve karmaşık logistics akışlarını doğrudan controller içinde değil, ilgili domain altındaki `Services` sınıflarına koy.
   - Örnek desen: `OrderService`, `DeliveryReportPivotService`, `VehicleService`, `FinanceDashboardService` gibi.

4. **Olaylar ve job'lar**
   - Asenkron/uzun süren işleri `Jobs` altına koy (`ProcessSapEventJob`, `GenerateWeeklyReportJob`, `RunAIAnalysisJob` gibi mevcut örneklere bak).
   - Domain olaylarında Laravel event/listener yapısını kullan (örn. `OrderCreated`, `ShipmentDelivered`, `InvoiceIssued`).

5. **Test konumlandırma**
   - Domain ile ilgili Feature testleri `tests/Feature/` altında, mevcut isimlendirmeye paralel yerleştir:
     - Örnekler: `OrderTest.php`, `ShipmentTest.php`, `DeliveryImportTest.php`, `WarehouseTest.php`, `FuelPriceTest.php`, `LogisticsB2BFlowTest.php` vb.
   - Domain servis testleri gerekiyorsa uygun alt klasör yapısını takip et (`tests/Feature/Delivery/DeliveryReportPivotTest.php` gibi).

6. **View ve UI**
   - Admin’e ait ekranlar için `resources/views/admin/{modul}/...`
   - Müşteri portalı için `resources/views/customer/...`
   - Ortak komponentleri `resources/views/components/` altındaki mevcut Blade component'lerine göre tasarla (`card`, `badge`, `empty-state`, `stat-card` vb.).

## Test Akışı (Domain Odaklı)

Genel Pest kuralları için `pest-testing` skill'ini kullan; bu bölüm yalnızca **hangi test dosyasına ne eklemek gerekir** sorusuna yardımcı olur:

- Yeni bir Order özelliği → `tests/Feature/OrderTest.php` veya mevcut `Order...Test` dosyalarına senaryo ekle.
- Sevkiyat durum değişikliği → `tests/Feature/ShipmentTest.php` ve gerekirse `OrderStatusTransitionTest.php`.
- Delivery import/export veya pivot raporları → `tests/Feature/Delivery/...` altındaki testleri takip et.
- Warehouse/stock değişiklikleri → `tests/Feature/WarehouseTest.php`.
- Yakıt, fiyatlandırma veya finans akışları → `FuelPriceTest`, `PricingConditionTest`, `PaymentTest` gibi mevcut dosyalara bak.

Her yeni domain özelliği için en az bir **happy path** ve kritik hata senaryolarını kapsayan Feature test ekle.

## Checklist (Yeni Logistics Özelliği)

- [ ] Doğru domain klasörünü seçtim (`app/Order`, `app/Shipment`, `app/Delivery`, `app/Warehouse` vb.)
- [ ] Aynı klasördeki mevcut controller/service/request/policy örneklerini inceledim ve aynı deseni takip ettim.
- [ ] İş kurallarını controller yerine servis katmanına koydum.
- [ ] Gerekliyse event/job yapısını, projedeki mevcut event/job örneklerine göre tasarladım.
- [ ] İlgili Feature test dosyasında yeni senaryolar ekledim veya yeni bir `*Test.php` oluşturup Pest ile yazdım.
- [ ] Admin veya customer tarafındaki Blade view'ları mevcut layout ve component pattern'lerine göre yerleştirdim.

## İlgili Dokümanlar (Referans)

Detaylı domain ve modül açıklamaları için skill içinden içerik kopyalamak yerine aşağıdaki dokümanlar referans alınmalıdır:

- `docs/architecture/01-project-overview.md` – Proje genel bakışı, B2B order lifecycle, güvenlik ve rol mimarisi
- `docs/modules/04-modules-documentation.md` – Tüm modüllerin detaylı özellik listeleri, iş akışları ve modül dizin yapıları
- `docs/reference/delivery-report-pivot-and-invoice-lines.md` – Teslimat raporu pivot ve fatura satırları referansı (Delivery & Invoice entegrasyonu)
- `docs/reference/sap-integration/` altındaki dosyalar – SAP lojistik entegrasyon rehberleri
- `docs/compliance/tcmb_pay/` altındaki dosyalar – TCMB uyumlu B2B ödeme mimarisi ve regülasyon rehberleri

Bu skill, yukarıdaki dokümanlara yönlendirici ve klasör/test konumlandırma odaklı kalmalı; iş kuralları, regülasyon metinleri ve kapsamlı domain açıklamaları `docs/` altında tutulmalıdır.

