# Chương 3 — Hồ Sơ Sức Khỏe Điện Tử: Dữ Liệu & Phân Loại

> **Mục tiêu:** Biết chính xác EHR chứa những loại dữ liệu gì, format nào, mã chuẩn nào — để model đúng khi build.

---

## 1. Phân biệt EMR / EHR / PHR

| Thuật ngữ | Đầy đủ | Phạm vi | Ai sở hữu |
|-----------|--------|---------|-----------|
| **EMR** | Electronic Medical Record | Hồ sơ của **một** cơ sở y tế duy nhất | Bệnh viện/phòng khám |
| **EHR** | Electronic Health Record | Liên thông **nhiều** cơ sở; toàn bộ lịch sử sức khỏe | Hệ thống / mạng lưới y tế |
| **PHR** | Personal Health Record | Do **bệnh nhân** tự quản lý; tổng hợp từ nhiều nguồn | Bệnh nhân |

---

## 2. Các loại dữ liệu trong EHR

| Loại dữ liệu | Ví dụ cụ thể | Format | Mã chuẩn |
|-------------|-------------|--------|----------|
| **Demographic** | Họ tên, ngày sinh, địa chỉ, SĐT | Structured text / HL7 ADT | HL7 v2, FHIR Patient resource |
| **Clinical Notes** | Ghi chú bác sĩ, tóm tắt xuất viện, tường trình phẫu thuật | Free text / CDA document | LOINC document types |
| **Lab Results** | Glucose, HbA1c, CBC, culture | Numeric + coded | LOINC, HL7 ORU message |
| **Vital Signs** | Huyết áp, nhịp tim, nhiệt độ, SpO2 | Numeric time-series | LOINC, FHIR Observation |
| **Medications** | Tên thuốc, liều, đường dùng, thời gian | Structured + coded | RxNorm, NDF-RT |
| **Diagnoses** | Chẩn đoán chính, kèm theo | Coded | ICD-10, SNOMED CT |
| **Procedures** | Phẫu thuật, thủ thuật đã thực hiện | Coded | CPT, ICD-10-PCS, SNOMED |
| **Imaging** | X-ray, MRI, CT, siêu âm | DICOM files + metadata | DICOM, SNOMED for radiology |
| **Allergies** | Dị ứng thuốc, thực phẩm, phản ứng | Coded + free text | RxNorm, SNOMED |

---

## 3. Sociotechnical Perspective — CNTT không chỉ là công nghệ

> EHR thất bại **không phải vì công nghệ kém** — mà vì không phù hợp với workflow thực tế của nhân viên y tế.

8 yếu tố cần cân bằng khi build hệ thống y tế:

```
1. Hardware        → thiết bị nhập liệu (workstations, tablets, barcode scanners, RFID)
2. Software        → EHR application, clinical apps, reporting tools
3. Data/Content    → chất lượng dữ liệu, coding accuracy, completeness
4. HCI             → giao diện phù hợp workflow, giảm click, tránh alert fatigue
5. People          → đào tạo nhân viên, change management, champion users
6. Workflow        → EHR phải hỗ trợ, không cản trở quy trình chăm sóc
7. Org. Policies   → quy trình, trách nhiệm, documentation requirements
8. External Env.   → quy định pháp lý, standards, payer requirements
```

---

## 4. CPOE — Computerized Provider Order Entry

Bác sĩ nhập lệnh (thuốc, xét nghiệm, hình ảnh học) trực tiếp vào máy tính thay vì viết tay.

### Tại sao quan trọng với lập trình viên?

- Giảm **50–80% lỗi thuốc** do chữ viết tay không đọc được → UI phải cực kỳ cẩn thận
- Cần tích hợp **real-time với CDS** để cảnh báo drug-drug interactions, dị ứng, liều sai
- Phải route orders đến đúng hệ thống phụ (pharmacy, LIS, radiology) qua interface engine
- **Audit trail bắt buộc**: ai ra lệnh, lúc nào, thay đổi gì — không thể xóa, chỉ được cancel

---

## 5. Bộ mã thuật ngữ y tế (Terminology Standards)

| Bộ mã | Phạm vi | Ví dụ code | Hệ thống dùng |
|-------|---------|-----------|--------------|
| **ICD-10-CM/PCS** | Chẩn đoán & thủ thuật | J18.9 = Pneumonia | EHR, billing, mortality |
| **CPT** | Thủ thuật & dịch vụ y tế | 99213 = Office visit | Billing, insurance claims |
| **SNOMED CT** | Toàn diện nhất — lâm sàng | 22298006 = Myocardial infarction | EHR clinical notes, CDS rules |
| **LOINC** | Lab & clinical observations | 2345-7 = Glucose in Serum | LIS, lab orders, vitals |
| **RxNorm** | Thuốc và liều dùng | 1049502 = Oxycodone 80MG | Pharmacy, e-prescribing |
| **NPI** | Định danh nhà cung cấp | 1234567890 = Dr. Smith | Provider directories, claims |

---

## 6. UX trong y tế — Đặc thù cần biết

Người dùng hệ thống y tế rất khác người dùng thông thường:

| Đặc điểm | Implication cho Dev |
|----------|---------------------|
| **Tay không rảnh** (đang đeo găng, đỡ bệnh nhân) | Ít click nhất có thể; voice input |
| **Áp lực thời gian** (phòng cấp cứu) | UI tối giản; action rõ ràng |
| **Interruptions liên tục** | Auto-save; không mất công nhập |
| **Nhiều loại người dùng** | Workflow khác nhau cho mỗi role |
| **Low lighting** (ICU, phòng mổ) | Contrast đủ cao; dark mode |
| **Shared workstations** | Logout nhanh; không lưu credentials |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết EMR ≠ EHR ≠ PHR và ai sở hữu cái gì
- [ ] Tôi biết 9 loại dữ liệu trong EHR và mã chuẩn tương ứng
- [ ] Tôi hiểu CPOE và tại sao audit trail không được xóa
- [ ] Tôi biết 6 bộ mã terminology (ICD, CPT, SNOMED, LOINC, RxNorm, NPI)

**Tiếp theo:** `01-ehr-systems/03-he-thong-hanh-chinh.md` →