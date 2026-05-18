# Chương 34-35 — Mental Models, Patterns & Anti-patterns

> Tài liệu này giúp bạn connect toàn bộ kiến thức lại và nhớ những gì không được làm.

---

## 1. The Big Picture — 5 Layers của Health IT

```
┌────────────────────────────────────────────────────┐
│  Layer 5 — GOVERNANCE LAYER                        │
│  HIPAA, ONC, FDA, TEFCA — Luật chơi               │
│  Tất cả layer bên dưới phải comply                │
├────────────────────────────────────────────────────┤
│  Layer 4 — DATA LAYER                              │
│  CDR, Data Warehouse, OMOP CDM                    │
│  Analytics và ML hoạt động ở đây                 │
├────────────────────────────────────────────────────┤
│  Layer 3 — INTEGRATION LAYER                       │
│  Interface Engine, HIE, FHIR API                  │
│  "Plumbing" của health IT                         │
├────────────────────────────────────────────────────┤
│  Layer 2 — APPLICATION LAYER                       │
│  EHR, CDSS, Telehealth, mHealth                   │
│  Nơi user tương tác — lập trình viên build đây   │
├────────────────────────────────────────────────────┤
│  Layer 1 — CLINICAL LAYER                          │
│  Bác sĩ, Điều dưỡng, Dược sĩ                     │
│  Workflow của họ là yêu cầu quan trọng nhất       │
└────────────────────────────────────────────────────┘
```

---

## 2. The Interoperability Pyramid

```
         ┌──────────────────┐
         │  ORGANIZATIONAL  │ ← Trust, BAA, TEFCA, governance
         ├──────────────────┤
         │   SEMANTIC       │ ← SNOMED, LOINC, ICD-10, RxNorm
         ├──────────────────┤
         │   STRUCTURAL     │ ← FHIR schema, CDA, HL7 v3
         ├──────────────────┤
         │  FOUNDATIONAL    │ ← HL7 v2 transport, MLLP
         └──────────────────┘

Giải thích:
- Foundational: Có thể gửi/nhận data (kết nối cơ bản)
- Structural:   Format được parse đúng (data structure)
- Semantic:     Ý nghĩa được hiểu đúng (code meaning)
- Organizational: Quy trình và policy align (trust)
```

---

## 3. PHI Decision Framework — Mỗi khi thiết kế feature mới

```
Feature mới có process/store/transmit data không?
                    │
                    ▼
        Có chứa PHI không?
         ┌──────────────┐
         │   CÓ PHI     │         │   KHÔNG PHI   │
         └──────┬───────┘         └───────────────┘
                │
                ▼
    1. Authentication & Authorization đúng chưa?
       → Least privilege; MFA nếu sensitive
       
    2. Audit log đã ghi lại đủ chưa?
       → Who, what, when, from where
       
    3. Encryption đúng chưa?
       → At rest (AES-256) + in transit (TLS 1.2+)
       
    4. Error messages có leak PHI không?
       → Sanitize trước khi return về client
       
    5. Đã test với clinical staff chưa?
       → Workflow phù hợp? Click count hợp lý?
```

---

## 4. Design Patterns phổ biến trong Health IT

| Pattern | Mô tả | Dùng khi nào |
|---------|-------|-------------|
| **Event Sourcing** | Lưu tất cả events thay vì state hiện tại | Audit trail bắt buộc; medication history |
| **CQRS** | Separate read model và write model | Analytics (read-heavy) vs documentation (write) |
| **Saga Pattern** | Distributed transaction qua choreography | Multi-system order: CPOE → Pharmacy → LIS |
| **Outbox Pattern** | Reliably publish events từ DB transaction | Ensure HL7 message gửi đi khi order lưu |
| **Circuit Breaker** | Stop calls đến failing service; fail fast | CDS service down → không block CPOE |
| **Dead Letter Queue** | Route failed messages để review | HL7 messages rejected → manual review |
| **Retry with backoff** | Retry với exponential backoff | HL7 delivery retry khi destination busy |

---

## 5. Anti-patterns nguy hiểm — KHÔNG được làm

| Anti-pattern | Hậu quả | Cách đúng |
|-------------|---------|-----------|
| **Log PHI vào application logs** | HIPAA violation | Implement PHI scrubbing trong logging pipeline |
| **Soft delete patient records** | PHI vẫn exposed | Dùng access control + archiving |
| **Cache PHI không encrypted** | Redis/Memcached expose PHI | Encrypt trước khi cache |
| **Query-by-name trả về list** | Expose PHI của người khác | Require thêm identifiers (DOB, MRN) |
| **Ignore timezone trong timestamps** | Medication timing errors | Normalize về UTC; store timezone |
| **Hard-code terminology codes** | Break khi update ICD/LOINC | Dùng data dictionary |
| **Skip interface testing với real data** | Miss encoding issues | Test với synthetic data cùng encoding |
| **Random split trong ML** | Data leakage; inflated metrics | Temporal split bắt buộc |
| **Black box model trong clinical** | Bác sĩ reject; FDA reject | Implement explainability (SHAP) |
| **Không test restore backup** | Không biết backup hoạt động | Test restore quarterly |

---

## 6. Clinical Workflow — Inpatient Admission (tóm tắt)

```
Bệnh nhân đến
    ↓
Registration → ADT^A01 → EHR, LIS, RIS, Pharmacy
    ↓
Triage → FHIR Encounter created; initial Observations
    ↓
Bác sĩ ra lệnh (CPOE) → ORM^O01 → LIS/RIS/Pharmacy
    ↓
Kết quả lab ← ORU^R01 ← LIS → EHR + alert nếu critical
    ↓
Điều dưỡng dùng thuốc (MAR, barcode scan) → FHIR MedicationAdministration
    ↓
Bác sĩ ghi discharge summary → FHIR Composition; HL7 MDM^T02
    ↓
Billing coding (ICD-10, CPT) → HL7 DFT^P03
    ↓
Xuất viện → ADT^A03 → billing, public health
             FHIR Bundle (Summary of Care) → HIE
```

---

## 7. Lab Result Workflow

```
CPOE order → ORC (order ID, provider, STAT/Routine)
    ↓
LIS accession → specimen barcode
    ↓
Analyzer → LIS (ASTM/HL7 instrument interface)
    ↓
Auto-verify nếu trong range
    ↓
Critical value? OBX-8 = "HH"/"LL" → pager/SMS alert
    ↓
ORU^R01 → EHR → bác sĩ acknowledge
    ↓
CPT code → billing
```

---

## 8. Quick Reference: Standards Map

```
Loại dữ liệu → Chuẩn nào:

Chẩn đoán bệnh          → ICD-10-CM
Thủ thuật                → CPT / ICD-10-PCS
Thuốc                    → RxNorm
Lab & vital signs        → LOINC
Clinical findings (rộng) → SNOMED CT
Provider identity        → NPI
Imaging                  → DICOM
Messaging                → HL7 v2.x
Modern API               → FHIR R4
Tài liệu lâm sàng       → CDA / FHIR Composition
Billing transactions     → X12
```

---

## 9. "Nếu... thì..." — Decision table

| Nếu bạn cần... | Dùng... |
|---------------|---------|
| Gửi lab order từ EHR đến LIS | HL7 ORM^O01 |
| Nhận kết quả lab về EHR | HL7 ORU^R01 |
| Update patient demographics | HL7 ADT^A08 |
| Build mobile health app | FHIR R4 + SMART on FHIR |
| Query patient medications | `GET /MedicationRequest?patient=123` |
| Export tất cả data của population | FHIR Bulk Data API |
| Nhúng CDS vào EHR | CDS Hooks |
| Lưu data cho nghiên cứu | OMOP CDM |
| Share data giữa các bệnh viện | HIE + TEFCA |
| Bảo vệ data y tế | HIPAA Technical Safeguards |
| Build interoperability hub | Interface Engine (Mirth Connect) |

---

## ✅ Mental Model cuối cùng

> **"Bệnh nhân trước, Technology sau."**
> 
> Mọi quyết định technical — alert design, data model, API, UX — đều phải đặt câu hỏi:
> **"Điều này có cải thiện/giảm nguy hại cho bệnh nhân không?"**
> 
> Đây là điều phân biệt healthcare dev với dev thông thường.