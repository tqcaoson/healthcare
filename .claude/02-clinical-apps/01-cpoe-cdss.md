# Chương 5 — CPOE & Hệ Thống Hỗ Trợ Quyết Định Lâm Sàng (CDSS)

> **Mục tiêu:** Hiểu CDSS hoạt động thế nào, cách thiết kế alerts đúng, và kiến trúc kỹ thuật.

---

## 1. CDSS là gì?

**CDSS (Clinical Decision Support System)** cung cấp thông tin và gợi ý đúng lúc, đúng nơi để hỗ trợ bác sĩ ra quyết định lâm sàng tốt hơn.

---

## 2. Phân loại CDSS theo cơ chế hoạt động

| Loại | Cơ chế | Ví dụ | Ưu/Nhược điểm |
|------|--------|-------|--------------|
| **Rule-based** | IF-THEN rules từ guidelines | Nếu creatinine > 2.0 VÀ dùng metformin → cảnh báo | Dễ giải thích; khó scale; cần update thủ công |
| **Machine Learning** | Pattern từ dữ liệu lịch sử | Dự đoán nguy cơ sepsis từ vitals + labs | Scale tốt; khó giải thích (black box) |
| **Knowledge Retrieval** | Tìm kiếm tài liệu lâm sàng | Tra cứu dose theo cân nặng bệnh nhân | Luôn up-to-date; cần internet; không tự động |
| **Workflow Integration** | Nhúng vào quy trình CPOE | Gợi ý ICD code khi nhập chẩn đoán | Hiệu quả cao; phải design UX cẩn thận |

---

## 3. Alert Fatigue — Thách thức lớn nhất

> **Nghiên cứu:** Bác sĩ bỏ qua **49–96% alerts** từ CDSS.

Nguyên nhân: quá nhiều alerts không liên quan, không đủ thông tin để quyết định.

### Best practices giảm Alert Fatigue

| Nguyên tắc | Cách implement |
|-----------|---------------|
| **Chỉ hiển thị khi actionable** | Alert phải có hành động cụ thể bác sĩ CÓ THỂ làm ngay |
| **Phân cấp độ ưu tiên** | Critical (không thể bỏ qua) / Warning / Informational |
| **Smart timing** | Hiển thị đúng lúc trong workflow — khi kê thuốc, không phải random |
| **Contextual alerts** | Cùng drug interaction có thể OK hoặc không tùy bệnh nhân |
| **Track override rates** | Nếu >90% alerts bị override → review lại logic |
| **Patient-specific** | "BN này đã thử thuốc X và thất bại" quan trọng hơn guideline chung |

### Phân cấp severity

```
🔴 CRITICAL  → modal popup, không thể dismiss không có lý do
🟡 WARNING   → inline notification, visible nhưng không block
🔵 INFO      → passive notification, không interrupt
```

---

## 4. Five Rights of CDS (AMIA Framework)

| Right | Mô tả |
|-------|-------|
| **Right Information** | Dữ liệu lâm sàng chính xác, phù hợp bệnh nhân cụ thể |
| **Right Person** | Đúng người cần nhận (bác sĩ? điều dưỡng? dược sĩ?) |
| **Right Format** | Dạng trình bày phù hợp — alert popup? dashboard? infobutton? |
| **Right Channel** | Qua kênh nào — EHR? mobile? email? SMS? |
| **Right Time** | Đúng lúc trong quy trình — không quá sớm, không quá muộn |

---

## 5. Kiến trúc kỹ thuật của CDSS hiện đại

```
┌─────────────────────────────────────────────┐
│              Presentation Layer              │
│    (Alert card, Order suggestion, Prompt)    │
└──────────────────────┬──────────────────────┘
                       │
┌──────────────────────▼──────────────────────┐
│           CDS Hooks (FHIR Standard)          │
│    patient-view / order-sign / order-select  │
└──────────────────────┬──────────────────────┘
                       │
          ┌────────────┴──────────────┐
          ▼                           ▼
┌─────────────────┐       ┌─────────────────────┐
│ Inference Engine │       │  Data Access Layer   │
│ (thực thi rules) │       │ (lấy data real-time) │
└────────┬────────┘       └──────────┬──────────┘
         │                           │
         ▼                           ▼
┌─────────────────┐       ┌─────────────────────┐
│   Knowledge     │       │    CDR / EHR         │
│   Repository    │       │    (patient data)    │
│ (rules, models) │       └─────────────────────┘
└─────────────────┘
```

**Các thành phần chính:**

- **Knowledge Repository**: Rules, guidelines, ML models dưới dạng "artifacts" — update độc lập với code
- **Inference Engine**: Đọc patient data + knowledge → sinh recommendations
- **Data Access Layer**: Kết nối CDR/EHR để lấy dữ liệu bệnh nhân real-time
- **CDS Hooks**: Chuẩn FHIR để nhúng CDS vào bất kỳ EHR nào qua API
- **Presentation Layer**: Hiển thị theo format phù hợp workflow

---

## 6. CDS Hooks — Cách nhúng CDS vào EHR

CDS Hooks là chuẩn FHIR: CDS service bên ngoài hook vào workflow của EHR. EHR tự động gọi CDS service → nhận gợi ý → hiển thị card.

| Hook | Khi nào kích hoạt | Dùng để |
|------|------------------|---------|
| `patient-view` | Mở hồ sơ bệnh nhân | Hiển thị risk scores, pending actions |
| `order-sign` | Bác sĩ sắp ký lệnh | Kiểm tra drug interactions, dosing |
| `order-select` | Chọn loại thuốc | Gợi ý alternatives, formulary check |
| `appointment-book` | Đặt lịch | Gợi ý screenings, preventive care |

---

## 7. Medication Management — Closed Loop

### 5 Rights of Medication Administration — IT phải enforce

| Right | Cách implement |
|-------|---------------|
| **Right Patient** | Barcode wristband scan xác nhận đúng bệnh nhân |
| **Right Medication** | Barcode thuốc scan → so sánh với MAR order |
| **Right Dose** | CDS check dose theo cân nặng, chức năng thận |
| **Right Route** | Documentation bắt buộc (oral vs IV vs topical) |
| **Right Time** | MAR hiển thị window thời gian; cảnh báo sớm/muộn |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 4 loại CDSS và ưu/nhược điểm của từng loại
- [ ] Tôi hiểu Alert Fatigue là vấn đề UX/clinical, không chỉ kỹ thuật
- [ ] Tôi nhớ được Five Rights of CDS
- [ ] Tôi hiểu CDS Hooks là gì và 4 hook phổ biến nhất
- [ ] Tôi biết 5 Rights of Medication Administration

**Tiếp theo:** `02-clinical-apps/02-alert-fatigue.md` (đã tích hợp vào chương này) → `02-clinical-apps/03-telehealth.md` →