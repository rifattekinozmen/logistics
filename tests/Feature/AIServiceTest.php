<?php

use App\AI\Jobs\RunAIAnalysisJob;
use App\AI\Services\AIFinanceService;
use App\AI\Services\AIFleetService;
use App\AI\Services\AIHRService;
use App\Models\AiReport;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Vehicle;

it('can predict vehicle maintenance needs', function () {
    $vehicle = Vehicle::factory()->create();

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
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Vehicle::factory()->count(5)->create(['branch_id' => $branch->id]);

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
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Employee::factory()->count(10)->create(['branch_id' => $branch->id]);

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

it('AIFinanceService detectOverdueAnomaly returns null when no overdue payments', function () {
    Payment::query()->forceDelete();
    Payment::factory()->paid()->count(3)->create([
        'paid_date' => now()->subDays(10),
        'amount' => 10000,
    ]);

    $financeService = app(AIFinanceService::class);
    $result = $financeService->detectOverdueAnomaly();

    expect($result)->toBeNull();
});

it('AIFinanceService detectOverdueAnomaly returns report when overdue exceeds 1.5x avg monthly paid', function () {
    Payment::query()->forceDelete();
    Payment::factory()->paid()->count(3)->create([
        'paid_date' => now()->subDays(15),
        'amount' => 10000,
    ]);
    Payment::factory()->overdue()->create([
        'due_date' => now()->subDays(5),
        'amount' => 25000,
    ]);

    $financeService = app(AIFinanceService::class);
    $result = $financeService->detectOverdueAnomaly();

    expect($result)->toBeArray();
    expect($result)->toHaveKeys(['type', 'summary_text', 'severity', 'data_snapshot', 'generated_at']);
    expect($result['type'])->toBe('finance');
    expect($result['data_snapshot'])->toHaveKey('ratio');
    expect($result['data_snapshot']['ratio'])->toBeGreaterThanOrEqual(1.5);
});

it('AIFleetService analyze returns array of reports with correct shape', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Vehicle::factory()->count(3)->create(['branch_id' => $branch->id]);

    $fleetService = app(AIFleetService::class);
    $reports = $fleetService->analyze($company->id);

    expect($reports)->toBeArray();
    foreach ($reports as $report) {
        expect($report)->toHaveKeys(['type', 'summary_text', 'severity', 'data_snapshot', 'generated_at']);
        expect($report['type'])->toBe('fleet');
    }
});

it('AIFleetService analyze includes utilization anomaly when half or more vehicles are idle', function () {
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Vehicle::factory()->count(4)->create(['branch_id' => $branch->id]);

    $fleetService = app(AIFleetService::class);
    $reports = $fleetService->analyze($company->id);

    $utilizationReport = collect($reports)->first(fn ($r) => str_contains($r['summary_text'], 'atıl'));
    expect($utilizationReport)->not->toBeNull();
    expect($utilizationReport['data_snapshot'])->toHaveKey('idle_count');
});

it('RunAIAnalysisJob persists fleet reports to ai_reports when company has ai_enabled', function () {
    $company = Company::factory()->create(['is_active' => true]);
    $company->setSetting('ai_enabled', 'true');
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    Vehicle::factory()->count(3)->create(['branch_id' => $branch->id]);

    $initialCount = AiReport::count();
    (new RunAIAnalysisJob($company))->handle(
        app(\App\AI\Services\AIOperationsService::class),
        app(AIFinanceService::class),
        app(AIHRService::class),
        app(AIFleetService::class)
    );

    expect(AiReport::count())->toBeGreaterThan($initialCount);
    expect(AiReport::where('type', 'fleet')->exists())->toBeTrue();
});
