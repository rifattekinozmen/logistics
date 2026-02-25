# Session State

**Last updated:** 2026-02-25
**Branch:** main
**Status:** Active development

## Current Focus

Faz 2/3 backlog: queue yapılandırması, Order/Shipment testleri, AIFleetService gelişmiş kurallar.

Recent completed work (2026-02-25):
- AI anomaly: `AIFinanceService::detectOverdueAnomaly`, `AIFleetService::analyze()` (bakım + filo kullanım anomali), `ai_reports` yazımı
- Dashboard: yüksek öncelikli AI uyarı sayısı ve banner (kritik/high)
- Faz 3 GPS: `vehicle_gps_positions` tablosu, `VehicleGpsPosition` modeli, API placeholder, VehicleGpsApiTest
- `AIDocumentService::analyze()`: süresi dolacak belgeler + eksik file_path/category, `RunAIAnalysisJob` entegrasyonu
- Python POC genişletme: `buildFuelAndShipmentsPayload`, `pushFuelAndShipmentsToPython`, `python:push-fuel-shipments` komutu, haftalık schedule, PythonBridgeTest güncellemesi
- DeliveryReportPivotService edge-case testleri: boş satır (buildPivot/buildInvoiceLines []), grupsuz fatura (groupBy=false 2 satır), eksik/boş miktar (0.0)

Earlier:
- Queue tries + dokümantasyon, Order/Shipment 404 ve filtre testleri, AIFleetService eşik sabitleri
- DeliveryReportPivotService eager loading, pivot/invoice CSV export, DeliveryReportPivotTest, DeliveryReportExportTest
- PythonBridgeService POC: analytics:push-python, SendToPythonJob, PythonBridgeTest

## Active Areas

| Module | File(s) | Status |
|---|---|---|
| Delivery | `DeliveryReportPivotService`, `DeliveryImportController` | Active |
| AI | `AIFleetService`, `AIFinanceService`, `AIDocumentService`, `RunAIAnalysisJob` | Active |
| Vehicle | `VehicleGpsPosition`, `VehicleGpsController` (API) | Placeholder |
| Finance | `FinanceDashboardService`, `PaymentController` | Stable |
| Employee | `PersonnelAttendanceController`, `Personel` model | Stable |

## Pending Work (Known TODOs)

- Queue: job'lara queue adı ve tries atanması, kritik/non-kritik ayrımı dokümante
- Order/Shipment: edge-case feature testleri (filtre, 404, status geçişleri)
- AIFleetService: bakım eşikleri ve kuralların zenginleştirilmesi
- Python bridge: POC genişletme (ek veri setleri)
- Flutter/React Native mobile app (Faz 2 — not started)

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

1. Queue: RunAIAnalysisJob, SendToLogoJob, ProcessDeliveryImportJob vb. için `$queue` / `$tries` tanımla; docs veya yorumla kritik/non-kritik notu ekle.
2. Order ve Shipment modülleri için edge-case feature testleri (filtre, 404, geçersiz durum).
3. AIFleetService: bakım eşikleri (muayene süresi, km) ve ek kural (çok eski muayene = high).

## Session Archive

Previous session summaries: `docs/sessions/2026-02-21-vite-integration-and-project-gaps.md`
