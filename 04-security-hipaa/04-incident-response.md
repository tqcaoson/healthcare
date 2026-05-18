# Chương 16c — Incident Response & Breach Notification

> **Mục tiêu:** Biết chính xác phải làm gì khi có security incident — từ phút đầu đến báo cáo cuối cùng.

---

## 1. Phân loại Security Incidents trong Healthcare

| Loại | Mô tả | Ví dụ |
|------|-------|-------|
| **PHI Breach** | PHI accessed / disclosed không authorized | Hacker lấy được patient records |
| **Ransomware** | Hệ thống bị encrypt, yêu cầu tiền chuộc | WannaCry tấn công NHS UK 2017 |
| **Insider Threat** | Nhân viên access PHI không có lý do | Điều dưỡng xem hồ sơ người nổi tiếng |
| **Lost/Stolen Device** | Laptop/USB chứa PHI thất lạc | Laptop không encrypt của bác sĩ bị mất |
| **Misdirected Communication** | PHI gửi nhầm địa chỉ | Email discharge summary đến sai patient |
| **Business Associate Breach** | Vendor của bệnh viện bị tấn công | **Change Healthcare 2024** — 100M+ records |
| **System Misconfiguration** | PHI exposed do cài đặt sai | S3 bucket public accidentally |

---

## 2. HIPAA Breach Definition — Khi nào phải notify?

**Breach** = unsecured PHI được accessed, acquired, used, hoặc disclosed vi phạm Privacy Rule.

### Presumption of Breach

> **Mặc định là breach** — trừ khi tổ chức **chứng minh được** nguy cơ ảnh hưởng đến bệnh nhân là low.

### 4-Factor Risk Assessment (để xác định có phải notify không)

| Factor | Câu hỏi |
|--------|---------|
| **1. PHI nature & extent** | Loại PHI gì? (SSN + clinical = nguy hiểm hơn chỉ tên) Bao nhiêu records? |
| **2. Who accessed** | Unauthorized person là ai? Có khả năng exploit PHI không? |
| **3. PHI actually acquired?** | Chỉ viewed hay thực sự copied/downloaded? Có logs chứng minh không? |
| **4. Mitigation** | Đã làm gì để giảm risk? (Thu hồi device, change credentials...) |

Nếu cả 4 factors đều low risk → **có thể không phải breach** → document analysis cẩn thận.  
Nếu có bất kỳ factor nào không thể xác định → **phải assume là breach**.

### Exceptions — Không phải Breach

| Exception | Điều kiện |
|-----------|----------|
| **Unintentional access by workforce** | Employee accidental, trong scope of authority, không retained/disclosed |
| **Inadvertent disclosure within entity** | Giữa hai authorized persons trong cùng tổ chức |
| **Good faith belief unauthorized person couldn't retain** | PHI chỉ được nhìn thoáng qua, không có cách retain |

---

## 3. HIPAA Notification Requirements

### Timeline bắt buộc

```
DAY 0: Incident discovered
   ↓
DAY 1–5: Contain + Preserve evidence
   ↓
DAY 30: Internal investigation hoàn thành
         → Xác định scope: bao nhiêu records, PHI gì, ai bị ảnh hưởng
   ↓
DAY 60: NOTIFY AFFECTED INDIVIDUALS (bắt buộc)
         → Letter hoặc email nếu có địa chỉ hợp lệ
         → Substitute notice (media/website) nếu không có contact info
   ↓
NGAY nếu ≥ 500 người trong 1 BANG:
         → Notify prominent media in that state
         → Notify HHS Secretary NGAY (không đợi cuối năm)
   ↓
MARCH 1 hàng năm:
         → Small breaches (< 500 người): submit HHS annual log
```

### Nội dung bắt buộc trong Breach Notification Letter

```
1. Mô tả breach (what happened, when, how discovered)
2. Loại PHI bị ảnh hưởng (tên, SSN, lab results...)
3. Bước tổ chức đã làm để investigate và mitigate
4. Bước tổ chức sẽ làm để phòng tránh tương lai
5. Contact information để individuals hỏi thêm (hotline)
6. Khuyến nghị cho individuals (credit monitoring, fraud alerts)
```

---

## 4. Incident Response Plan — 6 Phases (NIST SP 800-61)

### Phase 1: Preparation (trước incident)

| Việc cần làm | Mô tả |
|-------------|-------|
| IR Team | Xác định roles: IR Lead, Forensics, Legal, Communications, Clinical |
| Contact list | 24/7 contacts cho CISO, legal, FBI (cybercrime), cyber insurance, PR |
| Playbooks | Documented procedures cho mỗi loại incident |
| Tools ready | Forensics laptop, write blockers, network capture tools |
| Tabletop exercises | Drill ít nhất 2 lần/năm |
| Communication templates | Pre-drafted HHS notification, patient letter, press release |

### Phase 2: Detection & Analysis

```
Signals cần investigate ngay:
□ IDS/IPS alert cho unusual traffic (đặc biệt sau giờ)
□ Bulk PHI download (>100 records trong 1 session)
□ Login từ impossible locations (VN lúc 2am, NY lúc 2am+5min)
□ Privileged account activity ngoài giờ làm
□ Antivirus alert trên clinical workstations
□ SIEM correlation rule fires
□ Staff report "system acting weird"
□ Ransomware note xuất hiện trên màn hình

Analysis questions:
- Timeline: first evidence of compromise?
- Scope: bao nhiêu systems, bao nhiêu records?
- Vector: làm thế nào attacker vào?
- PHI impact: PHI nào bị accessed/exfiltrated?
```

### Phase 3: Containment

**Short-term containment (ngay lập tức):**

```
□ Isolate affected systems (disconnect network, KHÔNG shutdown)
   → Shutdown destroy forensic evidence (RAM)
   → Network isolation preserves evidence while stopping spread

□ Block attacker's access:
   → Disable compromised accounts
   → Revoke API tokens / sessions
   → Block malicious IPs at firewall

□ Notify clinical staff:
   → Activate downtime procedures
   → Switch to paper-based workflow nếu cần
   → Alert pharmacy, lab, radiology về potential downtime
```

**Long-term containment:**
```
□ Deploy network monitoring tăng cường
□ Enable enhanced logging
□ Out-of-band communication (attacker có thể monitor email)
□ Backup systems to clean environment
```

### Phase 4: Eradication

```
□ Remove malware / unauthorized access
□ Patch exploited vulnerabilities
□ Reset ALL potentially compromised credentials
   (không chỉ confirmed compromised)
□ Review all privileged accounts
□ Rebuild systems từ known-good baseline (không patch)
□ Verify integrity của production data
□ Test systems trước khi restore
```

### Phase 5: Recovery

```
□ Restore từ clean backups (verify backup integrity trước)
□ Phased restoration — critical systems trước:
   Priority 1: EHR, CPOE, Pharmacy (patient safety)
   Priority 2: LIS, RIS, PACS
   Priority 3: Billing, Analytics

□ Enhanced monitoring ít nhất 30 ngày sau recovery
□ Verify normal operations với clinical staff
□ Document lessons learned

Validation checklist trước khi go-live lại:
□ No malware detected
□ All credentials reset
□ Patches applied
□ Logging enhanced
□ Clinical staff trained on new procedures
```

### Phase 6: Post-Incident Activity

```
□ Post-Incident Review (PIR) trong 2 tuần
   → Root cause analysis
   → Timeline reconstruction
   → What went well / what didn't
   → Recommendations (không blame individuals)

□ Update IR Plan dựa trên lessons learned
□ Update security controls
□ Brief leadership và board
□ File regulatory notifications (HHS, state AG, FBI nếu cần)
□ Coordinate với cyber insurance
```

---

## 5. Ransomware Response — Healthcare Specific

Healthcare là target số 1 vì **downtime = patient harm** → tổ chức sẵn sàng trả tiền chuộc.

### Quyết định có trả tiền chuộc không?

| Yếu tố | Cân nhắc |
|--------|---------|
| Backups có sạch không? | Nếu backups sạch → không nên trả |
| Patient safety ngay lập tức? | ICU patients không access được data → trả để cứu người? |
| Attacker có tin cậy không? | 40% cases trả tiền nhưng không nhận được key |
| Insurance coverage? | Cyber insurance có cover không? |
| Law enforcement guidance? | FBI khuyến cáo không trả nhưng không cấm |
| OFAC concerns? | Nếu attacker là sanctioned entity → illegal to pay |

**Quyết định phải có: CEO, CFO, CISO, Legal, Clinical leadership.**

### Ransom Communication — Dev không làm việc này

Mọi communication với attacker phải qua: Legal + CISO + Cyber Insurance + FBI.

---

## 6. Developer Checklist khi phát hiện Potential Breach

```
Bạn (developer) phát hiện anomaly — làm ngay:

STEP 1: STOP làm việc bình thường
   → Đừng cố "fix" nó một mình
   → Đừng alert attacker bằng cách thay đổi hành vi đột ngột

STEP 2: DOCUMENT ngay lập tức
   → Screenshot timestamps, error messages, suspicious activity
   → Ghi xuống mọi thứ: "Tôi thấy gì, lúc nào, trên hệ thống nào"
   → KHÔNG xóa bất cứ thứ gì

STEP 3: ESCALATE ngay
   → CISO / Security team (bất kể giờ nào)
   → Manager
   → ĐỪNG tự notify patients hoặc press

STEP 4: PRESERVE evidence
   → Giữ session open nếu có thể (không logout, không restart)
   → Request snapshot/backup của affected systems
   → Export relevant logs ngay (logs có thể bị overwrite)

STEP 5: COOPERATE với investigation
   → Cung cấp access logs, code, architecture
   → Không discuss qua Slack/email công ty (attacker có thể đọc)
   → Dùng out-of-band communication (phone, Signal)

STEP 6: AVOID common mistakes
   → Đừng inform colleagues trên email (nguy cơ leak)
   → Đừng tự mình remediate (phá vỡ forensics)
   → Đừng post lên social media
   → Đừng discuss với bệnh nhân hoặc media
```

---

## 7. Key Metrics — Post-Breach Accountability

| Metric | Mô tả | Target |
|--------|-------|--------|
| **MTTD** | Mean Time to Detect | < 24 giờ (industry average: 207 ngày — quá tệ) |
| **MTTR** | Mean Time to Respond / Contain | < 4 giờ sau detect |
| **MTTRC** | Mean Time to Recover | < RTO (thường < 4h cho EHR) |
| **Records Exposed** | Số PHI records bị ảnh hưởng | Track để report HHS |
| **Cost of Breach** | Total cost (remediation + notification + fines + lost business) | Healthcare average: $10.9M per breach (2023) |

---

## ✅ Checklist sau chương này

- [ ] Tôi hiểu HIPAA Breach định nghĩa và 4-factor risk assessment
- [ ] Tôi biết timeline bắt buộc: 60 ngày notify individuals, ngay nếu ≥500 người
- [ ] Tôi nhớ 6 phases của IR Plan (NIST SP 800-61)
- [ ] Tôi biết KHÔNG shutdown hệ thống bị ransomware (mất forensic evidence)
- [ ] Tôi biết 6 bước developer phải làm khi phát hiện potential breach
- [ ] Tôi biết healthcare average breach cost = $10.9M

**Tiếp theo:** `05-integration-api/01-fhir-api-cheatsheet.md` →