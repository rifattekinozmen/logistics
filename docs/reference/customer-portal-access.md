# Müşteri Portalına Giriş Rehberi

## Gereksinimler

Müşteri portalına giriş yapabilmek için:

1. **Kullanıcı hesabı** - Sistemde bir kullanıcı hesabı olmalı
2. **Customer rolü** - Kullanıcının "customer" rolüne sahip olması gerekiyor
3. **Customer kaydı** - `customers` tablosunda kullanıcının email'i ile eşleşen bir müşteri kaydı olmalı

## Hızlı Kurulum

### 1. Customer Rolünü Oluştur

```bash
php artisan db:seed --class=CustomRoleSeeder
```

Bu komut "customer" rolünü oluşturur.

### 2. Müşteri Kullanıcısı Oluştur

```bash
php artisan customer:create-user musteri@example.com --name="Test Müşteri"
```

Bu komut:
- Kullanıcı hesabı oluşturur
- Customer rolünü atar
- Customer kaydı oluşturur/günceller

**Örnek:**
```bash
php artisan customer:create-user test@musteri.com --name="Test Müşteri A.Ş." --password="test123"
```

### 3. Giriş Yap

1. Tarayıcıda `/login` sayfasına gidin
2. Oluşturduğunuz email ve şifre ile giriş yapın
3. Giriş yaptıktan sonra:
   - Admin panelinde sidebar'da "Müşteri Portalı" linkine tıklayın
   - Veya direkt `/customer/dashboard` adresine gidin

## Manuel Kurulum

### 1. Kullanıcı Oluştur

Admin panelinden veya tinker ile:

```php
$user = \App\Models\User::create([
    'name' => 'Müşteri Adı',
    'email' => 'musteri@example.com',
    'username' => 'musteri@example.com',
    'password' => 'şifre',
    'status' => 1,
]);
```

### 2. Customer Rolünü Ata

```php
$customerRole = \App\Models\CustomRole::where('name', 'customer')->first();
$user->roles()->attach($customerRole->id);
```

### 3. Customer Kaydı Oluştur

```php
\App\Models\Customer::create([
    'name' => 'Müşteri Adı',
    'email' => 'musteri@example.com',
    'status' => 1,
]);
```

## Önemli Notlar

- Customer kaydındaki `email` alanı, User tablosundaki `email` ile **tam olarak eşleşmelidir**
- Kullanıcı hem admin hem customer rolüne sahip olabilir (her iki panele de erişebilir)
- Customer portalı route'ları `/customer/*` prefix'i ile başlar
- Müşteri portalı sadece kendi siparişlerini ve belgelerini görebilir

## Route'lar

- `/customer/dashboard` - Müşteri dashboard
- `/customer/orders` - Sipariş listesi
- `/customer/orders/create` - Yeni sipariş
- `/customer/documents` - Belge listesi
- `/customer/profile` - Profil sayfası
