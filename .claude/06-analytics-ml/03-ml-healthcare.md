# Chương 14c — Machine Learning trong Y Tế

> **Mục tiêu:** Build clinical ML models đúng cách — tránh pitfalls chết người và đáp ứng yêu cầu FDA.

---

## 1. Clinical ML khác ML thông thường như thế nào?

| Khía cạnh | ML thông thường | Clinical ML |
|-----------|----------------|------------|
| **Cost of error** | User thấy quảng cáo sai | Bệnh nhân nhận điều trị sai → tử vong |
| **Explainability** | "Black box OK nếu accurate" | Bác sĩ không accept nếu không hiểu tại sao |
| **Bias** | Unfair nhưng OK | Disparate outcomes theo race/gender = unethical + illegal |
| **Data split** | Random OK | PHẢI temporal split |
| **Validation** | Single-site | Multi-site bắt buộc |
| **Regulation** | Không có | FDA approval có thể cần |
| **Deployment** | Release anytime | Cần clinical validation, IRB, IT security review |
| **Monitoring** | Accuracy metrics | Clinical outcomes + patient safety events |

---

## 2. Use Cases ML Phổ Biến Nhất

### 2.1 Sepsis Early Warning (Use Case #1 thực tế)

**Vấn đề:** Sepsis giết ~270,000 người/năm ở Mỹ. Phát hiện sớm 6h → mortality giảm 7% mỗi giờ.

**Input features:**
```python
SEPSIS_FEATURES = {
    # Vital signs (time-series, past 24h)
    'heart_rate_mean', 'heart_rate_max', 'heart_rate_trend',
    'systolic_bp_min', 'respiratory_rate_max',
    'temperature_max', 'spo2_min',
    
    # Lab values
    'wbc',           # White blood cell count
    'lactate',       # Tissue hypoxia marker (most important)
    'creatinine',    # Kidney function
    'bilirubin',     # Liver function
    'platelet',      # Coagulation
    
    # Clinical
    'icu_los_hours',       # Length of stay
    'mechanical_ventilation',  # Binary
    'vasopressor_use',         # Binary
    
    # Derived (engineered)
    'sofa_score',          # Sequential Organ Failure Assessment
    'news_score',          # National Early Warning Score
}
```

**Label:** Sepsis diagnosis trong 6 giờ tiếp theo (ICD-10 A41.x, R65.x)

**Key challenge:** Sepsis definition thay đổi (Sepsis-2 vs Sepsis-3) → label noise.

---

### 2.2 30-Day Readmission Prediction

**Input features:**
```python
READMISSION_FEATURES = {
    # Clinical
    'primary_diagnosis_icd10',    # One-hot encoded groups
    'charlson_comorbidity_index', # Comorbidity burden
    'num_prior_admissions_12m',   # Prior utilization
    'length_of_stay_days',
    'discharge_disposition',      # Home, SNF, rehab...
    
    # Lab at discharge
    'sodium', 'potassium', 'bun', 'creatinine', 'albumin',
    
    # Medications
    'num_medications_discharge',  # Polypharmacy
    'high_risk_medication_flag',  # Warfarin, insulin, digoxin
    
    # Social determinants (if available)
    'insurance_type',             # Medicaid proxy for low income
    'zip_code_poverty_rate',      # Area deprivation index
    'lives_alone',
    'has_follow_up_appointment',  # Strong predictor!
}
```

**Label:** Inpatient readmission trong 30 ngày (same or any hospital)

---

### 2.3 Clinical Note NLP

**Task:** Extract structured data từ unstructured clinical notes.

```python
# Clinical NER pipeline (spaCy + medspaCy)
import spacy
import medspacy

nlp = spacy.load("en_core_web_sm")
nlp.add_pipe("medspacy_pyrush")      # Rule-based sentence splitting
nlp.add_pipe("medspacy_context")     # Negation, uncertainty, family history

text = "Patient denies chest pain. Mother had MI at age 60. Suspect PE, not yet confirmed."

doc = nlp(text)
for ent in doc.ents:
    print(f"Entity: {ent.text:20} | Label: {ent.label_:15} | "
          f"Negated: {ent._.is_negated} | Family: {ent._.is_family}")

# Output:
# Entity: chest pain          | Label: PROBLEM         | Negated: True  | Family: False
# Entity: MI                  | Label: PROBLEM         | Negated: False | Family: True
# Entity: PE                  | Label: PROBLEM         | Negated: False | Family: False
# (PE: is_uncertain=True)
```

**Pitfall:** "Patient denies chest pain" → entity là "chest pain" nhưng **negated** — KHÔNG phải patient có chest pain!

---

### 2.4 Radiology Image AI

**Use cases thực tế được FDA approve:**
- Chest X-ray: pneumonia, pneumothorax, pleural effusion (triage)
- Diabetic retinopathy screening (IDx-DR — first autonomous AI device approved)
- CT brain: intracranial hemorrhage detection
- Mammography: cancer detection assistance

**Architecture điển hình:**
```
DICOM input → Preprocessing → CNN (ResNet/EfficientNet/ViT) → Output
              - Windowing         - Transfer learning           - Probability
              - Normalization     - Fine-tune on clinical data  - Bounding box
              - De-identification                               - Heat map (Grad-CAM)
```

---

## 3. ML Pitfalls Đặc Thù Healthcare — Chi Tiết

### Pitfall 1: Random Split (Cực kỳ phổ biến)

```python
# ❌ SAI — Data leakage
from sklearn.model_selection import train_test_split
X_train, X_test = train_test_split(df, test_size=0.2, random_state=42)
# Future visits của cùng patient có thể vào train set → inflated AUC

# ✅ ĐÚNG — Temporal + Patient-level split
def temporal_patient_split(df, test_cutoff='2023-01-01', val_cutoff='2022-01-01'):
    # No patient appears in multiple splits
    train = df[df['index_date'] < val_cutoff]
    val   = df[(df['index_date'] >= val_cutoff) & (df['index_date'] < test_cutoff)]
    test  = df[df['index_date'] >= test_cutoff]
    
    # Verify no patient overlap
    assert len(set(train.patient_id) & set(test.patient_id)) == 0
    return train, val, test
```

---

### Pitfall 2: Label Leakage

```python
# ❌ SAI — Dùng feature chỉ có AFTER event xảy ra
features = df[['age', 'diagnosis_code', 'discharge_diagnosis_icd10']]
# discharge_diagnosis_icd10 chỉ có KHI discharge → không có trước khi predict

# ✅ ĐÚNG — Chỉ dùng data available tại "prediction time"
# Nếu predict readmission lúc discharge:
features_at_discharge = [
    'admission_diagnosis',        # OK: available at admission
    'length_of_stay',             # OK: available at discharge
    'labs_at_discharge',          # OK: available at discharge
    'medications_at_discharge',   # OK: available at discharge
]
# KHÔNG dùng: post-discharge appointments, follow-up labs
```

---

### Pitfall 3: Missing Data Handling Sai

```python
# ❌ SAI — Mean imputation
from sklearn.impute import SimpleImputer
imputer = SimpleImputer(strategy='mean')
# Vấn đề: lactate không được order VÌ patient ổn → missing != average lactate

# ✅ ĐÚNG — Missingness indicator
def handle_missing_clinical(df, lab_cols):
    for col in lab_cols:
        # Create missingness indicator (informative in healthcare)
        df[f'{col}_missing'] = df[col].isna().astype(int)
        # Impute với median hoặc sentinel (mô hình học được missingness là feature)
        df[col] = df[col].fillna(df[col].median())
    return df
```

---

### Pitfall 4: Hospital-Specific Patterns (Site Bias)

```python
# Model trained tại Hospital A có thể không hoạt động ở Hospital B

# Vì:
# - Hospital A có ICU level 1, Hospital B level 2 → khác nhau về case mix
# - Lab reference ranges khác nhau
# - Documentation patterns khác nhau (Epic vs Cerner users)
# - Demographics khác nhau

# ✅ ĐÚNG — Multi-site validation
sites = ['hospital_a', 'hospital_b', 'hospital_c']
for site in sites:
    site_data = test_df[test_df['site'] == site]
    metrics = evaluate_model(model, site_data)
    print(f"{site}: AUC={metrics['auc']:.3f}, PPV={metrics['ppv']:.3f}")

# Nếu performance variance > 10% → model không generalizable
```

---

### Pitfall 5: Disparate Performance (Model Bias)

```python
# ✅ PHẢI check performance theo subgroups
subgroups = ['race', 'gender', 'age_group', 'insurance_type', 'primary_language']

fairness_report = {}
for group_col in subgroups:
    for group_val in df[group_col].unique():
        subset = test_df[test_df[group_col] == group_val]
        if len(subset) < 100:  # cần đủ sample size
            continue
        fairness_report[f'{group_col}={group_val}'] = {
            'n': len(subset),
            'auc': roc_auc_score(subset['label'], subset['pred_prob']),
            'ppv': precision_score(subset['label'], subset['pred_class']),
        }

# Nếu AUC cho Black patients < White patients > 0.05 → model biased → KHÔNG deploy
```

---

## 4. Model Evaluation — Healthcare-Specific Metrics

| Metric | Công thức | Ý nghĩa clinical |
|--------|----------|----------------|
| **AUC-ROC** | Area under ROC curve | Overall discrimination ability (0.5 = random, 1.0 = perfect) |
| **Sensitivity (Recall)** | TP / (TP + FN) | % cases model phát hiện được — quan trọng cho life-threatening |
| **Specificity** | TN / (TN + FP) | % non-cases model correctly identify — giảm false alarms |
| **PPV (Precision)** | TP / (TP + FP) | Khi model alert, bao nhiêu % thực sự positive |
| **NPV** | TN / (TN + FN) | Khi model không alert, bao nhiêu % thực sự negative |
| **Brier Score** | Mean squared error của probabilities | Calibration — model có overcalibrated không |
| **NNT** | 1 / (PPV × base rate) | "Need to treat": cần alert bao nhiêu bác sĩ để có 1 true positive |

### Trade-off thực tế

```
Sepsis model example:
- High Sensitivity (0.90), Low Specificity (0.60)
  → Bắt được 90% sepsis cases
  → 40% non-sepsis patients bị alert → alert fatigue → ignore alerts

- Low Sensitivity (0.70), High Specificity (0.90)
  → Bỏ sót 30% sepsis → nguy hiểm
  → Ít false positives → alerts được tin tưởng

Clinical consensus: Sensitivity ≥ 0.85, Specificity ≥ 0.75 cho sepsis
```

---

## 5. Explainability — Bắt buộc trong Clinical Setting

Bác sĩ KHÔNG tin model nếu không biết tại sao. FDA cũng yêu cầu.

### SHAP Values — Standard trong Healthcare

```python
import shap

# Train model
model = XGBClassifier().fit(X_train, y_train)

# SHAP explainer
explainer = shap.TreeExplainer(model)
shap_values = explainer.shap_values(X_test)

# Global feature importance
shap.summary_plot(shap_values, X_test, feature_names=feature_names)

# Individual prediction explanation (cho bác sĩ xem)
patient_idx = 42
shap.force_plot(
    explainer.expected_value,
    shap_values[patient_idx],
    X_test.iloc[patient_idx],
    feature_names=feature_names
)
# Output: "Lactate=3.2 pushed risk UP by 23%, Heart rate=115 pushed risk UP by 15%"
```

### Alert Card với Explanation (CDS Hook format)

```json
{
  "summary": "🔴 HIGH SEPSIS RISK: 78% probability",
  "indicator": "critical",
  "source": {"label": "Sepsis Early Warning AI v2.1"},
  "detail": "**Top contributing factors:**\n- Lactate: 3.2 mmol/L (↑ HIGH) — +23% risk\n- Heart rate: 115 bpm (↑ HIGH) — +15% risk\n- Temp: 38.9°C (↑) — +8% risk\n- Low BP trend last 2h — +12% risk\n\n[View SOFA Score] | [Sepsis Protocol] | [Evidence]",
  "suggestions": [
    {"label": "Order Sepsis Bundle", "actions": [...]},
    {"label": "Consult Infectious Disease"}
  ]
}
```

---

## 6. Model Card — FDA và Good ML Practices

Mỗi clinical ML model phải có **Model Card** (documentation):

```markdown
## Model Card: Sepsis Early Warning Model v2.1

### Intended Use
- Purpose: Early detection of sepsis in adult inpatients
- Intended users: Physicians, RNs in ICU and general med-surg units
- NOT intended for: Pediatric patients, ED triage, outpatient settings

### Model Architecture
- Algorithm: XGBoost (tree ensemble)
- Features: 24 vital sign and lab features (list in Appendix A)
- Output: Probability 0-1; alert threshold = 0.65

### Training Data
- Source: 3 hospitals, 2015-2022
- N: 125,000 inpatient encounters
- Positive rate: 8.2% (Sepsis-3 definition)

### Performance (held-out test set 2023)
| Metric | Overall | ICU | Med-Surg |
|--------|---------|-----|----------|
| AUC | 0.87 | 0.89 | 0.84 |
| Sensitivity @ threshold | 0.86 | 0.88 | 0.83 |
| Specificity @ threshold | 0.76 | 0.78 | 0.73 |
| PPV | 0.31 | 0.45 | 0.22 |

### Fairness Analysis
| Subgroup | AUC | Note |
|----------|-----|------|
| White | 0.88 | |
| Black | 0.85 | Monitor closely |
| Hispanic | 0.87 | |
| Male | 0.87 | |
| Female | 0.87 | |

### Limitations
- Not validated on patients <18 years old
- Performance may degrade if documentation patterns change
- External validation needed before deployment at new sites

### Monitoring Plan
- Monthly: AUC, alert volume, override rate
- Quarterly: Subgroup performance
- Trigger for retraining: AUC drops > 0.03 from baseline

### Regulatory Status
- Classified as: Clinical Decision Support (advisory)
- FDA classification: Not SaMD (physician makes final decision)
- IRB approval: Protocol #2023-001

### Version History
- v1.0 (2021): Logistic regression baseline
- v2.0 (2022): XGBoost, added social determinants
- v2.1 (2023): Retrained with Sepsis-3 labels, improved fairness
```

---

## 7. ML Deployment Pipeline

```
Development          Validation           Production
────────────         ──────────           ──────────
Feature eng.    →   Clinical review   →  Shadow mode (no alerts)
Model training      IRB approval           ↓
Backtesting         IT security review  A/B test (50% users)
Model card          Downtime plan          ↓
                    Training for users  Full rollout
                                           ↓
                                        Continuous monitoring
                                        Model drift detection
                                        Monthly retraining
```

**Shadow mode:** Model chạy và generate predictions nhưng KHÔNG hiển thị alerts. Lưu predictions vào DB. So sánh với ground truth sau. Đây là bước an toàn nhất trước khi deploy.

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 4 lý do tại sao clinical ML khác ML thông thường
- [ ] Tôi biết 5 pitfalls và cách fix mỗi cái
- [ ] Tôi hiểu tại sao temporal split bắt buộc (không random split)
- [ ] Tôi biết SHAP và có thể implement để explain từng prediction
- [ ] Tôi biết Model Card là gì và tại sao cần thiết
- [ ] Tôi biết shadow mode là bước gì trong deployment pipeline

**Tiếp theo:** `07-governance-legal/01-luat-quy-dinh.md` →