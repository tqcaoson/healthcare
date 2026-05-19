# Chương 14b — Data Engineering cho Healthcare

> **Mục tiêu:** Build production-grade data pipelines cho health data — từ raw HL7/FHIR đến analytics-ready.

---

## 1. Healthcare Data Sources — Map đầy đủ

```
Structured Data:
  EHR (HL7 v2 / FHIR)  → Lab, vitals, medications, diagnoses, procedures
  Claims (X12 837)      → Billing, insurance, utilization
  ADT feeds             → Admission/discharge events
  Pharmacy IS           → Dispensing records
  LIS                   → Lab raw results + reference ranges

Semi-structured Data:
  HL7 v2 messages       → Free-text NTE segments mixed with structured
  FHIR Resources        → JSON with coded + narrative fields
  CDA Documents         → XML clinical documents

Unstructured Data:
  Clinical notes        → Physician notes, nursing notes (80% of clinical info)
  Radiology reports     → Free text with impression/findings
  Discharge summaries   → Narrative with key clinical info
  Scanned documents     → PDF/image requiring OCR

External Data:
  Census / SDoH         → Demographics, socioeconomic indicators
  Weather / environment → Air quality, flu season predictors
  Claims benchmarks     → Normative values for comparison
```

---

## 2. Data Warehouse Architecture — Healthcare Pattern

### Medallion Architecture (Lake House)

```
Bronze (Raw)           Silver (Curated)        Gold (Serving)
────────────           ────────────────        ──────────────
Raw HL7 messages    →  Parsed, validated    →  OMOP CDM tables
Raw FHIR JSONs         Standardized codes       Reporting views
Flat files             Deduplication            ML feature store
Claims EDI             PHI handling            eCQM calculations
                       Terminology mapping      Patient cohorts
                       Audit lineage
```

### Công nghệ Stack phổ biến

| Layer | Open Source | Cloud |
|-------|------------|-------|
| Ingestion | Apache Kafka, NiFi | AWS Kinesis, Azure Event Hub |
| Storage | Delta Lake, Iceberg | S3, ADLS Gen2 |
| Processing | Apache Spark, dbt | Databricks, Azure Synapse |
| Orchestration | Airflow, Prefect | AWS MWAA, Azure Data Factory |
| Serving | PostgreSQL, Trino | BigQuery, Redshift, Snowflake |
| Visualization | Superset | Tableau, Power BI |

---

## 3. HL7 v2 to FHIR — Migration Pattern

Hầu hết bệnh viện vẫn có HL7 v2 legacy. Cần bridge sang FHIR cho analytics.

```python
# Pseudocode: HL7 v2 ADT^A01 → FHIR Patient + Encounter
def transform_adt_to_fhir(hl7_message: str) -> dict:
    msg = parse_hl7(hl7_message)
    
    patient = {
        "resourceType": "Patient",
        "id": msg.PID.field(3).component(1),  # MRN
        "name": [{
            "family": msg.PID.field(5).component(1),
            "given": [msg.PID.field(5).component(2)]
        }],
        "birthDate": format_date(msg.PID.field(7)),  # YYYYMMDD → YYYY-MM-DD
        "gender": map_gender(msg.PID.field(8))       # M/F/U → male/female/unknown
    }
    
    encounter = {
        "resourceType": "Encounter",
        "status": "in-progress" if msg.MSH.field(9) == "ADT^A01" else "finished",
        "subject": {"reference": f"Patient/{patient['id']}"},
        "period": {"start": format_datetime(msg.PV1.field(44))}
    }
    
    return {"patient": patient, "encounter": encounter}
```

---

## 4. Temporal Handling — Lỗi hay gặp nhất

Healthcare data có nhiều timestamps — phải phân biệt rõ:

| Timestamp | Nghĩa | FHIR Field |
|-----------|-------|-----------|
| **Event time** | Khi event thực sự xảy ra | Observation.effective |
| **Record time** | Khi data được nhập vào hệ thống | Observation.issued |
| **Processing time** | Khi pipeline xử lý | ETL metadata |

**Vấn đề late arrival:** Lab result order lúc 8am, result arrive lúc 10am, nhập vào EHR lúc 11am, pipeline chạy lúc 2am hôm sau.  
→ **Phải dùng event time**, không phải processing time, cho clinical analytics.

```sql
-- Sai: dùng processing time
WHERE etl_loaded_at >= CURRENT_DATE - 1

-- Đúng: dùng event time với grace period cho late arrival
WHERE observation_datetime >= CURRENT_DATE - 1
  AND etl_loaded_at <= CURRENT_DATE + INTERVAL '3 days'  -- cho phép late arrival 3 ngày
```

---

## 5. Terminology Mapping Pipeline

```
Raw source data (local codes)
        ↓
Terminology Mapping Service
  - Lookup local_code → LOINC/SNOMED/RxNorm
  - Version-controlled mapping tables
  - Unmapped codes → flagged for review
        ↓
Standardized coded data
        ↓
OMOP CDM (uses standard_concept_id)
```

### Mapping table structure

```sql
CREATE TABLE terminology_mapping (
  source_system       VARCHAR,   -- 'LIS_HOSPITAL_A', 'EHR_EPIC'
  source_code         VARCHAR,   -- 'GLU'
  source_display      VARCHAR,   -- 'Glucose POC'
  target_system       VARCHAR,   -- 'http://loinc.org'
  target_code         VARCHAR,   -- '2345-7'
  target_display      VARCHAR,   -- 'Glucose [Mass/volume] in Serum'
  mapping_type        VARCHAR,   -- 'EXACT', 'BROAD', 'NARROW'
  valid_from          DATE,
  valid_to            DATE,       -- NULL = still active
  mapped_by           VARCHAR,   -- clinical expert who reviewed
  reviewed_date       DATE
);
```

---

## 6. Data Quality Framework

### Dimensions đo Data Quality trong Healthcare

| Dimension | Mô tả | Cách đo |
|-----------|-------|---------|
| **Completeness** | Dữ liệu có đầy đủ không? | % records có field bắt buộc |
| **Correctness** | Dữ liệu có đúng không? | % values trong valid range |
| **Consistency** | Dữ liệu nhất quán giữa systems? | Lab result có match EHR? |
| **Timeliness** | Dữ liệu có đến đúng giờ không? | Median lag time: event → available |
| **Uniqueness** | Có duplicate không? | Duplicate rate per entity |
| **Conformance** | Có đúng chuẩn không? | % records valid theo FHIR profile |

### Automated DQ Checks

```python
# Great Expectations / dbt tests
checks = [
    # Completeness
    expect_column_to_not_be_null("patient_id"),
    expect_column_to_not_be_null("observation_datetime"),
    
    # Range validity
    expect_column_values_to_be_between("systolic_bp", min=50, max=300),
    expect_column_values_to_be_between("heart_rate", min=20, max=300),
    
    # Referential integrity
    expect_column_values_to_be_in_set("gender", ["male", "female", "other", "unknown"]),
    expect_column_values_to_match_regex("loinc_code", r"^\d{1,5}-\d$"),
    
    # Freshness
    expect_table_row_count_to_be_between(min=1000, max=1000000),  # catch empty loads
]
```

---

## 7. De-identification Pipeline cho Research

```
Clinical Database (PHI)
        ↓
Step 1: Suppress direct identifiers
  - Remove name, SSN, phone, email, address
  - Replace MRN với pseudonymous research ID (maintain linkage key separately)
  
        ↓
Step 2: Generalize quasi-identifiers
  - Dates → shift randomly (-365 to +365 days, consistent per patient)
  - Age > 89 → "90+"
  - Zipcode → first 3 digits (check population ≥ 20k)

        ↓
Step 3: Handle free text (NLP)
  - NER to detect PHI in notes
  - Replace with [PERSON], [DATE], [LOCATION] tags
  
        ↓
Step 4: Validate re-identification risk
  - k-anonymity check (k ≥ 5)
  - l-diversity check
  
        ↓
Research Database (De-identified)
```

---

## 8. FHIR-native Data Engineering

Modern approach: Store tất cả data dưới dạng FHIR resources natively.

```
FHIR Server (HAPI FHIR / Azure AHDS / Google FHIR)
        ↓
FHIR Bulk Export (ndjson)
        ↓
FHIR-to-columnar transformation
  (Google Healthcare API / AWS HealthLake auto-flatten)
        ↓
BigQuery / Snowflake với FHIR schema
        ↓
Analytics với SQL trên flattened FHIR
```

```sql
-- Query FHIR data trong BigQuery (auto-flattened)
SELECT
  p.id AS patient_id,
  p.name[OFFSET(0)].family AS last_name,
  o.effective.dateTime AS obs_date,
  o.value.quantity.value AS glucose_value,
  o.value.quantity.unit AS unit
FROM fhir.Patient p
JOIN fhir.Observation o 
  ON JSON_EXTRACT_SCALAR(o.subject.reference, '$.reference') = CONCAT('Patient/', p.id)
WHERE o.code.coding[OFFSET(0)].code = '2345-7'  -- LOINC Glucose
  AND DATE(o.effective.dateTime) >= DATE_SUB(CURRENT_DATE(), INTERVAL 90 DAY)
```

---

## ✅ Checklist sau chương này

- [ ] Tôi hiểu Bronze/Silver/Gold architecture và mỗi layer làm gì
- [ ] Tôi biết phân biệt Event time vs Record time vs Processing time
- [ ] Tôi biết late arrival handling trong healthcare ETL
- [ ] Tôi biết 6 dimensions của data quality và cách đo
- [ ] Tôi hiểu de-identification pipeline 4 bước
- [ ] Tôi biết FHIR Bulk Export format (ndjson) và dùng để làm gì

**Tiếp theo:** `07-governance-legal/01-luat-quy-dinh.md` →