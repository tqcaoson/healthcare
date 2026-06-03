# Tổng quan & Kiến trúc

## Tổng quan dự án

Hệ thống thông tin y tế (EHR/HIS) xây dựng trên Laravel, phục vụ bệnh viện/phòng khám vừa và nhỏ. Hệ thống bao gồm quản lý hồ sơ bệnh nhân, điều phối lâm sàng, tích hợp xét nghiệm/hình ảnh, dược phòng, thanh toán và báo cáo chất lượng.

**Bỏ qua:** tính năng gọi điện (call), AI/ML inference trực tiếp.

---

## Tech Stack

```
Backend:     Laravel 11 (PHP 8.3)
Database:    MySQL 8.0 (primary) + Redis 7 (cache, queue, session)
Queue:       Laravel Horizon (Redis-backed)
Search:      Meilisearch (patient search, medication lookup)
Storage:     Laravel Filesystem → S3-compatible (tài liệu, DICOM metadata)
Auth:        Laravel Sanctum (API) + Laravel Fortify (web) + 2FA bắt buộc
Testing:     Pest PHP
API:         RESTful JSON + FHIR R4 endpoints
```

---

## Kiến trúc tổng thể

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│   Blade + Alpine.js (Web UI)  │  REST API (mobile/3rd party) │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                   APPLICATION LAYER                          │
│  Http/Controllers  │  Http/Requests  │  Http/Resources      │
│  Actions/          │  Services/      │  Jobs/               │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                    DOMAIN LAYER                              │
│  Patient │ Encounter │ Order │ Result │ Medication │ Billing  │
└────────────────────┬────────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────────┐
│                 INFRASTRUCTURE LAYER                         │
│    MySQL    │    Redis    │    S3    │    Queue    │   Audit   │
└─────────────────────────────────────────────────────────────┘
```

---

## Cấu trúc thư mục

```
app/
├── Console/Commands/
│   ├── ProcessHl7MessageCommand.php
│   ├── GenerateQualityReportCommand.php
│   └── PurgeExpiredSessionsCommand.php
│
├── Domain/                          # Business logic thuần — không phụ thuộc Laravel
│   ├── Patient/
│   │   ├── Models/Patient.php
│   │   ├── Models/PatientIdentifier.php
│   │   ├── Services/MpiService.php          # Master Patient Index
│   │   ├── Services/PatientSearchService.php
│   │   └── ValueObjects/MedicalRecordNumber.php
│   │
│   ├── Encounter/
│   │   ├── Models/Encounter.php
│   │   ├── Models/EncounterDiagnosis.php
│   │   ├── Services/AdtService.php          # Admission/Discharge/Transfer
│   │   └── Enums/EncounterStatus.php
│   │
│   ├── Order/
│   │   ├── Models/Order.php
│   │   ├── Models/LabOrder.php
│   │   ├── Models/RadiologyOrder.php
│   │   ├── Models/MedicationOrder.php
│   │   ├── Services/CpoeService.php         # Computerized Provider Order Entry
│   │   └── Services/OrderRoutingService.php
│   │
│   ├── Result/
│   │   ├── Models/LabResult.php
│   │   ├── Models/RadiologyReport.php
│   │   ├── Services/CriticalValueService.php
│   │   └── Services/ResultRoutingService.php
│   │
│   ├── Medication/
│   │   ├── Models/MedicationAdministration.php
│   │   ├── Models/MedicationReconciliation.php
│   │   ├── Services/PharmacyService.php
│   │   ├── Services/DrugInteractionService.php
│   │   └── Services/MedicationAdministrationService.php
│   │
│   ├── Clinical/
│   │   ├── Models/VitalSign.php
│   │   ├── Models/ClinicalNote.php
│   │   ├── Models/Allergy.php
│   │   ├── Models/Condition.php
│   │   ├── Models/Procedure.php
│   │   └── Services/CdsAlertService.php     # Clinical Decision Support
│   │
│   ├── Scheduling/
│   │   ├── Models/Appointment.php
│   │   ├── Models/Resource.php              # Phòng, thiết bị, bác sĩ
│   │   └── Services/SchedulingService.php
│   │
│   ├── Billing/
│   │   ├── Models/Claim.php
│   │   ├── Models/ChargeItem.php
│   │   ├── Services/CodingService.php       # ICD-10, CPT coding
│   │   └── Services/BillingService.php
│   │
│   └── Audit/
│       ├── Models/AuditLog.php
│       └── Services/AuditService.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/                          # REST API controllers
│   │   │   ├── PatientController.php
│   │   │   ├── EncounterController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ResultController.php
│   │   │   ├── MedicationController.php
│   │   │   ├── VitalSignController.php
│   │   │   ├── AllergyController.php
│   │   │   ├── AppointmentController.php
│   │   │   └── ReportController.php
│   │   │
│   │   ├── Fhir/                            # FHIR R4 endpoints
│   │   │   ├── PatientFhirController.php
│   │   │   ├── ObservationFhirController.php
│   │   │   ├── MedicationRequestFhirController.php
│   │   │   ├── ConditionFhirController.php
│   │   │   ├── EncounterFhirController.php
│   │   │   └── BundleFhirController.php
│   │   │
│   │   └── Web/                             # Blade UI controllers
│   │       ├── DashboardController.php
│   │       ├── PatientController.php
│   │       ├── ClinicalController.php
│   │       └── ReportingController.php
│   │
│   ├── Middleware/
│   │   ├── AuditPhiAccess.php               # Log mọi access vào PHI
│   │   ├── EnforcePasswordExpiry.php
│   │   ├── RequireMfa.php
│   │   ├── AutoLogout.php                   # Tự logout sau idle
│   │   └── ScrubPhiFromLogs.php             # Xóa PHI khỏi application logs
│   │
│   └── Resources/
│       ├── PatientResource.php
│       ├── EncounterResource.php
│       ├── OrderResource.php
│       └── FhirResources/                   # FHIR-compliant JSON resources
│
├── Jobs/
│   ├── ProcessIncomingHl7Message.php
│   ├── SendOutgoingHl7Message.php
│   ├── NotifyCriticalLabValue.php
│   ├── ProcessMedicationAdministration.php
│   ├── GenerateQualityMetrics.php
│   └── RetryFailedHl7Delivery.php
│
├── Listeners/
│   ├── LogPhiAccess.php
│   ├── TriggerCdsAlert.php
│   ├── RouteOrderToSubsystem.php
│   └── UpdateBedCensus.php
│
├── Events/
│   ├── PatientAdmitted.php
│   ├── PatientDischarged.php
│   ├── PatientTransferred.php
│   ├── OrderPlaced.php
│   ├── LabResultReceived.php
│   ├── CriticalValueFlagged.php
│   ├── MedicationAdministered.php
│   └── PhiAccessed.php
│
└── Services/
    ├── Hl7/
    │   ├── Hl7Parser.php
    │   ├── Hl7Builder.php
    │   └── MllpClient.php
    ├── Fhir/
    │   ├── FhirMapper.php           # Domain models ↔ FHIR resources
    │   └── FhirValidator.php
    ├── Terminology/
    │   ├── LoincService.php
    │   ├── SnomedService.php
    │   ├── IcdService.php
    │   └── RxNormService.php
    └── Reporting/
        ├── QualityMetricsService.php
        └── CensusReportService.php

database/migrations/
config/
├── health.php                       # Health IT specific config
├── hl7.php
└── fhir.php

routes/
├── api.php                          # /api/v1/*
├── fhir.php                         # /fhir/R4/*
└── web.php
```
