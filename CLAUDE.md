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
