# Laravel B2B Ödeme Mimarisi --- TCMB Uyumlu Yasal ve Teknik Rehber (V2.0)

## ⚖️ Hukuki Bildirim

Bu doküman teknik rehber niteliğindedir. Türkiye Cumhuriyeti Merkez
Bankası (TCMB) ve 6493 Sayılı Ödeme ve Menkul Kıymet Mutabakat
Sistemleri Kanunu esas alınarak hazırlanmıştır. Bu doküman hukuki
danışmanlık yerine geçmez; üretim ortamına geçmeden önce hukuk danışmanı
ve mali müşavir incelemesi önerilir.

------------------------------------------------------------------------

## 1. Amaç

Bu rehberin amacı:

-   Laravel tabanlı B2B / lojistik sistemlerinde
-   TCMB lisansı gerektirmeyen
-   Yasal risk oluşturmayan
-   Denetime hazır ödeme mimarisi kurmaktır.

------------------------------------------------------------------------

## 2. Mevzuat Temeli

Türkiye'de aşağıdaki faaliyetler lisans gerektirir:

-   Kullanıcı adına para tutmak
-   Sistem içi TL bakiyesi oluşturmak
-   Kullanıcılar arası para transferi
-   Marketplace escrow (emanet para)

Dayanak: **6493 Sayılı Kanun -- Madde 12**

### ✅ Lisans Gerektirmeyen Model

Laravel sistemi:

-   Para tutmaz
-   Para transfer etmez
-   Sadece kayıt ve raporlama yapar

Gerçek tahsilat lisanslı ödeme kuruluşunda gerçekleşir.

------------------------------------------------------------------------

## 3. Yasal Mimari Model

    Müşteri
       ↓
    Ödeme Kuruluşu (iyzico / PayTR / Paynet)
       ↓
    Banka / Kart / FAST
       ↓
    Laravel (Sadece kayıt)

### Kritik Ayrım

  Kavram        Tanım
  ------------- ---------------------------
  Cari Hesap    Muhasebe kaydıdır
  Gerçek Para   Ödeme kuruluşunda tutulur

------------------------------------------------------------------------

## 4. Sistem Modülleri

### Sipariş

-   Sipariş oluşturma
-   Fatura eşleme
-   Ödeme bekliyor durumu

### Ödeme

-   Payment intent
-   Redirect
-   Webhook doğrulama

### Cari Hesap

-   Borç / Alacak
-   Komisyon
-   Tahsilat kaydı

------------------------------------------------------------------------

## 5. Veritabanı Tasarımı (Uyumlu)

### payments

-   id
-   order_id
-   provider
-   provider_payment_id
-   amount
-   status
-   signature_hash
-   created_at

### ledger_entries (Immutable)

-   id
-   account_id
-   debit
-   credit
-   reference_type
-   reference_id
-   immutable_hash

------------------------------------------------------------------------

## 6. Güvenlik Standartları

Zorunlu:

-   Webhook imza doğrulama
-   Replay attack koruması
-   Audit log
-   HTTPS zorunlu

Önerilen:

-   OAuth2 veya Token API
-   Rate limit
-   IP whitelist

------------------------------------------------------------------------

## 7. Yasaklı UI Terimleri

  Kullanılmamalı   Kullanılmalı
  ---------------- --------------
  Cüzdan           Cari Hesap
  Bakiye (TL)      Hesap Durumu
  Para Gönder      Mahsup

------------------------------------------------------------------------

## 8. Webhook Güvenliği

Akış:

    Webhook → Queue → Validation → DB Commit

Kontroller:

-   HMAC signature
-   Timestamp ±5 dk
-   Payload hash tekrar kontrolü

------------------------------------------------------------------------

## 9. Idempotency

Her ödeme isteği için UUID anahtarı:

-   Aynı ödeme ikinci kez işlenmez
-   Network retry güvenlidir

------------------------------------------------------------------------

## 10. Fraud Kontrolleri

-   Aynı IP tekrar ödeme
-   Başarısız işlem limiti
-   Tutar anomali analizi
-   Risk skoru

------------------------------------------------------------------------

## 11. Yedekleme Politikası

  Periyot    İşlem
  ---------- ---------------------
  Günlük     DB Snapshot
  Haftalık   Şifreli Full Backup
  Aylık      Offline Cold Backup

AES‑256 şifreleme önerilir.

------------------------------------------------------------------------

## 12. Laravel Mimari Yapısı

    app/
     ├── Domains/
     ├── Services/Payment
     ├── Services/Fraud
     ├── Jobs/
     └── Events/

Amaç:

-   Gateway bağımsızlık
-   Modüler fintech mimarisi

------------------------------------------------------------------------

## 13. Payment Service Layer

Controller → PaymentManager → Gateway Adapter

Sorumluluklar:

-   Gateway seçimi
-   Intent oluşturma
-   Callback doğrulama
-   Ledger kaydı

------------------------------------------------------------------------

## 14. Denetim Hazırlığı (TCMB Perspektifi)

Denetçi sorarsa:

-   Sistem para tutmaz
-   Settlement provider tarafındadır
-   Laravel yalnızca kayıt sistemidir

------------------------------------------------------------------------

## 15. Lisanslı Yapıya Geçiş Roadmap

### Faz 1 --- Lisanssız Model

Gateway entegrasyonu

### Faz 2 --- White Label

Lisanslı kuruluş üzerinden marka

### Faz 3 --- TCMB Başvurusu

-   ISO 27001
-   PCI DSS
-   AML & Fraud sistemi
-   İç denetim

------------------------------------------------------------------------

## 16. Production Güvenlik Checklist

-   CSRF hariç webhook
-   ENV secrets vault
-   Key rotation
-   Queue monitoring
-   Webhook failure alert

------------------------------------------------------------------------

## 17. Sonuç

Bu mimari:

✅ TCMB uyumludur\
✅ Lisans gerektirmez\
✅ Denetime hazırdır\
✅ Fintech dönüşümüne açıktır

------------------------------------------------------------------------

**Versiyon:** 2.0\
**Platform:** Laravel B2B / Lojistik Sistemleri\
**Durum:** Resmi ve Teknik Olarak Revize Edildi
