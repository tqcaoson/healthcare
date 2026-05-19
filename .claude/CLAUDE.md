# CLAUDE.md — Laravel Healthcare Information System

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

---

## Database Schema

### Core Tables

```sql
-- ================================================================
-- PATIENT & IDENTITY
-- ================================================================

CREATE TABLE patients (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,          -- FHIR resource ID
    mrn             VARCHAR(20) NOT NULL UNIQUE,        -- Medical Record Number
    status          ENUM('active','inactive','deceased') DEFAULT 'active',
    deceased_at     TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP NULL                      -- soft delete only, không hard delete PHI
) ENGINE=InnoDB;

CREATE TABLE patient_demographics (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      BIGINT UNSIGNED NOT NULL,
    -- Versioned: mỗi thay đổi tạo row mới (event sourcing)
    version         INT UNSIGNED NOT NULL DEFAULT 1,
    given_name      VARCHAR(100) NOT NULL,
    family_name     VARCHAR(100) NOT NULL,
    birth_date      DATE NOT NULL,
    gender          ENUM('male','female','other','unknown') NOT NULL,
    phone           VARCHAR(20) NULL,
    email           VARCHAR(255) NULL,
    address_line    VARCHAR(255) NULL,
    city            VARCHAR(100) NULL,
    postal_code     VARCHAR(10) NULL,
    language        VARCHAR(10) DEFAULT 'vi',
    is_current      BOOLEAN DEFAULT TRUE,
    changed_by      BIGINT UNSIGNED NOT NULL,           -- user_id
    changed_at      TIMESTAMP NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE patient_identifiers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      BIGINT UNSIGNED NOT NULL,
    system          VARCHAR(255) NOT NULL,               -- 'MRN','BHYT','CCCD','PASSPORT'
    value           VARCHAR(100) NOT NULL,
    assigning_authority VARCHAR(100),
    period_start    DATE,
    period_end      DATE NULL,
    UNIQUE KEY (system, value),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE next_of_kin (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      BIGINT UNSIGNED NOT NULL,
    relationship    VARCHAR(50) NOT NULL,               -- 'MTH','FTH','SPO','CHD'
    name            VARCHAR(200) NOT NULL,
    phone           VARCHAR(20),
    is_emergency_contact BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

-- ================================================================
-- ENCOUNTERS (Lần khám/nhập viện)
-- ================================================================

CREATE TABLE encounters (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    patient_id      BIGINT UNSIGNED NOT NULL,
    encounter_type  ENUM('inpatient','outpatient','emergency','telehealth') NOT NULL,
    status          ENUM('planned','arrived','triaged','in-progress','onleave','finished','cancelled') DEFAULT 'planned',
    class           ENUM('IMP','AMB','EMER','HH') NOT NULL,
    admission_source VARCHAR(50),                        -- 'ED','referral','scheduled'
    discharge_disposition VARCHAR(50) NULL,              -- 'home','snf','expired'
    period_start    TIMESTAMP NOT NULL,
    period_end      TIMESTAMP NULL,
    attending_provider_id BIGINT UNSIGNED NULL,
    admitting_provider_id BIGINT UNSIGNED NULL,
    department_id   BIGINT UNSIGNED NOT NULL,
    bed_id          BIGINT UNSIGNED NULL,
    reason_text     TEXT NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE encounter_diagnoses (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    encounter_id    BIGINT UNSIGNED NOT NULL,
    icd10_code      VARCHAR(10) NOT NULL,
    icd10_display   VARCHAR(500) NOT NULL,
    diagnosis_type  ENUM('admitting','primary','secondary','discharge') NOT NULL,
    rank            TINYINT UNSIGNED DEFAULT 1,
    onset_date      DATE NULL,
    confirmed_by    BIGINT UNSIGNED NULL,
    confirmed_at    TIMESTAMP NULL,
    FOREIGN KEY (encounter_id) REFERENCES encounters(id)
) ENGINE=InnoDB;

-- ================================================================
-- ORDERS (CPOE — Computerized Provider Order Entry)
-- ================================================================

CREATE TABLE orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    order_number    VARCHAR(30) NOT NULL UNIQUE,         -- human-readable order ID
    encounter_id    BIGINT UNSIGNED NOT NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    order_type      ENUM('lab','radiology','medication','nursing','diet','referral','procedure') NOT NULL,
    status          ENUM('draft','pending','active','on-hold','completed','cancelled','stopped','entered-in-error') DEFAULT 'pending',
    priority        ENUM('routine','urgent','stat','asap') DEFAULT 'routine',
    ordered_by      BIGINT UNSIGNED NOT NULL,             -- provider user_id
    ordered_at      TIMESTAMP NOT NULL,
    scheduled_for   TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    cancelled_at    TIMESTAMP NULL,
    cancelled_by    BIGINT UNSIGNED NULL,
    cancel_reason   TEXT NULL,
    clinical_indication TEXT NULL,
    -- Audit: không hard delete, chỉ cancel
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (encounter_id) REFERENCES encounters(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE lab_orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL UNIQUE,
    loinc_code      VARCHAR(20) NOT NULL,
    loinc_display   VARCHAR(500) NOT NULL,
    local_code      VARCHAR(50) NULL,
    specimen_type   VARCHAR(100) NULL,                   -- 'blood','urine','csf'
    fasting_required BOOLEAN DEFAULT FALSE,
    routed_to_lis_at TIMESTAMP NULL,
    lis_accession   VARCHAR(50) NULL,                    -- LIS accession number
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

CREATE TABLE medication_orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL UNIQUE,
    rxnorm_code     VARCHAR(20) NULL,
    drug_name       VARCHAR(255) NOT NULL,
    dose_value      DECIMAL(10,3) NULL,
    dose_unit       VARCHAR(20) NULL,
    route           VARCHAR(50) NULL,                    -- 'oral','IV','IM','topical'
    frequency       VARCHAR(100) NULL,                   -- 'BID','TID','q8h','PRN'
    duration_days   SMALLINT NULL,
    start_date      DATE NULL,
    end_date        DATE NULL,
    instructions    TEXT NULL,
    substitution_allowed BOOLEAN DEFAULT TRUE,
    verified_by_pharmacist BIGINT UNSIGNED NULL,
    verified_at     TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

CREATE TABLE radiology_orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL UNIQUE,
    modality        ENUM('XR','CT','MR','US','NM','PET','DEXA','FLUORO','MAMMO') NOT NULL,
    body_part       VARCHAR(100) NOT NULL,
    laterality      ENUM('left','right','bilateral','na') DEFAULT 'na',
    contrast        BOOLEAN DEFAULT FALSE,
    portable        BOOLEAN DEFAULT FALSE,
    clinical_history TEXT NULL,
    routed_to_ris_at TIMESTAMP NULL,
    ris_accession   VARCHAR(50) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

-- ================================================================
-- LAB RESULTS
-- ================================================================

CREATE TABLE lab_results (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    order_id        BIGINT UNSIGNED NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NULL,
    loinc_code      VARCHAR(20) NOT NULL,
    loinc_display   VARCHAR(500) NOT NULL,
    -- Result value (one of these, depending on type)
    value_quantity  DECIMAL(15,6) NULL,
    value_unit      VARCHAR(50) NULL,
    value_string    VARCHAR(500) NULL,
    value_coded     VARCHAR(50) NULL,                    -- SNOMED code for coded results
    value_coded_display VARCHAR(255) NULL,
    -- Reference range
    reference_low   DECIMAL(15,6) NULL,
    reference_high  DECIMAL(15,6) NULL,
    reference_text  VARCHAR(200) NULL,
    -- Flags
    abnormal_flag   ENUM('N','L','H','LL','HH','A','AA','null') DEFAULT 'null',
    interpretation  VARCHAR(50) NULL,
    -- Status
    status          ENUM('registered','preliminary','final','corrected','amended','cancelled') DEFAULT 'final',
    -- Timing
    collected_at    TIMESTAMP NULL,
    received_at     TIMESTAMP NULL,
    resulted_at     TIMESTAMP NOT NULL,
    -- Source
    performer_lab   VARCHAR(255) NULL,
    -- Acknowledgment
    acknowledged_by BIGINT UNSIGNED NULL,
    acknowledged_at TIMESTAMP NULL,
    -- Critical value workflow
    is_critical     BOOLEAN DEFAULT FALSE,
    critical_notified_at TIMESTAMP NULL,
    critical_notified_to BIGINT UNSIGNED NULL,
    critical_readback_confirmed BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    INDEX idx_patient_loinc (patient_id, loinc_code),
    INDEX idx_encounter (encounter_id),
    INDEX idx_critical_pending (is_critical, acknowledged_at)
) ENGINE=InnoDB;

CREATE TABLE lab_result_panels (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    panel_name      VARCHAR(200) NOT NULL,
    order_id        BIGINT UNSIGNED NOT NULL,
    results         JSON NOT NULL,                        -- array of lab_result IDs
    report_text     TEXT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

-- ================================================================
-- VITAL SIGNS
-- ================================================================

CREATE TABLE vital_signs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NOT NULL,
    recorded_by     BIGINT UNSIGNED NOT NULL,
    recorded_at     TIMESTAMP NOT NULL,
    -- LOINC 8480-6
    systolic_bp     SMALLINT UNSIGNED NULL,
    -- LOINC 8462-4
    diastolic_bp    SMALLINT UNSIGNED NULL,
    -- LOINC 8867-4
    heart_rate      SMALLINT UNSIGNED NULL,
    -- LOINC 9279-1
    respiratory_rate TINYINT UNSIGNED NULL,
    -- LOINC 8310-5
    temperature     DECIMAL(4,1) NULL,
    temp_route      ENUM('oral','rectal','axillary','tympanic','temporal') NULL,
    -- LOINC 59408-5
    spo2            TINYINT UNSIGNED NULL,
    -- LOINC 29463-7
    weight_kg       DECIMAL(6,2) NULL,
    -- LOINC 8302-2
    height_cm       DECIMAL(5,1) NULL,
    -- LOINC 39156-5 (computed)
    bmi             DECIMAL(5,2) NULL,
    -- LOINC 9267-6
    pain_score      TINYINT UNSIGNED NULL CHECK (pain_score BETWEEN 0 AND 10),
    -- LOINC 59542-1
    gcs_total       TINYINT UNSIGNED NULL CHECK (gcs_total BETWEEN 3 AND 15),
    notes           TEXT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (encounter_id) REFERENCES encounters(id),
    INDEX idx_patient_time (patient_id, recorded_at DESC)
) ENGINE=InnoDB;

-- ================================================================
-- MEDICATIONS & PHARMACY
-- ================================================================

CREATE TABLE medication_administrations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    order_id        BIGINT UNSIGNED NOT NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NOT NULL,
    status          ENUM('in-progress','completed','not-done','on-hold','stopped','unknown') DEFAULT 'completed',
    -- 5 Rights enforcement
    patient_verified_by_barcode BOOLEAN DEFAULT FALSE,
    medication_verified_by_barcode BOOLEAN DEFAULT FALSE,
    -- Drug details
    rxnorm_code     VARCHAR(20) NULL,
    drug_name       VARCHAR(255) NOT NULL,
    dose_value      DECIMAL(10,3) NOT NULL,
    dose_unit       VARCHAR(20) NOT NULL,
    route           VARCHAR(50) NOT NULL,
    -- Timing
    scheduled_at    TIMESTAMP NULL,
    administered_at TIMESTAMP NOT NULL,
    administered_by BIGINT UNSIGNED NOT NULL,             -- nurse user_id
    -- PRN documentation
    is_prn          BOOLEAN DEFAULT FALSE,
    prn_indication  TEXT NULL,
    prn_effectiveness TEXT NULL,
    -- Site (for injections)
    site            VARCHAR(100) NULL,
    -- Waste (for controlled substances)
    wasted_amount   DECIMAL(10,3) NULL,
    waste_witnessed_by BIGINT UNSIGNED NULL,
    notes           TEXT NULL,
    created_at      TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

-- ================================================================
-- ALLERGIES
-- ================================================================

CREATE TABLE allergies (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id      BIGINT UNSIGNED NOT NULL,
    category        ENUM('food','medication','environment','biologic') NOT NULL,
    clinical_status ENUM('active','inactive','resolved') DEFAULT 'active',
    verification    ENUM('unconfirmed','confirmed','refuted','entered-in-error') DEFAULT 'unconfirmed',
    -- Substance
    rxnorm_code     VARCHAR(20) NULL,
    substance_name  VARCHAR(255) NOT NULL,
    -- Reaction
    reaction_manifestation VARCHAR(500) NULL,
    reaction_severity ENUM('mild','moderate','severe') NULL,
    -- Metadata
    onset_date      DATE NULL,
    recorded_by     BIGINT UNSIGNED NOT NULL,
    recorded_at     TIMESTAMP NOT NULL,
    updated_by      BIGINT UNSIGNED NULL,
    updated_at      TIMESTAMP NULL,
    note            TEXT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    INDEX idx_patient_active (patient_id, clinical_status)
) ENGINE=InnoDB;

-- ================================================================
-- CLINICAL NOTES
-- ================================================================

CREATE TABLE clinical_notes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    patient_id      BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NOT NULL,
    author_id       BIGINT UNSIGNED NOT NULL,
    -- LOINC document type
    loinc_type_code VARCHAR(20) NOT NULL,                -- '11506-3'=progress, '18842-5'=discharge
    loinc_type_display VARCHAR(255) NOT NULL,
    note_type       ENUM('progress','admission','discharge','procedure','consult','nursing','other') NOT NULL,
    -- Content
    content         LONGTEXT NOT NULL,                   -- plain text hoặc HTML
    content_type    ENUM('text/plain','text/html') DEFAULT 'text/plain',
    -- Status
    status          ENUM('draft','preliminary','final','amended','corrected','cancelled') DEFAULT 'draft',
    -- Attestation
    attested_by     BIGINT UNSIGNED NULL,
    attested_at     TIMESTAMP NULL,
    -- Timing
    documented_at   TIMESTAMP NOT NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (encounter_id) REFERENCES encounters(id)
) ENGINE=InnoDB;

CREATE TABLE clinical_note_versions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    note_id         BIGINT UNSIGNED NOT NULL,
    version         INT UNSIGNED NOT NULL,
    content         LONGTEXT NOT NULL,
    changed_by      BIGINT UNSIGNED NOT NULL,
    changed_at      TIMESTAMP NOT NULL,
    change_reason   VARCHAR(500) NULL,
    FOREIGN KEY (note_id) REFERENCES clinical_notes(id)
) ENGINE=InnoDB;

-- ================================================================
-- CONDITIONS / PROBLEM LIST
-- ================================================================

CREATE TABLE conditions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    patient_id      BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NULL,
    -- Coding
    icd10_code      VARCHAR(10) NULL,
    icd10_display   VARCHAR(500) NULL,
    snomed_code     VARCHAR(20) NULL,
    snomed_display  VARCHAR(500) NULL,
    free_text       VARCHAR(500) NULL,                   -- nếu chưa có code
    -- Category
    category        ENUM('encounter-diagnosis','problem-list-item','health-concern') DEFAULT 'encounter-diagnosis',
    -- Status
    clinical_status ENUM('active','recurrence','relapse','inactive','remission','resolved') DEFAULT 'active',
    verification    ENUM('unconfirmed','provisional','differential','confirmed','refuted','entered-in-error') DEFAULT 'confirmed',
    severity        ENUM('mild','moderate','severe') NULL,
    -- Timing
    onset_date      DATE NULL,
    abatement_date  DATE NULL,
    recorded_at     TIMESTAMP NOT NULL,
    recorded_by     BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    INDEX idx_patient_active (patient_id, clinical_status)
) ENGINE=InnoDB;

-- ================================================================
-- SCHEDULING
-- ================================================================

CREATE TABLE appointments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    patient_id      BIGINT UNSIGNED NOT NULL,
    provider_id     BIGINT UNSIGNED NOT NULL,
    department_id   BIGINT UNSIGNED NOT NULL,
    room_id         BIGINT UNSIGNED NULL,
    status          ENUM('proposed','pending','booked','arrived','fulfilled','cancelled','noshow','entered-in-error','checked-in','waitlist') DEFAULT 'booked',
    appointment_type VARCHAR(100) NULL,                  -- 'routine','followup','emergency'
    reason          VARCHAR(500) NULL,
    -- Scheduling
    start_at        TIMESTAMP NOT NULL,
    end_at          TIMESTAMP NOT NULL,
    duration_minutes SMALLINT UNSIGNED,
    -- Patient arrival
    arrived_at      TIMESTAMP NULL,
    roomed_at       TIMESTAMP NULL,
    -- Cancellation
    cancelled_at    TIMESTAMP NULL,
    cancel_reason   VARCHAR(500) NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    INDEX idx_provider_date (provider_id, start_at),
    INDEX idx_department_date (department_id, start_at)
) ENGINE=InnoDB;

CREATE TABLE appointment_reminders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id  BIGINT UNSIGNED NOT NULL,
    channel         ENUM('sms','email','push') NOT NULL,
    send_at         TIMESTAMP NOT NULL,
    sent_at         TIMESTAMP NULL,
    status          ENUM('pending','sent','failed','cancelled') DEFAULT 'pending',
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
) ENGINE=InnoDB;

-- ================================================================
-- BILLING & REVENUE CYCLE
-- ================================================================

CREATE TABLE charge_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    encounter_id    BIGINT UNSIGNED NOT NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    -- CPT code cho thủ thuật/dịch vụ
    cpt_code        VARCHAR(10) NULL,
    cpt_display     VARCHAR(500) NULL,
    -- ICD-10-CM cho chẩn đoán (billing)
    icd10_code      VARCHAR(10) NULL,
    quantity        DECIMAL(8,2) DEFAULT 1,
    unit_price      DECIMAL(12,2) NOT NULL,
    total_amount    DECIMAL(12,2) NOT NULL,
    status          ENUM('billable','billed','not-billable','aborted') DEFAULT 'billable',
    service_date    DATE NOT NULL,
    billed_by       BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (encounter_id) REFERENCES encounters(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE claims (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    claim_number    VARCHAR(50) NOT NULL UNIQUE,
    encounter_id    BIGINT UNSIGNED NOT NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    insurance_id    BIGINT UNSIGNED NULL,
    claim_type      ENUM('institutional','professional','pharmacy') DEFAULT 'professional',
    status          ENUM('draft','submitted','pending','accepted','rejected','partial','paid','cancelled') DEFAULT 'draft',
    total_amount    DECIMAL(12,2) NOT NULL,
    approved_amount DECIMAL(12,2) NULL,
    patient_portion DECIMAL(12,2) NULL,
    submitted_at    TIMESTAMP NULL,
    processed_at    TIMESTAMP NULL,
    denial_reason   TEXT NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (encounter_id) REFERENCES encounters(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

-- ================================================================
-- AUDIT LOG — WORM (Write Once Read Many)
-- ================================================================

CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- Event
    event_type      VARCHAR(100) NOT NULL,               -- 'phi.viewed','phi.modified','login.success'
    event_at        TIMESTAMP NOT NULL,
    -- Actor
    user_id         BIGINT UNSIGNED NULL,
    user_name       VARCHAR(200) NULL,                   -- snapshot (user có thể bị xóa sau)
    user_role       VARCHAR(100) NULL,
    session_id      VARCHAR(255) NULL,
    ip_address      VARCHAR(45) NULL,
    user_agent      TEXT NULL,
    -- Resource
    resource_type   VARCHAR(100) NULL,                   -- 'Patient','LabResult','Order'
    resource_id     BIGINT UNSIGNED NULL,
    patient_id      BIGINT UNSIGNED NULL,                -- luôn link về patient nếu có
    -- Detail
    action          VARCHAR(50) NOT NULL,                -- 'view','create','update','delete','export','print'
    old_values      JSON NULL,                           -- snapshot trước khi thay đổi
    new_values      JSON NULL,                           -- snapshot sau khi thay đổi
    -- Context
    request_path    VARCHAR(500) NULL,
    request_method  VARCHAR(10) NULL,
    correlation_id  CHAR(36) NULL,                       -- trace across services
    -- Integrity
    log_hash        CHAR(64) NULL,                       -- SHA-256 hash để detect tampering
    INDEX idx_patient (patient_id, event_at DESC),
    INDEX idx_user (user_id, event_at DESC),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_event_type (event_type, event_at DESC)
) ENGINE=InnoDB;
-- NOTE: KHÔNG có foreign key vào audit_logs — log phải tồn tại kể cả khi record bị xóa
-- NOTE: KHÔNG có updated_at — audit logs không được sửa
-- NOTE: Dùng MySQL partitioning by year cho retention 6+ năm

-- ================================================================
-- HL7 MESSAGE QUEUE
-- ================================================================

CREATE TABLE hl7_outbox (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    correlation_id  CHAR(36) NOT NULL,
    message_type    VARCHAR(20) NOT NULL,                -- 'ADT^A01','ORM^O01','ORU^R01'
    destination     VARCHAR(100) NOT NULL,               -- 'LIS','RIS','PHARMACY'
    payload         LONGTEXT NOT NULL,                   -- raw HL7 message
    status          ENUM('pending','sent','failed','dead') DEFAULT 'pending',
    attempts        TINYINT UNSIGNED DEFAULT 0,
    max_attempts    TINYINT UNSIGNED DEFAULT 3,
    last_error      TEXT NULL,
    created_at      TIMESTAMP,
    sent_at         TIMESTAMP NULL,
    next_retry_at   TIMESTAMP NULL,
    INDEX idx_pending (status, next_retry_at)
) ENGINE=InnoDB;

CREATE TABLE hl7_inbox (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_control_id VARCHAR(100) NOT NULL UNIQUE,    -- MSH-10 (idempotency)
    message_type    VARCHAR(20) NOT NULL,
    source          VARCHAR(100) NOT NULL,
    payload         LONGTEXT NOT NULL,
    status          ENUM('received','processing','processed','failed') DEFAULT 'received',
    processed_at    TIMESTAMP NULL,
    error_message   TEXT NULL,
    created_at      TIMESTAMP,
    INDEX idx_status (status, created_at)
) ENGINE=InnoDB;

-- ================================================================
-- BEDS & DEPARTMENTS
-- ================================================================

CREATE TABLE departments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(20) NOT NULL UNIQUE,
    name            VARCHAR(200) NOT NULL,
    type            ENUM('inpatient','outpatient','icu','ed','or','radiology','lab','pharmacy','admin') NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;

CREATE TABLE beds (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_id   BIGINT UNSIGNED NOT NULL,
    bed_number      VARCHAR(20) NOT NULL,
    room_number     VARCHAR(20) NOT NULL,
    status          ENUM('available','occupied','housekeeping','maintenance','reserved') DEFAULT 'available',
    type            ENUM('general','icu','isolation','bariatric') DEFAULT 'general',
    current_patient_id BIGINT UNSIGNED NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB;

-- ================================================================
-- USERS & ROLES (Healthcare-specific)
-- ================================================================

CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) NOT NULL UNIQUE,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    npi             VARCHAR(10) NULL UNIQUE,             -- National Provider Identifier
    license_number  VARCHAR(50) NULL,
    role            ENUM('physician','nurse','pharmacist','lab_tech','radiologist','admin','billing','receptionist','superadmin') NOT NULL,
    department_id   BIGINT UNSIGNED NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    mfa_enabled     BOOLEAN DEFAULT TRUE,               -- MFA bắt buộc cho PHI access
    mfa_secret      VARCHAR(100) NULL,
    last_login_at   TIMESTAMP NULL,
    password_changed_at TIMESTAMP NULL,
    failed_login_attempts TINYINT UNSIGNED DEFAULT 0,
    locked_until    TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB;

CREATE TABLE user_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    token_hash      CHAR(64) NOT NULL UNIQUE,
    ip_address      VARCHAR(45),
    last_activity   TIMESTAMP NOT NULL,
    created_at      TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
```

---

## Eloquent Models — Conventions

```php
// Tất cả models liên quan đến PHI phải có:

abstract class PhiModel extends Model
{
    // KHÔNG dùng $fillable = ['*'] — whitelist explicit
    // Luôn soft delete
    use SoftDeletes;

    // Boot: tự động fire PhiAccessed event khi retrieve
    protected static function booted(): void
    {
        static::retrieved(function (self $model) {
            event(new PhiAccessed($model));
        });

        // Không cho hard delete
        static::deleting(function (self $model) {
            if (! $model->isForceDeleting()) {
                return; // soft delete OK
            }
            throw new \Exception('Hard delete của PHI records không được phép.');
        });
    }
}

// Patient model
class Patient extends PhiModel
{
    protected $fillable = [
        'mrn', 'status',
    ];

    // Demographics qua relationship (versioned)
    public function currentDemographics(): HasOne
    {
        return $this->hasOne(PatientDemographic::class)
                    ->where('is_current', true);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class)
                    ->where('clinical_status', 'active');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class)
                    ->where('clinical_status', 'active');
    }

    public function latestVitals(): HasOne
    {
        return $this->hasOne(VitalSign::class)
                    ->latestOfMany('recorded_at');
    }

    // Canned scope thường dùng
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
```

---

## Services — Business Logic

### MpiService (Master Patient Index)

```php
class MpiService
{
    // Tìm patient — tránh duplicate trước khi tạo mới
    public function findOrMatch(array $demographics): ?Patient
    {
        // 1. Exact match bằng identifier (BHYT, CCCD)
        // 2. Probabilistic match: tên + ngày sinh + giới tính
        // 3. Return null nếu không match (tạo mới)
    }

    // Merge 2 duplicate records
    public function mergePatients(Patient $primary, Patient $secondary): void
    {
        // Chuyển tất cả encounters, orders, results về primary
        // Mark secondary là inactive
        // Log merge event vào audit
        // KHÔNG xóa secondary record
    }
}
```

### CpoeService (Order Entry)

```php
class CpoeService
{
    public function placeOrder(PlaceOrderDto $dto, User $provider): Order
    {
        return DB::transaction(function () use ($dto, $provider) {
            // 1. Validate order (diagnosis required for lab orders, etc.)
            $this->validateOrder($dto);

            // 2. Tạo Order record
            $order = Order::create([...]);

            // 3. Tạo type-specific record (LabOrder/MedicationOrder/etc.)
            $this->createTypeSpecificOrder($order, $dto);

            // 4. Persist vào outbox TRONG CÙNG TRANSACTION (outbox pattern)
            $this->queueHl7Message($order);

            // 5. Fire event → CDS check async (không block)
            OrderPlaced::dispatch($order);

            return $order;
        });
    }

    // Cancel order — KHÔNG delete, chỉ cancel với lý do
    public function cancelOrder(Order $order, string $reason, User $by): void
    {
        // Validate: không cancel nếu đã completed
        // Update status + log
        // Gửi cancel message đến downstream (LIS/Pharmacy)
    }
}
```

### CdsAlertService (Clinical Decision Support)

```php
class CdsAlertService
{
    // Rule: không bao giờ block order — chỉ cảnh báo
    public function checkOrder(Order $order): Collection
    {
        $alerts = collect();

        // Drug-drug interaction
        if ($order->order_type === 'medication') {
            $alerts = $alerts->merge($this->checkDrugInteractions($order));
        }

        // Drug-allergy
        $alerts = $alerts->merge($this->checkAllergyConflicts($order));

        // Dose range check
        $alerts = $alerts->merge($this->checkDoseRange($order));

        // Sort by severity: critical first
        return $alerts->sortByDesc('severity');
    }

    // Alert fatigue prevention: track override rates
    public function recordOverride(CdsAlert $alert, string $reason, User $provider): void
    {
        CdsAlertOverride::create([
            'alert_type'    => $alert->type,
            'overridden_by' => $provider->id,
            'reason'        => $reason,
            'overridden_at' => now(),
        ]);
    }
}
```

### AuditService

```php
class AuditService
{
    public function log(string $eventType, array $context = []): void
    {
        // Lấy current user + request context
        $entry = array_merge([
            'event_type'    => $eventType,
            'event_at'      => now(),
            'user_id'       => Auth::id(),
            'user_name'     => Auth::user()?->username,
            'user_role'     => Auth::user()?->role,
            'session_id'    => session()->getId(),
            'ip_address'    => request()->ip(),
            'request_path'  => request()->path(),
            'request_method'=> request()->method(),
            'correlation_id'=> request()->header('X-Correlation-ID', (string) Str::uuid()),
        ], $context);

        // Hash chain cho integrity
        $entry['log_hash'] = $this->computeHash($entry);

        // Insert — KHÔNG update, KHÔNG delete
        AuditLog::insert($entry);
    }

    private function computeHash(array $entry): string
    {
        $lastHash = AuditLog::latest('id')->value('log_hash') ?? '';
        return hash('sha256', $lastHash . json_encode($entry));
    }
}
```

---

## Middleware

### AuditPhiAccess

```php
class AuditPhiAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log PHI access sau khi response thành công
        if ($response->isSuccessful() && $this->isPhiEndpoint($request)) {
            app(AuditService::class)->log('phi.accessed', [
                'resource_type' => $this->extractResourceType($request),
                'resource_id'   => $this->extractResourceId($request),
                'patient_id'    => $this->extractPatientId($request, $response),
                'action'        => 'view',
            ]);
        }

        return $response;
    }
}
```

### ScrubPhiFromLogs

```php
class ScrubPhiFromLogs
{
    // Chạy trước tất cả requests — đảm bảo Laravel logs không chứa PHI
    private array $patterns = [
        'mrn'   => '/\bMRN[-:\s]*\d{6,12}\b/i',
        'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
        'email' => '/\b[\w.]+@[\w.]+\.\w+\b/',
        'dob'   => '/\b(DOB|date_of_birth|birth_date)[":\s]+\d{4}-\d{2}-\d{2}\b/i',
        'ssn'   => '/\b\d{3}-\d{2}-\d{4}\b/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Register custom log formatter với PHI scrubbing
        // Áp dụng cho toàn bộ log output trong request này
        return $next($request);
    }
}
```

### AutoLogout

```php
class AutoLogout
{
    private int $idleMinutes = 15; // HIPAA: auto-logout sau idle

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_at');

            if ($lastActivity && now()->diffInMinutes($lastActivity) > $this->idleMinutes) {
                Auth::logout();
                session()->invalidate();

                app(AuditService::class)->log('session.auto_logout', [
                    'reason' => 'idle_timeout',
                    'idle_minutes' => $this->idleMinutes,
                ]);

                return response()->json(['message' => 'Session expired'], 401);
            }

            session(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}
```

---

## Jobs & Queues

```php
// Queue config — priorities
// Queue: critical > clinical > default > analytics

class NotifyCriticalLabValue implements ShouldQueue
{
    public string $queue = 'critical';
    public int $tries = 5;
    public int $timeout = 30;

    public function handle(): void
    {
        // 1. Notify ordering provider qua in-app notification
        // 2. Ghi vào audit log
        // 3. Require acknowledgment trong 30 phút
        // 4. Nếu chưa acknowledge → escalate đến charge nurse
    }
}

class ProcessIncomingHl7Message implements ShouldQueue
{
    public string $queue = 'clinical';

    public function handle(): void
    {
        // Idempotency check: đã process message_control_id này chưa?
        if (Hl7Inbox::where('message_control_id', $this->messageControlId)->exists()) {
            return; // đã xử lý, bỏ qua
        }

        // Parse HL7 → domain objects → persist
        // Send ACK về sender
    }
}

class SendOutgoingHl7Message implements ShouldQueue
{
    public string $queue = 'clinical';
    public int $tries = 3;

    public function handle(): void
    {
        // Dùng outbox record (đã ghi trong transaction với business event)
        // Gửi qua MLLP → nhận ACK
        // Mark outbox record là 'sent'
        // Nếu NACK hoặc timeout → mark 'failed' → retry với backoff
    }

    public function failed(\Throwable $e): void
    {
        // Sau max retries → mark 'dead' → alert on-call
        $this->outbox->update(['status' => 'dead']);
        // Notify engineering team
    }
}
```

---

## Routes

```php
// routes/api.php — /api/v1/*
Route::middleware(['auth:sanctum', 'require.mfa', 'audit.phi', 'auto.logout'])
    ->prefix('v1')
    ->group(function () {

        // Patients
        Route::apiResource('patients', PatientController::class);
        Route::get('patients/{patient}/summary', [PatientController::class, 'summary']);
        Route::get('patients/{patient}/timeline', [PatientController::class, 'timeline']);

        // Encounters
        Route::apiResource('patients/{patient}/encounters', EncounterController::class);
        Route::post('encounters/{encounter}/admit', [AdtController::class, 'admit']);
        Route::post('encounters/{encounter}/discharge', [AdtController::class, 'discharge']);
        Route::post('encounters/{encounter}/transfer', [AdtController::class, 'transfer']);

        // Clinical data
        Route::apiResource('encounters/{encounter}/vital-signs', VitalSignController::class);
        Route::apiResource('patients/{patient}/allergies', AllergyController::class);
        Route::apiResource('patients/{patient}/conditions', ConditionController::class);
        Route::apiResource('encounters/{encounter}/notes', ClinicalNoteController::class);

        // Orders (CPOE)
        Route::apiResource('encounters/{encounter}/orders', OrderController::class);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('orders/{order}/cds-alerts', [OrderController::class, 'cdsAlerts']);
        Route::post('orders/{order}/override-alert', [OrderController::class, 'overrideAlert']);

        // Results
        Route::get('patients/{patient}/lab-results', [LabResultController::class, 'index']);
        Route::get('lab-results/{result}', [LabResultController::class, 'show']);
        Route::post('lab-results/{result}/acknowledge', [LabResultController::class, 'acknowledge']);

        // Medications
        Route::apiResource('encounters/{encounter}/medication-administrations', MedicationAdministrationController::class);
        Route::get('patients/{patient}/medication-reconciliation', [MedicationController::class, 'reconciliation']);

        // Scheduling
        Route::apiResource('appointments', AppointmentController::class);
        Route::post('appointments/{appointment}/arrive', [AppointmentController::class, 'arrive']);
        Route::get('providers/{provider}/slots', [SchedulingController::class, 'availableSlots']);

        // Bed management
        Route::get('beds', [BedController::class, 'index']);
        Route::patch('beds/{bed}/status', [BedController::class, 'updateStatus']);

        // Billing
        Route::apiResource('encounters/{encounter}/charge-items', ChargeItemController::class);
        Route::apiResource('claims', ClaimController::class);

        // Reports
        Route::get('reports/census', [ReportController::class, 'census']);
        Route::get('reports/quality-metrics', [ReportController::class, 'qualityMetrics']);
        Route::get('reports/lab-turnaround', [ReportController::class, 'labTurnaround']);
        Route::get('reports/medication-errors', [ReportController::class, 'medicationErrors']);
    });

// routes/fhir.php — /fhir/R4/*
Route::middleware(['auth:sanctum', 'audit.phi'])
    ->prefix('fhir/R4')
    ->group(function () {
        // FHIR Capability Statement
        Route::get('metadata', [CapabilityController::class, 'show']);

        // Core FHIR Resources
        Route::get('Patient', [PatientFhirController::class, 'search']);
        Route::get('Patient/{id}', [PatientFhirController::class, 'read']);
        Route::post('Patient', [PatientFhirController::class, 'create']);
        Route::put('Patient/{id}', [PatientFhirController::class, 'update']);

        Route::get('Observation', [ObservationFhirController::class, 'search']);
        Route::get('Observation/{id}', [ObservationFhirController::class, 'read']);

        Route::get('MedicationRequest', [MedicationRequestFhirController::class, 'search']);
        Route::get('Condition', [ConditionFhirController::class, 'search']);
        Route::get('Encounter', [EncounterFhirController::class, 'search']);
        Route::get('AllergyIntolerance', [AllergyFhirController::class, 'search']);
        Route::get('DiagnosticReport', [DiagnosticReportFhirController::class, 'search']);

        // $everything operation
        Route::post('Patient/{id}/\$everything', [PatientFhirController::class, 'everything']);

        // Transaction/Batch Bundle
        Route::post('/', [BundleFhirController::class, 'process']);
    });
```

---

## Security — Implementation Rules

### Authentication & Authorization

```php
// 1. MFA bắt buộc cho mọi user có PHI access
// 2. Password: min 12 chars, complexity, expire 90 ngày
// 3. Account lock sau 5 failed attempts, unlock sau 30 phút
// 4. Session idle timeout: 15 phút

// Policy-based authorization (không role check trong controller)
class PatientPolicy
{
    public function view(User $user, Patient $patient): bool
    {
        // Bác sĩ/điều dưỡng: chỉ xem patient trong department của mình
        // Admin: xem tất cả
        // Billing: chỉ xem demographics + billing info
        // KHÔNG xem nếu không có encounter active
    }

    public function viewFull(User $user, Patient $patient): bool
    {
        // Chỉ treating provider của encounter hiện tại
        // Hoặc "break the glass" với lý do ghi lại
    }
}
```

### Encryption

```php
// config/health.php
return [
    // Các fields nhạy cảm encrypt trong DB
    'encrypted_fields' => [
        'patient_demographics' => ['phone', 'email', 'address_line'],
        'patient_identifiers'  => ['value'],  // CCCD, BHYT, passport
        'users'                => ['mfa_secret'],
    ],

    // Dùng Laravel's built-in encryption (AES-256-CBC)
    // Key rotation: config riêng cho encrypted columns
];

// Model cast
protected $casts = [
    'phone' => EncryptedCast::class,
    'email' => EncryptedCast::class,
];
```

### Rate Limiting

```php
// routes/api.php
RateLimiter::for('phi-access', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id);
});

RateLimiter::for('auth', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->ip()),            // brute force protection
        Limit::perDay(100)->by($request->user()?->id ?? $request->ip()),
    ];
});
```

---

## Quality Metrics — Reporting

```php
class QualityMetricsService
{
    // 30-day readmission rate
    public function readmissionRate(Carbon $from, Carbon $to, ?int $departmentId = null): float
    {
        // Đếm patients discharge trong period
        // Cross-reference với admissions trong 30 ngày sau discharge
    }

    // Average Length of Stay
    public function alos(Carbon $from, Carbon $to): float
    {
        return Encounter::whereBetween('period_start', [$from, $to])
            ->whereNotNull('period_end')
            ->where('encounter_type', 'inpatient')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, period_start, period_end) / 24'));
    }

    // Lab Turnaround Time (order → result)
    public function labTurnaround(string $loincCode, Carbon $from, Carbon $to): array
    {
        // Min, median, p75, p90, max TAT
    }

    // Bed occupancy rate
    public function bedOccupancy(Carbon $date, ?int $departmentId = null): float
    {
        $total = Bed::when($departmentId, fn($q) => $q->where('department_id', $departmentId))->count();
        $occupied = Bed::where('status', 'occupied')->when($departmentId, ...)->count();
        return $total > 0 ? round($occupied / $total * 100, 1) : 0;
    }

    // Medication error rate
    public function medicationErrorRate(Carbon $from, Carbon $to): float
    {
        // Errors logged / Total administrations × 1000
    }
}
```

---

## HL7 Integration

```php
// config/hl7.php
return [
    'connections' => [
        'LIS' => [
            'host'     => env('LIS_HOST'),
            'port'     => env('LIS_PORT', 6661),
            'timeout'  => 10,
            'retry'    => 3,
        ],
        'RIS' => [
            'host'     => env('RIS_HOST'),
            'port'     => env('RIS_PORT', 6662),
            'timeout'  => 10,
        ],
        'PHARMACY' => [
            'host'     => env('PHARMACY_HOST'),
            'port'     => env('PHARMACY_PORT', 6663),
        ],
    ],

    'message_types' => [
        'ADT_A01' => ['destinations' => ['LIS', 'RIS', 'PHARMACY']],   // Admit
        'ADT_A03' => ['destinations' => ['LIS', 'RIS', 'PHARMACY']],   // Discharge
        'ADT_A08' => ['destinations' => ['LIS', 'RIS', 'PHARMACY']],   // Update demographics
        'ORM_O01' => ['destinations' => ['LIS']],                       // Lab order
        'ORM_RAD' => ['destinations' => ['RIS']],                       // Radiology order
        'OMP_O09' => ['destinations' => ['PHARMACY']],                  // Pharmacy order
    ],
];
```

---

## FHIR R4 — Resource Mapping

Mọi FHIR response phải tuân theo cấu trúc chuẩn. Controller không build JSON thủ công — dùng FhirMapper.

### Domain Model → FHIR Resource

```php
class FhirMapper
{
    // Patient → FHIR Patient resource
    public function patientToFhir(Patient $patient): array
    {
        $demo = $patient->currentDemographics;

        return [
            'resourceType' => 'Patient',
            'id'           => $patient->uuid,
            'meta' => [
                'versionId'   => (string) $patient->getVersion(),
                'lastUpdated' => $patient->updated_at->toIso8601String(),
            ],
            'identifier' => $patient->identifiers->map(fn($i) => [
                'system' => $i->system,
                'value'  => $i->value,
            ])->toArray(),
            'active' => $patient->status === 'active',
            'name' => [[
                'use'    => 'official',
                'family' => $demo->family_name,
                'given'  => [$demo->given_name],
            ]],
            'gender'    => $demo->gender,
            'birthDate' => $demo->birth_date->toDateString(),
            'telecom'   => array_filter([
                $demo->phone ? ['system' => 'phone', 'value' => $demo->phone] : null,
                $demo->email ? ['system' => 'email', 'value' => $demo->email] : null,
            ]),
            'address' => $demo->address_line ? [[
                'line'       => [$demo->address_line],
                'city'       => $demo->city,
                'postalCode' => $demo->postal_code,
            ]] : [],
            'communication' => [[
                'language' => [
                    'coding' => [['system' => 'urn:ietf:bcp:47', 'code' => $demo->language]],
                ],
            ]],
            'deceasedDateTime' => $patient->deceased_at?->toIso8601String(),
        ];
    }

    // VitalSign → FHIR Observation (Bundle of Observations)
    public function vitalSignToFhir(VitalSign $vs): array
    {
        $components = [];

        $map = [
            'systolic_bp'      => ['8480-6', 'Systolic blood pressure',    'mm[Hg]'],
            'diastolic_bp'     => ['8462-4', 'Diastolic blood pressure',   'mm[Hg]'],
            'heart_rate'       => ['8867-4', 'Heart rate',                  '/min'],
            'respiratory_rate' => ['9279-1', 'Respiratory rate',            '/min'],
            'temperature'      => ['8310-5', 'Body temperature',            'Cel'],
            'spo2'             => ['59408-5','Oxygen saturation',           '%'],
            'weight_kg'        => ['29463-7','Body weight',                 'kg'],
            'height_cm'        => ['8302-2', 'Body height',                 'cm'],
            'bmi'              => ['39156-5','Body mass index',             'kg/m2'],
            'pain_score'       => ['72514-3','Pain severity',               '{score}'],
        ];

        foreach ($map as $field => [$loinc, $display, $unit]) {
            if ($vs->$field !== null) {
                $components[] = [
                    'code' => [
                        'coding' => [['system' => 'http://loinc.org', 'code' => $loinc, 'display' => $display]],
                    ],
                    'valueQuantity' => [
                        'value'  => (float) $vs->$field,
                        'unit'   => $unit,
                        'system' => 'http://unitsofmeasure.org',
                        'code'   => $unit,
                    ],
                ];
            }
        }

        return [
            'resourceType' => 'Observation',
            'id'           => (string) $vs->id,
            'status'       => 'final',
            'category'     => [[
                'coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                              'code'   => 'vital-signs']],
            ]],
            'code' => [
                'coding' => [['system' => 'http://loinc.org', 'code' => '85353-1', 'display' => 'Vital signs panel']],
            ],
            'subject'          => ['reference' => "Patient/{$vs->patient->uuid}"],
            'encounter'        => ['reference' => "Encounter/{$vs->encounter->uuid}"],
            'effectiveDateTime' => $vs->recorded_at->toIso8601String(),
            'performer'        => [['reference' => "Practitioner/{$vs->recordedBy->id}"]],
            'component'        => $components,
        ];
    }

    // LabResult → FHIR Observation
    public function labResultToFhir(LabResult $result): array
    {
        $interpretation = match($result->abnormal_flag) {
            'H'  => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'H']]]],
            'L'  => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'L']]]],
            'HH' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'HH']]]],
            'LL' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'LL']]]],
            default => [],
        };

        $value = [];
        if ($result->value_quantity !== null) {
            $value = ['valueQuantity' => [
                'value'  => (float) $result->value_quantity,
                'unit'   => $result->value_unit,
                'system' => 'http://unitsofmeasure.org',
            ]];
        } elseif ($result->value_string !== null) {
            $value = ['valueString' => $result->value_string];
        } elseif ($result->value_coded !== null) {
            $value = ['valueCodeableConcept' => [
                'coding' => [['system' => 'http://snomed.info/sct', 'code' => $result->value_coded,
                              'display' => $result->value_coded_display]],
            ]];
        }

        return array_merge([
            'resourceType' => 'Observation',
            'id'           => $result->uuid,
            'status'       => $result->status,
            'category'     => [[
                'coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                              'code'   => 'laboratory']],
            ]],
            'code' => [
                'coding' => [['system' => 'http://loinc.org', 'code' => $result->loinc_code,
                              'display' => $result->loinc_display]],
            ],
            'subject'           => ['reference' => "Patient/{$result->patient->uuid}"],
            'effectiveDateTime' => $result->resulted_at->toIso8601String(),
            'issued'            => $result->resulted_at->toIso8601String(),
            'interpretation'    => $interpretation,
            'referenceRange'    => $result->reference_low || $result->reference_high ? [[
                'low'  => $result->reference_low  ? ['value' => (float) $result->reference_low,  'unit' => $result->value_unit] : null,
                'high' => $result->reference_high ? ['value' => (float) $result->reference_high, 'unit' => $result->value_unit] : null,
                'text' => $result->reference_text,
            ]] : [],
        ], $value);
    }

    // MedicationOrder → FHIR MedicationRequest
    public function medicationOrderToFhir(MedicationOrder $mo): array
    {
        return [
            'resourceType'   => 'MedicationRequest',
            'id'             => $mo->order->uuid,
            'status'         => $this->mapOrderStatusToFhir($mo->order->status),
            'intent'         => 'order',
            'medicationCodeableConcept' => [
                'coding' => array_filter([
                    $mo->rxnorm_code ? [
                        'system'  => 'http://www.nlm.nih.gov/research/umls/rxnorm',
                        'code'    => $mo->rxnorm_code,
                        'display' => $mo->drug_name,
                    ] : null,
                ]),
                'text' => $mo->drug_name,
            ],
            'subject'        => ['reference' => "Patient/{$mo->order->patient->uuid}"],
            'encounter'      => ['reference' => "Encounter/{$mo->order->encounter->uuid}"],
            'authoredOn'     => $mo->order->ordered_at->toIso8601String(),
            'requester'      => ['reference' => "Practitioner/{$mo->order->orderedBy->id}"],
            'dosageInstruction' => [[
                'text'               => $mo->instructions,
                'route'              => ['text' => $mo->route],
                'timing'             => ['code' => ['text' => $mo->frequency]],
                'doseAndRate'        => [[
                    'doseQuantity' => [
                        'value' => (float) $mo->dose_value,
                        'unit'  => $mo->dose_unit,
                    ],
                ]],
            ]],
            'substitution' => ['allowedBoolean' => $mo->substitution_allowed],
        ];
    }

    // Condition → FHIR Condition
    public function conditionToFhir(Condition $condition): array
    {
        return [
            'resourceType' => 'Condition',
            'id'           => $condition->uuid,
            'clinicalStatus' => [
                'coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                              'code'   => $condition->clinical_status]],
            ],
            'verificationStatus' => [
                'coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                              'code'   => $condition->verification]],
            ],
            'category' => [[
                'coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                              'code'   => $condition->category]],
            ]],
            'severity' => $condition->severity ? [
                'coding' => [['system' => 'http://snomed.info/sct',
                              'code'   => match($condition->severity) {
                                  'mild'     => '255604002',
                                  'moderate' => '6736007',
                                  'severe'   => '24484000',
                              }]],
            ] : null,
            'code' => [
                'coding' => array_filter([
                    $condition->icd10_code ? [
                        'system'  => 'http://hl7.org/fhir/sid/icd-10-cm',
                        'code'    => $condition->icd10_code,
                        'display' => $condition->icd10_display,
                    ] : null,
                    $condition->snomed_code ? [
                        'system'  => 'http://snomed.info/sct',
                        'code'    => $condition->snomed_code,
                        'display' => $condition->snomed_display,
                    ] : null,
                ]),
                'text' => $condition->free_text ?? $condition->icd10_display,
            ],
            'subject'         => ['reference' => "Patient/{$condition->patient->uuid}"],
            'onsetDateTime'   => $condition->onset_date?->toDateString(),
            'abatementDateTime' => $condition->abatement_date?->toDateString(),
            'recordedDate'    => $condition->recorded_at->toIso8601String(),
        ];
    }
}
```

### FHIR Bundle — $everything operation

```php
class PatientFhirController extends Controller
{
    public function everything(string $id): JsonResponse
    {
        $patient = Patient::where('uuid', $id)->firstOrFail();
        $this->authorize('view', $patient);

        $mapper  = app(FhirMapper::class);
        $entries = [];

        // Patient
        $entries[] = ['resource' => $mapper->patientToFhir($patient)];

        // Encounters (last 12 months)
        $patient->encounters()
            ->where('period_start', '>=', now()->subYear())
            ->with(['diagnoses', 'provider'])
            ->get()
            ->each(fn($e) => $entries[] = ['resource' => $mapper->encounterToFhir($e)]);

        // Conditions (active)
        $patient->conditions()
            ->where('clinical_status', 'active')
            ->get()
            ->each(fn($c) => $entries[] = ['resource' => $mapper->conditionToFhir($c)]);

        // Allergies
        $patient->allergies()
            ->get()
            ->each(fn($a) => $entries[] = ['resource' => $mapper->allergyToFhir($a)]);

        // Vital signs (last 30 days)
        VitalSign::where('patient_id', $patient->id)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderByDesc('recorded_at')
            ->get()
            ->each(fn($v) => $entries[] = ['resource' => $mapper->vitalSignToFhir($v)]);

        // Lab results (last 90 days)
        LabResult::where('patient_id', $patient->id)
            ->where('resulted_at', '>=', now()->subDays(90))
            ->orderByDesc('resulted_at')
            ->get()
            ->each(fn($r) => $entries[] = ['resource' => $mapper->labResultToFhir($r)]);

        // Medications (active)
        MedicationOrder::whereHas('order', fn($q) => $q
            ->where('patient_id', $patient->id)
            ->where('status', 'active'))
            ->with('order')
            ->get()
            ->each(fn($m) => $entries[] = ['resource' => $mapper->medicationOrderToFhir($m)]);

        return response()->json([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'timestamp'    => now()->toIso8601String(),
            'entry'        => $entries,
        ]);
    }
}
```

---

## Telehealth

> Chỉ hỗ trợ video session qua HTTPS + WSS — không tích hợp PSTN call.

### Database

```sql
CREATE TABLE telehealth_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    appointment_id  BIGINT UNSIGNED NOT NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    provider_id     BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NULL,           -- tạo sau khi bắt đầu
    status          ENUM('scheduled','waiting','in-progress','completed','cancelled','no-show') DEFAULT 'scheduled',
    room_id         VARCHAR(100) NOT NULL,          -- unique room identifier
    -- Timing
    scheduled_at    TIMESTAMP NOT NULL,
    patient_joined_at  TIMESTAMP NULL,
    provider_joined_at TIMESTAMP NULL,
    ended_at        TIMESTAMP NULL,
    duration_seconds   INT UNSIGNED NULL,
    -- Technical
    patient_connection_quality  ENUM('good','fair','poor') NULL,
    provider_connection_quality ENUM('good','fair','poor') NULL,
    -- Patient access
    patient_token   VARCHAR(500) NULL,             -- JWT cho patient join
    patient_token_expires_at TIMESTAMP NULL,
    -- Audit
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    FOREIGN KEY (patient_id) REFERENCES patients(id)
) ENGINE=InnoDB;

CREATE TABLE telehealth_waiting_room_events (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id      BIGINT UNSIGNED NOT NULL,
    event_type      ENUM('patient_arrived','provider_arrived','patient_left','provider_left','session_started','session_ended') NOT NULL,
    actor           ENUM('patient','provider','system') NOT NULL,
    occurred_at     TIMESTAMP NOT NULL,
    metadata        JSON NULL,
    FOREIGN KEY (session_id) REFERENCES telehealth_sessions(id)
) ENGINE=InnoDB;
```

### TelehealthService

```php
class TelehealthService
{
    // Tạo session và generate secure patient token
    public function createSession(Appointment $appointment): TelehealthSession
    {
        $room = Str::uuid()->toString();

        $session = TelehealthSession::create([
            'uuid'           => Str::uuid(),
            'appointment_id' => $appointment->id,
            'patient_id'     => $appointment->patient_id,
            'provider_id'    => $appointment->provider_id,
            'room_id'        => $room,
            'scheduled_at'   => $appointment->start_at,
        ]);

        // Generate patient token (JWT, valid 2 giờ trước scheduled time)
        $this->generatePatientToken($session);

        return $session;
    }

    // Generate limited-scope token cho bệnh nhân
    private function generatePatientToken(TelehealthSession $session): void
    {
        $payload = [
            'sub'     => 'patient:' . $session->patient_id,
            'room'    => $session->room_id,
            'session' => $session->uuid,
            'iat'     => now()->timestamp,
            'exp'     => $session->scheduled_at->addHours(2)->timestamp,
            'perms'   => ['video:publish', 'video:subscribe'],
        ];

        $token = JWT::encode($payload, config('telehealth.secret'), 'HS256');

        $session->update([
            'patient_token'            => $token,
            'patient_token_expires_at' => $session->scheduled_at->addHours(2),
        ]);
    }

    // Bắt đầu session → tạo Encounter
    public function startSession(TelehealthSession $session, User $provider): Encounter
    {
        $encounter = Encounter::create([
            'uuid'               => Str::uuid(),
            'patient_id'         => $session->patient_id,
            'encounter_type'     => 'telehealth',
            'status'             => 'in-progress',
            'class'              => 'VR',                // virtual
            'period_start'       => now(),
            'attending_provider_id' => $provider->id,
            'department_id'      => $provider->department_id,
        ]);

        $session->update([
            'status'             => 'in-progress',
            'provider_joined_at' => now(),
            'encounter_id'       => $encounter->id,
        ]);

        $this->logWaitingRoomEvent($session, 'session_started', 'provider');

        return $encounter;
    }

    // Kết thúc session
    public function endSession(TelehealthSession $session): void
    {
        $duration = $session->provider_joined_at
            ? now()->diffInSeconds($session->provider_joined_at)
            : 0;

        $session->update([
            'status'           => 'completed',
            'ended_at'         => now(),
            'duration_seconds' => $duration,
        ]);

        // Finalize encounter
        if ($session->encounter_id) {
            Encounter::find($session->encounter_id)->update([
                'status'     => 'finished',
                'period_end' => now(),
            ]);
        }

        $this->logWaitingRoomEvent($session, 'session_ended', 'system');
    }

    private function logWaitingRoomEvent(TelehealthSession $session, string $type, string $actor): void
    {
        TelehealthWaitingRoomEvent::create([
            'session_id'  => $session->id,
            'event_type'  => $type,
            'actor'       => $actor,
            'occurred_at' => now(),
        ]);
    }
}
```

### Routes cho Telehealth

```php
Route::middleware(['auth:sanctum', 'audit.phi'])->prefix('v1/telehealth')->group(function () {
    Route::post('sessions', [TelehealthController::class, 'create']);
    Route::get('sessions/{uuid}', [TelehealthController::class, 'show']);
    Route::post('sessions/{uuid}/start', [TelehealthController::class, 'start']);
    Route::post('sessions/{uuid}/end', [TelehealthController::class, 'end']);
    Route::post('sessions/{uuid}/patient-arrived', [TelehealthController::class, 'patientArrived']);
    Route::get('sessions/{uuid}/waiting-room', [TelehealthController::class, 'waitingRoom']);
});

// Patient-facing — token auth (không cần Sanctum account)
Route::post('telehealth/join', [TelehealthController::class, 'patientJoin'])
    ->middleware('telehealth.token');
```

---

## Notification System

### Database

```sql
CREATE TABLE notifications (
    id              CHAR(36) PRIMARY KEY,          -- UUID (Laravel default)
    type            VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id   BIGINT UNSIGNED NOT NULL,
    data            JSON NOT NULL,
    read_at         TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    INDEX idx_notifiable (notifiable_type, notifiable_id, read_at)
) ENGINE=InnoDB;

CREATE TABLE notification_preferences (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(100) NOT NULL,       -- 'critical_lab','new_order','appointment_reminder'
    channel_inapp   BOOLEAN DEFAULT TRUE,
    channel_email   BOOLEAN DEFAULT FALSE,
    channel_sms     BOOLEAN DEFAULT FALSE,
    UNIQUE KEY (user_id, notification_type),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
```

### Notification Classes

```php
// Kết quả lab nguy kịch — gửi ngay, không queue delay
class CriticalLabValueNotification extends Notification implements ShouldQueue
{
    public string $queue    = 'critical';
    public int    $tries    = 5;
    public int    $timeout  = 30;

    public function __construct(private LabResult $result) {}

    public function via(object $notifiable): array
    {
        // Luôn gửi in-app + email cho critical values
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'critical_lab',
            'title'         => '⚠️ Kết quả nguy kịch cần xử lý',
            'message'       => "{$this->result->loinc_display}: {$this->result->value_quantity} {$this->result->value_unit} [{$this->result->abnormal_flag}]",
            'lab_result_id' => $this->result->id,
            'patient_id'    => $this->result->patient_id,
            'patient_name'  => $this->result->patient->currentDemographics->family_name,
            'action_url'    => "/patients/{$this->result->patient->uuid}/lab-results/{$this->result->id}",
            'requires_acknowledgment' => true,
            'acknowledge_by' => now()->addMinutes(30)->toIso8601String(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[URGENT] Kết quả nguy kịch — {$this->result->patient->mrn}")
            ->line("Kết quả xét nghiệm cần xử lý ngay:")
            ->line("**{$this->result->loinc_display}**: {$this->result->value_quantity} {$this->result->value_unit}")
            ->line("**Flag**: {$this->result->abnormal_flag}")
            ->action('Xem ngay', url("/patients/{$this->result->patient->uuid}/lab-results/{$this->result->id}"))
            ->line('Vui lòng acknowledge trong vòng 30 phút.');
    }
}

// Nhắc lịch hẹn — gửi trước 24h và 1h
class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(
        private Appointment $appointment,
        private string $reminderType  // '24h' | '1h'
    ) {}

    public function via(object $notifiable): array
    {
        $prefs = $notifiable->notificationPreference('appointment_reminder');
        $channels = ['database'];
        if ($prefs?->channel_email) $channels[] = 'mail';
        if ($prefs?->channel_sms)   $channels[] = 'vonage';  // hoặc Twilio
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'reminder_type'  => $this->reminderType,
            'message'        => "Lịch hẹn của bạn với {$this->appointment->provider->username} "
                              . "vào lúc {$this->appointment->start_at->format('H:i d/m/Y')}",
        ];
    }
}

// Order mới cần dược sĩ verify
class NewMedicationOrderNotification extends Notification implements ShouldQueue
{
    public string $queue = 'clinical';

    public function __construct(private MedicationOrder $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'new_medication_order',
            'order_id'   => $this->order->order_id,
            'drug_name'  => $this->order->drug_name,
            'patient_id' => $this->order->order->patient_id,
            'priority'   => $this->order->order->priority,
            'action_url' => "/pharmacy/orders/{$this->order->order_id}",
        ];
    }
}

// Kết quả lab đã có — cho ordering provider
class LabResultAvailableNotification extends Notification implements ShouldQueue
{
    public string $queue = 'clinical';

    public function __construct(private LabResult $result) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'lab_result_available',
            'lab_result_id' => $this->result->id,
            'loinc_display' => $this->result->loinc_display,
            'is_abnormal'   => $this->result->abnormal_flag !== 'N',
            'patient_id'    => $this->result->patient_id,
        ];
    }
}
```

### Critical Value Escalation Workflow

```php
// Chạy theo schedule: kiểm tra critical values chưa được acknowledge
class CheckUnacknowledgedCriticalValues implements ShouldQueue
{
    public function handle(): void
    {
        LabResult::where('is_critical', true)
            ->whereNull('acknowledged_at')
            ->where('resulted_at', '<', now()->subMinutes(30))   // quá 30 phút
            ->with(['patient', 'order.orderedBy'])
            ->chunk(100, function ($results) {
                foreach ($results as $result) {
                    // Escalate: thông báo charge nurse
                    $chargeNurse = $this->getChargeNurse($result->encounter->department_id);
                    $chargeNurse?->notify(new CriticalValueEscalationNotification($result));

                    // Log escalation
                    app(AuditService::class)->log('critical_value.escalated', [
                        'lab_result_id' => $result->id,
                        'patient_id'    => $result->patient_id,
                        'minutes_unacknowledged' => $result->resulted_at->diffInMinutes(now()),
                    ]);
                }
            });
    }
}
```

---

## Pharmacy — MAR & Medication Administration

### PharmacyService

```php
class PharmacyService
{
    // Verify order — dược sĩ review
    public function verifyOrder(MedicationOrder $order, User $pharmacist): void
    {
        // Chỉ pharmacist mới được verify
        abort_unless($pharmacist->role === 'pharmacist', 403);

        // Re-check drug interactions tại thời điểm verify
        $alerts = app(CdsAlertService::class)->checkOrder($order->order);
        if ($alerts->where('severity', 'critical')->isNotEmpty()) {
            throw new CriticalDrugInteractionException($alerts->where('severity', 'critical')->first());
        }

        DB::transaction(function () use ($order, $pharmacist) {
            $order->update([
                'verified_by_pharmacist' => $pharmacist->id,
                'verified_at'            => now(),
            ]);
            $order->order->update(['status' => 'active']);

            app(AuditService::class)->log('medication_order.verified', [
                'order_id'   => $order->order_id,
                'patient_id' => $order->order->patient_id,
            ]);

            // Notify điều dưỡng: thuốc đã sẵn sàng
            $nurse = $this->getAssignedNurse($order->order->encounter_id);
            $nurse?->notify(new MedicationReadyForAdministrationNotification($order));
        });
    }

    // Record medication administration (5 Rights enforced)
    public function recordAdministration(RecordAdministrationDto $dto, User $nurse): MedicationAdministration
    {
        // 1. Verify right patient (barcode check)
        $this->verifyPatientIdentity($dto->patientBarcode, $dto->patientId);

        // 2. Verify right medication (barcode check)
        $this->verifyMedicationBarcode($dto->medicationBarcode, $dto->orderId);

        // 3. Verify right time (± window)
        $this->verifyAdministrationWindow($dto->orderId);

        // 4. Create administration record
        return DB::transaction(function () use ($dto, $nurse) {
            $admin = MedicationAdministration::create([
                'uuid'                           => Str::uuid(),
                'order_id'                       => $dto->orderId,
                'patient_id'                     => $dto->patientId,
                'encounter_id'                   => $dto->encounterId,
                'status'                         => 'completed',
                'patient_verified_by_barcode'    => $dto->patientBarcodeVerified,
                'medication_verified_by_barcode' => $dto->medicationBarcodeVerified,
                'rxnorm_code'                    => $dto->rxnormCode,
                'drug_name'                      => $dto->drugName,
                'dose_value'                     => $dto->doseValue,
                'dose_unit'                      => $dto->doseUnit,
                'route'                          => $dto->route,
                'scheduled_at'                   => $dto->scheduledAt,
                'administered_at'                => now(),
                'administered_by'                => $nurse->id,
                'is_prn'                         => $dto->isPrn,
                'prn_indication'                 => $dto->prnIndication,
                'site'                           => $dto->site,
                'notes'                          => $dto->notes,
            ]);

            // Fire event → update MAR, pharmacy inventory
            MedicationAdministered::dispatch($admin);

            return $admin;
        });
    }

    private function verifyPatientIdentity(string $barcode, int $patientId): void
    {
        $patient = Patient::where('mrn', $barcode)->firstOrFail();
        abort_if($patient->id !== $patientId, 422, 'Patient identity mismatch — barcode không khớp');
    }

    private function verifyMedicationBarcode(string $barcode, int $orderId): void
    {
        // Parse barcode → NDC code → match với order drug
        // Throw nếu không match
    }

    private function verifyAdministrationWindow(int $orderId): void
    {
        $order = MedicationOrder::findOrFail($orderId);
        // Check scheduled time ± 30 phút
        // Warn nếu ngoài window, nhưng không block (ghi lại deviation)
    }
}
```

---

## API Response Format

Tất cả API responses tuân theo format chuẩn:

```php
// app/Http/Responses/ApiResponse.php
class ApiResponse
{
    public static function success(mixed $data, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success'   => false,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $resourceClass): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $resourceClass::collection($paginator),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

// Ví dụ sử dụng trong controller:
public function index(Request $request): JsonResponse
{
    $patients = Patient::active()
        ->with('currentDemographics')
        ->paginate($request->integer('per_page', 20));

    return ApiResponse::paginated($patients, PatientResource::class);
}
```

### Error Handling — Handler

```php
// app/Exceptions/Handler.php
public function register(): void
{
    $this->renderable(function (ModelNotFoundException $e, Request $request) {
        if ($request->expectsJson()) {
            return ApiResponse::error('Không tìm thấy dữ liệu', 404);
        }
    });

    $this->renderable(function (AuthorizationException $e, Request $request) {
        if ($request->expectsJson()) {
            // KHÔNG tiết lộ resource có tồn tại hay không (ngăn enumeration)
            return ApiResponse::error('Không có quyền truy cập', 403);
        }
    });

    $this->renderable(function (ValidationException $e, Request $request) {
        if ($request->expectsJson()) {
            return ApiResponse::error('Dữ liệu không hợp lệ', 422, $e->errors());
        }
    });

    $this->renderable(function (CriticalDrugInteractionException $e, Request $request) {
        return ApiResponse::error($e->getMessage(), 409, [
            'alert_type' => 'critical_drug_interaction',
            'details'    => $e->getAlertDetails(),
        ]);
    });

    // Log tất cả 5xx nhưng không leak stack trace trong response
    $this->reportable(function (\Throwable $e) {
        if ($e instanceof \Exception && !$e instanceof HttpException) {
            app(AuditService::class)->log('system.error', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                // Không log PHI trong stack trace
            ]);
        }
    });
}
```

---

## Artisan Commands

```php
// Xử lý HL7 messages từ queue (chạy liên tục)
class ProcessHl7MessageCommand extends Command
{
    protected $signature   = 'hl7:process {--queue=clinical}';
    protected $description = 'Process incoming HL7 messages from queue';

    public function handle(Hl7Parser $parser): void
    {
        $this->info('HL7 processor started...');

        Hl7Inbox::where('status', 'received')
            ->orderBy('created_at')
            ->chunk(50, function ($messages) use ($parser) {
                foreach ($messages as $msg) {
                    try {
                        $msg->update(['status' => 'processing']);
                        ProcessIncomingHl7Message::dispatch($msg);
                        $msg->update(['status' => 'processed', 'processed_at' => now()]);
                    } catch (\Throwable $e) {
                        $msg->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                        $this->error("Failed: {$msg->message_control_id} — {$e->getMessage()}");
                    }
                }
            });
    }
}

// Retry HL7 messages trong dead letter queue
class RetryDeadHl7Command extends Command
{
    protected $signature   = 'hl7:retry-dead {--limit=50}';
    protected $description = 'Retry dead HL7 outbox messages';

    public function handle(): void
    {
        Hl7Outbox::where('status', 'dead')
            ->limit($this->option('limit'))
            ->get()
            ->each(function ($msg) {
                $msg->update([
                    'status'   => 'pending',
                    'attempts' => 0,
                ]);
                SendOutgoingHl7Message::dispatch($msg);
                $this->line("Queued: {$msg->id} ({$msg->message_type})");
            });
    }
}

// Generate quality metrics hàng ngày
class GenerateQualityReportCommand extends Command
{
    protected $signature   = 'quality:generate {--date=}';
    protected $description = 'Generate daily quality metrics snapshot';

    public function handle(QualityMetricsService $svc): void
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now()->subDay();

        $from = $date->copy()->startOfDay();
        $to   = $date->copy()->endOfDay();

        $metrics = [
            'date'              => $date->toDateString(),
            'bed_occupancy'     => $svc->bedOccupancy($date),
            'alos_days'         => $svc->alos($from, $to),
            'ed_arrivals'       => $svc->edArrivals($from, $to),
            'admissions'        => $svc->admissions($from, $to),
            'discharges'        => $svc->discharges($from, $to),
            'lab_stat_tat_min'  => $svc->labTurnaround('STAT', $from, $to)['p50'],
            'critical_labs'     => $svc->criticalLabCount($from, $to),
            'unacknowledged_critical' => $svc->unacknowledgedCriticalCount($from, $to),
        ];

        QualityMetricSnapshot::create($metrics);
        $this->info("Quality metrics saved for {$date->toDateString()}");
    }
}

// Purge sessions hết hạn
class PurgeExpiredSessionsCommand extends Command
{
    protected $signature   = 'sessions:purge';
    protected $description = 'Remove expired user sessions';

    public function handle(): void
    {
        $count = UserSession::where('last_activity', '<', now()->subMinutes(15))->delete();
        $this->info("Purged {$count} expired sessions.");
    }
}

// Archive audit logs cũ sang cold storage
class ArchiveAuditLogsCommand extends Command
{
    protected $signature   = 'audit:archive {--before=}';
    protected $description = 'Archive audit logs older than specified date to cold storage';

    public function handle(): void
    {
        $before = Carbon::parse($this->option('before') ?? now()->subYears(2)->toDateString());

        // Export sang S3 dạng parquet/json per month
        AuditLog::where('event_at', '<', $before)
            ->chunk(10000, function ($logs, $page) use ($before) {
                $filename = "audit-archive/{$before->format('Y-m')}/batch-{$page}.jsonl";
                Storage::disk('cold')->put(
                    $filename,
                    $logs->map->toJson()->implode("\n")
                );
                $this->line("Archived batch {$page} to {$filename}");
            });
    }
}
```

---

## Scheduled Tasks

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Critical value escalation — check every 5 minutes
    $schedule->job(new CheckUnacknowledgedCriticalValues)
        ->everyFiveMinutes()
        ->name('check-critical-values')
        ->withoutOverlapping()
        ->onFailure(fn() => Log::error('Critical value check failed'));

    // Appointment reminders
    $schedule->command('appointment:send-reminders --hours=24')
        ->dailyAt('08:00')
        ->name('appointment-reminders-24h');

    $schedule->command('appointment:send-reminders --hours=1')
        ->hourly()
        ->name('appointment-reminders-1h');

    // Quality metrics snapshot
    $schedule->command('quality:generate')
        ->dailyAt('01:00')
        ->name('quality-metrics');

    // Retry dead HL7 messages
    $schedule->command('hl7:retry-dead')
        ->everyThirtyMinutes()
        ->name('hl7-retry-dead');

    // Session cleanup
    $schedule->command('sessions:purge')
        ->everyFifteenMinutes()
        ->name('session-purge');

    // Audit log archive (hàng tháng)
    $schedule->command('audit:archive --before=' . now()->subYears(2)->toDateString())
        ->monthly()
        ->name('audit-archive');

    // Password expiry reminder (nhắc nhở trước 14 ngày)
    $schedule->command('users:notify-password-expiry')
        ->dailyAt('07:00')
        ->name('password-expiry-reminder');
}
```

---

## config/health.php

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Clinical Settings
    |--------------------------------------------------------------------------
    */
    'critical_value_acknowledge_minutes' => env('CRITICAL_ACK_MINUTES', 30),

    'alert_fatigue' => [
        'max_alerts_per_session'   => 15,
        'override_rate_threshold'  => 0.90,  // 90% override → flag for review
        'alert_cooldown_minutes'   => 60,    // Không hiện lại cùng alert trong 60 phút
    ],

    'medication' => [
        'administration_window_minutes' => 30,  // ±30 phút so với giờ scheduled
        'high_alert_drugs_require_scan' => true,
        'high_alert_rxnorm_codes'       => [
            '5489',    // Hydrocodone
            '7052',    // Morphine
            '56795',   // Insulin
            '11289',   // Warfarin
            '3498',    // Digoxin
        ],
    ],

    'order' => [
        'stat_turnaround_minutes' => 60,    // TAT target cho STAT orders
        'routine_turnaround_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'session' => [
        'idle_timeout_minutes'      => env('SESSION_LIFETIME', 15),
        'max_concurrent_sessions'   => 3,
        'force_logout_on_breach'    => true,
    ],

    'password' => [
        'min_length'                => 12,
        'require_uppercase'         => true,
        'require_number'            => true,
        'require_special'           => true,
        'expiry_days'               => env('PASSWORD_EXPIRY_DAYS', 90),
        'history_count'             => 12,   // Không được dùng lại 12 passwords gần nhất
        'failed_attempts_lockout'   => 5,
        'lockout_minutes'           => 30,
    ],

    'mfa' => [
        'required'                  => env('MFA_REQUIRED', true),
        'totp_window'               => 1,    // ±1 TOTP period tolerance
        'backup_codes_count'        => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Settings
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'retention_years'           => 6,    // HIPAA minimum 6 năm
        'archive_after_years'       => 2,    // Archive sang cold storage sau 2 năm
        'phi_fields_to_never_log'   => [     // Không bao giờ xuất hiện trong log
            'password', 'mfa_secret', 'patient_token',
            'phone', 'email', 'address_line',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Terminology Lookup Config
    |--------------------------------------------------------------------------
    */
    'terminology' => [
        'loinc_version'     => '2.76',
        'snomed_version'    => '2024-03-01',
        'icd10_version'     => '2024',
        'rxnorm_version'    => 'current',
        'cache_ttl_hours'   => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration — Downtime Handling
    |--------------------------------------------------------------------------
    */
    'downtime' => [
        'cds_timeout_ms'            => 500,   // Fail-open sau 500ms
        'lis_timeout_seconds'       => 10,
        'ris_timeout_seconds'       => 10,
        'pharmacy_timeout_seconds'  => 10,
        'queue_max_size_warning'    => 500,   // Alert khi queue > 500
    ],

];
```

---

## Testing

```php
// tests/Feature/Patient/PatientControllerTest.php
class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_physician_can_view_assigned_patient(): void
    {
        $physician = User::factory()->physician()->create();
        $patient   = Patient::factory()->withDemographics()->create();
        $encounter = Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)
            ->getJson("/api/v1/patients/{$patient->uuid}")
            ->assertOk()
            ->assertJsonPath('data.id', $patient->uuid)
            ->assertJsonStructure(['data' => ['id', 'mrn', 'demographics']]);
    }

    public function test_unauthorized_user_cannot_view_patient(): void
    {
        $otherPhysician = User::factory()->physician()->create();
        $patient = Patient::factory()->withDemographics()->create();
        // Không có encounter với otherPhysician

        $this->actingAs($otherPhysician)
            ->getJson("/api/v1/patients/{$patient->uuid}")
            ->assertForbidden();  // 403, không phải 404 — jangan reveal existence
    }

    public function test_phi_access_is_audited(): void
    {
        $physician = User::factory()->physician()->create();
        $patient   = Patient::factory()->withDemographics()->create();
        Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)
            ->getJson("/api/v1/patients/{$patient->uuid}")
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event_type'    => 'phi.accessed',
            'user_id'       => $physician->id,
            'resource_type' => 'Patient',
        ]);
    }
}

// tests/Feature/Order/CpoeTest.php
class CpoeTest extends TestCase
{
    use RefreshDatabase;

    public function test_medication_order_triggers_pharmacy_notification(): void
    {
        Notification::fake();
        Queue::fake();

        $physician   = User::factory()->physician()->create();
        $pharmacist  = User::factory()->pharmacist()->create();
        $patient     = Patient::factory()->withDemographics()->create();
        $encounter   = Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $this->actingAs($physician)
            ->postJson("/api/v1/encounters/{$encounter->uuid}/orders", [
                'order_type' => 'medication',
                'priority'   => 'routine',
                'drug_name'  => 'Metformin',
                'rxnorm_code' => '861004',
                'dose_value'  => 500,
                'dose_unit'   => 'mg',
                'route'       => 'oral',
                'frequency'   => 'BID',
            ])
            ->assertCreated();

        Queue::assertPushedOn('clinical', SendOutgoingHl7Message::class);
        Notification::assertSentTo($pharmacist, NewMedicationOrderNotification::class);
    }

    public function test_cds_alert_does_not_block_order(): void
    {
        // CDS có alert nhưng order vẫn được tạo thành công
        $physician = User::factory()->physician()->create();
        $patient   = Patient::factory()
            ->withDemographics()
            ->withAllergy('Penicillin')          // có dị ứng
            ->create();
        $encounter = Encounter::factory()->for($patient)->inProgress()->create([
            'attending_provider_id' => $physician->id,
        ]);

        $response = $this->actingAs($physician)
            ->postJson("/api/v1/encounters/{$encounter->uuid}/orders", [
                'order_type'  => 'medication',
                'drug_name'   => 'Amoxicillin',  // penicillin-class
                'rxnorm_code' => '723',
                'dose_value'  => 500,
                'dose_unit'   => 'mg',
                'route'       => 'oral',
                'frequency'   => 'TID',
            ]);

        // Tạo được nhưng có warnings trong response
        $response->assertCreated()
                 ->assertJsonPath('data.cds_alerts.0.severity', 'warning');
    }

    public function test_audit_log_captures_order_cancellation(): void
    {
        $physician = User::factory()->physician()->create();
        $order     = Order::factory()
            ->for(Encounter::factory()->create(['attending_provider_id' => $physician->id]))
            ->active()
            ->create();

        $this->actingAs($physician)
            ->postJson("/api/v1/orders/{$order->uuid}/cancel", [
                'reason' => 'Patient refused medication',
            ])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'order.cancelled',
            'user_id'    => $physician->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);

        // KHÔNG bị xóa khỏi DB
        $this->assertModelExists($order->fresh());
    }
}

// tests/Feature/Medication/AdministrationTest.php
class MedicationAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_requires_barcode_verification(): void
    {
        $nurse   = User::factory()->nurse()->create();
        $patient = Patient::factory()->withDemographics()->create();
        $order   = MedicationOrder::factory()->verified()->create([
            'patient_id' => $patient->id,
        ]);

        // Sai MRN barcode → phải bị reject
        $this->actingAs($nurse)
            ->postJson("/api/v1/encounters/{$order->encounter_id}/medication-administrations", [
                'order_id'              => $order->order_id,
                'patient_barcode'       => 'WRONG-MRN',          // sai
                'medication_barcode'    => $order->medication_barcode,
                'administered_at'       => now()->toIso8601String(),
                'dose_value'            => $order->dose_value,
                'dose_unit'             => $order->dose_unit,
                'route'                 => $order->route,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Patient identity mismatch — barcode không khớp');
    }
}

// tests/Unit/Services/AuditServiceTest.php
class AuditServiceTest extends TestCase
{
    public function test_audit_log_hash_chain_is_consistent(): void
    {
        $service = app(AuditService::class);

        $service->log('phi.accessed', ['resource_type' => 'Patient', 'resource_id' => 1]);
        $service->log('phi.accessed', ['resource_type' => 'Patient', 'resource_id' => 2]);

        $logs = AuditLog::orderBy('id')->get();

        $this->assertCount(2, $logs);
        $this->assertNotEquals($logs[0]->log_hash, $logs[1]->log_hash);
    }

    public function test_phi_is_scrubbed_from_log_messages(): void
    {
        $scrubber = new ScrubPhiFromLogs();
        $dirty    = 'Processing patient John Doe DOB 1980-01-01 MRN: 123456 phone 555-1234';
        $clean    = $scrubber->scrub($dirty);

        $this->assertStringNotContainsString('John Doe', $clean);
        $this->assertStringNotContainsString('123456', $clean);
        $this->assertStringNotContainsString('555-1234', $clean);
    }
}
```

---

## Terminology Lookup Tables

Không hardcode codes — quản lý qua database:

```sql
CREATE TABLE terminology_value_sets (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vs_name     VARCHAR(100) NOT NULL,          -- 'VitalSignLoincCodes', 'DiabetesIcd10'
    description TEXT,
    version     VARCHAR(20) NOT NULL,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE terminology_concepts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    value_set_id    BIGINT UNSIGNED NOT NULL,
    code            VARCHAR(50) NOT NULL,
    code_system     VARCHAR(255) NOT NULL,      -- 'http://loinc.org', 'http://snomed.info/sct'
    display         VARCHAR(500) NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    valid_from      DATE,
    valid_to        DATE NULL,
    properties      JSON NULL,                  -- extra info (unit, scale, etc.)
    UNIQUE KEY (value_set_id, code, code_system),
    FOREIGN KEY (value_set_id) REFERENCES terminology_value_sets(id)
) ENGINE=InnoDB;

CREATE TABLE terminology_mappings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_system   VARCHAR(100) NOT NULL,      -- 'LIS_LOCAL', 'PHARMACY_LOCAL'
    source_code     VARCHAR(100) NOT NULL,
    source_display  VARCHAR(500),
    target_system   VARCHAR(255) NOT NULL,      -- 'http://loinc.org'
    target_code     VARCHAR(50) NOT NULL,
    target_display  VARCHAR(500),
    mapping_type    ENUM('exact','broad','narrow','related') DEFAULT 'exact',
    valid_from      DATE,
    valid_to        DATE NULL,
    mapped_by       BIGINT UNSIGNED NULL,       -- user who reviewed
    reviewed_at     TIMESTAMP NULL,
    UNIQUE KEY (source_system, source_code, target_system)
) ENGINE=InnoDB;
```

```php
// Dùng trong code — không bao giờ hardcode
class TerminologyService
{
    public function resolve(string $vsName, string $code): ?array
    {
        return Cache::remember("terminology.{$vsName}.{$code}", now()->addHours(24), function () use ($vsName, $code) {
            return TerminologyConcept::whereHas('valueSet', fn($q) => $q->where('vs_name', $vsName)->where('is_active', true))
                ->where('code', $code)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', today()))
                ->first()?->toArray();
        });
    }

    public function mapLocalCode(string $sourceSystem, string $sourceCode): ?array
    {
        return Cache::remember("terminology.map.{$sourceSystem}.{$sourceCode}", now()->addHours(24), fn() =>
            TerminologyMapping::where('source_system', $sourceSystem)
                ->where('source_code', $sourceCode)
                ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', today()))
                ->first()?->only(['target_system', 'target_code', 'target_display'])
        );
    }
}
```

---

## Downtime Procedures — Khi hệ thống gặp sự cố

```php
// HealthCheckController — endpoint /health
class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database'   => $this->checkDatabase(),
            'redis'      => $this->checkRedis(),
            'queue'      => $this->checkQueue(),
            'lis'        => $this->checkHl7Connection('LIS'),
            'ris'        => $this->checkHl7Connection('RIS'),
            'pharmacy'   => $this->checkHl7Connection('PHARMACY'),
            'disk_space' => $this->checkDiskSpace(),
        ];

        $healthy = collect($checks)->every(fn($c) => $c['status'] === 'ok');

        return response()->json([
            'status'    => $healthy ? 'healthy' : 'degraded',
            'checks'    => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');
            return ['status' => 'ok'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => 'DB unreachable'];
        }
    }

    private function checkQueue(): array
    {
        $pending = Hl7Outbox::where('status', 'pending')->count();
        $dead    = Hl7Outbox::where('status', 'dead')->count();

        return [
            'status'         => $pending > 500 ? 'warning' : 'ok',
            'pending_hl7'    => $pending,
            'dead_hl7'       => $dead,
        ];
    }

    private function checkDiskSpace(): array
    {
        $freeBytes = disk_free_space('/');
        $freePct   = round($freeBytes / disk_total_space('/') * 100, 1);

        return [
            'status'     => $freePct < 10 ? 'warning' : 'ok',
            'free_pct'   => $freePct,
        ];
    }
}

// Downtime mode — chỉ cho phép read-only access
class DowntimeController extends Controller
{
    // Endpoint đặc biệt: không cần full auth nhưng cần downtime token
    // Trả về cached snapshot của patient data để clinicians vẫn xem được
    public function patientSnapshot(string $mrn): JsonResponse
    {
        $snapshot = Cache::get("downtime.patient.{$mrn}");

        abort_if(! $snapshot, 404, 'Patient snapshot không có trong downtime cache');

        return response()->json([
            'mode'     => 'downtime',
            'warning'  => 'Dữ liệu có thể không cập nhật. Dùng cho tham khảo khẩn cấp.',
            'snapshot' => $snapshot,
            'cached_at' => Cache::get("downtime.patient.{$mrn}.cached_at"),
        ]);
    }
}

// Artisan: pre-warm downtime cache mỗi giờ
class WarmDowntimeCacheCommand extends Command
{
    protected $signature   = 'downtime:warm-cache';
    protected $description = 'Cache patient snapshots for downtime use';

    public function handle(): void
    {
        // Cache summary cho tất cả inpatient encounters hiện tại
        Encounter::where('status', 'in-progress')
            ->with(['patient.currentDemographics', 'patient.allergies', 'patient.conditions'])
            ->chunk(100, function ($encounters) {
                foreach ($encounters as $enc) {
                    $snapshot = [
                        'mrn'         => $enc->patient->mrn,
                        'name'        => $enc->patient->currentDemographics?->family_name,
                        'dob'         => $enc->patient->currentDemographics?->birth_date,
                        'allergies'   => $enc->patient->allergies->map->only(['substance_name', 'reaction_severity']),
                        'conditions'  => $enc->patient->conditions->map->only(['icd10_code', 'icd10_display']),
                        'department'  => $enc->department_id,
                        'bed'         => $enc->bed_id,
                    ];
                    Cache::put("downtime.patient.{$enc->patient->mrn}", $snapshot, now()->addHours(2));
                    Cache::put("downtime.patient.{$enc->patient->mrn}.cached_at", now()->toIso8601String(), now()->addHours(2));
                }
            });

        $this->info('Downtime cache warmed.');
    }
}
```

---

## Naming Conventions

```
Models:         PascalCase, singular        Patient, LabResult, MedicationOrder
Tables:         snake_case, plural          patients, lab_results, medication_orders
Controllers:    PascalCase + Controller     PatientController, LabResultController
Services:       PascalCase + Service        MpiService, CpoeService, AuditService
Jobs:           PascalCase, verb phrase     ProcessIncomingHl7Message, NotifyCriticalLabValue
Events:         PascalCase, past tense      PatientAdmitted, OrderPlaced, LabResultReceived
Policies:       PascalCase + Policy         PatientPolicy, OrderPolicy
DTOs:           PascalCase + Dto            PlaceOrderDto, AdmitPatientDto
Enums:          PascalCase                  EncounterStatus, OrderPriority, AbnormalFlag
```

---

## Environment Variables Bắt buộc

```ini
# App
APP_ENV=production
APP_KEY=                          # 32-char random key

# Database
DB_CONNECTION=mysql
DB_HOST=
DB_DATABASE=healthsystem
DB_USERNAME=
DB_PASSWORD=

# Redis
REDIS_HOST=
REDIS_PASSWORD=

# HL7 Connections (điền theo hệ thống tích hợp)
LIS_HOST=
LIS_PORT=6661
RIS_HOST=
RIS_PORT=6662
PHARMACY_HOST=
PHARMACY_PORT=6663

# Storage (S3-compatible)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=
AWS_ENDPOINT=                     # nếu dùng MinIO

# Security
SESSION_LIFETIME=15               # idle timeout minutes (HIPAA requirement)
PASSWORD_EXPIRY_DAYS=90
MFA_REQUIRED=true

# Queues
QUEUE_CONNECTION=redis
HORIZON_ENABLED=true

# Search
MEILISEARCH_HOST=
MEILISEARCH_KEY=

# Notifications
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Monitoring
SENTRY_DSN=                       # error tracking
LOG_CHANNEL=stack
LOG_LEVEL=warning                 # production: warning, không log PHI
```

---

## Các quy tắc tuyệt đối KHÔNG vi phạm

```
1. KHÔNG hard delete bất kỳ PHI record nào — chỉ soft delete hoặc logical deactivate
2. KHÔNG log PHI vào application logs (names, DOB, MRN, phone, email, IP)
3. KHÔNG gửi PHI trong URL/query string — dùng POST body hoặc encrypted path param
4. KHÔNG share user accounts — mỗi nhân viên một account với MFA
5. KHÔNG lưu plain text password hoặc credentials trong code/config/log
6. KHÔNG để CDS alert block việc đặt lệnh — CDS là advisory, không bao giờ interrupt
7. KHÔNG random split data cho analytics — dùng temporal split
8. KHÔNG cache PHI response không có expiry ngắn (max 5 phút)
9. KHÔNG skip audit log cho bất kỳ PHI access nào dù là internal job
10. KHÔNG hard-code terminology codes (ICD, LOINC) trong code — dùng lookup table
```

---

## Checklist trước khi deploy

```
Security:
□ MFA enabled cho tất cả accounts
□ TLS 1.2+ enforce (disable TLS 1.0/1.1)
□ AES-256 encryption cho sensitive DB columns
□ Audit log không thể bị xóa/sửa
□ PHI scrubbing middleware active
□ Rate limiting configured
□ Session idle timeout = 15 phút
□ Password complexity + expiry configured

Integration:
□ HL7 outbox pattern implemented (atomic với business events)
□ Idempotency check cho incoming HL7 (MSH-10)
□ DLQ configured cho failed messages
□ MLLP retry với exponential backoff

Clinical Safety:
□ Critical value workflow: notify → acknowledge → escalate
□ CDS alerts không block CPOE
□ Medication barcode verification bắt buộc
□ Allergy check tự động khi đặt lệnh thuốc
□ 5 Rights of Medication enforced

Operations:
□ DB backup daily, test restore hàng tuần
□ Queue monitoring (Horizon dashboard)
□ Error tracking (Sentry) configured
□ Health check endpoint /health
□ Downtime procedures documented
```
