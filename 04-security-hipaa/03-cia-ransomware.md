# Chương 16b — Ransomware, Pentest & Incident Response

> **Mục tiêu:** Biết cách phòng thủ ransomware, các attack vector đặc thù healthcare, và cách handle breach.

---

## 1. Healthcare là target số 1

> PHI có giá trị **~$250/record** trên dark web vs credit card **~$5/record**.
> Ransomware tấn công bệnh viện tăng **300%** từ 2020.

---

## 2. Healthcare-Specific Attack Vectors

| Attack Vector | Cơ chế | Ví dụ thực tế | Mitigation |
|-------------|--------|--------------|-----------|
| **Phishing** | Email giả mạo lừa credential | Universal Health Services 2020 — $67M loss | Security training; MFA; email filtering |
| **VPN/RDP Exploitation** | Exploit unpatched remote access | Common entry point post-COVID | Patch management; MFA for VPN; network segmentation |
| **Medical Device Compromise** | Exploit unpatched connected devices | Insulin pump exploit demo; pacemaker vulnerabilities | Network segmentation; device inventory; patching |
| **Insider Threat** | Employee access PHI của celebrities | UCLA Health employees accessed Kim Kardashian records | Least privilege; audit log monitoring; DLP |
| **Third-party Vendor** | Attack via vendor access | **Change Healthcare 2024** — largest healthcare breach ever | Vendor security assessment; network isolation; BAA |
| **API Exploitation** | Exploit FHIR/HL7 API endpoints | Unauthenticated patient data access | API authentication; rate limiting; OWASP API Top 10 |

---

## 3. Phòng chống Ransomware

| Biện pháp | Cách implement | Tại sao hiệu quả |
|---------|---------------|----------------|
| **Offline backups** | Backup không nối mạng (airgap) | Ransomware không encrypt được |
| **Network segmentation** | IoT medical devices trên VLAN riêng | Compromise một device không spread |
| **Patch management** | Ưu tiên medical devices cũ (Windows XP/7) | Điểm yếu lớn nhất hiện tại |
| **Email filtering** | Block macros, filter attachments | 91% ransomware vào qua phishing |
| **Incident response plan** | Cách ly ngay → thông báo ai → restore từ đâu | Giảm RTO khi bị tấn công |
| **Cyber insurance** | Bao gồm breach notification costs | HIPAA yêu cầu notify trong 60 ngày |

---

## 4. OWASP Top 10 cho Healthcare APIs

| Vulnerability | Mô tả | Fix |
|-------------|-------|-----|
| **Broken Object Level Authorization** | User A access data của User B bằng cách đổi patient ID | Validate authorization cho từng resource |
| **Broken Authentication** | Weak password; no MFA; session không expire | Implement OAuth2/SMART; MFA bắt buộc |
| **Excessive Data Exposure** | API trả về nhiều data hơn client cần | Return chỉ fields cần thiết; filter PHI |
| **Lack of Rate Limiting** | Không giới hạn request → bulk data harvesting | Rate limiting per user/IP; progressive backoff |
| **Function Level Authorization** | Regular user gọi được admin APIs | Explicit auth check cho mỗi endpoint |
| **Mass Assignment** | API accept extra fields → modify read-only | Whitelist allowed fields; ignore extra |
| **Injection** | SQL injection, FHIR query injection | Parameterized queries; input validation; WAF |

---

## 5. Downtime & Disaster Recovery

### Phân loại Downtime

| Loại | Nguyên nhân | Thời gian tối đa | Quy trình |
|------|------------|-----------------|----------|
| **Planned** | Maintenance, upgrade | Thông báo trước; giờ thấp điểm | Downtime procedures; backup staff |
| **Unplanned** | Hardware failure, software bug | RTO thường < 4h | Incident response; war room |
| **Disaster** | Ransomware, fire, flood | RPO thường < 24h data loss | DR site activation; full restore |

### RTO và RPO — Hai chỉ số quan trọng nhất

```
RTO (Recovery Time Objective)
= Thời gian tối đa hệ thống được phép down
= Ví dụ: RTO = 4h → phải restore trong 4 giờ

RPO (Recovery Point Objective)  
= Lượng data tối đa có thể mất
= Ví dụ: RPO = 1h → backup mỗi giờ

RTO và RPO càng nhỏ → infrastructure càng đắt (hot standby, real-time replication)
Healthcare thường yêu cầu: RTO < 4h, RPO < 1h cho hệ thống EHR critical
```

### High Availability Architecture

| Kiến trúc | Failover time | Mô tả |
|---------|-------------|-------|
| **Active-Passive Failover** | Minutes | 1 server chính + 1 dự phòng |
| **Active-Active Clustering** | Seconds | Nhiều servers cùng xử lý |
| **Database Replication** | Near-zero (sync) / Fast (async) | Synchronous: zero data loss; Asynchronous: nhỏ hơn |
| **Geographic Redundancy** | DR site khác địa điểm | Chống thiên tai/mất điện diện rộng |

**Backup Strategy — 3-2-1 Rule:**
- **3** copies của data
- **2** media types khác nhau
- **1** offsite location
- → Test restore **quarterly**!

### Downtime Procedures — Khi EHR không hoạt động

> Khi EHR down, bác sĩ không tra được dị ứng, không ra được lệnh thuốc — **có thể gây tai nạn y tế**.

Dev phải chuẩn bị **downtime kit** từ trước:

| Item | Mô tả |
|------|-------|
| **Downtime Reports** | In trước danh sách bệnh nhân, thuốc, dị ứng |
| **Paper-based Orders** | Form giấy backup → nhập lại sau khi hệ thống lên |
| **Read-only Access** | "Downtime viewer" — không ghi nhưng đọc được data cũ |
| **Critical Lab Alerts** | Kênh thông báo thủ công cho kết quả nguy hiểm |
| **Reconciliation** | Sau khi lên lại → nhập hết orders/notes đã làm trên giấy |

---

## 6. HIPAA Breach Response Timeline

| Thời điểm | Hành động bắt buộc |
|-----------|-------------------|
| **Ngay khi phát hiện** | Contain breach; preserve evidence; notify CISO và legal |
| **24–72 giờ** | Initial assessment; activate IR team; document everything |
| **1–2 tuần** | Xác định scope: bao nhiêu records, loại PHI nào, ai bị ảnh hưởng |
| **30 ngày** | Internal investigation hoàn thành; remediation bắt đầu |
| **60 ngày** | Notify affected individuals (letter/email) |
| **Ngay nếu ≥500 người** | Notify prominent media + notify HHS ngay |
| **Hàng năm** | Small breaches (<500): gửi HHS trước 1/3 năm sau |

---

## 7. Incident Response Playbook cho Developer

Khi phát hiện potential breach — **Developer phải làm gì:**

```
1. STOP và DOCUMENT
   → Ghi lại mọi thứ: timestamps, IPs, user accounts, files accessed
   → ĐỪNG xóa logs

2. CONTAIN ngay
   → Disable compromised accounts
   → Isolate affected systems  
   → Revoke suspicious API tokens

3. ESCALATE ngay
   → Notify CISO, security team, legal
   → KHÔNG tự xử lý một mình

4. PRESERVE evidence
   → Snapshot VMs/containers
   → Copy logs to secure storage
   → Chain of custody

5. KHÔNG thông báo ngoài
   → Chưa notify users hay public khi chưa có sự đồng ý của legal

6. COOPERATE với investigation
   → Cung cấp access logs, code, architecture diagrams

7. POST-MORTEM
   → Viết đầy đủ với root cause và remediation
```

---

## ✅ Checklist sau chương này

- [ ] Tôi biết healthcare là target số 1 và tại sao PHI có giá trị cao
- [ ] Tôi biết 6 attack vectors đặc thù healthcare
- [ ] Tôi hiểu RTO và RPO và giá trị thường gặp trong healthcare
- [ ] Tôi biết 3-2-1 backup rule
- [ ] Tôi biết khi phát hiện breach cần làm gì (7 bước)
- [ ] Tôi biết HIPAA yêu cầu notify affected individuals trong 60 ngày

**Tiếp theo:** `05-integration-api/01-fhir-api-cheatsheet.md` →