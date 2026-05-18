# Chương 2 — Kiến Trúc EHR & Hạ Tầng Kỹ Thuật

> **Mục tiêu:** Hiểu EHR Component Model để build và debug integration đúng cách.

---

## 1. EHR Component Model — Tổng quan

Không có EHR nào tự mình đáp ứng toàn bộ nhu cầu — **luôn cần tích hợp nhiều hệ thống**.

| Thành phần | Mô tả kỹ thuật | Dữ liệu lưu trữ |
|-----------|----------------|----------------|
| **CDR** (Clinical Data Repository) | Lõi lưu trữ trung tâm | Lab, vitals, medication, notes, images |
| **MPI** (Master Person Index) | Quản lý định danh bệnh nhân duy nhất | Patient ID, demographics, cross-system identifiers |
| **Data Dictionary** | Từ điển dữ liệu chuẩn hóa | SNOMED, ICD, LOINC mappings, local codes |
| **Knowledge Base** | Tri thức lâm sàng | Drug-drug interactions, dosing rules, guidelines |
| **Interface Engine (IE)** | Middleware kết nối hệ thống | HL7 v2/v3, CDA, routing rules |
| **Clinical Applications** | Front-end cho user | Review/reporting, order entry, patient management |

---

## 2. CDR — Clinical Data Repository (Thành phần cốt lõi)

CDR là nơi lưu trữ **tất cả dữ liệu instance của bệnh nhân**.

Dữ liệu có thể ở dạng:
- **Unstructured**: free text, PDF tài liệu
- **Structured**: cột trong relational DB, elements trong object-oriented DB

### Yêu cầu kỹ thuật bắt buộc của CDR

| Yêu cầu | Mô tả | Implication cho Dev |
|---------|-------|---------------------|
| **Accessibility** | Truy xuất nhanh 24/7 | Real-time read; caching strategy |
| **Reliability** | Redundant storage + backup | HA architecture; monitoring |
| **Security** | HIPAA — bảo vệ PHI | RBAC, encryption, audit log |
| **Longevity** | Dữ liệu tồn tại hàng chục năm | Migration plan khi đổi hệ thống (5–10 năm) |

---

## 3. Central vs Distributed Storage

| Mô hình | Cơ chế | Ưu điểm | Nhược điểm |
|---------|--------|---------|------------|
| **Central Storage** | Tất cả data map về CDR trung tâm | CDS hiệu quả; single source of truth | Phức tạp mapping; chi phí transform cao |
| **Distributed Storage** | Mỗi hệ thống lưu riêng; federated query | Linh hoạt; không cần transform | Chậm khi query; consistency khó đảm bảo |

---

## 4. Interface Engine (IE) — Tim mạch của tích hợp

### Tại sao cần IE?

Point-to-point connections: **N hệ thống = N×(N−1)/2 interfaces** → không thể bảo trì!

IE giải quyết: mỗi hệ thống chỉ cần **1 outbound interface**.

IE đảm nhận:
- Queue messages
- Transform format
- Route đến đúng nơi nhận
- Dịch terminology

### Ví dụ luồng dữ liệu thực tế qua IE

```
1. Bệnh nhân đăng ký
   → Registration System gửi ADT message (HL7 v2.x) → IE
   → IE forward đến EHR + LIS

2. Bác sĩ ra lệnh xét nghiệm
   → EHR gửi order (ICD code cục bộ) → IE → LIS (LOINC code)

3. Kết quả lab
   → LIS trả về → IE route đến EHR và billing system
   → IE translate: local lab codes ↔ LOINC codes
```

---

## 5. Interoperability Standards

Hai loại cần phân biệt:
- **Syntactic interoperability**: đồng ý format message (cú pháp)
- **Semantic interoperability**: đảm bảo ý nghĩa được hiểu đúng (ngữ nghĩa)

| Tiêu chuẩn | Loại | Mô tả | Dùng khi nào |
|-----------|------|-------|-------------|
| **HL7 v2.x** | Messaging | Chuẩn phổ biến nhất ở Mỹ; ADT, ORM, ORU | EHR ↔ LIS, radiology, pharmacy |
| **HL7 v3 / CDA** | Document | Formal spec; Clinical Document Architecture | Tài liệu lâm sàng có cấu trúc |
| **FHIR R4** | API/REST | Chuẩn mới nhất; RESTful; JSON/XML | Mobile app, modern API integration |
| **DICOM** | Imaging | Chuẩn hình ảnh y tế (X-ray, MRI, CT) | PACS, radiology systems |
| **X12** | Admin | Giao dịch hành chính (billing, claims) | Thanh toán bảo hiểm |
| **IHE Profiles** | Framework | Tập profiles tích hợp HL7+DICOM | Cross-enterprise document sharing |

---

## 6. Cloud Computing trong Healthcare

| Mô hình | Ai quản lý gì | Phù hợp với |
|---------|--------------|------------|
| **IaaS** | Cloud: server/storage/network. Bệnh viện: OS, app, DB | Đội IT mạnh, muốn kiểm soát |
| **PaaS** | Cloud: platform + middleware. Bệnh viện: deploy app | Phát triển app nội bộ |
| **SaaS** | Cloud: tất cả. Bệnh viện: chỉ dùng | Chi phí thấp; ít tùy chỉnh |

> ⚠️ **Quan trọng:** Cloud provider **phải ký BAA** (Business Associate Agreement) với bệnh viện theo yêu cầu HIPAA.

---

## 7. SOA & Modern APIs

SOA (Service-Oriented Architecture) cho phép các dịch vụ độc lập giao tiếp qua API chuẩn.

Các service phổ biến trong health IT:

| Service | Chức năng |
|---------|-----------|
| **CDS Service** | Clinical decision support qua API — bất kỳ EHR nào cũng gọi được |
| **Terminology Service** | Tra cứu codes, mappings SNOMED/LOINC/ICD |
| **Patient Identity Service** | Xác định unique patient ID qua MPI |
| **Notification Service** | Gửi alert khi có event lâm sàng |
| **Document Service** | Upload/download tài liệu lâm sàng qua IHE XDS profile |

---

## ✅ Checklist sau chương này

- [ ] Tôi có thể vẽ sơ đồ EHR Component Model với 6 thành phần
- [ ] Tôi hiểu tại sao IE tồn tại (không dùng point-to-point)
- [ ] Tôi biết phân biệt Syntactic vs Semantic interoperability
- [ ] Tôi biết cloud provider phải ký BAA trước khi access PHI

**Tiếp theo:** `01-ehr-systems/02-loai-du-lieu.md` →