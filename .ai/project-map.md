# Project Map

**Stack:** PHP 8.2.12 / Laravel 12 / MSSQL (sqlsrv) / Bootstrap 5 + Tailwind CSS v4 / Pest v3
**Purpose:** Anti-hallucination reference. Load this before guessing any file path or class name.

---

## Directory Structure

```
app/
├── AI/            Jobs/RunAIAnalysisJob, Services/{AIService, AIOperationsService, AIFinanceService, AIHRService, AIFleetService, AIDocumentService}
├── Core/          Middleware/{RoleMiddleware, PermissionMiddleware, ActiveCompanyMiddleware},
│                  Scopes/CompanyScope, Services/{AuditService, ExportService, GeocodingService}
├── Customer/      Controllers/Web/{CustomerController, CustomerPortalController}
├── Delivery/      Controllers/Web/DeliveryImportController,
│                  Jobs/ProcessDeliveryImportJob,
│                  Services/{DeliveryReportImportService, DeliveryReportPivotService,
│                            LocationMatchingService, AutoOrderCreationService}
├── Document/      Controllers/Web/DocumentController
├── Driver/        Controllers/Api/DriverController
├── Employee/      Controllers/Web/{EmployeeController, AdvanceController, LeaveController,
│                                   PayrollController, PersonnelAttendanceController},
│                  Controllers/Api/EmployeeApiController,
│                  Services/EmployeeService, Policies/EmployeePolicy,
│                  Requests/{StoreEmployeeRequest, UpdateEmployeeRequest}
├── Enums/         (shared enums)
├── Excel/         Jobs/ProcessExcelJob,
│                  Services/{ExcelImportService, AnalysisService, BillingService, PeriodCalculationService}
├── Finance/       Controllers/Web/PaymentController, Services/FinanceDashboardService
├── FuelPrice/     Controllers/Web/FuelPriceController
├── Http/          Controllers/{Admin/{CompanyController, UserController, ProfileController, SettingsController},
│                               Auth/AuthenticatedSessionController}
├── Integration/   Jobs/{SendToLogoJob, SendToPythonJob},
│                  Services/{LogoIntegrationService, PythonBridgeService}
├── Models/        47 Eloquent models — ALL models are here, never per-domain
├── Notification/  Console/Commands/, Controllers/Web/NotificationController
├── Order/         Controllers/{Web/OrderController, Api/OrderApiController},
│                  Services/{OrderService, OperationsPerformanceService},
│                  Policies/OrderPolicy, Requests/{StoreOrderRequest, UpdateOrderRequest}
├── Providers/     AppServiceProvider
├── Shipment/      Controllers/Web/ShipmentController
├── Shift/         Controllers/Web/ShiftController
├── Vehicle/       Controllers/{Web/VehicleController, Api/VehicleApiController},
│                  Services/VehicleService, Policies/VehiclePolicy,
│                  Requests/{StoreVehicleRequest, UpdateVehicleRequest}
├── Warehouse/     Controllers/{Web/WarehouseController, Api/BarcodeController},
│                  Services/...
└── WorkOrder/     Controllers/Web/WorkOrderController
```

---

## Routes

| File | Prefix | Middleware | Notes |
|---|---|---|---|
| `routes/web.php` | `/` | guest / auth | Auth, geocoding, personel resource |
| `routes/admin.php` | `/admin` | auth + active.company | All admin CRUD routes |
| `routes/customer.php` | `/customer` | auth + role:customer | Self-service portal |
| `routes/api.php` | `/api/v1` | auth:sanctum | Driver, warehouse, order APIs |
| `routes/console.php` | — | — | Scheduled jobs (Laravel 12 style) |

---

## Key Models (app/Models/)

| Model | Table | Key Notes |
|---|---|---|
| User | users | BelongsToMany companies via user_companies |
| Company | companies | Tax info, e-invoice tags, API key; HasMany branches/settings/addresses |
| CompanySetting | company_settings | Key-value store per company |
| Branch | branches | Company sub-unit |
| Department | departments | Branch sub-unit |
| Position | positions | Job title |
| Order | orders | Customer orders; HasMany shipments, deliveryNumbers |
| Shipment | shipments | BelongsTo order, vehicle, driver (Employee) |
| Customer | customers | HasMany orders, payments, favoriteAddresses |
| FavoriteAddress | favorite_addresses | Saved delivery addresses |
| OrderTemplate | order_templates | Reusable order stubs |
| DeliveryImportBatch | delivery_import_batches | Excel import batch; invoice_status (pending/created/sent); petrokok_route_preference (ekinciler/isdemir) |
| DeliveryReportRow | delivery_report_rows | row_data = nvarchar(max) array; indices map to config/delivery_report.php headers |
| DeliveryNumber | delivery_numbers | BelongsTo batch, location; links delivery to order |
| Vehicle | vehicles | Plate, brand, capacity (kg/m³), status |
| VehicleInspection | vehicle_inspections | Periodic inspection records |
| VehicleDamage | vehicle_damages | Damage reports |
| Employee | employees | Staff; BelongsTo branch, position; HasMany leaves, advances, payrolls |
| Personel | personels | Personnel identity/contact info (separate from Employee) |
| PersonnelAttendance | personnel_attendance | Daily attendance records |
| Leave | leaves | Leave requests |
| Advance | advances | Salary advances |
| Payroll | payrolls | Monthly payroll |
| ShiftTemplate | shift_templates | Shift type definitions |
| ShiftSchedule | shift_schedules | Recurring schedules |
| ShiftAssignment | shift_assignments | Employee shift assignments |
| Warehouse | warehouses | BelongsTo company, branch; HasMany locations |
| WarehouseLocation | warehouse_locations | Storage slots |
| InventoryItem | inventory_items | SKUs |
| InventoryStock | inventory_stocks | Stock level at location |
| Payment | payments | Polymorphic (customer/vendor payments) |
| Document | documents | File storage, versioning |
| WorkOrder | work_orders | Maintenance/repair tasks |
| ServiceProvider | service_providers | External vendors |
| FuelPrice | fuel_prices | Fuel cost tracking |
| Notification | notifications | System notifications |
| AiReport | ai_reports | AI-generated analysis reports |
| AuditLog | audit_logs | Change log |
| Location | locations | Delivery locations |
| Country | countries | — |
| City | cities | BelongsTo country |
| District | districts | BelongsTo city |
| Neighborhood | neighborhoods | BelongsTo district |
| CompanyAddress | company_addresses | Company address records |
| CompanyDigitalService | company_digital_services | E-invoice/e-waybill subscriptions |
| CustomRole | custom_roles | — |
| CustomPermission | custom_permissions | — |

---

## Multi-Tenant Pattern

```
session('active_company_id')  ← active company stored in session
CompanyScope                   ← global scope, auto-filters by company_id
user_companies (pivot)         ← controls which companies a user can access
POST /admin/companies/switch   ← company switch endpoint
```

Cross-company queries require: `->withoutGlobalScope(CompanyScope::class)`

---

## Auth / Authorization

- **Session auth:** Laravel default (web guard)
- **API auth:** Laravel Sanctum (Bearer token, drivers)
- **Roles:** `CustomRole` model, `role_user` pivot, `RoleMiddleware`
- **Permissions:** `CustomPermission` model, `permission_role` pivot, `PermissionMiddleware`
- **NOT Spatie:** do not add `spatie/laravel-permission`
- **Policies:** OrderPolicy, EmployeePolicy, VehiclePolicy

---

## Key Config Files

| File | Purpose |
|---|---|
| `config/delivery_report.php` | Report types, column headers, date/numeric indices, pivot dimensions, invoice line mapping. CRITICAL: header array positions map to `row_data` integer indices in DB. |
| `config/activitylog.php` | Spatie activity log |
| `config/permission.php` | Custom permission config |
| `config/database.php` | MSSQL sqlsrv connection |

---

## Database

- **Engine:** MS SQL Server (sqlsrv driver)
- **Migrations:** 70 files in `database/migrations/`
- **Conventions:** No `unsigned`, use `datetime2`, `nvarchar` for text, soft deletes on all core tables
- **JSON storage:** `nvarchar(max)` with `'array'` cast in model

---

## Queue / Jobs

| Job | Module | Trigger |
|---|---|---|
| RunAIAnalysisJob | AI | Scheduled / manual |
| ProcessDeliveryImportJob | Delivery | After Excel upload |
| ProcessExcelJob | Excel | Manual trigger |
| SendToLogoJob | Integration | After invoice creation |
| SendToPythonJob | Integration | Data pipeline trigger |

**Backend:** Redis. **Pattern:** `{Action}{Subject}Job::dispatch($model)` in controllers.

---

## Frontend

| Layer | Tech | Usage |
|---|---|---|
| Layout/Components | Bootstrap 5.3 | Nav, grid, cards, tables, modals, forms |
| Utilities | Tailwind CSS v4 | Spacing, color, flex/grid overrides |
| Interactivity | Alpine.js | `x-data`, `x-show`, `x-bind` |
| Build | Vite 7 | Asset bundling (`npm run build`) |

Views: `resources/views/admin/{module}/`, `resources/views/customer/`, `resources/views/layouts/`

---

## Testing

```bash
php artisan test --compact                    # run all tests
php artisan test --compact --filter=Delivery  # filter by module
```

Test files: `tests/Feature/{Auth/, CompanySwitchTest, DeliveryImportTest, FuelPriceTest, LeaveTest, NotificationTest}`

---

## Protect These Files

Never modify:
- `AGENTS.md`, `CLAUDE.md`
- `.cursor/`
- `.mcp.json`, `boost.json`
- `.ai/boost/`, `.ai/boost-main/`
