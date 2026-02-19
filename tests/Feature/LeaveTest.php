<?php

use App\Models\Employee;
use App\Models\Leave;

it('kullanıcı izin listesine erişebilir', function () {
    [$user] = createAdminUser();
    $this->actingAs($user);

    $response = $this->get(route('admin.leaves.index'));

    $response->assertSuccessful();
});

it('kullanıcı yeni izin talebi oluşturabilir', function () {
    [$user] = createAdminUser();
    $employee = Employee::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('admin.leaves.store'), [
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'start_date' => now()->addDays(7)->format('Y-m-d'),
        'end_date' => now()->addDays(10)->format('Y-m-d'),
        'reason' => 'Tatil',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('leaves', [
        'employee_id' => $employee->id,
        'leave_type' => 'annual',
        'status' => 'pending',
    ]);
});

it('yönetici izin talebini onaylayabilir', function () {
    [$user] = createAdminUser();
    $employee = Employee::factory()->create();
    $leave = Leave::factory()->create([
        'employee_id' => $employee->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    $response = $this->post(route('admin.leaves.approve', $leave), [
        'action' => 'approve',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('leaves', [
        'id' => $leave->id,
        'status' => 'approved',
        'approved_by' => $user->id,
    ]);
});
