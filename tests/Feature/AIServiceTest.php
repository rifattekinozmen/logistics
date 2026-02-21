<?php

use App\AI\Services\AIFleetService;
use App\AI\Services\AIHRService;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Vehicle;

it('can predict vehicle maintenance needs', function () {
    $vehicle = Vehicle::factory()->create([
        'current_mileage' => 85000,
    ]);

    $fleetService = app(AIFleetService::class);
    $prediction = $fleetService->predictMaintenanceNeeds($vehicle);

    expect($prediction)->toHaveKey('vehicle_id');
    expect($prediction)->toHaveKey('maintenance_score');
    expect($prediction)->toHaveKey('upcoming_maintenance');
    expect($prediction['vehicle_id'])->toBe($vehicle->id);
});

it('can analyze fuel consumption', function () {
    $vehicle = Vehicle::factory()->create();

    $fleetService = app(AIFleetService::class);
    $analysis = $fleetService->analyzeFuelConsumption($vehicle, 3);

    expect($analysis)->toHaveKey('vehicle_id');
    expect($analysis)->toHaveKey('metrics');
    expect($analysis)->toHaveKey('efficiency_score');
    expect($analysis['vehicle_id'])->toBe($vehicle->id);
});

it('can optimize fleet deployment', function () {
    $company = Company::factory()->create();
    Vehicle::factory()->count(5)->create(['company_id' => $company->id]);

    $fleetService = app(AIFleetService::class);
    $optimization = $fleetService->optimizeFleetDeployment($company->id);

    expect($optimization)->toHaveKey('total_vehicles');
    expect($optimization)->toHaveKey('utilization_data');
    expect($optimization['total_vehicles'])->toBeGreaterThanOrEqual(5);
});

it('can analyze employee performance', function () {
    $employee = Employee::factory()->create();

    $hrService = app(AIHRService::class);
    $performance = $hrService->analyzeEmployeePerformance($employee);

    expect($performance)->toHaveKey('employee_id');
    expect($performance)->toHaveKey('metrics');
    expect($performance)->toHaveKey('score');
    expect($performance['employee_id'])->toBe($employee->id);
});

it('can predict employee turnover', function () {
    $company = Company::factory()->create();
    Employee::factory()->count(10)->create(['company_id' => $company->id]);

    $hrService = app(AIHRService::class);
    $prediction = $hrService->predictTurnover($company);

    expect($prediction)->toHaveKey('company_id');
    expect($prediction)->toHaveKey('total_employees');
    expect($prediction)->toHaveKey('at_risk_count');
    expect($prediction['company_id'])->toBe($company->id);
});

it('provides actionable recommendations', function () {
    $vehicle = Vehicle::factory()->create();

    $fleetService = app(AIFleetService::class);
    $prediction = $fleetService->predictMaintenanceNeeds($vehicle);

    expect($prediction)->toHaveKey('recommendations');
    expect($prediction['recommendations'])->toBeArray();
    expect(count($prediction['recommendations']))->toBeGreaterThan(0);
});
