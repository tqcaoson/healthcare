## Public Health Informatics: Syndromic Surveillance, ELR, IIS

---

### 1. Electronic Lab Reporting (ELR)

LIS tự động gửi **reportable lab results** đến public health agencies — không cần nhân viên gửi thủ công.

```
LIS → ELR Message (HL7 ORU^R01) → State Health Department LIMS
                                         ↓
                               Automated disease surveillance
```

**Reportable conditions:** TB, HIV, COVID-19, STIs, foodborne illness, hepatitis...

**Yêu cầu kỹ thuật:**
- HL7 v2.5.1 với ELR guide (CDC)
- LOINC cho test codes; SNOMED cho organisms
- Transport: PHIN Messaging System (SOAP/HTTPS)

### 2. Syndromic Surveillance

Phát hiện dịch bệnh **trước khi** có chẩn đoán xác định — dùng chief complaints và triage data.

```
ED/Urgent Care
    ↓ (near real-time)
Chief complaint data (free text + ICD codes)
    ↓
ESSENCE / BioSense Platform (CDC)
    ↓
Anomaly detection → alert khi spike bất thường
    ↓
Public health investigation
```

**Use case thực tế:** Phát hiện cluster ILI (influenza-like illness) → alert trước khi lab confirm → khởi động vaccination response.

### 3. Immunization Information Systems (IIS)

**IIS** = Population-based vaccine registry. Tất cả 50 bang Mỹ có IIS riêng.

```
EHR/Pharmacy
    ↓  HL7 VXU^V04 (Vaccination Update)
IIS (State Registry)
    ↓  HL7 QBP^Q11 (Query)
EHR query → trả về vaccination history
```

**Key HL7 messages:**
- `VXU^V04` — gửi vaccination record mới lên IIS
- `QBP^Q11` — query vaccination history của patient
- `RSP^K11` — response với vaccination records

### 4. Quality Reporting — eCQMs

**eCQM** = Electronic Clinical Quality Measure — tự động calculate quality metrics từ EHR data.

| Ví dụ eCQM | Numerator | Denominator |
|-----------|-----------|-------------|
| Diabetes HbA1c control | Patients DM type 2 với HbA1c < 8% | Tất cả patients DM type 2 |
| Flu vaccination | Patients được tiêm flu vaccine | Patients ≥65 tuổi |
| Breast cancer screening | Patients có mammogram trong 2 năm | Phụ nữ 50-74 tuổi |

**Format:** FHIR Measure resource + CQL (Clinical Quality Language) expressions

---

## ✅ Checklist — Integration & HIE

- [ ] Tôi biết viết Mirth Connect transformer để đọc/sửa HL7 field
- [ ] Tôi hiểu 4 integration patterns và khi nào dùng pattern nào
- [ ] Tôi biết HIE là gì và 3 mô hình (Centralized/Federated/Hybrid)
- [ ] Tôi biết TEFCA tạo ra national network qua QHINs
- [ ] Tôi biết ELR và IIS dùng HL7 message types nào

**Tiếp theo:** `06-analytics-ml/01-analytics-data-ml.md` →