---
name: pest-testing
description: Pest v3 ile PHP test yazımı. Test oluşturma, assertion'lar, dataset'ler, mocking, Pest Laravel helper'ları, feature/unit testler. "test", "spec", "TDD", "expects", "assertion", "coverage", "feature test", "unit test" anahtar kelimeleri geçtiğinde veya bir özelliğin çalışıp çalışmadığı doğrulanması gerektiğinde kullanılır.
---

# Pest Testing Skill

Bu projede **Pest v3** ve PHPUnit v11 kullanılıyor. Tam kurallar için `.cursor/rules/laravel-boost.mdc` geçerlidir.

## Ne Zaman Uygulanır

- Yeni veya mevcut feature/unit test yazılırken veya güncellenirken
- Pest assertion, dataset, mock veya helper konuşulduğunda
- API veya Eloquent davranışını doğrulamak gerektiğinde
- "test", "spec", "TDD", "expects", "assert", "coverage" denildiğinde

## Hızlı Akış

1. **Oluşturma:** `php artisan make:test --pest {Name}` (feature) veya `--unit` (unit)
2. **Konum:** `tests/Feature/` veya `tests/Unit/`
3. **Model:** Factory kullan; mevcut state'leri kontrol et.
4. **Assert:** `assertSuccessful`, `assertNotFound`, `assertForbidden` — `assertStatus(403)` değil.
5. **Çalıştır:** `php artisan test --compact --filter=TestAdi`

## Test Yazım Stili

```php
it('yeni sipariş oluşturur', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('admin.orders.store'), [
            'customer_id' => $customer->id,
            'description' => 'Test siparişi',
        ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('orders', ['description' => 'Test siparişi']);
});
```

## HTTP Assertion Tablosu

| Durum | Doğru | Yanlış |
|-------|-------|--------|
| 200-299 | `assertSuccessful()` | `assertStatus(200)` |
| 404 | `assertNotFound()` | `assertStatus(404)` |
| 403 | `assertForbidden()` | `assertStatus(403)` |
| 302 | `assertRedirect()` | `assertStatus(302)` |

## Dataset (Validasyon Testi)

```php
it('geçersiz email reddeder', function (string $email) {
    $response = $this->postJson(route('admin.users.store'), ['email' => $email]);
    $response->assertUnprocessable();
})->with([
    'boş' => '',
    'geçersiz format' => 'notanemail',
]);
```

## Mocking

```php
use function Pest\Laravel\mock;

mock(AIService::class)
    ->shouldReceive('analyze')
    ->once()
    ->andReturn(['result' => 'ok']);
```

## Pest Laravel Helper'ları

```php
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

actingAs($user);
get(route('admin.orders.index'))->assertSuccessful();
```

## Çalıştırma

```bash
php artisan test --compact
php artisan test --compact tests/Feature/Order/OrderTest.php
php artisan test --compact --filter="sipariş oluştur"
```
