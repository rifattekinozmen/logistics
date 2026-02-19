# Session State

**Last updated:** 2026-02-19
**Branch:** main
**Status:** Active development

## Current Focus

Delivery import pipeline (DeliveryImportBatch / DeliveryReportPivotService).

Recent completed work (last 5 commits):
- `invoice_status` field on `delivery_import_batches` (pending/created/sent)
- `petrokok_route_preference` field (ekinciler/isdemir) on `delivery_import_batches`
- `DeliveryReportPivotService` — company-specific material tracking, BOŞ-DOLU/DOLU-DOLU formulas
- Pivot view enhancements: material codes, short descriptions, route details
- Pagination support (10/20 items) in DeliveryImportController

## Active Areas

| Module | File(s) | Status |
|---|---|---|
| Delivery | `DeliveryReportPivotService`, `DeliveryImportController` | Active |
| Finance | `FinanceDashboardService`, `PaymentController` | Stable |
| Employee | `PersonnelAttendanceController`, `Personel` model | Stable |
| AI | `AIOperationsService`, `AIFinanceService` | Partial |

## Pending Work (Known TODOs)

- Logo integration (`LogoIntegrationService`) — invoice export to LOGO ERP
- Python bridge (`PythonBridgeService`) — data pipeline to Python backend
- AI submodule expansion: AIHRService, AIFleetService, AIDocumentService (stubs in docs, not yet implemented)
- E-Fatura / E-Arşiv XML export
- Flutter/React Native mobile app (Faz 2 — not started)
- Performance indexing on `delivery_report_rows` (large table, batch_id index needed)
- Queue optimization review

## Module Status Snapshot

| Module | Models | Controller | Service | Tests |
|---|---|---|---|---|
| Auth/Core | User, CustomRole, CustomPermission | AuthenticatedSessionController | AuditService | Auth/ |
| Company | Company, CompanySetting, CompanyAddress, Branch | CompanyController | — | CompanySwitchTest |
| Order | Order, OrderTemplate | OrderController (Web+API) | OrderService, OperationsPerformanceService | — |
| Delivery | DeliveryImportBatch, DeliveryNumber, DeliveryReportRow | DeliveryImportController | DeliveryReportImportService, DeliveryReportPivotService, LocationMatchingService, AutoOrderCreationService | DeliveryImportTest |
| Shipment | Shipment | ShipmentController | — | — |
| Vehicle | Vehicle, VehicleInspection, VehicleDamage | VehicleController (Web+API) | VehicleService | — |
| Employee/HR | Employee, Personel, PersonnelAttendance, Leave, Advance, Payroll | EmployeeController, AdvanceController, LeaveController, PayrollController, PersonnelAttendanceController | EmployeeService | LeaveTest |
| Warehouse | Warehouse, WarehouseLocation, InventoryItem, InventoryStock | WarehouseController, BarcodeController | — | — |
| Finance | Payment | PaymentController | FinanceDashboardService | — |
| FuelPrice | FuelPrice | FuelPriceController | — | FuelPriceTest |
| Customer | Customer, FavoriteAddress, OrderTemplate | CustomerController, CustomerPortalController | — | — |
| Driver | — | DriverController (API) | — | — |
| Document | Document | DocumentController | — | — |
| WorkOrder | WorkOrder, ServiceProvider | WorkOrderController | — | — |
| Shift | ShiftTemplate, ShiftSchedule, ShiftAssignment | ShiftController | — | — |
| Notification | Notification | NotificationController | — | NotificationTest |
| AI | AiReport | — | AIService, AIOperationsService, AIFinanceService | — |
| Integration | — | — | LogoIntegrationService, PythonBridgeService | — |
| Location | Country, City, District, Neighborhood, Location | GeocodingController | GeocodingService | — |
| Excel | — | — | ExcelImportService, AnalysisService, BillingService, PeriodCalculationService | — |

## Safe Next Actions

1. Add index on `delivery_report_rows.delivery_import_batch_id` (performance — large table)
2. Implement `LogoIntegrationService::exportInvoice()` (pending integration)
3. Expand AI module: add `AIHRService` for personnel analytics
4. Add missing feature tests for Order and Shipment modules
5. Optimize `DeliveryReportPivotService` eager loading

## Session Archive

Previous session summaries: `docs/sessions/` (empty — first structured session)
