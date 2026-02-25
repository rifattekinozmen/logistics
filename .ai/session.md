# Session State

**Last updated:** 2026-02-21
**Branch:** main
**Status:** Active development

## Current Focus

Documentation & backlog alignment, analytics dashboards ve delivery import pipeline (DeliveryImportBatch / DeliveryReportPivotService).

Recent completed work (last session):
- Vite entegrasyonu: layouts.app'e @vite eklendi, CDN Bootstrap kaldırıldı — HMR aktif
- Document modülü: DocumentFactory, DocumentTest (7 test), controller schema uyumu (category/valid_until)
- README HMR notu, Development guide TODO referansı, docs/sessions arşiv

Recent completed work (last 5 commits):
- `DeliveryReportPivotService` eager loading optimization (reportRows N+1 fix)
- Index on `delivery_report_rows.delivery_import_batch_id` (migration applied)
- `invoice_status` field on `delivery_import_batches` (pending/created/sent)
- `petrokok_route_preference` field (ekinciler/isdemir) on `delivery_import_batches`
- Pivot view enhancements: material codes, short descriptions, route details

## Active Areas

| Module | File(s) | Status |
|---|---|---|
| Delivery | `DeliveryReportPivotService`, `DeliveryImportController` | Active |
| Finance | `FinanceDashboardService`, `PaymentController` | Stable |
| Employee | `PersonnelAttendanceController`, `Personel` model | Stable |
| AI | `AIOperationsService`, `AIFinanceService`, `AIHRService` | Partial |

## Pending Work (Known TODOs)

- Python bridge (`PythonBridgeService`) — ileri seviye analizler için veri hattı (advanced analytics POC)
- Advanced AI capabilities: anomaly detection ve daha derin içgörüler için `AIFleetService`, `AIDocumentService`, `AIFinanceService` üzerinde genişletmeler
- Flutter/React Native mobile app (Faz 2 — not started)
- Queue optimization review (AI, Logo, Excel, notification job'ları için)

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
| Document | Document | DocumentController | — | DocumentTest |
| WorkOrder | WorkOrder, ServiceProvider | WorkOrderController | — | — |
| Shift | ShiftTemplate, ShiftSchedule, ShiftAssignment | ShiftController | — | — |
| Notification | Notification | NotificationController | — | NotificationTest |
| AI | AiReport | — | AIService, AIOperationsService, AIFinanceService, AIHRService, AIFleetService, AIDocumentService | — |
| Integration | — | — | LogoIntegrationService, PythonBridgeService | — |
| Location | Country, City, District, Neighborhood, Location | GeocodingController | GeocodingService | — |
| Excel | — | — | ExcelImportService, AnalysisService, BillingService, PeriodCalculationService | — |

## Safe Next Actions

1. Order ve Shipment modülleri için eksik edge-case feature testlerini güçlendir (gerekli yerlerde kapsamı genişlet).
2. `AIFleetService` içinde bakım tahmini metrikleri ve eşik değerlerini zenginleştir (advanced rules).
3. `AIDocumentService` için daha zengin belge sınıflandırma ve compliance kontrol senaryoları ekle.
4. Python bridge (`PythonBridgeService`) için data pipeline POC tasarla ve entegre et.

## Session Archive

Previous session summaries: `docs/sessions/2026-02-21-vite-integration-and-project-gaps.md`
