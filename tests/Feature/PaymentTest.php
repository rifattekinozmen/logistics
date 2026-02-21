<?php

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;

it('can create a payment', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.payments.store'), [
            'related_type' => Customer::class,
            'related_id' => $customer->id,
            'payment_type' => 'incoming',
            'amount' => 5000.00,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 0,
            'notes' => 'Test payment',
        ]);

    $response->assertRedirect();
    expect(Payment::count())->toBe(1);
    expect(Payment::first()->amount)->toEqual('5000.00');
});

it('can list payments', function () {
    [$user, $company] = createAdminUser();
    Payment::factory()->count(3)->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.payments.index'));

    $response->assertSuccessful();
    $response->assertViewHas('payments');
});

it('can show a payment', function () {
    [$user, $company] = createAdminUser();
    $payment = Payment::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->get(route('admin.payments.show', $payment));

    $response->assertSuccessful();
    $response->assertViewHas('payment');
});

it('can update a payment', function () {
    [$user, $company] = createAdminUser();
    $payment = Payment::factory()->pending()->create(['amount' => 1000]);

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->put(route('admin.payments.update', $payment), [
            'related_type' => $payment->related_type,
            'related_id' => $payment->related_id,
            'payment_type' => $payment->payment_type,
            'amount' => 1500.00,
            'due_date' => $payment->due_date->format('Y-m-d'),
            'status' => $payment->status,
        ]);

    $response->assertRedirect();
    expect($payment->fresh()->amount)->toEqual('1500.00');
});

it('can delete a payment', function () {
    [$user, $company] = createAdminUser();
    $payment = Payment::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->delete(route('admin.payments.destroy', $payment));

    $response->assertRedirect();
    expect(Payment::count())->toBe(0);
});

it('has polymorphic relationship', function () {
    $customer = Customer::factory()->create();
    $payment = Payment::factory()->create([
        'related_type' => Customer::class,
        'related_id' => $customer->id,
    ]);

    expect($payment->related)->toBeInstanceOf(Customer::class);
    expect($payment->related->id)->toBe($customer->id);
});

it('belongs to a creator', function () {
    $creator = User::factory()->create();
    $payment = Payment::factory()->create(['created_by' => $creator->id]);

    expect($payment->creator)->toBeInstanceOf(User::class);
    expect($payment->creator->id)->toBe($creator->id);
});

it('can be marked as paid', function () {
    $payment = Payment::factory()->pending()->create();

    expect($payment->status)->toBe(0);
    expect($payment->paid_date)->toBeNull();

    $payment->update([
        'status' => 1,
        'paid_date' => now(),
        'payment_method' => 'bank_transfer',
        'reference_number' => 'REF-12345678',
    ]);

    expect($payment->fresh()->status)->toBe(1);
    expect($payment->fresh()->paid_date)->not->toBeNull();
});

it('can identify overdue payments', function () {
    $overdue = Payment::factory()->overdue()->create();
    $pending = Payment::factory()->pending()->create([
        'due_date' => now()->addDays(10),
    ]);

    expect($overdue->due_date)->toBeLessThan(now());
    expect($overdue->status)->toBe(0);
    expect($pending->due_date)->toBeGreaterThan(now());
});

it('requires authentication to access payment routes', function () {
    $payment = Payment::factory()->create();

    $this->get(route('admin.payments.index'))
        ->assertRedirect('/login');

    $this->get(route('admin.payments.show', $payment))
        ->assertRedirect('/login');
});

it('stores amount with proper decimals', function () {
    $payment = Payment::factory()->create(['amount' => 12345.67]);

    expect($payment->amount)->toEqual('12345.67');
});

it('validates payment type', function () {
    [$user, $company] = createAdminUser();
    $customer = Customer::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['active_company_id' => $company->id])
        ->post(route('admin.payments.store'), [
            'related_type' => Customer::class,
            'related_id' => $customer->id,
            'payment_type' => 'invalid_type',
            'amount' => 5000.00,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 0,
        ]);

    $response->assertSessionHasErrors(['payment_type']);
});

it('calculates days until due date', function () {
    $payment = Payment::factory()->create([
        'due_date' => now()->addDays(15)->startOfDay(),
        'status' => 0,
    ]);

    $daysUntilDue = now()->startOfDay()->diffInDays($payment->due_date, false);

    expect($daysUntilDue)->toBeGreaterThanOrEqual(14);
    expect($daysUntilDue)->toBeLessThanOrEqual(15);
});
