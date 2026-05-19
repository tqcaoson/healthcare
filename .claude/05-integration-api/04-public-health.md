# Chương — Public Health Informatics: ELR, Syndromic Surveillance & IIS

> **Mục tiêu:** Hiểu cách bệnh viện "nói chuyện" với cơ quan y tế công cộng — chuẩn kỹ thuật và data flows.

---

## 1. Public Health Informatics là gì?

Public Health Informatics = ứng dụng IT để bảo vệ sức khỏe **dân số** (không phải từng bệnh nhân).

### Các hoạt động chính

| Hoạt động | Mô tả | IT System |
|---------|-------|----------|
| **Disease Surveillance** | Theo dõi bệnh trong dân số | NEDSS, BioSense |
| **Outbreak Detection** | Phát hiện cluster bất thường | ESSENCE, EARS |
| **Immunization Registry** | Track vaccination toàn dân số | IIS (mỗi bang) |
| **Laboratory Reporting** | Auto-report kết quả bệnh bắt buộc khai báo | ELR system |
| **Contact Tracing** | Track contacts của ca bệnh | CRMs, điều tra dịch tễ |
| **Emergency Response** | Coordinate phản ứng khẩn cấp | PHEP, EOC systems |

---

## 2. Notifiable Disease Reporting — Bắt buộc báo cáo

Hầu hết các bệnh quốc gia đều có danh sách **Nationally Notifiable Conditions** — phải báo cáo cho cơ quan y tế.

### Phân loại theo tốc độ báo cáo

| Loại | Timing | Ví dụ |
|------|--------|-------|
| **Immediate (24h)** | Ngay khi nghi ngờ — không cần confirm | Anthrax, botulism, smallpox, SARS, Ebola |
| **Urgent (24-48h)** | Lab confirm hoặc strongly suspected | Measles, rabies, TB (new case) |
| **Standard (1 week)** | Routine lab confirm | Syphilis, gonorrhea, hepatitis B, Salmonella |
| **Aggregated (monthly)** | Counts — không phải individual cases | Influenza deaths, Lyme disease (một số bang) |

### Ai báo cáo?

```
Phòng xét nghiệm (LIS)  ──→ ELR ──→ State Health Dept
Bác sĩ / Bệnh viện      ──→ Manual report (phone/fax/online form)
Pharmacy                 ──→ (một số conditions)
```

---

## 3. ELR — Electronic Lab Reporting (Chi tiết kỹ thuật)

### HL7 Message Spec cho ELR

ELR dùng **HL7 v2.5.1** với **CDC ELR Implementation Guide** — không phải generic HL7.

Key differences so với standard ORU^R01:

| Element | Standard HL7 | ELR Requirement |
|---------|-------------|-----------------|
| Test code | Local code OK | LOINC bắt buộc |
| Organism | Free text OK | SNOMED bắt buộc cho organisms |
| Provider | Name OK | NPI bắt buộc |
| Patient ID | MRN OK | Multiple identifiers cần |
| Sending facility | Name | OID (unique org identifier) bắt buộc |

### ELR Message Example

```
MSH|^~\&|LIS|0123456789^HOSPITAL_OID^2.16.840.1.113883.3.7.1.99999|
    STATE_LIMS|StateHD|20240115120000||ORU^R01|MSG20240115001|P|2.5.1|||NE|AL|USA
PID|1||MRN123^^^HOSPITAL_OID^MR~SSN123^^^&2.16.840.1.113883.4.1&ISO^SS||
    Nguyen^Van^A||19800101|M|||123 Main St^^Hanoi^^VN||555-1234
OBR|1|ORD001^LIS|ACC001^LIS|31777-2^Chlamydia trachomatis DNA [Presence] Genital^LN|||
    20240114|||||||20240115|
OBX|1|CWE|31777-2^Chlamydia trachomatis DNA [Presence] Genital^LN||
    10828004^Positive^SCT||||||F|||20240115
SPM|1|SPEC001^LIS||258497007^Cervical swab^SCT|||||||P
```

### Transport — PHIN Messaging System

```
LIS → PHIN MS (Public Health Information Network Messaging System)
    → SOAP/HTTPS
    → State Health Department

Alternative (newer): FHIR-based ELR
    → FHIR Bundle (DiagnosticReport + Observation)
    → POST to state FHIR endpoint
```

---

## 4. Syndromic Surveillance — Deep Dive

### Syndrome Definitions (CDC Standard)

| Syndrome | Chief Complaint Keywords | ICD-10 Codes |
|---------|------------------------|-------------|
| **ILI** (Influenza-Like Illness) | fever, flu, influenza, chills, body ache | J09-J11, J00, R50.9 |
| **GI** (Gastrointestinal) | vomit, diarrhea, nausea, stomach | A08-A09, K52, R11 |
| **Respiratory** (non-ILI) | cough, SOB, pneumonia, bronchitis | J12-J18, J40-J47 |
| **Rash** | rash, skin, measles, chickenpox | B01-B09, L50-L54 |
| **Neurological** | headache, seizure, altered mental | G40-G41, R51 |
| **Hemorrhagic Illness** | bleeding, hemorrhage, bruising | D65-D69 |

### ESSENCE Query (SQL-like)

```sql
-- Query ILI cases, last 7 days, compare to baseline
SELECT
    DATE(visit_datetime)    AS visit_date,
    COUNT(*)                AS observed_count,
    AVG(baseline_count)     AS expected_baseline,
    COUNT(*) / AVG(baseline_count) AS ratio
FROM syndrome_data
WHERE syndrome = 'ILI'
  AND visit_datetime >= CURRENT_DATE - 7
  AND zip_code BETWEEN '700000' AND '799999'  -- geographic filter
GROUP BY DATE(visit_datetime)
HAVING ratio > 2.0  -- 2x above baseline → alert
ORDER BY visit_date;
```

### BioSense Platform (CDC)

BioSense Platform nhận data từ EDs trên toàn quốc:
- **Data elements:** chief complaint, triage notes, ICD codes, age, sex, zip, visit type
- **Latency:** <24 hours từ ED visit đến available trong BioSense
- **Volume:** ~60 million ED visits/year nationwide
- **Use:** Real-time national surveillance; COVID-19 tracking; flu monitoring

---

## 5. Immunization Information Systems (IIS) — Chi tiết kỹ thuật

### IIS Data Model

```sql
-- Simplified IIS schema
CREATE TABLE patient (
    iis_patient_id  BIGINT PRIMARY KEY,
    first_name      VARCHAR,
    last_name       VARCHAR,
    dob             DATE,
    sex             CHAR(1),
    -- Multiple address history
    current_zip     VARCHAR(5)
);

CREATE TABLE immunization (
    imm_id          BIGINT PRIMARY KEY,
    patient_id      BIGINT REFERENCES patient,
    vaccine_cvx     VARCHAR(3),      -- CVX code (CDC vaccine code)
    vaccine_ndc     VARCHAR(11),     -- NDC of specific lot
    admin_date      DATE,
    lot_number      VARCHAR,
    expiration_date DATE,
    admin_site      VARCHAR,         -- LA = left arm, RA = right arm...
    admin_route     VARCHAR,         -- IM, SC, oral...
    provider_npi    VARCHAR(10),
    facility_id     VARCHAR,
    information_source VARCHAR,     -- 00=new, 01=historical
);
```

### CVX Codes — Vaccine Codes (Must Know)

| CVX | Vaccine | Schedule |
|-----|---------|---------|
| **141** | Influenza, seasonal, injectable | Annual |
| **08** | Hepatitis B, adult | 3 doses: 0, 1, 6 months |
| **110** | DTaP-Hep B-IPV (Pediarix) | 3 doses: 2, 4, 6 months |
| **133** | PCV13 (Prevnar) | 4 doses: 2, 4, 6, 12-15 months |
| **03** | MMR | 2 doses: 12-15 months, 4-6 years |
| **21** | Varicella | 2 doses: 12-15 months, 4-6 years |
| **20** | DTaP | 5 doses in childhood |
| **115** | Tdap | Booster ≥11 years |
| **212** | COVID-19 mRNA (Moderna) | Per current schedule |
| **208** | COVID-19 mRNA (Pfizer) | Per current schedule |

### VXU vs QBP — Khi nào dùng cái nào

```python
# VXU^V04: Submit vaccination record → IIS
# Dùng khi: bệnh nhân vừa được tiêm, cần gửi lên IIS

def submit_vaccination(patient, immunization):
    vxu_message = build_hl7_vxu(
        patient=patient,
        cvx_code=immunization.cvx,
        admin_date=immunization.date,
        lot_number=immunization.lot,
        provider_npi=immunization.provider
    )
    send_to_iis(vxu_message)

# QBP^Q11: Query vaccination history ← IIS
# Dùng khi: bệnh nhân đến khám, cần xem đã tiêm gì

def get_vaccination_history(patient):
    query = build_qbp_q11(
        patient_name=patient.name,
        patient_dob=patient.dob,
        patient_gender=patient.gender
    )
    response = send_to_iis_and_await_rsp(query)
    return parse_rsp_k11(response)  # returns list of immunizations
```

### VFC (Vaccines for Children) Program

VFC = federally-funded vaccines cho children who can't afford. EHR phải track:
- Patient VFC eligibility (Medicaid, uninsured, American Indian/Alaska Native)
- Vaccines administered as VFC stock vs private stock
- Report to state VFC program via IIS

```
OBX segment trong VXU:
OBX|1|CE|64994-7^Funding eligibility^LN||
    V02^VFC eligible – Medicaid^HL70064
```

---

## 6. NHSN — HAI Reporting

**NHSN** = National Healthcare Safety Network (CDC) — bắt buộc với hospitals nhận Medicare.

### HAI Metrics phải report hàng tháng

| HAI | Denominator | Metric |
|-----|-------------|--------|
| **CLABSI** | Central line days | Infections per 1000 line-days |
| **CAUTI** | Urinary catheter days | Infections per 1000 catheter-days |
| **VAP/VAE** | Ventilator days | Events per 1000 vent-days |
| **SSI** | Surgical procedures | Infections per 100 procedures by type |
| **MRSA** | Patient days | Bacteremia per 10,000 patient-days |
| **C.diff** | Facility-days | Lab-identified events per 10,000 facility-days |

### NHSN CDA Reporting

```xml
<!-- CDA document submitted to NHSN via SOAP -->
<ClinicalDocument>
  <templateId root="2.16.840.1.113883.10.20.5.1.1"/>
  <!-- NHSN template for HAI reporting -->
  
  <component>
    <structuredBody>
      <!-- CLABSI Numerator Data -->
      <component>
        <section>
          <templateId root="2.16.840.1.113883.10.20.5.4.2"/>
          <entry>
            <!-- BSI event details -->
            <observation classCode="OBS" moodCode="EVN">
              <code code="INFECTION" codeSystem="2.16.840.1.114222.4.5.232"/>
              <effectiveTime value="20240115"/>
              <!-- organism, type, location details -->
            </observation>
          </entry>
        </section>
      </component>
    </structuredBody>
  </component>
</ClinicalDocument>
```

---

## 7. FHIR-based ELR — Hướng đi hiện đại

CDC và ONC đang đẩy các bang chuyển sang **FHIR-based ELR** thay vì HL7 v2.

### So sánh HL7 v2 vs FHIR ELR

| Khía cạnh | HL7 v2.5.1 ELR | FHIR R4 ELR |
|-----------|---------------|------------|
| Format | Pipe-delimited text | JSON/XML |
| Transport | MLLP / SOAP | REST HTTPS |
| Validation | Manual / limited | Automatic schema + terminology |
| Implementation | Complex, need HL7 expertise | Easier, standard REST |
| Adoption | Legacy — still majority | Growing — mandated for new |
| Test | Hard to test locally | Sandbox servers available |

### FHIR ELR Bundle Structure

```json
POST /Bundle

{
  "resourceType": "Bundle",
  "type": "message",
  "entry": [
    {
      "resource": {
        "resourceType": "MessageHeader",
        "eventCoding": {
          "system": "http://terminology.hl7.org/CodeSystem/v2-0003",
          "code": "R01",
          "display": "ORU/ACK - Unsolicited transmission of an observation"
        },
        "destination": [{
          "name": "State Health Department LIMS",
          "endpoint": "https://statehd.example.gov/fhir"
        }],
        "sender": {
          "reference": "Organization/hospital-a"
        },
        "focus": [{"reference": "DiagnosticReport/elr-001"}]
      }
    },
    {
      "resource": {
        "resourceType": "DiagnosticReport",
        "id": "elr-001",
        "status": "final",
        "category": [{
          "coding": [{"system": "http://loinc.org", "code": "LAB"}]
        }],
        "code": {
          "coding": [{
            "system": "http://loinc.org",
            "code": "31777-2",
            "display": "Chlamydia trachomatis DNA [Presence] Genital"
          }]
        },
        "subject": {"reference": "Patient/patient-001"},
        "result": [{"reference": "Observation/obs-001"}]
      }
    },
    {
      "resource": {
        "resourceType": "Observation",
        "id": "obs-001",
        "status": "final",
        "code": {
          "coding": [{"system": "http://loinc.org", "code": "31777-2"}]
        },
        "valueCodeableConcept": {
          "coding": [{
            "system": "http://snomed.info/sct",
            "code": "10828004",
            "display": "Positive"
          }]
        },
        "performer": [{"reference": "Organization/lab-a"}]
      }
    }
  ]
}
```

---

## 8. Electronic Death Registration System (EDRS)

### Tại sao quan trọng với IT?

Death certificates là nguồn dữ liệu quan trọng cho:
- Mortality surveillance (nguyên nhân tử vong dân số)
- COVID-19 death tracking
- Opioid overdose monitoring
- Cancer mortality rates

### Quy trình (truyền thống vs điện tử)

```
Truyền thống (vẫn còn phổ biến):
  Bác sĩ viết giấy → Funeral home → Vital records office → nhập máy thủ công
  → Lag: 2-4 tuần

Điện tử (EDRS):
  Bác sĩ ký điện tử trong EHR/EDRS portal
       ↓
  Funeral home điền thông tin tang lễ
       ↓
  Vital records office xác nhận
       ↓
  Auto-feed vào CDC NVSS (National Vital Statistics System)
  → Lag: 24-72 giờ
```

### IT Integration với EHR

```python
# Trigger death certificate workflow khi patient status = deceased
def handle_patient_death(patient_id: str, death_datetime: str, attending_physician_npi: str):
    # Update FHIR Patient resource
    patch_patient_deceased(patient_id, death_datetime)
    
    # Notify EDRS system
    edrs_case = create_edrs_case(
        patient_id=patient_id,
        death_datetime=death_datetime,
        facility_id=current_facility_id,
        attending_npi=attending_physician_npi
    )
    
    # Pre-populate death certificate với data từ EHR
    prefill_data = {
        'diagnoses': get_active_conditions(patient_id),
        'last_admission': get_last_encounter(patient_id),
        'demographics': get_patient_demographics(patient_id)
    }
    
    send_to_edrs(edrs_case, prefill_data)
    
    # Notify family, billing, organ donation coordinator
    trigger_downstream_workflows(patient_id, 'DECEASED')
```

---

## 9. Social Determinants of Health (SDoH)

### SDoH là gì?

> **"Zip code predicts health outcomes better than genetic code."**

SDoH = điều kiện sống ảnh hưởng đến sức khỏe: thu nhập, nhà ở, thực phẩm, giáo dục, môi trường.

| Domain | Ví dụ SDoH | Impact lên sức khỏe |
|--------|-----------|---------------------|
| **Economic stability** | Thất nghiệp, nghèo, nợ nần | Stress, không mua được thuốc |
| **Food** | Food insecurity, "food desert" | Malnutrition, diabetes khó kiểm soát |
| **Housing** | Vô gia cư, nhà không an toàn | Respiratory disease, injury |
| **Transportation** | Không có xe, xa bệnh viện | No-show appointments, late diagnosis |
| **Education** | Literacy thấp | Không hiểu hướng dẫn thuốc |
| **Social isolation** | Sống một mình, không có support | Depression, CHF rehospitalization |

### SDoH Screening trong EHR

**FHIR Resources cho SDoH:**
- `Observation` — kết quả screening (food insecurity = yes/no)
- `Condition` — SDoH diagnosis (Z codes trong ICD-10)
- `ServiceRequest` — referral đến community resources
- `Task` — follow up với patient về SDoH needs

**ICD-10 Z codes cho SDoH:**
```
Z59.0  = Homelessness
Z59.4  = Lack of adequate food
Z59.1  = Inadequate housing
Z60.4  = Social exclusion and rejection
Z63.0  = Problems in relationship with spouse/partner
Z56.0  = Unemployment
Z72.3  = Lack of physical exercise
```

### FHIR Gravity Project — SDoH Standard

```json
// FHIR Observation: Food Insecurity Screening Result
{
  "resourceType": "Observation",
  "status": "final",
  "category": [{
    "coding": [{
      "system": "http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes",
      "code": "sdoh-category-unspecified"
    }]
  }],
  "code": {
    "coding": [{
      "system": "http://loinc.org",
      "code": "88122-7",
      "display": "Within the past 12 months we worried whether our food would run out before we got money to buy more"
    }]
  },
  "subject": {"reference": "Patient/123"},
  "valueCodeableConcept": {
    "coding": [{
      "system": "http://loinc.org",
      "code": "LA28397-0",
      "display": "Often true"
    }]
  }
}
```

### Closed-Loop Referral — SDoH Workflow

```
EHR Screen (FHIR Observation)
       ↓
Identify Need (food insecurity, housing)
       ↓
Generate Referral (FHIR ServiceRequest)
       ↓
Community Resource Platform (e.g., findhelp.org, Unite Us, 211)
  - Food bank
  - Housing assistance  
  - Transportation
       ↓
Outcome Feedback (FHIR Task updated)
  → "Referral completed", "Client enrolled", "Need met"
       ↓
Track SDoH outcomes in population health dashboard
```

---

## 10. Public Health Emergency Response (PHEP)

### Medical Surge — IT phải hỗ trợ

Khi có disaster (pandemic, mass casualty event):

| Challenge | IT Solution |
|-----------|------------|
| Bed capacity unknown | Real-time bed census feed đến EOC |
| Resource allocation | Ventilator, PPE inventory tracking system |
| Patient tracking | S.T.A.R.T triage tagging + barcode scan |
| Surge staffing | Credential verification system (rapid onboarding) |
| Communication | Mass notification (providers + patients) |
| Supply chain | PPE/medication demand forecasting |

### HAvBED — Hospital Bed Availability Reporting

Bệnh viện report bed availability đến state trong emergencies:

```json
// FHIR-based bed availability (emerging standard)
{
  "resourceType": "Location",
  "status": "active",
  "operationalStatus": {
    "coding": [{"system": "http://terminology.hl7.org/CodeSystem/v2-0116",
                "code": "O", "display": "Occupied"}]
  },
  "physicalType": {
    "coding": [{"system": "http://terminology.hl7.org/CodeSystem/location-physical-type",
                "code": "bd", "display": "Bed"}]
  },
  "extension": [{
    "url": "http://hl7.org/fhir/StructureDefinition/location-boundary-geojson",
    "valueAttachment": { "contentType": "application/geo+json" }
  }]
}
```

### 1135 Waiver — COVID-19 Emergency Rules

Trong COVID-19, CMS issued **1135 Waivers** cho phép:
- Telehealth thanh toán Medicare như in-person
- Prescribing opioids qua telehealth (không cần gặp trực tiếp)
- Out-of-state providers practice across state lines

**IT impact:** EHR phải support mode "telehealth encounter = billable" nhanh chóng.

---

## 11. COVID-19 — Case Study Public Health IT

COVID-19 cho thấy infrastructure public health còn yếu:

| Vấn đề | Bài học |
|--------|---------|
| Fax để báo cáo ca bệnh | ELR cần được implement rộng hơn |
| Không interoperable giữa bang | TEFCA/national HIE quan trọng hơn bao giờ hết |
| IIS không sẵn sàng cho vaccine mới | IIS cần flexible để add CVX codes mới nhanh |
| Death reporting lâu | Electronic death registration cần chuẩn hóa |
| Contact tracing manual | Cần digital contact tracing với privacy |

---

## ✅ Checklist sau chương này

**Nền tảng:**
- [ ] Tôi biết 6 hoạt động chính của Public Health Informatics và IT system tương ứng
- [ ] Tôi biết 4 loại timing cho disease reporting (Immediate / Urgent / Standard / Aggregated)

**ELR:**
- [ ] Tôi biết ELR dùng HL7 v2.5.1 với LOINC bắt buộc cho test codes và SNOMED cho organisms
- [ ] Tôi biết FHIR-based ELR khác HL7 v2 ELR thế nào và xu hướng chuyển đổi
- [ ] Tôi có thể đọc và giải thích cấu trúc FHIR Bundle cho ELR

**Syndromic Surveillance:**
- [ ] Tôi biết Syndromic Surveillance dùng chief complaints — không cần lab confirm
- [ ] Tôi biết ít nhất 4 syndrome categories (ILI, GI, Respiratory, Rash)
- [ ] Tôi biết BioSense nhận data từ ~60M ED visits/năm với latency <24h

**IIS & Vaccinations:**
- [ ] Tôi biết CVX codes là gì và ít nhất 5 CVX codes phổ biến
- [ ] Tôi biết VXU (submit) vs QBP (query) trong IIS workflows
- [ ] Tôi biết VFC program và cách EHR phải track eligibility

**HAI & NHSN:**
- [ ] Tôi biết NHSN là gì và 6 HAI metrics phải report hàng tháng
- [ ] Tôi biết NHSN dùng CDA format để submit

**Nâng cao:**
- [ ] Tôi hiểu EDRS là gì và tại sao lag reporting là vấn đề
- [ ] Tôi biết SDoH là gì và ICD-10 Z codes dùng để mã hóa
- [ ] Tôi biết FHIR Gravity Project chuẩn hóa SDoH data
- [ ] Tôi biết Closed-Loop Referral workflow cho SDoH

**Tiếp theo:** `06-analytics-ml/01-analytics-co-ban.md` →
