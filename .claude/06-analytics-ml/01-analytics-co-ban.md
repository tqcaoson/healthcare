# Chương 14a — Analytics Cơ Bản: 4 Cấp Độ, Big Data & Healthcare Metrics

> **Mục tiêu:** Hiểu landscape analytics trong y tế, từ reporting đơn giản đến predictive — và biết đo lường cái gì.

---

## 1. Tại sao Analytics quan trọng trong Healthcare?

> **"In God we trust. All others must bring data."** — W. Edwards Deming

Không có analytics:
- Không biết bao nhiêu bệnh nhân tái nhập viện trong 30 ngày
- Không phát hiện được cluster nhiễm khuẩn bệnh viện sớm
- Không biết bác sĩ nào đang prescribe opioids bất thường
- Không optimize staffing → under/over-staffed → quality giảm / cost tăng

---

## 2. 4 Cấp Độ Analytics — Framework Cốt Lõi

```
Độ phức tạp ↑
Giá trị ↑
                    ┌─────────────────────────────────┐
                    │  4. PRESCRIPTIVE                 │
                    │  "Nên làm gì?"                   │
                    │  Tối ưu hóa, simulation          │
                    │  → Phân bổ y tá tối ưu           │
                    ├─────────────────────────────────┤
                    │  3. PREDICTIVE                   │
                    │  "Điều gì sẽ xảy ra?"            │
                    │  ML, statistical models          │
                    │  → Dự đoán readmission, sepsis   │
                    ├─────────────────────────────────┤
                    │  2. DIAGNOSTIC                   │
                    │  "Tại sao xảy ra?"               │
                    │  Drill-down, root cause          │
                    │  → Tại sao CAUTI rate tăng?      │
                    ├─────────────────────────────────┤
                    │  1. DESCRIPTIVE                  │
                    │  "Điều gì đã xảy ra?"            │
                    │  Reports, dashboards, BI tools   │
                    │  → Tỷ lệ tái nhập viện là bao nhiêu? │
                    └─────────────────────────────────┘
```

### Chi tiết từng cấp độ

| Cấp | Câu hỏi | Công cụ IT | Ví dụ healthcare | Khó implement |
|-----|---------|-----------|-----------------|--------------|
| **Descriptive** | Đã xảy ra gì? | SQL, BI dashboard, Crystal Reports | "30-day readmission rate = 12.3%" | ⭐ |
| **Diagnostic** | Tại sao xảy ra? | OLAP drill-down, data exploration | "ICU CAUTI rate tăng từ khi shift team C đổi procedure" | ⭐⭐ |
| **Predictive** | Sẽ xảy ra gì? | ML models, regression, time series | "Patient này có 78% risk readmit trong 30 ngày" | ⭐⭐⭐ |
| **Prescriptive** | Nên làm gì? | Optimization, simulation, RL | "Assign 3 thêm điều dưỡng cho ca tối thứ 6" | ⭐⭐⭐⭐ |

---

## 3. Big Data 5V trong Healthcare

| V | Mô tả | Healthcare Example | Scale thực tế |
|---|-------|-------------------|--------------|
| **Volume** | Lượng data khổng lồ | DICOM images, genomics, EHR records | 1 bệnh viện lớn: hàng TB/năm; DICOM = 90% |
| **Velocity** | Data đến nhanh, real-time | ICU monitors: vitals mỗi giây từ 30 giường | 30 giường × 6 params × 1/sec = 180 events/sec |
| **Variety** | Nhiều loại data khác nhau | Structured labs + unstructured notes + DICOM + claims | Text, numbers, images, audio (dictation), video |
| **Veracity** | Chất lượng, độ tin cậy | Missing values, conflicting codes, copy-paste notes | 20–30% clinical data có lỗi; cần cẩn thận hơn ngành khác |
| **Value** | Mục tiêu cuối cùng | Improve outcomes, reduce cost, increase safety | Không phải chỉ lưu data — phải actionable |

---

## 4. Healthcare-Specific Metrics — Phải biết

### Clinical Quality Metrics

| Metric | Định nghĩa | Formula | Target |
|--------|-----------|---------|--------|
| **30-day Readmission Rate** | % bệnh nhân xuất viện và nhập viện trở lại trong 30 ngày | Readmits / Total Discharges × 100 | < 10% (penalty nếu cao) |
| **Average Length of Stay (ALOS)** | Số ngày nằm viện trung bình | Total patient days / Total discharges | Benchmark theo DRG |
| **Mortality Rate** | % bệnh nhân tử vong trong bệnh viện | Deaths / Admissions × 100 | Risk-adjusted |
| **Surgical Site Infection (SSI)** | Nhiễm khuẩn vết mổ | Infections / Surgeries × 1000 | < 1% (HAI metric) |
| **CAUTI Rate** | Catheter-associated UTI | CAUTI cases / Device days × 1000 | 0 (national target) |
| **CLABSI Rate** | Central line bloodstream infection | Infections / Line days × 1000 | 0 (national target) |
| **Patient Falls Rate** | Té ngã trong bệnh viện | Falls / Patient days × 1000 | < 3.5/1000 patient days |
| **Medication Error Rate** | Lỗi thuốc | Errors / Medication administrations | < 1/1000 |

### Operational Metrics

| Metric | Mô tả | Formula |
|--------|-------|---------|
| **ED Door-to-Provider** | Thời gian từ đến ED đến gặp bác sĩ | Median/Mean time in minutes |
| **ED Length of Stay** | Tổng thời gian ở ED | From arrival to disposition |
| **OR Utilization** | % phòng mổ được sử dụng | Scheduled time / Available time |
| **Bed Occupancy Rate** | % giường đang có bệnh nhân | Occupied beds / Total beds × 100 |
| **Lab TAT** | Turnaround time xét nghiệm | Order time → result time |

### Financial Metrics

| Metric | Mô tả |
|--------|-------|
| **Cost per Case** | Chi phí trung bình cho mỗi admission |
| **Revenue per Adjusted Discharge** | Doanh thu sau điều chỉnh case mix |
| **CMI** | Case Mix Index — complexity của patient population |
| **Denial Rate** | % claims bị insurance từ chối |

---

## 5. HAI (Hospital-Acquired Infections) Surveillance

HAI là quality metric quan trọng nhất — **50% bị phòng ngừa được**.

```
Surveillance Data Pipeline:
┌────────────────┐    ┌──────────────┐    ┌───────────────────┐
│ Microbiology   │    │ Nursing      │    │ Procedure data    │
│ (blood cultures│ →  │ (device days,│ →  │ (surgical cases,  │
│  cultures)     │    │  catheter in)│    │  implants)        │
└────────────────┘    └──────────────┘    └───────────────────┘
         ↓                   ↓                      ↓
         └───────────────────┼──────────────────────┘
                             ↓
                  Infection Control Database
                  (typically separate system)
                             ↓
                  NHSN Reporting (CDC)
                  (National Healthcare Safety Network)
                             ↓
                  Public reporting (hospitalcompare.hhs.gov)
```

**Developer task:** Build interface để auto-send HAI data đến NHSN (CDA-based XML).

---

## 6. Cohort Identification — Core Analytics Task

Tìm tất cả patients thỏa mãn criteria nhất định (cho nghiên cứu, quality measure, population health).

### ATLAS (OHDSI Tool) — Cohort Definition Example

```json
{
  "name": "Type 2 Diabetes patients on Metformin",
  "Expression": {
    "InclusionRules": [
      {
        "name": "Has Type 2 Diabetes",
        "Criteria": {
          "ConditionOccurrence": {
            "CodesetId": 1,  // SNOMED codes for T2DM
            "OccurrenceStartDate": {"Value": "2010-01-01", "Op": "gte"}
          }
        }
      },
      {
        "name": "On Metformin",
        "Criteria": {
          "DrugExposure": {
            "CodesetId": 2  // RxNorm codes for Metformin
          }
        }
      }
    ],
    "ExclusionRules": [
      {
        "name": "Exclude CKD Stage 4-5",
        "Criteria": { "ConditionOccurrence": { "CodesetId": 3 } }
      }
    ]
  }
}
```

### SQL trực tiếp trên OMOP CDM

```sql
-- Bệnh nhân T2DM có HbA1c > 9% trong 12 tháng gần nhất
WITH latest_a1c AS (
    SELECT
        m.person_id,
        m.value_as_number AS a1c_value,
        m.measurement_date,
        ROW_NUMBER() OVER (PARTITION BY m.person_id ORDER BY m.measurement_date DESC) AS rn
    FROM omop.measurement m
    JOIN omop.concept c ON m.measurement_concept_id = c.concept_id
    WHERE c.concept_code = '4548-4'        -- LOINC HbA1c
      AND c.vocabulary_id = 'LOINC'
      AND m.measurement_date >= CURRENT_DATE - INTERVAL '12 months'
)
SELECT
    p.person_id,
    p.year_of_birth,
    p.gender_source_value,
    la.a1c_value
FROM omop.person p
-- Has T2DM diagnosis
JOIN omop.condition_occurrence co
    ON p.person_id = co.person_id
JOIN omop.concept c_diag
    ON co.condition_concept_id = c_diag.concept_id
-- Get latest A1c
JOIN latest_a1c la
    ON p.person_id = la.person_id AND la.rn = 1
WHERE c_diag.concept_code IN (SELECT concept_code FROM omop.concept
                               WHERE vocabulary_id = 'SNOMED'
                               AND domain_id = 'Condition'
                               AND concept_class_id IN ('Clinical Finding')
                               AND concept_name ILIKE '%type 2 diabetes%')
  AND la.a1c_value > 9.0
ORDER BY la.a1c_value DESC;
```

---

## 7. Real-World Evidence (RWE) — Xu hướng

RWE = phân tích dữ liệu EHR thực tế để bổ sung/thay thế clinical trials trong một số trường hợp.

| Ứng dụng | Ví dụ |
|---------|-------|
| **Post-market surveillance** | FDA yêu cầu track drug performance sau khi approve |
| **Comparative effectiveness** | Drug A vs Drug B trong thực tế (không phải RCT) |
| **Safety signal detection** | Phát hiện adverse effects mới từ hàng triệu records |
| **Label expansion** | Chứng minh drug hiệu quả cho indication mới |
| **Health economics** | Cost-effectiveness analysis trong dân số thực |

**FDA Framework (2018):** RWE có thể dùng trong regulatory submissions với điều kiện quality đủ cao.

---

## 8. Population Health Management

Quản lý sức khỏe toàn bộ dân số (không chỉ patients đang nhập viện).

```
Inputs:                    Processing:              Outputs:
EHR data              →   Risk stratification   →  High-risk patient lists
Claims data               Care gap identification   Automated outreach
Lab results               Predictive modeling       Care management plans
Social determinants                                 Quality measure reports
(food, housing, income)
```

**Risk Stratification tiers:**
| Tier | % Population | Approach |
|------|-------------|---------|
| Low risk | ~70% | Preventive care, education, self-management |
| Rising risk | ~20% | Proactive outreach, care coaching |
| High risk | ~8% | Embedded care management, frequent contact |
| Very high/Complex | ~2% | Intensive case management, specialist team |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 4 cấp độ analytics và cho ví dụ healthcare cho từng cấp
- [ ] Tôi biết 5V của Big Data và đặc thù healthcare (Veracity quan trọng nhất)
- [ ] Tôi biết ít nhất 6 clinical quality metrics và cách tính
- [ ] Tôi hiểu Cohort Identification là gì và có thể viết SQL cơ bản trên OMOP
- [ ] Tôi biết HAI là gì và tại sao quan trọng

**Tiếp theo:** `06-analytics-ml/02-data-engineering.md` →