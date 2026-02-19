# Cemiloglu Malzeme Pivot Referansı

Cemiloglu projesindeki `excel-imports/show` ve `ExcelImportController::createMaterialPivotTable` incelenerek özetlenen pivot mantığı.

## Veri yapısı

- **Kaynak:** `rawMaterials` (veya logistics’te `reportRows`) — her satırda `raw_payload` / `row_data` (sütun index’leri).
- **Pivot hücre değeri:** İlk **Geçerli Miktar** sütunu (`gecerli_miktar_1`), Teslimat miktarı değil.
- **Malzeme anahtarı:** `"Malzeme kodu | Malzeme kısa metni"` (örn. `CLN-0100 | KLİNKER (GRİ)`).

## Kullanılan sütunlar (endüstriyel hammadde)

| Alan               | Header           | Amaç                          |
|--------------------|------------------|-------------------------------|
| Tarih              | Tarih            | Satır gruplama                |
| Malzeme            | Malzeme          | Sütun gruplama                |
| Malzeme kısa metni | Malzeme kısa metni | Malzeme etiketi             |
| Hücre miktarı      | Geçerli Miktar (ilk) | Pivot hücre + formüller   |
| Dolu Ağırlık       | Dolu Ağırlık    | BOŞ-DOLU / malzeme metni      |
| Boş Ağırlık        | Boş Ağırlık     | BOŞ-DOLU / malzeme metni      |
| Geçerli Miktar (2) | Geçerli Miktar (ikinci) | Formül              |
| Firma Miktarı      | Firma Miktarı   | Formül                        |

## BOŞ-DOLU / DOLU-DOLU hesaplama (satır bazlı, tarih başına)

1. **Malzeme eşlemesi:** Klinker (Gri), CÜRUF, Petrokok(MS) — kod veya kısa metinde geçiyorsa tanınır.
2. **applyMaterialMatchingLogic:** Aynı tarihte üç malzeme de varsa:
   - CÜRUF ve Petrokok’tan küçük olan, Klinker ile “dolu-dolu”ya gider.
   - Dolu-Dolu = 2 × min(Klinker, Cüruf) (basit formül).
   - Boş-Dolu = abs(Klinker − Cüruf) + (P.kok veya P.kok+Curuf durumunda Petrokok).
3. **Boş-Dolu Taşınan Malzeme Kısa Metni:**
   - Önce her malzeme için `bos_dolu_malzeme_calculated` (Klinker(Gri), Curuf, P.kok) birleştirilir.
   - Yoksa formül: Dolu ≈ Firma+Geçerli2 → "--"; Dolu > … → "Klinker(Gri)"; Firma≈0 → "Curuf"; TOPLAM≤Firma → "P.kok"; değilse "P.kok+Curuf".

## Sütun sırası

- CÜRUF ve Petrokok sütunları tabloda yer değiştirir (Cüruf önce, Petrokok sonra).

## View (show.blade.php)

- `pivotData['dates']`, `pivotData['materials']`, `pivotData['data'][$date][$material]`.
- Hücre: `gecerli_miktar_1` veya `quantity`.
- Satır toplamı: malzeme hücreleri toplamı.
- BOŞ-DOLU / DOLU-DOLU: satırdaki ilk malzemeden `bos_dolu_tasinan`, `dolu_dolu_tasinan` (hepsi aynı).
- Malzeme kısa metni: formül veya `bos_dolu_malzemeler` birleşimi.
