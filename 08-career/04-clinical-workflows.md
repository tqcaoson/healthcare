# Chương — Clinical Workflows: Inpatient, Lab & Medication

> **Mục tiêu:** Hiểu quy trình lâm sàng thực tế để build đúng — không code ngược với workflow bác sĩ/điều dưỡng.

---

## 1. Tại sao Dev cần hiểu Clinical Workflows?

> **"EHR systems are most often abandoned not because of poor technology, but because of poor fit with clinical workflow."**

Nếu không hiểu workflow:
- Build feature nobody uses
- Create alert ở sai thời điểm
- Design UI làm chậm bác sĩ (thêm clicks = thêm rủi ro lỗi)
- Integration gửi data sai sequence

---

## 2. Inpatient Admission Workflow

### End-to-end với IT touchpoints

```
[1] PATIENT ARRIVAL
    Bệnh nhân đến ED hoặc scheduled admission
    ─────────────────────────────────────────
    IT: Registration system tạo encounter
        → HL7 ADT^A04 (register outpatient) hoặc ADT^A01 (admit)
        → Broadcast đến EHR, LIS, RIS, Pharmacy, Billing
        → MPI check: có duplicate record không?

[2] TRIAGE / INITIAL ASSESSMENT
    Điều dưỡng đo vital signs, lấy chief complaint
    ─────────────────────────────────────────────
    IT: FHIR Observation resources tạo (BP, HR, Temp, SpO2, Weight)
        FHIR Condition (chief complaint)
        Allergy verification — pull từ EHR + query IIS
        Medication reconciliation — pull home medications

[3] PHYSICIAN ASSESSMENT
    Bác sĩ khám, ghi notes, ra lệnh
    ─────────────────────────────────────────────
    IT: FHIR Encounter updated (attending physician, bed assignment)
        Clinical notes (FHIR DocumentReference / Composition)
        CPOE: orders nhập vào → CDS Hooks fire (patient-view, order-sign)

[4] ORDER EXECUTION
    Điều dưỡng / kỹ thuật viên thực hiện lệnh
    ─────────────────────────────────────────────
    IT: Lab orders: HL7 ORM^O01 → LIS → barcode specimen label
        Radiology orders: ORM → RIS → PACS worklist
        Medication orders: CPOE → Pharmacy IS → MAR

[5] RESULTS RETURN
    Kết quả về, bác sĩ review
    ─────────────────────────────────────────────
    IT: Lab results: HL7 ORU^R01 ← LIS → EHR inbox
        Critical values: OBX-8 = HH/LL → pager/SMS alert ngay
        Radiology: report ← RIS → EHR + DICOM images ← PACS

[6] CLINICAL DECISION & TREATMENT
    Bác sĩ quyết định điều trị dựa trên results
    ─────────────────────────────────────────────
    IT: Additional orders, consults, medication adjustments
        CDSS fires additional alerts based on results

[7] DISCHARGE PLANNING
    Bác sĩ viết discharge summary, plan
    ─────────────────────────────────────────────
    IT: Discharge summary (FHIR Composition; LOINC 18842-5)
        After Visit Summary (AVS) — bản in cho bệnh nhân
        Medication reconciliation discharge — final med list
        Follow-up appointments scheduled

[8] DISCHARGE
    Bệnh nhân xuất viện
    ─────────────────────────────────────────────
    IT: HL7 ADT^A03 (discharge) → billing, public health, HIE
        FHIR Bundle (C-CDA / Summary of Care) → next provider
        Billing coding: ICD-10, CPT → HL7 DFT^P03
        Quality measure calculation (30-day readmission risk)
```

---

## 3. Lab Workflow — Chi tiết

```
[1] ORDER PLACED (CPOE)
    Bác sĩ order xét nghiệm trong EHR
    ─────────────────────────────────
    HL7: ORM^O01
    ORC: Order Control (NW = New Order), Order Number, Provider
    OBR: Test requested (LOINC code), Priority (STAT vs Routine)

[2] SPECIMEN COLLECTION
    Điều dưỡng lấy máu / mẫu
    ─────────────────────────────────
    IT: LIS tạo accession number, in barcode label
        Worklist cho điều dưỡng theo STAT priority

[3] SPECIMEN RECEIPT IN LAB
    Lab nhận mẫu, scan barcode
    ─────────────────────────────────
    IT: LIS timestamp: received time
        Chain of custody log (HIPAA: ai nhận mẫu khi nào)

[4] ANALYSIS
    Máy phân tích chạy test
    ─────────────────────────────────
    IT: Analyzer → LIS qua ASTM/HL7 instrument interface
        Auto-verify nếu value trong reference range
        Delta check: so sánh với kết quả trước (bất thường nếu thay đổi đột ngột)

[5] REVIEW & RELEASE
    Lab technologist review kết quả bất thường
    ─────────────────────────────────
    IT: OBX-11 status: P (Preliminary) → F (Final) → C (Corrected)

[6] RESULT DELIVERY
    Gửi kết quả về EHR
    ─────────────────────────────────
    HL7: ORU^R01
    OBX-5: actual value
    OBX-6: units
    OBX-7: reference range
    OBX-8: abnormal flag (H/L/HH/LL/A)
    
    Critical values (HH/LL):
    → Immediate callback to ordering provider
    → Two-challenge verification (read-back)
    → Documented in EHR within 30 minutes

[7] ACKNOWLEDGMENT
    Bác sĩ review kết quả trong EHR
    ─────────────────────────────────
    IT: "Unreviewed results" inbox
        Time-to-acknowledgment tracking (quality metric)
        FHIR Task cho critical results chưa được acknowledge
```

---

## 4. Medication Administration Workflow (Closed Loop)

### 5 Rights — Enforced bởi IT

```
Right Patient    → Scan wristband barcode
Right Medication → Scan medication barcode → compare với MAR order
Right Dose       → CDS check (wt-based dosing, renal function)
Right Route      → Documented in MAR (oral/IV/IM/topical)
Right Time       → MAR window: ±30 min của scheduled time
```

### IT Workflow

```
[1] PRESCRIBING (CPOE)
    Bác sĩ nhập medication order
    CDS Hooks: order-sign → drug interactions, allergies, dosing
    Order sent: FHIR MedicationRequest → Pharmacy IS

[2] PHARMACY REVIEW
    Dược sĩ verify order (clinical pharmacist)
    Drug-drug, drug-allergy, dose check
    Order status: Pending → Verified
    Pharmacy dispenses: FHIR MedicationDispense

[3] MEDICATION PREPARATION
    Pharmacy pha thuốc (đặc biệt IV)
    Barcode label gắn vào medication

[4] MEDICATION ADMINISTRATION
    Điều dưỡng đến giường bệnh nhân
    → Scan patient wristband (xác nhận Right Patient)
    → Scan medication barcode (xác nhận Right Medication + Dose)
    → System shows MAR: scheduled time, route, special instructions
    → Nếu match → administer
    → Nếu không match → BLOCK với alert rõ ràng
    → Document: FHIR MedicationAdministration

[5] POST-ADMINISTRATION MONITORING
    PRN medications: document effectiveness
    High-alert medications: vital signs q1h
    Adverse reactions: FHIR AdverseEvent
```

### High-Alert Medications — Cần thêm safeguards

| Loại thuốc | Safeguard IT cần build |
|-----------|----------------------|
| **Insulin** | Always require 2-nurse verification scan; unit-dose |
| **Anticoagulants** (heparin, warfarin) | Weight-based calc; INR/aPTT display real-time |
| **Opioids** | Pain scale documentation bắt buộc; respiratory rate check |
| **Concentrated electrolytes** (KCl) | Cannot be stocked in patient care areas; only pharmacy |
| **Chemotherapy** | Double-check với oncology protocol; body surface area calc |

---

## 5. Nursing Workflow — Thường bị Dev bỏ qua

Điều dưỡng dùng EHR **nhiều hơn** bác sĩ — tất cả medication administration, vitals, care notes đều qua tay điều dưỡng.

| Nursing Activity | IT System | Frequency |
|-----------------|-----------|-----------|
| Vital signs documentation | EHR Flowsheet | q4h stable, q1h ICU |
| Medication administration | MAR + Barcode | q4–8h per patient |
| I&O (Intake & Output) | Fluid balance module | q1h ICU, q8h general |
| Pain assessment | EHR structured assessment | q4h |
| Falls risk assessment | EHR structured form | Admission, q24h |
| Nursing notes | EHR narrative notes | per significant event |
| Care plan update | FHIR CarePlan | per shift |

### UX Requirements cho Nursing

```
Điều dưỡng quản lý 4–6 bệnh nhân cùng lúc. Mỗi task bị interrupt nhiều lần.

→ Auto-save mọi lúc (không mất data khi bị gọi đi giữa chừng)
→ Minimum clicks (target: < 3 clicks cho common tasks)
→ Context persists khi quay lại (biết đang làm đến đâu)
→ One-page view đủ để biết tình trạng 1 bệnh nhân
→ Offline capability (wifi yếu trong một số phòng)
→ Shift handoff report auto-generate từ EHR data
```

---

## 6. Handoff Workflow — Bàn giao ca

Handoff (bàn giao giữa các ca, giữa các khoa) là **điểm nguy hiểm nhất** — thông tin hay bị lost.

**IT hỗ trợ bằng:**
- **Structured Handoff Report**: auto-generate từ vitals, orders, labs, notes
- **SBAR Template**: Situation–Background–Assessment–Recommendation
- **Sign-out Tool**: list active problems, pending results, overnight tasks
- **EHR Messaging**: Secure messaging giữa care team members
- **Transition of Care Document**: FHIR Bundle khi chuyển khoa/xuất viện

```
SBAR Format (thường dùng):
S - Situation:   "Room 205, Mr. Nguyen, 65M admitted for CHF exacerbation"
B - Background:  "PMH: DM2, HTN, EF 35%. Admitted 2 days ago, diuresis started"
A - Assessment:  "Improving, weight down 2kg, creatinine trending up"
R - Recommendation: "Watch creatinine in AM labs, hold Lasix if Cr > 2.0"
```

---

## ✅ Checklist sau chương này

- [ ] Tôi có thể vẽ Inpatient Workflow với 8 bước và IT touchpoints
- [ ] Tôi biết HL7 messages nào fire ở mỗi bước của Lab Workflow
- [ ] Tôi hiểu Closed Loop Medication Administration và 5 Rights IT enforces
- [ ] Tôi biết tại sao điều dưỡng là primary user của EHR (không phải bác sĩ)
- [ ] Tôi biết Handoff là điểm nguy hiểm và IT giảm risk bằng cách nào

**Tiếp theo:** `08-career/05-patterns-antipatterns.md` →