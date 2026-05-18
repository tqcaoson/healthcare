# Chương 5b — Alert Fatigue: Thiết Kế CDS Alerts Đúng Cách

> **Mục tiêu:** Hiểu tại sao alert fatigue nguy hiểm và cách build CDS alerts mà bác sĩ không ignore.

---

## 1. Con số đáng sợ

> Bác sĩ bỏ qua **49–96% alerts** từ CDSS.
> Một nghiên cứu tại UCSF: bác sĩ nhận trung bình **63 alerts/ngày**.
> Kết quả: alerts bị "click through" mà không đọc — kể cả alerts quan trọng.

**Alert fatigue = khi quá nhiều alerts → người dùng ignore tất cả → kể cả alerts thực sự quan trọng.**

---

## 2. Nguyên nhân gốc rễ

| Nguyên nhân | Mô tả | Ví dụ |
|-------------|-------|-------|
| **Quá nhiều alerts** | Volume cao → cognitive overload | 63 alerts/ngày × 5 ngày = 315 alerts/tuần |
| **Low specificity** | Alert fire cho cả cases không relevant | Drug interaction nhẹ fire cho mọi patient, kể cả khi không dùng drug đó |
| **Không actionable** | Không có hành động cụ thể để làm | "Patient có nguy cơ cao" — làm gì bây giờ? |
| **Wrong timing** | Hiển thị sai lúc trong workflow | Alert về dị ứng hiện ra khi đang billing |
| **Wrong audience** | Gửi đến người không relevant | Alert cho dược sĩ về thông tin chỉ relevant với bác sĩ |
| **Không patient-specific** | Generic alert không tính context của patient | Drug dose alert không tính function thận của patient |

---

## 3. Five Rights of CDS — Framework thiết kế

| Right | Câu hỏi cần trả lời | Ví dụ đúng vs sai |
|-------|--------------------|--------------------|
| **Right Information** | Thông tin này có accurate và relevant với patient cụ thể này không? | ✅ "Creatinine của BN này là 2.1 → giảm dose metformin" vs ❌ "Metformin có thể gây lactic acidosis" |
| **Right Person** | Ai thực sự cần nhận alert này? | ✅ Alert dose → dược sĩ | ✅ Critical lab → ordering provider | ❌ Alert tất cả cho tất cả |
| **Right Format** | Alert popup, infobutton, hay dashboard widget? | ✅ Critical: modal | ✅ Info: inline card | ❌ Critical dùng passive notification |
| **Right Channel** | EHR, mobile app, pager, email, SMS? | ✅ Critical sepsis alert → pager/SMS ngay | ❌ Sepsis alert chỉ trong EHR khi bác sĩ đang offline |
| **Right Time** | Đúng lúc trong workflow — khi nào thì hữu ích? | ✅ Drug interaction → khi ký lệnh | ❌ Drug interaction → 2 giờ sau khi đã dùng thuốc |

---

## 4. Alert Severity Levels — Thiết kế phân cấp

```
🔴 CRITICAL (Hard stop)
   ├── Modal popup — không thể dismiss không có lý do
   ├── Yêu cầu nhập lý do override (free text hoặc dropdown)
   ├── Ghi lại override vào audit log
   ├── Chỉ dùng cho: life-threatening drug interactions, critical allergy, dangerous dose
   └── Target override rate: < 20%

🟡 WARNING (Interruptive)
   ├── Inline alert box, visible nhưng không block workflow
   ├── Có thể acknowledge và tiếp tục
   ├── Dùng cho: significant drug interactions, dosing concerns, guideline deviation
   └── Target override rate: 30–60%

🔵 INFO (Passive)
   ├── Badge, icon, sidebar notification
   ├── Không interrupt — user đọc khi có thời gian
   ├── Dùng cho: care gap reminders, preventive care suggestions
   └── Target override rate: N/A (không track)
```

---

## 5. Measuring Alert Fatigue

### KPIs cần track

| Metric | Định nghĩa | Target |
|--------|-----------|--------|
| **Alert Override Rate** | % alerts bị override | Critical < 20%, Warning < 60% |
| **Override Reason Distribution** | Lý do override phổ biến nhất | Insight về alerts cần tune |
| **Alert Volume per Provider per Day** | Số alerts trung bình | < 10–15 actionable alerts/day |
| **Time-to-Override** | Thời gian từ alert đến dismiss | Quá nhanh (< 3s) → không đọc |
| **Post-Override Adverse Events** | Adverse events sau khi override critical alert | Metric quan trọng nhất về safety |

### Query để tìm alerts cần tune

```sql
-- Tìm alerts có override rate cao (cần xem xét lại)
SELECT 
    alert_type,
    alert_name,
    COUNT(*) AS total_fired,
    SUM(CASE WHEN action = 'override' THEN 1 ELSE 0 END) AS total_overrides,
    ROUND(100.0 * SUM(CASE WHEN action = 'override' THEN 1 ELSE 0 END) / COUNT(*), 1) AS override_pct,
    AVG(time_to_action_seconds) AS avg_response_seconds
FROM cds_alert_log
WHERE fired_at >= CURRENT_DATE - 90
GROUP BY alert_type, alert_name
HAVING COUNT(*) >= 100  -- đủ volume để có ý nghĩa thống kê
ORDER BY override_pct DESC;

-- Kết quả > 90% override → candidate để remove hoặc downgrade severity
-- Kết quả avg_response < 3 seconds → alert không được đọc
```

---

## 6. Best Practices Giảm Alert Fatigue

| Practice | Cách implement |
|---------|----------------|
| **Prioritize ruthlessly** | Chỉ giữ alerts có evidence-based clinical impact. Nếu không có RCT hoặc guideline → don't alert |
| **Require clinical justification để thêm alert** | Mọi alert mới phải có: clinical rationale + estimated specificity + override threshold |
| **Inactive alert retirement** | Review alerts định kỳ (6 tháng). Override rate > 90% → tự động đề xuất retire |
| **Personalization** | Cho phép provider tune alert thresholds dựa trên specialty/practice |
| **Context-aware firing** | Alert về preventive care chỉ fire khi patient đến checkup, không phải ER visit |
| **Deduplication** | Nếu alert đã fired lần trước và bị override với lý do hợp lệ → không fire lại cùng session |
| **Link to evidence** | Mỗi alert có link đến guideline/evidence → bác sĩ hiểu tại sao, không chỉ click |
| **Measure và iterate** | Monthly alert performance review với clinical committee |

---

## 7. Override Reason Taxonomy — Thiết kế dropdown

Khi bác sĩ override một critical alert, cần capture lý do để phân tích. Ví dụ:

```
Lý do override Drug-Drug Interaction alert:
○ "Patient đang được monitor chặt chẽ"
○ "Benefit outweighs risk trong trường hợp này"
○ "Patient đã dùng combination này và dung nạp tốt"
○ "Alert không áp dụng — patient đã ngừng thuốc kia"
○ "Consulting với pharmacist / specialist"
○ "Alert sai / không accurate"    ← Quan trọng: capture bugs
○ Khác (free text)
```

---

## 8. Alert Fatigue và Patient Safety Events

Case study thực tế:
- **2004, Brigham & Women's Hospital:** CPOE với aggressive alerts → 49.1% override rate với drug interaction alerts
- **2005–2009:** Systematic alert reduction → override rate giảm xuống còn 35.8% → **adverse drug events giảm 55%**
- **Lesson:** Ít alerts hơn nhưng chuẩn hơn = **safer** hơn nhiều alerts

---

## ✅ Checklist sau chương này

- [ ] Tôi hiểu alert fatigue là vấn đề patient safety, không chỉ UX
- [ ] Tôi biết Five Rights of CDS và áp dụng khi thiết kế alert mới
- [ ] Tôi biết 3 severity levels và khi nào dùng mỗi loại
- [ ] Tôi biết cách query alert override rate và interpret kết quả
- [ ] Tôi biết > 90% override rate = alert cần retire hoặc downgrade

**Tiếp theo:** `02-clinical-apps/03-telehealth.md` →