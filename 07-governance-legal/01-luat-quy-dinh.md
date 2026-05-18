## Luật & Quy Định: HIPAA, HITECH, 21st Century Cures, FDA SaMD

> **Mục tiêu:** Biết đủ để không vi phạm luật và không bị legal team veto feature của mình.

---

### 1. Timeline Luật Quan Trọng

```
1996  HIPAA Privacy & Security Rule
        └── Bảo vệ PHI; BAA; 18 identifiers
        
2009  HITECH Act (Health IT for Economic & Clinical Health)
        └── Mở rộng HIPAA: tăng penalty; Meaningful Use
        └── Business Associates chịu trách nhiệm trực tiếp
        
2010  ACA (Affordable Care Act / Obamacare)
        └── Mở rộng bảo hiểm; value-based care; ACOs
        
2015  MACRA (Medicare Access & CHIP Reauthorization)
        └── Thay Meaningful Use bằng MIPS/APM; quality reporting
        
2016  21st Century Cures Act
        └── Interoperability bắt buộc; chống Information Blocking
        └── ONC Interoperability Rule (2020) — FHIR API bắt buộc
        
2022  TEFCA
        └── National HIE framework; QHINs
```

---

### 2. HIPAA — Luật nền tảng

| Rule | Nội dung |
|------|---------|
| **Privacy Rule** | Quyền bệnh nhân với PHI; giới hạn disclosure; consent |
| **Security Rule** | Technical/administrative/physical safeguards cho ePHI |
| **Breach Notification** | Notify HHS + individuals trong 60 ngày |
| **Enforcement Rule** | Penalties: $100–$50,000/violation; tối đa $1.9M/năm |

### Penalty Tiers

| Tier | Nguyên nhân | Min | Max |
|------|------------|-----|-----|
| 1 | Unknown (không biết có vi phạm) | $100 | $50,000 |
| 2 | Reasonable cause (biết nhưng không negligent) | $1,000 | $50,000 |
| 3 | Willful neglect (negligent + corrected) | $10,000 | $50,000 |
| 4 | Willful neglect (NOT corrected) | $50,000 | $50,000 |

---

### 3. HITECH Act (2009) — Điều gì thay đổi

| Thay đổi | Impact lên Dev |
|---------|--------------|
| BA chịu trách nhiệm trực tiếp | Vendor phần mềm phải tuân thủ HIPAA Security Rule |
| Tăng penalty lên $1.9M/năm | Audit và security nghiêm túc hơn |
| Breach Notification bắt buộc | Build incident detection và notification workflow |
| Meaningful Use (→ nay là Promoting Interoperability) | EHR phải certified và đáp ứng quality measures |

---

### 4. 21st Century Cures Act — Quan trọng nhất với Dev hiện nay

**Mục tiêu:** Ngăn Information Blocking và bắt buộc interoperability qua FHIR.

#### ONC Interoperability Rule (2020) — bắt buộc

| Yêu cầu | Deadline | Ai phải comply |
|---------|---------|---------------|
| FHIR R4 Patient Access API | 2021 | EHR vendors (CEHRT) |
| FHIR R4 Provider Directory API | 2021 | Health plans |
| SMART on FHIR app launch | 2022 | CEHRT |
| US Core profiles | Ongoing | CEHRT |

#### Information Blocking — Cấm

| Là Information Blocking | Không phải |
|------------------------|-----------|
| Charge phí unreasonable để export data | Từ chối vì privacy risk thực sự |
| Delay cung cấp data không có lý do | Downtime bất khả kháng |
| Design API để discourage third-party apps | Maintenance windows hợp lý |
| Vendor lock-in data | Security review vendor apps |

**Penalty Information Blocking:** Lên đến **$1 triệu/violation** cho IT vendors.

---

### 5. FDA Regulation cho Software

#### SaMD — Software as a Medical Device

FDA phân loại software theo **risk level**:

| Class | Risk | FDA Pathway | Ví dụ |
|-------|------|------------|-------|
| **Class I** | Low | Exempt hoặc 510(k) | General wellness app |
| **Class II** | Moderate | 510(k) (substantial equivalence) | AI đọc ECG hỗ trợ |
| **Class III** | High | PMA (Premarket Approval) | AI tự động chẩn đoán, no human override |

#### Không phải SaMD (không cần FDA)

- Administrative software (billing, scheduling)
- General wellness (BMI calculator, step counter)
- EHR software (clinical support, không phải diagnose)
- CDS với human oversight (bác sĩ vẫn quyết định cuối cùng)

#### AI/ML-based SaMD — Special Considerations

FDA 2021 Action Plan:
- **Pre-determined Change Control Plan**: Phải declare trước cách model update
- **Algorithm Change Protocol**: FDA approve process, không phải từng update
- **Performance monitoring**: Real-world performance phải track và report
- **Transparency**: Phải disclose training data, intended use, limitations

---

### 6. Value-Based Care — Ảnh hưởng lên IT

Chuyển từ **fee-for-service** (trả cho mỗi dịch vụ) → **value-based** (trả theo kết quả).

| Model | Mô tả | IT cần gì |
|-------|-------|----------|
| **ACO** | Accountable Care Organization — share savings | Population analytics; care gap identification |
| **Bundled Payment** | Một giá cho toàn bộ episode of care | Cost tracking across providers |
| **PCMH** | Patient-Centered Medical Home | Care coordination tools; chronic disease management |
| **Pay for Performance** | Thưởng nếu đạt quality metrics | Automated eCQM reporting; dashboards |

---
