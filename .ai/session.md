# Session State

**Last updated:** 2026-02-28
**Branch:** main
**Status:** Faz 2/3 tamamlandı

## Current Focus

Faz 2/3 tamamlandı. Opsiyonel: SMS/WhatsApp sağlayıcı entegrasyonu, notification rate limit, mobil uygulama.

## Recent Completed Work (2026-02-28)

- **Reporting API:** finance-summary, fleet-utilization, operations-kpi; 5 dk cache; `throttle:reporting` (10/dk)
- **Bildirim:** NotificationChannelDispatcher; CheckDocumentExpiry/CheckPaymentDue’da SMS/WhatsApp sendForScenario; config admin_phone
- **Advanced AI skorları:** Fleet (maintenance_risk, fleet_avg_score, deviation, trend); Finance (risk_score, volatility); Document (document_risk_score); scoreToSeverity ortak
- **PythonBridge production:** config/python_bridge.php, enabled/retry/backoff; SendToPythonJob tries/backoff; disabled iken skip
- **Filo harita:** admin fleet-map sayfası, fleetMapPositions (company-scoped)
- **Analytics KPI:** edge-case testleri (sıfır teslimat, sıfır araç)
- **Queue, Order/Shipment testleri, AIFleetService, GPS, driver-mobile doc, kpi-overview, python-bridge doc** (önceki oturumlar)

## Active Areas

| Module | File(s) | Status |
|---|---|---|
| Analytics | `AnalyticsDashboardService`, `ReportingController` | Stable + cache + throttle |
| AI | `AIFleetService`, `AIFinanceService`, `AIDocumentService` | Advanced scoring |
| Notification | `NotificationChannelDispatcher`, Sms/WhatsappChannel, Commands | SMS/WhatsApp stub + scenario |
| Integration | `PythonBridgeService`, `SendToPythonJob`, config | Production config |
| Vehicle | `VehicleGpsPosition`, fleet-map view | Placeholder + admin map |

## Pending Work (Optional)

- SMS/WhatsApp gerçek sağlayıcı (Twilio vb.)
- Bildirim: aynı tip/gün rate limit (notification_logs)
- Mobil uygulama (Faz 2)

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
