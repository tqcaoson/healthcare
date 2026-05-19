## IT Governance: Roles, EMRAM, System Lifecycle

---

### 1. Key Roles trong Health IT

| Role | Trách nhiệm | Người Dev nên biết |
|------|-------------|-------------------|
| **CMIO** | Chief Medical Informatics Officer | Cầu nối lâm sàng và IT; approve clinical requirements |
| **CIO** | Chief Information Officer | Chiến lược IT; budget; vendor selection |
| **CISO** | Chief Information Security Officer | Security policy; HIPAA compliance; incident response |
| **CNO** | Chief Nursing Officer | Nursing workflow requirements; đại diện điều dưỡng |
| **CMO** | Chief Medical Officer | Clinical policies; physician buy-in |
| **Health Informaticist** | Clinical + IT bilingual specialist | Translate requirements giữa clinician và dev |

---

### 2. Change Management — Tại sao EHR projects fail

> **70% của EHR implementations** gặp vấn đề nghiêm trọng. Lý do thường là **người dùng**, không phải công nghệ.

| Lý do thất bại | Giải pháp |
|---------------|----------|
| Clinicians không được hỏi ý kiến | Workflow analysis TRƯỚC khi design |
| Training không đủ | Super-user program; go-live support |
| Too much change at once | Phased rollout |
| Alert fatigue ngay từ đầu | Tune CDS trước go-live |
| Thiếu executive support | CMIO champion; C-suite commitment |

### 3. Go-Live Planning Checklist (Developer perspective)

```
T-6 months:
□ Integration testing với tất cả downstream systems
□ Performance testing (load test với expected peak volume)
□ Security assessment / penetration test
□ Disaster recovery test

T-3 months:
□ User acceptance testing (UAT) với real clinicians
□ Data migration dry run
□ Downtime procedures documented

T-1 month:
□ Final data migration dress rehearsal
□ Support desk training
□ Rollback plan tested

Go-live week:
□ 24/7 on-call rotation
□ Super users trên mọi unit/shift
□ Command center staffed
□ Monitoring dashboards live

Post go-live:
□ Daily issues review cho 30 ngày
□ Performance metrics baseline
□ User feedback collection
□ 90-day optimization plan
```

---

### 4. System Downtime Impact — Healthcare khác mọi ngành

> EHR downtime = bác sĩ không tra được dị ứng, không xem được kết quả lab, không ra được lệnh thuốc → **patient safety risk**.

| System | RTO Target | RPO Target | Lý do |
|--------|-----------|-----------|-------|
| EHR (inpatient) | < 30 phút | < 15 phút | Critical care, medication orders |
| PACS | < 2 giờ | < 1 giờ | Radiology reads can't wait long |
| LIS | < 1 giờ | < 30 phút | Stat lab results life-critical |
| Billing | < 24 giờ | < 4 giờ | Important but not life-critical |
| Analytics | < 4 giờ | < 24 giờ | Not patient-facing |

---

### 5. Contract & Vendor Management

| Điều khoản | Mục đích |
|-----------|---------|
| **SLA** | Uptime ≥99.9%; response time; penalty |
| **Data Ownership** | Patient data = bệnh viện; xuất được khi hết hợp đồng |
| **Source Code Escrow** | Vendor phá sản → code release |
| **Right to Audit** | Bệnh viện có quyền audit security |
| **Subcontractor limits** | Vendor phải notify nếu dùng BA khác |
| **Termination clause** | Data migration plan; transition period |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết HIPAA, HITECH, 21st Century Cures giải quyết vấn đề gì
- [ ] Tôi biết Information Blocking là gì và penalty $1M/violation
- [ ] Tôi biết FDA phân loại SaMD theo 3 class
- [ ] Tôi hiểu Value-Based Care cần IT gì (analytics, care gaps)
- [ ] Tôi biết CMIO, CISO, CIO vai trò khác nhau thế nào
- [ ] Tôi biết EHR RTO target < 30 phút và tại sao

**Tiếp theo:** `08-career/01-career-paths.md` →