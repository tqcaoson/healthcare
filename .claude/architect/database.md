# Database Schema

## Core Tables

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
    systolic_bp     SMALLINT UNSIGNED NULL,   -- LOINC 8480-6
    diastolic_bp    SMALLINT UNSIGNED NULL,   -- LOINC 8462-4
    heart_rate      SMALLINT UNSIGNED NULL,   -- LOINC 8867-4
    respiratory_rate TINYINT UNSIGNED NULL,   -- LOINC 9279-1
    temperature     DECIMAL(4,1) NULL,        -- LOINC 8310-5
    temp_route      ENUM('oral','rectal','axillary','tympanic','temporal') NULL,
    spo2            TINYINT UNSIGNED NULL,    -- LOINC 59408-5
    weight_kg       DECIMAL(6,2) NULL,        -- LOINC 29463-7
    height_cm       DECIMAL(5,1) NULL,        -- LOINC 8302-2
    bmi             DECIMAL(5,2) NULL,        -- LOINC 39156-5 (computed)
    pain_score      TINYINT UNSIGNED NULL CHECK (pain_score BETWEEN 0 AND 10),
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
    rxnorm_code     VARCHAR(20) NULL,
    drug_name       VARCHAR(255) NOT NULL,
    dose_value      DECIMAL(10,3) NOT NULL,
    dose_unit       VARCHAR(20) NOT NULL,
    route           VARCHAR(50) NOT NULL,
    scheduled_at    TIMESTAMP NULL,
    administered_at TIMESTAMP NOT NULL,
    administered_by BIGINT UNSIGNED NOT NULL,             -- nurse user_id
    is_prn          BOOLEAN DEFAULT FALSE,
    prn_indication  TEXT NULL,
    prn_effectiveness TEXT NULL,
    site            VARCHAR(100) NULL,
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
    rxnorm_code     VARCHAR(20) NULL,
    substance_name  VARCHAR(255) NOT NULL,
    reaction_manifestation VARCHAR(500) NULL,
    reaction_severity ENUM('mild','moderate','severe') NULL,
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
    loinc_type_code VARCHAR(20) NOT NULL,                -- '11506-3'=progress, '18842-5'=discharge
    loinc_type_display VARCHAR(255) NOT NULL,
    note_type       ENUM('progress','admission','discharge','procedure','consult','nursing','other') NOT NULL,
    content         LONGTEXT NOT NULL,
    content_type    ENUM('text/plain','text/html') DEFAULT 'text/plain',
    status          ENUM('draft','preliminary','final','amended','corrected','cancelled') DEFAULT 'draft',
    attested_by     BIGINT UNSIGNED NULL,
    attested_at     TIMESTAMP NULL,
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
    icd10_code      VARCHAR(10) NULL,
    icd10_display   VARCHAR(500) NULL,
    snomed_code     VARCHAR(20) NULL,
    snomed_display  VARCHAR(500) NULL,
    free_text       VARCHAR(500) NULL,
    category        ENUM('encounter-diagnosis','problem-list-item','health-concern') DEFAULT 'encounter-diagnosis',
    clinical_status ENUM('active','recurrence','relapse','inactive','remission','resolved') DEFAULT 'active',
    verification    ENUM('unconfirmed','provisional','differential','confirmed','refuted','entered-in-error') DEFAULT 'confirmed',
    severity        ENUM('mild','moderate','severe') NULL,
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
    appointment_type VARCHAR(100) NULL,
    reason          VARCHAR(500) NULL,
    start_at        TIMESTAMP NOT NULL,
    end_at          TIMESTAMP NOT NULL,
    duration_minutes SMALLINT UNSIGNED,
    arrived_at      TIMESTAMP NULL,
    roomed_at       TIMESTAMP NULL,
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
    cpt_code        VARCHAR(10) NULL,
    cpt_display     VARCHAR(500) NULL,
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
    event_type      VARCHAR(100) NOT NULL,               -- 'phi.viewed','phi.modified','login.success'
    event_at        TIMESTAMP NOT NULL,
    user_id         BIGINT UNSIGNED NULL,
    user_name       VARCHAR(200) NULL,                   -- snapshot (user có thể bị xóa sau)
    user_role       VARCHAR(100) NULL,
    session_id      VARCHAR(255) NULL,
    ip_address      VARCHAR(45) NULL,
    user_agent      TEXT NULL,
    resource_type   VARCHAR(100) NULL,                   -- 'Patient','LabResult','Order'
    resource_id     BIGINT UNSIGNED NULL,
    patient_id      BIGINT UNSIGNED NULL,
    action          VARCHAR(50) NOT NULL,                -- 'view','create','update','delete','export','print'
    old_values      JSON NULL,
    new_values      JSON NULL,
    request_path    VARCHAR(500) NULL,
    request_method  VARCHAR(10) NULL,
    correlation_id  CHAR(36) NULL,
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
    payload         LONGTEXT NOT NULL,
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
    mfa_enabled     BOOLEAN DEFAULT TRUE,
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

-- ================================================================
-- TELEHEALTH
-- ================================================================

CREATE TABLE telehealth_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    appointment_id  BIGINT UNSIGNED NOT NULL,
    patient_id      BIGINT UNSIGNED NOT NULL,
    provider_id     BIGINT UNSIGNED NOT NULL,
    encounter_id    BIGINT UNSIGNED NULL,
    status          ENUM('scheduled','waiting','in-progress','completed','cancelled','no-show') DEFAULT 'scheduled',
    room_id         VARCHAR(100) NOT NULL,
    scheduled_at    TIMESTAMP NOT NULL,
    patient_joined_at  TIMESTAMP NULL,
    provider_joined_at TIMESTAMP NULL,
    ended_at        TIMESTAMP NULL,
    duration_seconds   INT UNSIGNED NULL,
    patient_connection_quality  ENUM('good','fair','poor') NULL,
    provider_connection_quality ENUM('good','fair','poor') NULL,
    patient_token   VARCHAR(500) NULL,
    patient_token_expires_at TIMESTAMP NULL,
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

-- ================================================================
-- NOTIFICATIONS
-- ================================================================

CREATE TABLE notifications (
    id              CHAR(36) PRIMARY KEY,
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
    notification_type VARCHAR(100) NOT NULL,
    channel_inapp   BOOLEAN DEFAULT TRUE,
    channel_email   BOOLEAN DEFAULT FALSE,
    channel_sms     BOOLEAN DEFAULT FALSE,
    UNIQUE KEY (user_id, notification_type),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
```
