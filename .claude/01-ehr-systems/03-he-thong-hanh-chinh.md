# Chương 4 — Hệ Thống Hành Chính & Vận Hành Bệnh Viện

> **Mục tiêu:** Biết tên, chức năng và cách tích hợp của từng hệ thống phụ trong bệnh viện.

---

## 1. Hệ sinh thái hệ thống bệnh viện

Bên cạnh EHR, bệnh viện cần nhiều hệ thống hành chính khác. Lập trình viên thường phải **tích hợp EHR với các hệ thống này**.

| Hệ thống | Tên đầy đủ | Chức năng chính | Tích hợp với EHR |
|---------|-----------|----------------|-----------------|
| **ADT** | Admission, Discharge, Transfer | Quản lý vào/ra bệnh nhân; update demographics | HL7 ADT messages → update patient records |
| **RIS** | Radiology Information System | Quản lý lịch chụp, đọc kết quả X-ray/CT/MRI | Order routing từ EHR; result back via HL7 ORU |
| **LIS** | Laboratory Information System | Quản lý xét nghiệm, kết quả lab | Order từ CPOE; result routing + alert về EHR |
| **PACS** | Picture Archiving & Communication | Lưu trữ và xem hình ảnh DICOM | Liên kết với RIS; viewer trong EHR |
| **Pharmacy IS** | Pharmacy Information System | Dispensing, inventory, medication verification | Nhận medication orders từ CPOE; MAR update |
| **HIS/HMS** | Hospital Information/Management System | Quản lý tổng thể: giường bệnh, lịch mổ, nhân sự | ADT integration; billing reconciliation |
| **Billing/RCM** | Revenue Cycle Management | Coding, claim submission, collections | Nhận diagnoses/procedures từ EHR để billing |
| **Scheduling** | Appointment Scheduling | Đặt lịch khám, phòng mổ, thiết bị | Tạo encounter trong EHR khi check-in |

---

## 2. Master Patient Index (MPI) — Quản lý định danh bệnh nhân

### Vấn đề MPI giải quyết

> Làm sao biết "John Smith sinh 1980" trong hệ thống A là **cùng người** với "Nguyen Van A" trong hệ thống B?

MPI duy trì danh sách **unique identifiers** và cross-references giữa các hệ thống.

### Thách thức kỹ thuật

| Vấn đề | Mô tả | Hậu quả |
|--------|-------|---------|
| **Duplicate records** | Cùng bệnh nhân có nhiều hồ sơ trong một hệ thống | Dữ liệu phân tán, không đầy đủ |
| **Overlay records** | Hai bệnh nhân KHÁC bị ghép vào một hồ sơ | ⚠️ **Cực kỳ nguy hiểm** — bác sĩ thấy thông tin sai người |
| **Record linkage** | Ghép records từ nhiều nguồn | Cần thuật toán probabilistic matching |
| **Enterprise MPI** | MPI chung cho mạng lưới nhiều cơ sở | Phức tạp hơn, nhưng cần thiết cho hệ thống lớn |

---

## 3. EMRAM — Thang đo trưởng thành IT bệnh viện

EMRAM (Electronic Medical Record Adoption Model) đánh giá mức độ trưởng thành IT (Stage 0–7).

| Stage | Mô tả | Yêu cầu kỹ thuật |
|-------|-------|-----------------|
| **0** | Tất cả department vẫn dùng paper | Không có hệ thống |
| **1** | LIS, RIS, pharmacy có hệ thống riêng biệt | Các hệ thống departmental cơ bản |
| **2** | CDR; các hệ thống phụ kết nối; data dictionary | CDR, MPI, basic integration |
| **3** | CPOE, CDS cơ bản (drug-drug checks) | CPOE, interface engine, basic CDS |
| **4** | CPOE đầy đủ; CDS nâng cao | Advanced CDS, nursing documentation |
| **5** | PACS kết nối đầy đủ; closed loop medication | MAR, barcode medication, radiology integration |
| **6** | Physician documentation; full CDS; full closed loop | Advanced analytics; workflow automation |
| **7** | Paperless; analytics; HIE; data warehousing | Full interoperability; FHIR; analytics platform |

> Hầu hết bệnh viện Mỹ hiện ở Stage 4–6. Stage 7 là mục tiêu.

---

## 4. Vòng đời hệ thống thông tin (Systems Life Cycle)

| Giai đoạn | Hoạt động chính | Vai trò Dev/IT |
|-----------|----------------|---------------|
| **1. Planning** | Xác định nhu cầu, mục tiêu, ngân sách | Đánh giá technical feasibility; ước lượng effort |
| **2. Analysis** | Phân tích quy trình hiện tại, requirements | Gap analysis; phân tích kỹ thuật hiện tại |
| **3. Design** | Thiết kế solution, kiến trúc hệ thống | Technical design; integration architecture |
| **4. Selection** | RFI → RFP → Demo → Evaluation → Contract | Đánh giá API, security, scalability của vendor |
| **5. Implementation** | Cài đặt, migrate data, training, go-live | Migration, interface build, testing |
| **6. Maintenance** | Vận hành, update, optimize, support | Bug fixes, upgrades, performance tuning |
| **7. Evaluation** | Đánh giá hiệu quả, quyết định upgrade/replace | Metrics, analytics, technical assessment |

---

## 5. Technical Checklist khi chọn vendor (RFP)

Khi tổ chức đang chọn EHR vendor, dev/tech lead cần đánh giá:

- **API & Interoperability**: Hỗ trợ FHIR R4? HL7 v2.x? Developer portal? Rate limits?
- **Security**: Encryption at rest và in transit? RBAC? MFA? Pentest history?
- **Scalability**: Cloud-native hay on-premise? SLA uptime?
- **Data Portability**: Export dữ liệu dạng gì khi hết hợp đồng? Vendor lock-in risk?
- **Customization**: Mức độ customize? Có sandbox/dev environment không?
- **TCO**: License + implementation + training + maintenance 10 năm

---

## 6. Điều khoản hợp đồng kỹ thuật quan trọng

| Điều khoản | Ý nghĩa |
|-----------|---------|
| **Source Code Escrow** | Nếu vendor phá sản, source code được giải phóng |
| **Data Ownership** | Dữ liệu bệnh nhân thuộc về bệnh viện — phải xuất được |
| **SLA** | Uptime tối thiểu; response time; penalty nếu vi phạm |
| **Interoperability** | Vendor phải support FHIR, HL7 — không được lock-in |
| **Customization Rights** | Bệnh viện customize được không? Code thuộc về ai? |

---

## ✅ Checklist sau chương này

- [ ] Tôi có thể liệt kê 8 hệ thống bệnh viện và chức năng của từng hệ thống
- [ ] Tôi hiểu MPI giải quyết vấn đề gì và overlay record nguy hiểm thế nào
- [ ] Tôi biết EMRAM stage 0–7 và bệnh viện điển hình ở stage nào
- [ ] Tôi biết các câu hỏi kỹ thuật cần hỏi khi đánh giá EHR vendor

**Tiếp theo:** `02-clinical-apps/01-cpoe-cdss.md` →