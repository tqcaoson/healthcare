# 🏥 Health IT — Tài liệu học cho Lập trình viên

> Tổng hợp 80/20 từ *Health Informatics: An Interprofessional Approach, 2nd Ed.*  
> Nguồn: Nelson & Staggers | Ngôn ngữ: Tiếng Việt

---

## 📂 Cấu trúc thư mục

```
health-it-docs/
├── README.md                  ← Bạn đang ở đây
│
├── 00-overview/
│   └── 01-tong-quan.md        ← Health Informatics là gì? Vai trò dev
│
├── 01-ehr-systems/
│   ├── 01-kien-truc-ehr.md    ← EHR Component Model, CDR, Interface Engine
│   ├── 02-loai-du-lieu.md     ← EMR/EHR/PHR, dữ liệu trong EHR
│   └── 03-he-thong-hanh-chinh.md ← LIS, RIS, PACS, ADT, MPI
│
├── 02-clinical-apps/
│   ├── 01-cpoe-cdss.md        ← CPOE, Clinical Decision Support
│   ├── 02-alert-fatigue.md    ← Alert Fatigue, Five Rights of CDS
│   └── 03-telehealth.md       ← Telehealth, RPM, mHealth
│
├── 03-standards-data/
│   ├── 01-hl7v2.md            ← HL7 v2 message structure, ADT/ORM/ORU
│   ├── 02-fhir.md             ← FHIR R4, REST operations, US Core
│   ├── 03-terminology.md      ← SNOMED, LOINC, ICD-10, RxNorm
│   └── 04-smart-cds-hooks.md  ← SMART on FHIR, CDS Hooks
│
├── 04-security-hipaa/
│   ├── 01-hipaa-co-ban.md     ← PHI, ePHI, BAA, 18 identifiers
│   ├── 02-technical-safeguards.md ← Encryption, Access Control, Audit Log
│   ├── 03-cia-ransomware.md   ← CIA Triad, Ransomware, NIST Framework
│   └── 04-incident-response.md ← Breach Notification, IR Playbook
│
├── 05-integration-api/
│   ├── 01-fhir-api-cheatsheet.md ← FHIR REST cheat sheet, Search params
│   ├── 02-hl7-integration.md  ← Mirth Connect, HL7 patterns thực tế
│   ├── 03-hie.md              ← Health Information Exchange, TEFCA
│   └── 04-public-health.md    ← Syndromic Surveillance, ELR, IIS
│
├── 06-analytics-ml/
│   ├── 01-analytics-co-ban.md ← 4 cấp độ analytics, Big Data 5V
│   ├── 02-data-engineering.md ← ETL pipeline, OMOP CDM, data quality
│   └── 03-ml-healthcare.md    ← ML use cases, pitfalls, explainability
│
├── 07-governance-legal/
│   ├── 01-luat-quy-dinh.md    ← HIPAA, HITECH, 21st Cures, FDA SaMD
│   └── 02-it-governance.md    ← EMRAM, CMIO/CISO roles, vòng đời hệ thống
│
└── 08-career/
    ├── 01-career-paths.md     ← Career paths, mức lương, kỹ năng cần
    ├── 02-chung-chi.md        ← Certifications, lộ trình 6-12 tháng
    ├── 03-glossary.md         ← Bảng tra cứu thuật ngữ & từ viết tắt
    ├── 04-clinical-workflows.md ← Inpatient workflow, Lab, Medication
    ├── 05-patterns-antipatterns.md ← Design patterns, anti-patterns, checklist
    └── 06-mental-models.md    ← Big picture, Interoperability Pyramid
```

---

## 🗓 Lịch học gợi ý (4 tuần)

| Tuần | Chủ đề | File cần đọc |
|------|--------|-------------|
| **Tuần 1** | Nền tảng | `00-overview/` → `01-ehr-systems/` → `02-clinical-apps/` |
| **Tuần 2** | Standards cốt lõi | `03-standards-data/` (toàn bộ) |
| **Tuần 3** | Bảo mật + Integration | `04-security-hipaa/` → `05-integration-api/` |
| **Tuần 4** | Analytics + Career | `06-analytics-ml/` → `07-governance-legal/` → `08-career/` |

---

## 🚀 Bắt đầu nhanh theo role

| Bạn là... | Đọc trước |
|-----------|----------|
| Dev mới với Health IT | `00` → `01` → `03-standards-data/01-hl7v2.md` → `03-standards-data/02-fhir.md` |
| Integration Engineer | `01-ehr-systems/01` → `05-integration-api/` (toàn bộ) |
| Data/ML Engineer | `03-standards-data/03` → `06-analytics-ml/` (toàn bộ) |
| Security Engineer | `04-security-hipaa/` (toàn bộ) → `05-integration-api/01` |
| mHealth Developer | `02-clinical-apps/03` → `03-standards-data/02-fhir.md` → `04-security-hipaa/01` |

---

## ⚡ Ưu tiên học theo tầm quan trọng

| Mức | Chủ đề |
|-----|--------|
| 🔴 **Phải biết** | HIPAA/ePHI/BAA · HL7 v2 · FHIR R4 · EHR Component Model |
| 🟠 **Nên biết** | SNOMED/LOINC/ICD · CDSS · Alert Fatigue · CDR/Interface Engine |
| 🟡 **Tốt có** | OMOP CDM · NLP · Ransomware defense · FDA SaMD |

---

*Nguồn gốc: Health Informatics An Interprofessional Approach, 2nd Ed. — Nelson & Staggers*