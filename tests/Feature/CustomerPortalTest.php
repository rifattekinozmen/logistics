<?php

use App\Models\Customer;
use App\Models\CustomPermission;
use App\Models\CustomRole;
use App\Models\FavoriteAddress;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderTemplate;
use App\Models\Payment;
use App\Models\User;

it('customer can access dashboard', function () {
    [$user, $company] = createCustomerUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.dashboard'));

    $response->assertSuccessful();
});

it('customer can view their orders', function () {
    [$user, $company, $customer] = createCustomerUser();
    Order::factory()->count(3)->create(['customer_id' => $customer->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.orders.index'));

    $response->assertSuccessful();
    $response->assertViewHas('orders');
});

it('customer can create an order', function () {
    [$user, $company, $customer] = createCustomerUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.orders.store'), [
            'pickup_address' => 'İstanbul, Türkiye',
            'delivery_address' => 'Ankara, Türkiye',
            'planned_pickup_date' => now()->addDays(3)->format('Y-m-d'),
            'planned_delivery_date' => now()->addDays(5)->format('Y-m-d'),
            'total_weight' => 1500,
            'total_volume' => 10,
            'notes' => 'Test order from portal',
        ]);

    $response->assertRedirect();
    expect(Order::where('customer_id', $customer->id)->count())->toBe(1);
});

it('customer can view order details', function () {
    [$user, $company, $customer] = createCustomerUser();
    $order = Order::factory()->create(['customer_id' => $customer->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.orders.show', $order));

    $response->assertSuccessful();
    $response->assertViewHas('order');
});

it('customer can cancel their order', function () {
    [$user, $company, $customer] = createCustomerUser();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.orders.cancel', $order));

    $response->assertRedirect();
    expect($order->fresh()->status)->toBe('cancelled');
});

it('customer cannot cancel delivered order', function () {
    [$user, $company, $customer] = createCustomerUser();
    $order = Order::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'delivered',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.orders.cancel', $order));

    $response->assertStatus(403);
});

it('customer can view their documents', function () {
    [$user, $company, $customer] = createCustomerUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.documents.index'));

    $response->assertSuccessful();
});

it('customer can view their payments', function () {
    [$user, $company, $customer] = createCustomerUser();
    Payment::factory()->count(2)->create([
        'related_type' => Customer::class,
        'related_id' => $customer->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.payments.index'));

    $response->assertSuccessful();
    $response->assertViewHas('payments');
});

it('customer can manage favorite addresses', function () {
    [$user, $company, $customer] = createCustomerUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.favorite-addresses.store'), [
            'name' => 'Ev Adresi',
            'type' => 'both',
            'address' => 'İstanbul, Kadıköy',
            'latitude' => 40.9880,
            'longitude' => 29.0256,
        ]);

    $response->assertRedirect();
    expect(FavoriteAddress::where('customer_id', $customer->id)->count())->toBe(1);
});

it('customer can create order template', function () {
    [$user, $company, $customer] = createCustomerUser();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.order-templates.store'), [
            'name' => 'Standart Sevkiyat',
            'pickup_address' => 'İstanbul',
            'delivery_address' => 'Ankara',
            'total_weight' => 1000,
            'total_volume' => 5,
        ]);

    $response->assertRedirect();
    expect(OrderTemplate::where('customer_id', $customer->id)->count())->toBe(1);
});

it('customer can create order from template', function () {
    [$user, $company, $customer] = createCustomerUser();
    $template = OrderTemplate::factory()->create(['customer_id' => $customer->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.order-templates.create-order', $template), [
            'planned_pickup_date' => now()->addDays(2)->format('Y-m-d'),
            'planned_delivery_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

    $response->assertRedirect();
    expect(Order::where('customer_id', $customer->id)->count())->toBeGreaterThan(0);
});

it('customer can view notifications', function () {
    [$user, $company, $customer] = createCustomerUser();
    Notification::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.notifications.index'));

    $response->assertSuccessful();
});

it('customer can mark notification as read', function () {
    [$user, $company, $customer] = createCustomerUser();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('customer.notifications.mark-read', $notification));

    $response->assertRedirect();
    expect($notification->fresh()->is_read)->toBeTrue();
});

it('customer cannot access other customers orders', function () {
    [$user, $company, $customer] = createCustomerUser();
    $otherCustomer = Customer::factory()->create();
    $otherOrder = Order::factory()->create(['customer_id' => $otherCustomer->id]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('customer.orders.show', $otherOrder));

    $response->assertStatus(403);
});

it('requires authentication to access customer portal', function () {
    $this->get(route('customer.dashboard'))
        ->assertRedirect('/login');
});

/**
 * Müşteri rolü ve müşteri kaydı olan kullanıcı oluşturur.
 *
 * @return array{0: \App\Models\User, 1: \App\Models\Company, 2: \App\Models\Customer}
 */
function createCustomerUser(): array
{
    $role = CustomRole::firstOrCreate(
        ['name' => 'customer'],
        ['description' => 'Müşteri']
    );

    // Müşteri portal izinleri
    $permissions = [
        'customer.portal.dashboard',
        'customer.portal.orders.view',
        'customer.portal.orders.create',
        'customer.portal.orders.cancel',
        'customer.portal.documents.view',
        'customer.portal.payments.view',
        'customer.portal.notifications.view',
        'customer.portal.favorite-addresses.manage',
        'customer.portal.order-templates.manage',
        'customer.portal.profile.view',
        'customer.portal.profile.update',
    ];

    foreach ($permissions as $permCode) {
        $perm = CustomPermission::firstOrCreate(['code' => $permCode]);
        if (! $role->permissions()->where('custom_permission_id', $perm->id)->exists()) {
            $role->permissions()->attach($perm->id);
        }
    }

    $company = \App\Models\Company::factory()->create();
    $customer = Customer::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'customer', 'is_default' => true]);
    $user->roles()->attach($role->id);

    // Müşteriyi kullanıcıya bağla (CustomerPortal email ile eşleştirir)
    $customer->update(['email' => $user->email]);

    return [$user, $company, $customer];
}
