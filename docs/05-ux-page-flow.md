# Logistics Project – Sayfa Akışı & UX Flow

**Amaç:** Minimum tıklama, maksimum hız, rol bazlı net akış  
**İlke:** Kullanıcı düşündürülmez, sistem yönlendirir

---

## GENEL UX PRENSİPLERİ

### Sayfa Geçişleri
- Tam reload değil (Blade + Alpine.js)
- Smooth transitions
- Loading states gösterilir

### Kritik Veriler
- Dashboard üstünden erişilebilir
- Hızlı aksiyon butonları
- Kısayollar (keyboard shortcuts)

### Liste → Detay → Aksiyon
- Standart akış
- Breadcrumb navigation
- Geri dönüş kolaylığı

### Mobile Geçişi
- Sade ekranlar
- Touch-friendly butonlar
- Responsive tasarım

---

## ADMIN PANEL SAYFA AKIŞI

### Giriş
```
Login Sayfası
    ↓
2FA (Opsiyonel)
    ↓
Rol Kontrolü
    ↓
Dashboard
```

### Dashboard (Merkez)
**Widget'lar:**
- Günlük operasyon özeti
- Geciken belgeler (kırmızı uyarı)
- Yaklaşan ödemeler (sarı uyarı)
- Aktif siparişler
- AI uyarı kutusu
- Hızlı aksiyon butonları

**Ana Menü Akışı:**
```
Dashboard
    ├── Siparişler
    ├── Araçlar
    ├── Personel
    ├── Belgeler
    ├── Finans
    ├── Raporlar
    └── Sistem Ayarları
```

---

## SİPARİŞ AKIŞI

### Sipariş Listesi
```
Siparişler → Liste
    ├── Filtreler (Durum, Tarih, Müşteri)
    ├── Arama
    ├── Sıralama
    └── Yeni Sipariş Butonu
```

### Sipariş Detayı
```
Sipariş Listesi → Detay
    ├── Genel Bilgiler
    ├── Müşteri Bilgileri
    ├── Teslimat Adresi
    ├── Durum Geçmişi
    ├── Belgeler
    └── Hızlı Aksiyonlar:
        ├── Durum Değiştir
        ├── Belge Ekle
        ├── Müşteriye Bildir
        └── Araç Ata
```

### Sevkiyat Ata
```
Sipariş Detayı → Araç Ata
    ├── Araç Seçimi
    ├── Şoför Seçimi
    ├── Tarih/Saat Seçimi
    └── Onayla
```

### Durum Güncelle
```
Sipariş Detayı → Durum Güncelle
    ├── Yeni Durum Seç
    ├── Not Ekle
    └── Kaydet
```

### Teslim Onayı
```
Sipariş Detayı → Teslim Onayı
    ├── Teslim Tarihi/Saati
    ├── İmza/Onay
    ├── Belge Yükle
    └── Onayla
```

---

## ARAÇ AKIŞI

### Araç Listesi
```
Araçlar → Liste
    ├── Filtreler (Durum, Şube, Tip)
    ├── Arama
    └── Yeni Araç Butonu
```

### Araç Detayı
```
Araç Listesi → Detay
    ├── Genel Bilgiler
    ├── Belgeler
    │   ├── Ruhsat
    │   ├── Sigorta
    │   ├── Kasko
    │   └── Muayene
    ├── Bakım Kayıtları
    ├── Yakıt Kayıtları
    ├── KM Takibi
    ├── Ekspertiz & Hasar
    └── Maliyet Analizi
```

---

## PERSONEL AKIŞI

### Personel Listesi
```
Personel → Liste
    ├── Filtreler (Şube, Departman, Durum)
    ├── Arama
    └── Yeni Personel Butonu
```

### Personel Profili
```
Personel Listesi → Profil
    ├── Kişisel Bilgiler
    ├── Belgeler
    │   ├── Kimlik
    │   ├── Ehliyet
    │   ├── SRC
    │   └── Sağlık Raporu
    ├── Puantaj
    │   ├── Aylık Görünüm
    │   └── Detaylı Kayıtlar
    ├── Vardiya
    │   ├── Haftalık Plan
    │   └── Vardiya Geçmişi
    ├── İzin / Avans
    │   ├── İzin Talepleri
    │   └── Avans Talepleri
    └── Bordro
        ├── Aylık Bordrolar
        └── PDF İndir
```

---

## BELGE AKIŞI

### Belge Merkezi
```
Belgeler → Merkez
    ├── Filtreler
    │   ├── Tür (Personel/Araç/Sipariş/Firma)
    │   ├── Süre (Yaklaşan/Geciken)
    │   └── Modül
    ├── Liste Görünümü
    └── Yeni Belge Yükle
```

### Belge Detayı
```
Belge Merkezi → Detay
    ├── Belge Bilgileri
    ├── Geçerlilik Tarihi
    ├── Hatırlatma Ayarları
    ├── Görüntüle / İndir
    └── Sil (Yetki varsa)
```

---

## FİNANS AKIŞI

### Ödeme Takvimi
```
Finans → Ödeme Takvimi
    ├── Görünüm Seçenekleri
    │   ├── Günlük
    │   ├── Haftalık
    │   └── Aylık
    ├── Takvim Görünümü
    ├── Liste Görünümü
    └── Filtreler
        ├── Tür
        ├── Firma/Şube
        ├── Tutar
        └── Durum
```

### Ödeme Detayı
```
Ödeme Takvimi → Detay
    ├── Ödeme Bilgileri
    ├── Vade Tarihi
    ├── Durum
    ├── Hatırlatma Geçmişi
    └── Ödendi İşaretle
```

---

## OPERASYON KULLANICISI AKIŞI

### Dashboard
```
Giriş → Dashboard
    ├── Aktif Görevlerim
    ├── Bugünkü Sevkiyatlar
    └── Uyarılarım
```

### Görev Akışı
```
Dashboard → Görevlerim
    ├── Sipariş Listesi (Bana Atananlar)
    ├── Sipariş Detayı
    │   ├── Teslim Güncelle
    │   ├── Durum Güncelle
    │   └── Belge Yükle
    └── Tamamlandı İşaretle
```

---

## MÜŞTERİ PORTALI AKIŞI

### Giriş
```
Müşteri Login
    ↓
Dashboard
```

### Dashboard
```
Müşteri Dashboard
    ├── Aktif Siparişlerim
    ├── Teslim Durumu
    └── Bildirimler
```

### Akış
```
Dashboard
    ├── Sipariş Oluştur
    │   ├── Form Doldur
    │   ├── Teslimat Adresi
    │   └── Gönder
    ├── Siparişlerim
    │   ├── Liste
    │   └── Detay
    │       ├── Durum Takibi
    │       ├── Belgeler
    │       └── Faturalar
    └── Belgeler
        └── İndir (PDF/XML)
```

---

## BİLDİRİM & UYARI AKIŞI

### Dashboard Uyarı Paneli
```
Dashboard → Üst Kısım
    ├── Kırmızı: Geciken belgeler/ödeme
    ├── Sarı: Yaklaşan belgeler/ödeme
    └── Mavi: Bilgilendirme
```

### Bildirim Çanı
```
Sağ Üst Köşe → Bildirim İkonu
    ├── Okunmamış Bildirimler
    ├── Tüm Bildirimler
    └── Bildirim Detayı
```

### Bildirim Türleri
- **Email:** Detaylı bilgi
- **SMS:** Kısa özet
- **WhatsApp:** Hızlı bildirim
- **Dashboard:** Anlık uyarı

---

## AI DESTEKLİ UX DOKUNUŞLARI

### Dashboard AI Özet
```
Dashboard → AI Kutusu
    └── "Bugün dikkat edilmesi gerekenler"
        ├── Operasyon uyarıları
        ├── Finans riskleri
        └── Personel notları
```

### Finans AI Uyarı Kartı
```
Finans → Dashboard
    └── "Bu ay nakit akışında risk var"
        └── Detaylı Rapor
```

### Operasyon AI Tahmini
```
Sipariş Detayı → AI Yorum
    └── "Bu sipariş gecikme riski taşıyor"
```

---

## PERFORMANS ODAKLI GEÇİŞLER

### Liste Sayfaları
- **Pagination:** 25-50 kayıt/sayfa
- **Lazy load:** Scroll ile yükleme
- **Filtreler:** Server-side

### Detay Sayfaları
- **Lazy section:** Ağır veriler async yüklenir
- **Tab navigation:** İçerik bölümlenir
- **Cache:** Sık kullanılan veriler cache'lenir

### Ağır Grafikler
- **Async load:** Sayfa yüklendikten sonra
- **Skeleton loader:** Yüklenirken gösterilir
- **Error handling:** Hata durumunda alternatif gösterilir

### Filtreler
- **Server-side:** Tüm filtreler backend'de
- **Debounce:** Arama input'ları için
- **URL params:** Filtreler URL'de saklanır (bookmark)

---

## KULLANICI YÖNLENDİRME

### Breadcrumb
```
Dashboard > Siparişler > Sipariş #12345
```

### Hızlı Aksiyon Butonları
- Her sayfada üst kısımda
- Sabit pozisyon (sticky)
- Rol bazlı görünürlük

### Kısayollar
- `Ctrl+K`: Global arama
- `Ctrl+N`: Yeni kayıt (modüle göre)
- `Esc`: Modal kapat

---

## MOBILE UYUMLULUK

### Responsive Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

### Touch-Friendly
- Butonlar minimum 44x44px
- Swipe gestures
- Pull-to-refresh

### Mobile Navigation
- Hamburger menu
- Bottom navigation (mobile)
- Tab navigation (tablet)

---

## DEPO & STOK AKIŞI

### Depo Listesi
```
Depolar → Liste
    ├── Filtreler (Tür, Şube, Durum)
    ├── Arama
    └── Yeni Depo Butonu
```

### Depo Detayı
```
Depo Listesi → Detay
    ├── Genel Bilgiler
    ├── Lokasyon Hiyerarşisi
    ├── Stok Listesi
    ├── Stok Hareketleri
    ├── Transfer İşlemleri
    └── Kritik Stok Uyarıları
```

### Stok İşlemleri
```
Stok → İşlemler
    ├── Stok Girişi (Barkod okut)
    ├── Stok Çıkışı (Barkod okut)
    ├── Lokasyon Güncelleme
    ├── Transfer (Depo → Depo)
    └── Fiziksel Sayım
```

---

## VARDIYA YÖNETİMİ AKIŞI

### Vardiya Planlama
```
Vardiyalar → Planlama
    ├── Hafta Seç
    ├── Şablon Seç veya Manuel Oluştur
    ├── Personel Atama
    └── Planı Kaydet
```

### Vardiya Görünümü
```
Vardiyalar → Görünüm
    ├── Takvim Görünümü (Haftalık)
    ├── Liste Görünümü (Personel bazlı)
    └── Grafik Görünümü (Vardiya dağılımı)
```

---

## İŞ EMİRLERİ & BAKIM AKIŞI

### İş Emri Oluşturma
```
Bakım → İş Emri Oluştur
    ├── Araç Seç
    ├── Bakım Tipi Seç
    ├── Bakım Kalemleri Ekle
    ├── Servis Sağlayıcı / Teknisyen Ata
    └── Onayla
```

### İş Emri Takibi
```
Bakım → İş Emirleri
    ├── Onay Bekleyenler
    ├── Devam Edenler
    ├── Tamamlananlar
    └── İptal Edilenler
```

---

## PERSONEL DASHBOARD DETAYLARI

### Personel Dashboard Widget'ları
- **Geç Gelen Personeller:** Geçerli gün için mesaiye geç kalan personel listesi
- **Erken Çıkan Personeller:** Geçerli gün için mesai saatlerinden önce çıkış yapan personel listesi
- **Personel Bilgileri Kartı:** Genel personel durumu özeti
  - Aktif çalışan sayısı
  - İzinli / Raporlu sayısı
  - Devamsız sayısı
  - Toplam personel sayısı
- **Personel Giriş Bilgileri:** Giriş türlerine göre ayrıntılı bilgiler
  - Elle yapılan giriş kaydı
  - Konumsuz giriş
  - Uzak giriş
  - Normal giriş
  - Geç gelenler
- **Personel Çıkış Bilgileri:** Çıkış türlerine göre ayrıntılı bilgiler
  - Elle yapılan çıkış kaydı
  - Konumsuz çıkış
  - Uzak çıkış
  - Normal çıkış
  - Erken çıkanlar
- **İzin Talepleri Kartı:** İşlem bekleyen izin talepleri sayısı
- **Avans Talepleri Kartı:** İşlem bekleyen avans talepleri sayısı
- **Lisans Durumu:** Lisans paketi bilgileri, maksimum personel sayısı, bitiş tarihi

### Dashboard Özelleştirme
- Bilgi kartlarının yerlerini değiştirme (sürükle-bırak)
- Kartları kaldırma (X işareti)
- Kart genişliklerini ayarlama (+/- butonları)
- Kartları açma/kapama (toggle)

---

## VARDIYA YÖNETİMİ DETAYLARI

### Vardiya Atama Yöntemleri

#### 1. Toplu Personel Vardiya Planlama
- "Vardiya Listesi"nde tanımlanan vardiyaları sütunlar halinde gösterir
- Tarihleri satırlar halinde gösterir
- Planlanan Vardiya olarak adlandırılır
- Birinci önceliğe sahip vardiya türüdür
- Çoğaltma işleviyle kolayca vardiya planlaması yapılabilir

#### 2. Akıllı Vardiya Planı
- Haftalık düzenlerle personel vardiyalarının otomatik atanmasını sağlar
- Akıllı Vardiya Planı olarak adlandırılır
- İkinci önceliğe sahip vardiya türüdür
- Vardiya Listesindeki tanımlı vardiyalar plan matrisinin hazırlanmasında kullanılır

#### 3. Personel Vardiya Yönetimi
- Üçüncü önceliğe sahip vardiya türüdür
- Bireysel vardiya atamaları için kullanılır

### Vardiya Öncelik Sırası
1. Toplu Personel Vardiya Planlama
2. Akıllı Vardiya Planı
3. Personel Vardiya Yönetimi

Çakışma durumunda bu öncelik sırasına göre çözülür.

---

**Sonuç:** Bu akış ile kullanıcı kaybolmaz, sistem yön verir. Her rol için net bir yol haritası vardır.
