# Chương 8 — Terminology Standards: SNOMED, LOINC, ICD, RxNorm

> **Mục tiêu:** Biết dùng đúng bộ mã cho đúng mục đích — và lý do semantic interoperability cần chuẩn hóa.

---

## 1. Tại sao cần Terminology Standards?

> "Bác sĩ A ghi 'Heart Attack', bác sĩ B ghi 'MI', bác sĩ C ghi 'STEMI', LIS ghi code 22298006.  
> Máy tính không thể biết đây là **cùng một bệnh** nếu không có chuẩn chung."

Terminology chuẩn hóa đảm bảo:
- **Semantic interoperability**: ý nghĩa được hiểu đúng khi trao đổi
- **Analytics chính xác**: aggregate được patients cùng chẩn đoán
- **CDS hoạt động đúng**: trigger rules theo diagnosis/medication

---

## 2. Bộ mã nào dùng cho mục đích gì?

```
Muốn mã hóa...           Dùng...
─────────────────────────────────────────────
Chẩn đoán (billing)   →  ICD-10-CM
Thủ thuật (billing)   →  CPT / ICD-10-PCS
Lab & clinical obs     →  LOINC
Thuốc (pharmacy)      →  RxNorm
Mọi khái niệm lâm sàng→  SNOMED CT
Imaging radiology     →  RADLEX + SNOMED
```

---

## 3. ICD-10 — Billing & Mortality

### ICD-10-CM (dùng ở Mỹ cho chẩn đoán)

**Format code:** `[Letter][2 digits].[digits]`

```
J18.9    = Pneumonia, unspecified organism
│││
││└── Detail/Extension
│└─── Category detail
└──── Chapter (J = Respiratory)
```

| Chương | Phạm vi | Ví dụ |
|--------|---------|-------|
| A00–B99 | Infectious diseases | A09 = Gastroenteritis |
| C00–D49 | Neoplasms (ung thư) | C34.11 = Lung cancer |
| E00–E89 | Endocrine (nội tiết) | E11.9 = Type 2 diabetes |
| F01–F99 | Mental disorders | F32.1 = Major depressive disorder |
| I00–I99 | Circulatory | I21.9 = STEMI |
| J00–J99 | Respiratory | J45.20 = Mild intermittent asthma |
| Z00–Z99 | Health factors | Z23 = Vaccination encounter |

### ICD-10-PCS — Thủ thuật nội trú (7 ký tự)

```
0GR30JZ  = Replacement of Cervical Vertebral Joint
│││││││
│││││││── Qualifier
││││││─── Device
│││││──── Approach
││││───── Body Part
│││────── Root Operation (R = Replacement)
││─────── Body System (GR = Upper Joints)
│──────── Section (0 = Medical & Surgical)
```

---

## 4. LOINC — Lab & Clinical Observations

Developed by Regenstrief Institute. Free, 95,000+ terms.

### 6 Axis của LOINC concept

| Axis | Mô tả | Ví dụ |
|------|-------|-------|
| **Component** | Chất được đo | Glucose |
| **Property** | Loại đo lường | Mass concentration (MCnc) |
| **Time Aspect** | Pt (point) vs 24h | Pt |
| **System** | Loại mẫu | Ser (Serum) |
| **Scale** | Qn/Ord/Nom | Qn (quantitative) |
| **Method** | Phương pháp | Enzymatic |

### LOINC codes phải nhớ

| LOINC | Tên | Unit |
|-------|-----|------|
| **2345-7** | Glucose in Serum | mg/dL |
| **4548-4** | Hemoglobin A1c | % |
| **2160-0** | Creatinine in Serum | mg/dL |
| **8480-6** | Systolic blood pressure | mm[Hg] |
| **8462-4** | Diastolic blood pressure | mm[Hg] |
| **8310-5** | Body temperature | Cel |
| **8867-4** | Heart rate | /min |
| **59408-5** | Oxygen saturation | % |
| **29463-7** | Body weight | kg |
| **34133-9** | Summary of episode note (CCD) | — |
| **18842-5** | Discharge summary | — |

---

## 5. SNOMED CT — Comprehensive Clinical Terminology

Largest clinical terminology. 350,000+ concepts. Sở hữu bởi SNOMED International.

### 3 thành phần

| Thành phần | Mô tả | Ví dụ |
|-----------|-------|-------|
| **Concept** | Unique clinical meaning + numeric ID | 22298006 = Myocardial infarction |
| **Description** | Human-readable names | "Heart attack", "MI" → đều là 22298006 |
| **Relationship** | Liên kết giữa concepts | MI IS-A → Disorder of heart |

### Relationship types quan trọng

```
IS-A (hierarchy):
  Myocardial infarction IS-A Disorder of heart

FINDING-SITE:
  Pneumonia FINDING-SITE Lung structure

CAUSATIVE-AGENT:
  Streptococcal pneumonia CAUSATIVE-AGENT Streptococcus
```

### SNOMED Hierarchy Chapters

| Hierarchy | Nội dung |
|-----------|---------|
| Clinical finding | Diseases, symptoms, exam findings |
| Procedure | Surgery, imaging, lab tests |
| Body structure | Anatomy |
| Organism | Bacteria, viruses |
| Substance | Chemicals, drugs (raw) |
| Observable entity | Measurable observations |

---

## 6. RxNorm — Drug Terminology (miễn phí từ NLM)

### RxNorm Term Types (TTY)

| TTY | Ý nghĩa | Ví dụ |
|-----|---------|-------|
| **IN** | Ingredient | Metformin |
| **BN** | Brand Name | Glucophage |
| **SCD** | Semantic Clinical Drug | Metformin 500 MG Oral Tablet |
| **SBD** | Semantic Branded Drug | Glucophage 500 MG Oral Tablet |

### RxNorm Hierarchy

```
Metformin (IN: 6809)
 └── Metformin 500 MG (SCDC)
      └── Metformin 500 MG Oral Tablet (SCD: 861004)
           └── Glucophage 500 MG Oral Tablet (SBD: 861007)
```

### NDC vs RxNorm

```
NDC (11 digits):  0006-0952-68
                  Labeler-Product-Package
→ Thay đổi theo manufacturer/batch → KHÔNG dùng cho analytics

RxNorm: concept-based, stable → DÙNG cho analytics và CDS
```

---

## 7. Code Mapping — Chuyển đổi giữa bộ mã

| Mapping | Nguồn file | Lưu ý quan trọng |
|---------|-----------|----------------|
| ICD-9 → ICD-10 | CMS General Equivalence Mappings (GEM) | Không phải 1-to-1; có many-to-many |
| Local codes → LOINC | Regenstrief RELMA tool | Cần clinical expert review |
| NDC → RxNorm | NLM RxNav API | NDC thay đổi theo batch |
| SNOMED → ICD-10 | SNOMED International map | Available free download |

---

## 8. Free APIs từ NLM

| API | URL | Dùng để |
|-----|-----|---------|
| LOINC Search | loinc.org/search | Tìm LOINC code |
| RxNorm API | rxnav.nlm.nih.gov | Drug info, NDC↔RxNorm |
| SNOMED Browser | browser.ihtsdotools.org | Tra SNOMED concepts |
| VSAC | vsac.nlm.nih.gov | Value Sets cho FHIR profiles |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết chọn đúng bộ mã cho từng loại dữ liệu
- [ ] Tôi hiểu cấu trúc ICD-10 code (letter + digits)
- [ ] Tôi biết 6 axis của LOINC concept
- [ ] Tôi nhớ ít nhất 5 LOINC codes vital signs quan trọng
- [ ] Tôi biết SNOMED IS-A relationship và tại sao quan trọng
- [ ] Tôi biết NDC ≠ RxNorm và khi nào dùng cái nào

**Tiếp theo:** `03-standards-data/04-smart-cds-hooks.md` →