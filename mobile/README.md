# Logistics Driver (Expo)

Şoför mobil uygulaması. Laravel backend Driver API (v1) kullanır.

## Kurulum

```bash
cd mobile
npm install
```

## Ortam

`.env` (opsiyonel):

```
EXPO_PUBLIC_API_URL=http://BILGISAYAR_IP:8000
```

Expo Go ile test için backend ve telefon aynı ağda olmalı; bilgisayar IP'sini kullanın (localhost telefonda çalışmaz).

## Çalıştırma

```bash
npx expo start
```

Ardından Expo Go ile QR kodu tarayın.

## Özellikler

- Giriş (e-posta/şifre → Sanctum token)
- Sevkiyat listesi (filtre: Tümü, Atandı, Yolda, Teslim)
- Sevkiyat detay: durum güncelleme, konum gönder, POD yükle (galeri)

Backend API: `docs/api/driver-mobile.md`
