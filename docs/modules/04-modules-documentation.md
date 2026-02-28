# Logistics Project – Modül Dokümantasyonu

**Amaç:** Tüm modüllerin detaylı dokümantasyonu, özellikler, veritabanı şemaları ve kullanım senaryoları

---

## 1. DEPO & STOK YÖNETİMİ

**Durum:** Çekirdek fonksiyonlar production ortamında aktif; ileri seviye optimizasyon ve ek raporlar Faz 2 / Faz 3 backlog'unda.

### Özellikler
- Depo içi mikro yönetim
- Lokasyon hiyerarşisi (Alan > Koridor > Raf > Kat > Pozisyon)
- Barkod ile stok takibi
- Stok transfer fişleri (depo → depo)
- Minimum stok seviyesi uyarıları
- Seri/Lot takibi
- Fiziksel sayım

### Lokasyon Formatı
`A-01-B-02-C-03` (Alan-Koridor-Raf-Kat-Pozisyon)

### Stok Türleri
- **Stoklu Malzeme:** Fiziksel stok takibi yapılan
- **Stoğa Bağlı Malzeme:** Stok takibi yapılmayan (hizmet vb.)

### Otomasyon
- Günlük stok kontrolü (08:00)
- Kritik stok uyarıları (Dashboard + Email)
- AI yorum: "X stok kritik seviyede"

### Modül Yapısı
```
app/Inventory/
├── Models/
│   ├── Warehouse.php
│   ├── WarehouseLocation.php
│   ├── InventoryItem.php
│   ├── InventoryStock.php
│   └── InventoryMovement.php
├── Controllers/
│   ├── Web/WarehouseController.php
│   └── Api/BarcodeController.php
└── Services/
    ├── WarehouseService.php
    └── InventoryService.php
```

---

## 2. MOTORİN FİYAT TAKİP MODÜLÜ

**Durum:** Production; haftalık rapor ve email otomasyonları ROADMAP'te tamamlandı olarak işaretli.

### Özellikler
- Günlük motorin fiyatı kaydı
- Satın alma vs istasyon fiyatı karşılaştırması
- Haftalık Excel raporu
- Aylık grafik görselleştirme
- Dashboard widget

### Fiyat Türleri
- **Satın Alma Fiyatı:** Tedarikçiden alınan fiyat
- **İstasyon Fiyatı:** Piyasa fiyatı

### Otomasyon
- Haftalık Excel raporu (Pazar, 20:00)
- Günlük hatırlatma (08:00) - Fiyat girilmemiş günler için

### AI Entegrasyonu
- AI Fleet Service: Yakıt maliyeti analizi
- "Bu ay yakıt maliyeti ortalamanın %X üzerinde"

---

## 3. TESLİMAT NUMARASI OTOMASYONU

**Durum:** Çekirdek import ve otomatik sipariş oluşturma hattı production; pivot/fatura katmanı için Delivery Report hattı ile birlikte tuning ve edge-case sertleştirme Faz 2 kapsamında.

### Özellikler
- Excel'den toplu teslimat numarası yükleme
- Otomatik lokasyon eşleştirme
- Otomatik sipariş oluşturma
- Eşleşmeyen teslimatlar yönetimi
- Hatalı satır işaretleme ve düzeltme

### İş Akışı
```
Excel Yükleme
    ↓
Teslimat Numaraları Parse
    ↓
Lokasyon Eşleştirme
    ↓
Otomatik Sipariş Oluşturma
    ↓
Sevkiyat Atama
```

### Lokasyon Eşleştirme
- **Otomatik:** Adres parse → Lokasyon veritabanında ara
- **Manuel:** Eşleşmeyen teslimatlar ekranı

### Modül Yapısı
```
app/Delivery/
├── Models/
│   ├── DeliveryNumber.php
│   ├── DeliveryImportBatch.php
│   └── Location.php
├── Services/
│   ├── LocationMatchingService.php
│   └── AutoOrderCreationService.php
└── Imports/
    └── DeliveryNumberImport.php
```

---

## 4. VARDIYA YÖNETİMİ

### Özellikler
- Haftalık vardiya planlama
- Otomatik vardiya şablonları (Sabah/Öğle/Gece)
- Vardiya-puantaj entegrasyonu
- Vardiya değişiklik takibi
- Onay süreci

### Vardiya Türleri
- **Sabah:** 08:00 - 16:00 (8 saat)
- **Öğle:** 16:00 - 00:00 (8 saat)
- **Gece:** 00:00 - 08:00 (8 saat)
- **Esnek Vardiya:** Özel saatler

### Otomasyon
- Haftalık plan oluşturma (Pazar, 18:00)
- Puantaj senkronizasyonu
- Vardiya hatırlatma (07:00)

### Modül Yapısı
```
app/Shift/
├── Models/
│   ├── ShiftTemplate.php
│   ├── ShiftSchedule.php
│   └── ShiftAssignment.php
└── Services/
    ├── ShiftScheduleService.php
    └── ShiftAssignmentService.php
```

---

## 5. MOBİL SAHA ÖZELLİKLERİ

### Hedef Kullanıcılar
- **Sürücüler:** Sevkiyat takibi, teslimat, POD
- **Depo Personeli:** Stok işlemleri, barkod okutma
- **Saha Ekipleri:** Görev takibi, raporlama

### Özellikler
- Sürücü sevkiyat takibi
- POD (Teslimat kanıtı) yükleme
- Mobil barkod okutma
- GPS konum takibi
- Offline çalışma
- Push notifications

### API Endpoints (v1 – Sanctum auth)
```
GET  /api/v1/driver/shipments              # Şoföre atanmış sevkiyatlar (query: status)
PUT  /api/v1/driver/shipments/{shipment}/status
POST /api/v1/driver/shipments/{shipment}/pod   # multipart: pod_file, notes
POST /api/v1/driver/location                # JSON: latitude, longitude, shipment_id?
POST /api/v1/warehouse/barcode/scan
```
v2: `GET /api/v2/driver/dashboard`, `POST /api/v2/driver/checkin` (konum + opsiyonel shipment).
Tam sözleşme ve response örnekleri: **`docs/api/driver-mobile.md`**.

### Offline Çalışma
- Local database (SQLite)
- Otomatik senkronizasyon (bağlantı geldiğinde)

---

## 6. ARAÇ EKSPERTİZ VE HASAR SİSTEMİ

### Özellikler
- Detaylı ekspertiz kayıtları
- Hasar tespiti ve kayıt sistemi
- Dijital hasar çizimi (Canvas/SVG)
- Araç türüne göre hasar şablonları
- PDF export (ekspertiz/hasar raporu)

### Ekspertiz Test Kalemleri
- Motor testi
- Fren testi
- Emisyon testi
- Aydınlatma testi
- Lastik kontrolü
- Genel görünüm

### Hasar Durumları
- **Tespit Edildi:** Hasar kaydedildi
- **Onaylandı:** Yönetici onayladı
- **Tamir Edildi:** Tamir tamamlandı

### Otomasyon
- Ekspertiz hatırlatmaları
- Hasar onay süreci

### Modül Yapısı
```
app/Inspection/
├── Models/
│   ├── VehicleInspection.php
│   ├── VehicleInspectionItem.php
│   └── VehicleDamage.php
└── Services/
    ├── InspectionService.php
    └── PdfExportService.php
```

---

## 7. İŞ EMİRLERİ VE BAKIM YÖNETİMİ

### Özellikler
- Kapsamlı iş emri sistemi
- Bakım kalemleri yönetimi
- Servis sağlayıcıları yönetimi
- Bakım parçaları stok takibi
- Teknisyen atamaları
- Maliyet hesaplama

### İş Emri Durumları
- **Onay Bekliyor:** Oluşturuldu, onay bekliyor
- **Onaylandı:** Onaylandı, başlatılabilir
- **Devam Ediyor:** İş emri başlatıldı
- **Tamamlandı:** İş emri tamamlandı
- **İptal:** İş emri iptal edildi

### Bakım Kalemleri
- Motor bakımı
- Fren bakımı
- Lastik değişimi
- Yağ değişimi
- Filtre değişimi
- Elektrik sistemi
- Klima bakımı

### Otomasyon
- Periyodik bakım hatırlatmaları
- Stok uyarıları (bakım parçaları)

### Modül Yapısı
```
app/WorkOrder/
├── Models/
│   ├── WorkOrder.php
│   ├── WorkOrderItem.php
│   ├── ServiceProvider.php
│   └── MaintenancePart.php
└── Services/
    ├── WorkOrderService.php
    └── CostCalculationService.php
```

---

## 8. HİYERARŞİK LOKASYON YÖNETİMİ

### Hiyerarşi
```
Ülke (Country)
    └── Şehir (City)
        └── İlçe (District)
            └── Mahalle (Neighborhood)
```

### Özellikler
- API tabanlı lokasyon servisleri
- Lokasyon arama ve filtreleme
- Autocomplete desteği
- Müşteri ve depo adreslerinde entegrasyon

### API Endpoints
```
GET  /api/locations/countries
GET  /api/locations/cities/{countryId}
GET  /api/locations/districts/{cityId}
GET  /api/locations/neighborhoods/{districtId}
GET  /api/locations/search?q={query}
GET  /api/locations/autocomplete?q={query}
```

### Cache Stratejisi
- Lokasyon listeleri cache'lenir
- Arama sonuçları cache'lenir

### Modül Yapısı
```
app/Location/
├── Models/
│   ├── Country.php
│   ├── City.php
│   ├── District.php
│   └── Neighborhood.php
└── Services/
    └── LocationSearchService.php
```

---

## 9. AKTİVİTE İZLEME VE RAPORLAMA

### Özellikler
- Kapsamlı aktivite log sistemi
- Model bazlı değişiklik izleme
- Raporlama ve dışa aktarma
- Filtreleme ve arama

### İzlenen İşlemler
- Create (Oluşturma)
- Update (Güncelleme)
- Delete (Silme)
- View (Görüntüleme - opsiyonel)
- Login/Logout

### LogsActivity Trait
```php
use App\Activity\Traits\LogsActivity;

class Vehicle extends Model
{
    use LogsActivity;
    
    protected static $logAttributes = ['plate', 'brand', 'model'];
    protected static $logOnlyDirty = true;
}
```

### Modül Yapısı
```
app/Activity/
├── Models/
│   └── ActivityLog.php
├── Traits/
│   └── LogsActivity.php
└── Services/
    └── ActivityLogService.php
```

---

## 10. PERSONEL PUANTAJ SİSTEMİ

### Özellikler
- Detaylı puantaj yönetimi
- Aylık puantaj görüntüleme
- Excel import/export
- Raporlama

### Puantaj Türleri
- **Tam Gün:** Normal çalışma günü
- **Yarım Gün:** Yarı zamanlı çalışma
- **İzin:** Günlük izin
- **Yıllık İzin:** Yıllık izin kullanımı
- **Rapor:** Hastalık raporu
- **Fazla Mesai:** Fazla mesai çalışması

### Otomasyon
- Otomatik puantaj (QR giriş ile)
- İzin onay süreci
- Aylık puantaj kilitleme

### Modül Yapısı
```
app/Personnel/
├── Models/
│   └── PersonnelAttendance.php
└── Services/
    ├── AttendanceService.php
    └── AttendanceReportService.php
```

---

## 11. BİLDİRİM YÖNETİM PANELİ

### Özellikler
- Web tabanlı bildirim paneli
- Otomatik mail bildirimleri
- Cronjob yönetimi
- Gerçek zamanlı istatistikler

### Otomatik Mail Zamanlaması
- **Günlük:** Her gün saat 09:00'da
- **Acil Kontroller:** Her 4 saatte bir
- **Haftalık Raporlar:** Her pazartesi saat 10:00'da

### Bildirim Senaryoları
- Belge süre bildirimleri (geçmiş, bugün, 3 gün, 7 gün)
- Bakım hatırlatmaları
- Ceza ödeme bildirimleri
- Toplu bildirimler (5+ bildirim)

### Modül Yapısı
```
app/Notification/
├── Models/
│   ├── Notification.php
│   └── NotificationLog.php
├── Mail/
│   ├── DocumentExpiryNotification.php
│   └── MaintenanceReminderNotification.php
└── Console/Commands/
    └── SendDailyNotifications.php
```

---

## 12. AI YORUM & TAVSİYE SİSTEMİ

**Durum:** Çekirdek AI servisleri (Operations, Finance, HR, Fleet, Document) production seviyesinde çalışır; anomaly detection, daha gelişmiş skorlamalar ve dış Python analizleri Faz 2 / Faz 3 backlog'unda tanımlıdır (`docs/ROADMAP.md` ve `.ai/session.md`).

### Domain Bazlı AI Servisleri
- **AIOperationsService:** Operasyon analizi
- **AIFinanceService:** Finansal risk uyarıları
- **AIHRService:** Personel verimlilik önerileri
- **AIFleetService:** Araç bakım tahminleri
- **AIDocumentService:** Belge risk analizi

### AI Çalışma Modeli
- **Cronjob:** Günlük (09:00)
- **Manuel:** Yönetici isterse
- **Async & rapor bazlı** çalışır
- Operasyonel akışı **DURDURMAZ**

### AI Sonuçları
- `ai_reports` tablosuna kaydedilir
- Dashboard'a eklenir
- Severity: low, medium, high

### AI Yapmayacağı Şeyler
- ❌ Otomatik ödeme yapmaz
- ❌ Sipariş iptal etmez
- ❌ Personel çıkarmaz
- ❌ Karar vermez

### AI Yapacakları
- ✅ Yorumlar
- ✅ Önerir
- ✅ Uyarır
- ✅ Analiz eder
- ✅ Raporlar

### Modül Yapısı
```
app/AI/
├── Services/
│   ├── AIOperationsService.php
│   ├── AIFinanceService.php
│   ├── AIHRService.php
│   ├── AIFleetService.php
│   └── AIDocumentService.php
└── Jobs/
    └── RunAIAnalysisJob.php
```

---

## 13. BELGE HATIRLATMA SİSTEMİ

### Hatırlatma Periyotları
- **30 gün önce:** İlk uyarı
- **15 gün önce:** İkinci uyarı
- **7 gün önce:** Üçüncü uyarı
- **Gününde:** Son uyarı
- **Süresi geçen:** Acil uyarı (kırmızı)

### Bildirim Kanalları
- Email
- SMS
- WhatsApp
- Dashboard

### Otomasyon
- Günlük Cron (00:05)
- Süresi yaklaşan belgeler kontrol edilir
- Hatırlatma tarihine uyanlar kuyruğa alınır

---

## 14. ÖDEME HATIRLATMA SİSTEMİ

### Senaryolar
- **7 Gün Kala:** Email
- **3 Gün Kala:** SMS + Email
- **Gününde:** WhatsApp + Dashboard
- **Gecikince:** Dashboard (kırmızı) + Email

### Otomasyon
- Günlük Cron (00:05)
- Due_date yaklaşan ödemeler kontrol edilir
- Geciken ödemeler ayrı işlenir

---

## 14. EXCEL IMPORT/EXPORT MODÜLÜ

### Özellikler
- Excel dosyası yükleme (XLSX, CSV)
- Haftalık periyot tespiti
- Otomatik tarih hesaplama
- Veri doğrulama
- Analiz tablosu oluşturma
- Faturalandırma datası üretme
- Hatalı satır işaretleme ve raporlama

### Excel Kaynakları
- **Excel A:** Operasyon verileri
- **Excel B:** Hammadde / Ürün verileri
- **Excel C:** Sevkiyat / Lojistik verileri

### İşleme Süreci
1. Excel dosyası yükleme
2. Raw tablolara ham veri kaydı
3. Veri doğrulama ve normalize etme
4. Haftalık periyot hesaplama
5. Analiz ve raporlama
6. Faturalandırma datası üretme

### Performans Gereksinimleri
- 10.000+ satır Excel işleme
- Chunk processing (büyük dosyalar için)
- Queue kullanımı (ağır işlemler için)

### Modül Yapısı
```
app/Excel/
├── Services/
│   ├── ExcelImportService.php
│   ├── PeriodCalculationService.php
│   ├── AnalysisService.php
│   └── BillingService.php
├── Imports/
│   ├── OperationsImport.php
│   ├── MaterialsImport.php
│   └── LogisticsImport.php
└── Jobs/
    └── ProcessExcelJob.php
```

---

## 15. ENTEGRASYON MODÜLLERİ

**Durum:** Logo entegrasyonu production seviyesinde; Python ara katman (PythonBridgeService) şu an için opsiyonel/POC düzeyinde planlanmış ve Faz 2 içerisinde konumlanmıştır.

### ERP Entegrasyonu
- Fatura header mapping
- Satır bazlı ürün eşleşmesi
- Vergi & tarih uyumu
- JSON veri alışverişi

### Python Ara Katman (Opsiyonel)
- Logo SDK kısıtları için
- Queue üzerinden tetikleme
- JSON veri alışverişi

### Entegrasyon Servisleri
```
app/Integration/
├── Services/
│   ├── LogoIntegrationService.php
│   └── PythonBridgeService.php
└── Jobs/
    └── SendToLogoJob.php
```

---

**Not:** Bu dokümantasyon tüm modüllerin detaylı özelliklerini içerir. Her modül için veritabanı şemaları, modül yapıları ve otomasyon senaryoları belirtilmiştir.
