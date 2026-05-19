## Certifications & Lộ Trình Học

---

### 1. Certifications Quan Trọng

| Certification | Tổ chức | Focus | Phù hợp với |
|-------------|---------|-------|-------------|
| **RHIA** | AHIMA | Health Information Management | HIM professionals |
| **CHDA** | AHIMA | Healthcare Data Analytics | Data analysts/engineers |
| **CHPS** | AHIMA | Healthcare Privacy & Security | Security engineers |
| **CPHIMS** | HIMSS | Health Information & Management Systems | IT managers, generalist |
| **CAHIMS** | HIMSS | Associate level CPHIMS | Early career |
| **HCISPP** | (ISC)² | Healthcare Security & Privacy | Security professionals |
| **CISSP** | (ISC)² | General security (respected in healthcare) | Senior security |
| **AWS Certified** | AWS | Cloud — có Healthcare specialty | Cloud architects |

---

### 2. Lộ Trình 12 Tháng — Từ General Dev sang Health IT Dev

#### Tháng 1–2: Nền tảng

```
□ Đọc toàn bộ tài liệu này (health-it-docs)
□ HIPAA Overview course (HHS free online)
□ HL7 FHIR Fundamentals (hl7.org/fhir — free)
□ Tạo account HAPI FHIR sandbox, thực hành queries
□ Sign up Synthea — generate synthetic patient data
```

#### Tháng 3–4: Standards thực hành

```
□ Build FHIR REST client (Python/JS) query Patient, Observation, Medication
□ Parse một HL7 v2 ADT message thủ công — không dùng library
□ Sau đó dùng library (hl7apy hoặc HAPI HL7)
□ Setup Mirth Connect locally, build Hello World channel
□ LOINC/SNOMED lookup: tra cứu 10 lab tests, 5 diagnoses
```

#### Tháng 5–6: SMART on FHIR

```
□ Complete SMART on FHIR tutorial (smarthealthit.org)
□ Build simple SMART app: launch từ EHR, hiển thị patient vitals
□ Implement CDS Hooks service đơn giản (patient-view hook)
□ Deploy lên cloud, test với SMART App Launcher
```

#### Tháng 7–8: Security & Compliance

```
□ HIPAA Security Rule self-assessment (free HHS tool)
□ Audit log implementation: build audit middleware
□ Encryption: implement AES-256 at rest, enforce TLS
□ Review OWASP API Security Top 10 — test trên practice API
□ HIPAA compliance checklist tự audit app đã build
```

#### Tháng 9–10: Specialization

```
Chọn 1 track:

Integration Track:
□ Build Mirth Connect channel: ADT → 2 downstream systems
□ Implement ORU result routing với terminology mapping

Data Track:
□ Load Synthea data vào OMOP CDM
□ Build ETL pipeline: FHIR → OMOP
□ Viết ATLAS cohort definition

ML Track:
□ Build readmission prediction model với OMOP data
□ Implement SHAP explainability
□ Write model card (FDA requirement)
```

#### Tháng 11–12: Certification & Portfolio

```
□ Thi CAHIMS hoặc CHDA (tùy track)
□ Contribute to open source health IT project:
   - HAPI FHIR (Java)
   - Smile CDR plugins
   - OHDSI tools (Python/R)
□ Write 2-3 blog posts về health IT topics
□ Update LinkedIn với health IT skills
□ Apply cho health IT roles / switch nếu đang ở khác ngành
```

---

### 3. Resources Học Miễn Phí

| Resource | URL | Nội dung |
|---------|-----|---------|
| HL7 FHIR Spec | hl7.org/fhir/R4 | Official specification |
| SMART on FHIR | docs.smarthealthit.org | Tutorials + sandbox |
| ONC Training | healthit.gov | Health IT fundamentals |
| OHDSI | ohdsi.org | OMOP CDM + analytics tools |
| CMS Developer | developer.cms.gov | Medicare/Medicaid APIs |
| NLM RxNav | rxnav.nlm.nih.gov | Drug terminology APIs |
| LOINC | loinc.org | Lab code lookup |
| SNOMED | browser.ihtsdotools.org | Clinical terminology browser |
| Mirth Connect | nextgen.com/connect | Open source interface engine |
| Synthea | synthea.mitre.org | Synthetic patient generator |

---

### 4. Communities & Events

| Community | Mô tả |
|-----------|-------|
| **HIMSS** | Largest health IT conference (annual); local chapters |
| **HL7 International** | Standards body; working groups open to all |
| **OHDSI** | OMOP CDM community; annual symposium |
| **AMIA** | American Medical Informatics Association |
| **Health Dev Slack** | #fhir, #hl7 channels |
| **ONC Tech Lab** | US government health IT initiatives |

---

## ✅ Checklist sau phần Career

- [ ] Tôi biết mình muốn theo track nào (Integration/Data/Security/App)
- [ ] Tôi biết 3 certifications phù hợp với track của mình
- [ ] Tôi đã setup HAPI FHIR sandbox và chạy thử queries
- [ ] Tôi đã tải Mirth Connect và build channel đầu tiên
- [ ] Tôi có lộ trình 12 tháng rõ ràng

**Hoàn thành tất cả tài liệu! Quay lại README.md để xem tổng quan.**