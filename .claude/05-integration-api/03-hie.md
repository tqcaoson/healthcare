# Chương — HIE, TEFCA & Public Health Integration

> **Mục tiêu:** Hiểu kiến trúc chia sẻ thông tin y tế ở cấp độ tổ chức và quốc gia.

---

## 1. HIE — Health Information Exchange

### Tại sao HIE tồn tại?

**Vấn đề thực tế:**
> Bệnh nhân cấp cứu tại bệnh viện B — bác sĩ không biết:
> - Patient đang dùng warfarin từ bác sĩ tim mạch ở bệnh viện A
> - Patient dị ứng penicillin — không được ghi ở đây
> - Patient đã có CT scan 2 tuần trước — không cần chụp lại
>
> Kết quả: CT chụp lại (tốn $2,000 + radiation), warfarin không được giữ trước phẫu thuật → nguy hiểm.

HIE giải quyết: chia sẻ dữ liệu lâm sàng **an toàn** giữa các tổ chức y tế khác nhau.

---

## 2. Ba Mô Hình HIE

### Mô hình 1: Centralized (Consolidated)

```
Hospital A ──→ ┌──────────────────────┐ ←── Hospital B
Hospital C ──→ │   Central Repository  │ ←── Clinic D
Pharmacy E ──→ │   (Copy của tất cả   │ ←── Lab F
               │    records)          │
               └──────────────────────┘
                          ↓
                    Query từ bất kỳ authorized user
```

| Ưu điểm | Nhược điểm |
|---------|-----------|
| Query nhanh (data local) | Single point of failure |
| Data luôn available | Privacy risk nếu central repo bị breach |
| Dễ analytics | Governance phức tạp (ai sở hữu data?) |
| | Tốn chi phí storage trùng lặp |

---

### Mô hình 2: Federated (Distributed)

```
                    Query Broker
                   /     |     \
Hospital A ←──────       |      ──────→ Hospital B
(query local)            |              (query local)
                         |
                    Clinic C (query local)

→ Kết quả được aggregate từ nhiều sources
```

| Ưu điểm | Nhược điểm |
|---------|-----------|
| Data không rời khỏi tổ chức | Query chậm (phải đợi tất cả sources) |
| Privacy tốt hơn | Phụ thuộc uptime của từng node |
| Không duplicate storage | Khó analytics population-level |
| | Consistency khó đảm bảo |

---

### Mô hình 3: Hybrid (Most Common Today)

```
Metadata/Index (central):   ← nơi nào có records của patient này?
Data (federated):           ← lấy actual records từ source tại chỗ

Bước 1: Query index → biết Patient 123 có records ở Hospital A và Clinic B
Bước 2: Fetch records trực tiếp từ Hospital A và Clinic B
```

---

## 3. IHE Profiles — Chuẩn Tích Hợp

IHE (Integrating the Healthcare Enterprise) tạo "recipes" tích hợp từ HL7/DICOM.

| Profile | Mô tả | Protocol | Dùng để |
|---------|-------|---------|---------|
| **XDS.b** | Cross-Enterprise Document Sharing | SOAP/HTTP | Share CDA documents giữa tổ chức |
| **PIX** | Patient Identity Cross-Reference | HL7 v2 / HL7 v3 | Link patient IDs across systems |
| **PDQ** | Patient Demographics Query | HL7 v2 | Query demographics từ MPI |
| **XCA** | Cross-Community Access | SOAP | Federated query nhiều HIE domains |
| **MHD** | Mobile access to Health Documents | FHIR REST | Modern, mobile-friendly XDS replacement |
| **ATNA** | Audit Trail and Node Authentication | Syslog | Security audit logging |
| **BPPC** | Basic Patient Privacy Consents | CDA | Manage patient consent |

### XDS.b Flow — Document Sharing

```
Submitting System              XDS Registry           XDS Repository
(gửi document)                 (lưu metadata)         (lưu document)
──────────────                 ──────────────         ──────────────
Create CDA document
      ↓
Provide & Register             → Store metadata
(ITI-41 / ITI-42)              → index: patient,   → Store CDA file
                                 date, type, author    Return URL
      ↓ (later)
Query Registry                 ← Search by patient
(ITI-18)                         Return document list
      ↓
Retrieve Document              ← Download CDA
(ITI-43)                         from Repository
```

---

## 4. TEFCA — National HIE Framework (Mỹ, 2022)

**TEFCA** = Trusted Exchange Framework and Common Agreement

**Mục tiêu:** Tạo nationwide network — bất kỳ authorized clinician nào cũng query được data bệnh nhân trên cả nước Mỹ.

### Kiến trúc TEFCA

```
┌─────────────────────────────────────────────────────┐
│                  ONC (Oversight)                     │
└──────────────────────┬──────────────────────────────┘
                       │ Designated by ONC
         ┌─────────────┼─────────────────┐
         ▼             ▼                 ▼
    ┌─────────┐   ┌─────────┐      ┌─────────┐
    │  QHIN A │   │  QHIN B │      │  QHIN C │
    │(e.g.    │   │(e.g.    │      │(e.g.    │
    │ Carequality│ │ eHealth │      │ KONZA)  │
    └────┬────┘   └────┬────┘      └────┬────┘
         │              │               │
   ┌─────┴─────┐  ┌─────┴──────┐  ┌────┴────┐
   Hospital A  Clinic B    Pharmacy C  EHR Vendor
```

**QHIN** = Qualified Health Information Network — intermediaries được ONC approve.

### Exchange Purposes (ai được access gì)

| Purpose | Who | What |
|---------|-----|------|
| **Treatment** | Treating clinicians | Full clinical records |
| **Payment** | Insurance, billing | Claims-relevant data |
| **Operations** | Quality orgs | De-identified / aggregate |
| **Individual Access** | Patients themselves | Their own records |
| **Public Health** | Health departments | Reportable conditions |
| **Benefits Determination** | Government agencies | Benefits eligibility |
| **Research** | Researchers (with consent/IRB) | Approved datasets |

---

## 5. Electronic Lab Reporting (ELR)

LIS tự động gửi **reportable lab results** đến state/local public health.

### Reportable Conditions (bắt buộc report)

| Category | Ví dụ | Timing |
|---------|-------|--------|
| Notifiable infectious | TB, HIV, syphilis, gonorrhea | 24–72 hours |
| Foodborne illness | Salmonella, E.coli O157 | 24 hours |
| Vaccine-preventable | Measles, mumps, hepatitis A | Immediate (suspected) |
| Emerging threats | COVID-19, mpox | Per emergency orders |
| HAI (healthcare-acquired) | MRSA, C.diff | Via NHSN monthly |

### ELR Technical Flow

```
LIS (result finalized)
      ↓
HL7 ORU^R01 message (v2.5.1 ELR guide)
  - LOINC codes for test (required)
  - SNOMED codes for organisms (required)
  - NPI for ordering provider
  - Patient demographics
      ↓
State Health Dept LIMS
(via PHIN Messaging System — SOAP/HTTPS)
      ↓
CDC BioSense / NEDSS
(National Electronic Disease Surveillance System)
      ↓
National surveillance reporting
```

---

## 6. Syndromic Surveillance

Phát hiện dịch bệnh **trước khi** có xác nhận lab — dùng chief complaints và ED triage data.

### Data Sources

```
ED Registration/Triage:
- Chief complaint (free text → coded)
- ICD-10 discharge diagnosis
- Age, sex, zip code
- Visit date/time

Primary Care:
- ICD-10 visit codes (via claims or direct)
- Medication prescriptions (flu → Tamiflu spike?)

Pharmacy (OTC):
- OTC medication sales (cough/cold spikes)
```

### ESSENCE (CDC Platform)

ESSENCE (Electronic Surveillance System for Early Notification of Community-based Epidemics):

```
Near-real-time data flows:
ED arrives → within 24 hours → ESSENCE

ESSENCE monitors:
- ILI (Influenza-Like Illness) syndrome
- GI syndrome
- Respiratory syndrome
- Injury syndrome
- ...12+ syndrome categories

Anomaly detection:
- Adaptive regression
- CUSUM (Cumulative Sum)
- Alert when: observed > expected + statistical threshold
      ↓
Public health investigation
```

---

## 7. Immunization Information Systems (IIS)

**IIS** = Population-based vaccine registry. 50 bang Mỹ có IIS riêng, liên thông qua CDC.

### HL7 Messages cho IIS

| Message | Direction | Purpose |
|---------|-----------|---------|
| **VXU^V04** | Provider → IIS | Submit vaccination record |
| **QBP^Q11** | Provider → IIS | Query patient's vaccination history |
| **RSP^K11** | IIS → Provider | Return vaccination records |
| **ACK^V04** | IIS → Provider | Acknowledge receipt |

### VXU^V04 Example (Vaccination Update)

```
MSH|^~\&|EHR|CLINIC|IIS|STATE|20240101||VXU^V04|MSG001|P|2.5.1
PID|1||MRN123^^^CLINIC^MR||Nguyen^Van^A||19800101|M
NK1|1|Nguyen^Thi^B|MTH^Mother
ORC|RE|ORD123|||CM
RXA|0|1|20240101|20240101|141^Influenza^CVX|0.5|mL^^ISO+|||01^Historical^NIP001
RXR|IM^Intramuscular^HL70162
OBX|1|CE|64994-7^Funding eligibility^LN||V02^VFC eligible-Medicaid/Medicaid Managed Care^HL70064
```

### IIS Integration — Lookup trước khi vaccinate

```javascript
// FHIR-based IIS query (modern approach)
async function checkVaccinationHistory(patientId) {
    // Query IIS via FHIR
    const response = await fetch(
        `${iisBaseUrl}/Immunization?patient=${patientId}&_sort=-date`,
        { headers: { Authorization: `Bearer ${accessToken}` } }
    );
    const bundle = await response.json();
    
    // Check flu shot
    const currentYear = new Date().getFullYear();
    const fluVaccines = bundle.entry?.filter(e => {
        const cvxCode = e.resource.vaccineCode?.coding
            ?.find(c => c.system === 'http://hl7.org/fhir/sid/cvx')?.code;
        return ['141', '140', '158', '168'].includes(cvxCode); // flu CVX codes
    });
    
    const hasCurrentFlu = fluVaccines?.some(v => 
        new Date(v.resource.occurrenceDateTime).getFullYear() === currentYear
    );
    
    return { hasCurrentFlu, allVaccinations: bundle.entry?.map(e => e.resource) };
}
```

---

## 8. Quality Reporting — eCQMs

**eCQM** = Electronic Clinical Quality Measure — auto-calculate từ EHR.

### eCQM Anatomy

```
Measure:
  Title: Diabetes: Hemoglobin A1c (HbA1c) Poor Control (> 9%)
  CMS ID: CMS122v12
  
  Denominator (who's included):
    Patients 18-75 with Type 2 Diabetes
    Active member of health plan
    
  Numerator (the "bad" outcome we're measuring):
    Patients with MOST RECENT HbA1c > 9%
    OR no HbA1c result in measurement period
    
  Denominator Exclusions:
    Patients in hospice
    Pregnancy
    
  Measurement Period: January 1 – December 31
```

### CQL (Clinical Quality Language) — Executable Logic

```sql
-- CQL: Diabetes Poor Control
library DiabetesPoorControl version '12.0.0'
using QDM version '5.6'

include MATGlobalCommonFunctionsFHIR4 called Global

valueset "Diabetes": 'urn:oid:2.16.840.1.113883.3.464.1003.103.12.1001'
valueset "HbA1c Laboratory Test": 'urn:oid:2.16.840.1.113883.3.464.1003.198.12.1013'

define "Initial Population":
  exists ( [Diagnosis: "Diabetes"] Diabetes
    where Diabetes.prevalencePeriod overlaps "Measurement Period"
  ) and Patient.age() >= 18 and Patient.age() <= 75

define "Numerator":
  not exists ( [Laboratory Test, Performed: "HbA1c Laboratory Test"] A1c
    where A1c.result <= 9 ('%')
      and A1c.relevantPeriod ends during "Measurement Period"
  )
```

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 3 mô hình HIE (Centralized/Federated/Hybrid) và trade-offs
- [ ] Tôi biết TEFCA là gì và vai trò của QHIN
- [ ] Tôi biết 7 Exchange Purposes trong TEFCA
- [ ] Tôi biết ELR dùng HL7 ORU^R01 để gửi reportable conditions
- [ ] Tôi biết Syndromic Surveillance phát hiện dịch bệnh trước lab confirm
- [ ] Tôi biết 3 HL7 messages cho IIS (VXU/QBP/RSP)
- [ ] Tôi hiểu cấu trúc eCQM: Denominator / Numerator / Exclusion

**Tiếp theo:** `06-analytics-ml/01-analytics-co-ban.md` →