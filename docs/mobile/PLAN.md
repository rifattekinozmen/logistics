# Mobil Uygulama Planı (Driver / Saha)

**Hedef:** Laravel backend (Driver API v1/v2) kullanan Expo (React Native) mobil uygulama. Şoför sevkiyat listesi, durum güncelleme, POD yükleme ve konum gönderme.

**Stack:** Expo (SDK 52+) + React Navigation + Sanctum token. API: `docs/api/driver-mobile.md`.

---

## Faz 1 – İskelet ve Auth
- [x] Plan dokümanı
- [ ] Expo projesi `mobile/` (create-expo-app)
- [ ] API client (base URL, Sanctum Bearer token)
- [ ] Login ekranı (email + password → POST /login → token)
- [ ] Token saklama (SecureStore) + auth context
- [ ] Tab/drawer: Giriş yoksa Login, varsa Ana sayfa

## Faz 2 – Sevkiyatlar
- [ ] GET /api/v1/driver/shipments → liste ekranı
- [ ] Filtre (status query)
- [ ] Sevkiyat detay ekranı (order_number, adresler, status)
- [ ] Durum güncelleme: PUT .../status (assigned → loaded → in_transit → delivered)

## Faz 3 – POD ve Konum
- [ ] POD yükleme: seçilen sevkiyat için POST .../pod (multipart, pod_file)
- [ ] Konum: POST /api/v1/driver/location (latitude, longitude, shipment_id?)
- [ ] v2: GET /api/v2/driver/dashboard, POST /api/v2/driver/checkin (opsiyonel)

## Faz 4 – İyileştirmeler
- [ ] Çekme-yenile (pull-to-refresh)
- [ ] Hata/offline mesajları
- [ ] .env (EXPO_PUBLIC_API_URL) ile API base

---

**Ortam:** `mobile/.env` veya `app.config.js` ile `EXPO_PUBLIC_API_URL=https://...` (Laravel backend). Geliştirme: `http://localhost` veya bilgisayar IP (Expo Go aynı ağda).
