# Chương 1 — Tổng Quan Health Informatics

> **Mục tiêu:** Hiểu Health IT là gì, tại sao dev cần học, và xu hướng công nghệ định hình ngành.

---

## 1. Định nghĩa & Phạm vi

**Health Informatics** = Ứng dụng CNTT để thu thập, lưu trữ, xử lý, chia sẻ dữ liệu y tế → cải thiện chăm sóc sức khỏe.

Đây là giao điểm của **3 lĩnh vực**:
- Computer Science
- Khoa học thông tin (Information Science)
- Khoa học y tế (Health Science)

### Các nhánh con

| Thuật ngữ | Phạm vi |
|-----------|---------|
| **Clinical Informatics** | Dữ liệu lâm sàng trực tiếp (chẩn đoán, điều trị, xét nghiệm) |
| **Biomedical Informatics** | Sinh học phân tử + gen học + CNTT |
| **Public Health Informatics** | Giám sát dịch bệnh, y tế cộng đồng |

---

## 2. Vai trò Developer trong Health IT

| Role | Trách nhiệm kỹ thuật | Kiến thức nghiệp vụ cần có |
|------|---------------------|---------------------------|
| **EHR Developer** | Xây dựng hệ thống hồ sơ bệnh nhân | Quy trình lâm sàng, HL7/FHIR |
| **Integration Engineer** | Kết nối các hệ thống bệnh viện | Interoperability, interface engine, message routing |
| **Data Engineer** | ETL pipeline, data warehouse y tế | SNOMED, LOINC, ICD, mã hóa lâm sàng |
| **CDS Developer** | Công cụ hỗ trợ quyết định | Clinical guidelines, rule engine, alert logic |
| **Security Engineer** | Bảo vệ dữ liệu bệnh nhân | HIPAA, PHI, CIA triad, audit log |
| **Mobile/App Dev** | mHealth, telehealth | FDA regulation, quyền riêng tư dữ liệu sức khỏe |

---

## 3. Xu hướng công nghệ định hình ngành

### 🔗 Interoperability (Khả năng tương tác)
Mục tiêu quan trọng nhất của ngành — các hệ thống khác nhau phải trao đổi dữ liệu được với nhau.

### ☁️ Cloud Computing
Dịch chuyển EHR lên cloud (AWS, Azure, GCP) → giảm chi phí hạ tầng.

### 🤖 AI/ML trong lâm sàng
- Phát hiện sớm bệnh
- Gợi ý điều trị
- Phân tích hình ảnh y tế

### 📡 FHIR & Open APIs
Chuẩn mới nhất để xây dựng ứng dụng y tế có thể tích hợp dễ dàng.

### 💻 Telehealth
COVID-19 đẩy nhanh số hóa khám chữa bệnh từ xa (tăng 154 lần chỉ trong vài tháng).

### 📊 Big Data & Real-world Evidence
Phân tích hàng triệu hồ sơ bệnh nhân để tìm pattern điều trị hiệu quả.

---

## 4. Lưu ý quan trọng khi làm việc trong ngành

> **"Mỗi quy định trong y tế đều có lý do cụ thể — thường là đã có người chết vì thiếu nó."**

- Health IT **không chỉ là kỹ thuật** — failure thường do không phù hợp với workflow thực tế
- Luôn đặt câu hỏi "Tại sao?" trước "Như thế nào?"
- Shadow bác sĩ/điều dưỡng để hiểu workflow thực — không cuốn sách nào thay thế được

---

## ✅ Checklist sau chương này

- [ ] Tôi hiểu Health Informatics là gì và khác ngành IT thông thường thế nào
- [ ] Tôi biết role của mình maps sang loại kiến thức nghiệp vụ nào
- [ ] Tôi biết FHIR là tương lai và HL7 là hiện tại của integration

**Tiếp theo:** `01-ehr-systems/01-kien-truc-ehr.md` →