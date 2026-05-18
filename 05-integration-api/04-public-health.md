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

## 7. COVID-19 — Case Study Public Health IT

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

- [ ] Tôi biết 3 loại timing cho disease reporting (Immediate/Urgent/Standard)
- [ ] Tôi biết ELR dùng HL7 v2.5.1 với LOINC bắt buộc cho test codes
- [ ] Tôi biết Syndromic Surveillance dùng chief complaints — không cần lab confirm
- [ ] Tôi biết CVX codes là gì và ít nhất 5 CVX codes phổ biến
- [ ] Tôi biết VXU (submit) vs QBP (query) trong IIS workflows
- [ ] Tôi biết NHSN là gì và 5 HAI metrics phải report

**Tiếp theo:** `06-analytics-ml/01-analytics-co-ban.md` →