# Teslimat Raporu: Pivot Tablo ve Fatura Kalemleri Yol Haritası

Yüklenen teslimat raporu Excel'inden **pivot tablo** (özet) ve **fatura kalemleri** üretmek için izlenecek yol.

---

## 1. Mevcut Veri

- **DeliveryImportBatch:** Yüklenen dosya, rapor tipi, durum.
- **DeliveryReportRow:** Her satır `row_data` (array) ile normalize edilmiş; sütun sırası config'teki `headers` ile aynı.
- Rapor tipleri: `endustriyel_hammadde`, `dokme_cimento` (config: `delivery_report.report_types`).

---

## 2. Pivot Tablo (Özet)

**Amaç:** Rapor satırlarını gruplayıp özet metrikler üretmek (örn. tarihe, firmaya, malzemeye göre toplam miktar).

**Önerilen gruplama alanları (rapor tipine göre config’te tanımlanır):**

| Boyut        | Açıklama           | Örnek metrikler                    |
|-------------|--------------------|------------------------------------|
| Tarih       | Gün / ay           | Toplam teslimat miktarı, irsaliye adedi |
| Firma / Satıcı | Nakliye / satıcı  | Toplam miktar, sefer sayısı       |
| Malzeme     | Malzeme kodu / adı | Toplam miktar, birim               |
| İrsaliye No | Belge bazlı        | Kalem sayısı, toplam miktar       |
| Plaka       | Araç bazlı         | Sefer sayısı, toplam miktar        |

**Akış:**

1. **Config:** Her rapor tipi için `pivot_dimensions` ve `pivot_metrics` tanımla (hangi sütun index = boyut, hangisi = metrik).
2. **Servis:** `DeliveryReportPivotService::buildPivot(DeliveryImportBatch $batch, array $groupBy, array $metrics)` — `reportRows` üzerinden grupla, topla.
3. **Çıktı:**  
   - Rapor detay sayfasında "Pivot / Özet" sekmesi veya buton → tablo.  
   - "Excel’e aktar" ile pivot özeti indir.

---

## 3. Fatura Kalemleri

**Amaç:** Rapor satırlarından fatura satırı (kalem) listesi üretmek; ileride e-Fatura / Logo’ya beslenebilir.

**Mantık:**

- **Seçenek A – Satır = Kalem:** Her `DeliveryReportRow` = 1 fatura kalemi (gruplama yok).
- **Seçenek B – Gruplu kalem:** Aynı İrsaliye No + Malzeme + Teslimat (veya benzeri anahtar) için miktarları topla → 1 kalem.

**Fatura kaleminde olması gereken alanlar (Logo / e-Fatura uyumu):**

- Malzeme kodu, malzeme adı/kısa metni  
- Miktar, birim  
- İrsaliye no, tarih  
- Satıcı / nakliye firması (opsiyonel)  
- Birim fiyat / tutar (raporda yoksa sonradan eklenir veya 0)

**Akış:**

1. **Config:** Her rapor tipi için `invoice_line_mapping` — hangi header index’lerin hangi fatura alanına gideceği (malzeme, miktar, birim, irsaliye_no, tarih vb.).
2. **Servis:** `DeliveryReportPivotService::buildInvoiceLines(DeliveryImportBatch $batch, bool $groupByIrsaliyeAndMaterial = true)` — `reportRows`’u oku, map’le, istenirse grupla.
3. **Çıktı:**  
   - Rapor detay sayfasında "Fatura kalemleri" listesi veya Excel/CSV export.  
   - İleride: Bu kalemler `LogoIntegrationService::sendInvoice` formatına dönüştürülüp kuyruğa atılabilir.

---

## 4. Uygulama Adımları

| Adım | Ne yapılacak | Nerede |
|------|----------------|--------|
| 1 | Rapor tipine göre pivot boyutları ve metrikleri tanımla | `config/delivery_report.php` → `pivot_dimensions`, `pivot_metrics` |
| 2 | Rapor tipine göre fatura kalem alan eşlemesi tanımla | `config/delivery_report.php` → `invoice_line_mapping` |
| 3 | Pivot ve fatura kalemleri üreten servis | `App\Delivery\Services\DeliveryReportPivotService` |
| 4 | Rapor detay sayfasında "Pivot" / "Fatura kalemleri" sekmeleri veya butonlar | `DeliveryImportController`, view |
| 5 | Pivot ve fatura kalemleri Excel/CSV export | Mevcut export benzeri, yeni action’lar |

---

## 5. Config Örnekleri (Kısaltılmış)

**Pivot (endustriyel_hammadde):**

- Boyutlar: Tarih (index 4), FİRMA (index 52), Malzeme (index 12).  
- Metrikler: Teslimat miktarı (index 41) toplam, Geçerli Miktar (index 15 veya 17) toplam, row sayısı.

**Fatura kalem eşlemesi (endustriyel_hammadde):**

- malzeme_kodu → 12, malzeme_adi → 13, miktar → 41, birim → 42, irsaliye_no → 45, tarih → 4, firma → 52, plaka → 51.

Bu sayede hem pivot hem fatura kalemleri rapor tipine göre esnek ve tek yerden (config) yönetilir.
