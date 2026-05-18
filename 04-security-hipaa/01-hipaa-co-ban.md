# Chương 16a — HIPAA Cơ Bản: PHI, ePHI & Technical Safeguards

> **Mục tiêu:** Hiểu HIPAA đủ để code compliant ngay từ đầu, không phải vá sau khi bị phạt.

---

## 1. Tại sao HIPAA quan trọng với dev?

> Vi phạm HIPAA có thể bị phạt lên đến **$1.9 triệu USD/năm** và hình sự tối đa **10 năm tù**.

HIPAA (Health Insurance Portability and Accountability Act, 1996) là luật liên bang Mỹ quy định bảo vệ thông tin y tế cá nhân.

---

## 2. Các khái niệm cốt lõi

| Thuật ngữ | Định nghĩa | Ý nghĩa với Dev |
|-----------|-----------|----------------|
| **PHI** | Protected Health Information — thông tin y tế có thể nhận dạng cá nhân | Bất kỳ data nào chứa PHI phải encrypt, access control, audit |
| **ePHI** | Electronic PHI — PHI ở dạng điện tử | Focus chính của HIPAA Security Rule — áp dụng trực tiếp cho software |
| **Covered Entity** | Bệnh viện, bác sĩ, bảo hiểm — tổ chức phải tuân thủ HIPAA | Client/employer của dev phần mềm y tế |
| **Business Associate (BA)** | Vendor có access đến PHI | **Dev/vendor phần mềm y tế PHẢI ký BAA và tuân thủ HIPAA** |
| **BAA** | Business Associate Agreement — hợp đồng buộc BA tuân thủ HIPAA | Bắt buộc ký trước khi access PHI — cloud provider phải có |

---

## 3. 18 PHI Identifiers — Phải de-identify tất cả

Đây là 18 loại thông tin được coi là PHI:

| # | Identifier | Ví dụ |
|---|-----------|-------|
| 1 | **Names** | Họ tên, tên viết tắt |
| 2 | **Geographic data** (nhỏ hơn bang) | Địa chỉ, zipcode, county |
| 3 | **Dates** (nhỏ hơn năm) | Ngày sinh, ngày nhập viện, ngày mất |
| 4 | **Ages > 89** | "Bệnh nhân 92 tuổi" |
| 5 | **Phone numbers** | SĐT, fax |
| 6 | **Fax numbers** | |
| 7 | **Email addresses** | |
| 8 | **Social Security numbers** | SSN |
| 9 | **Medical record numbers** | MRN |
| 10 | **Health plan beneficiary numbers** | |
| 11 | **Account numbers** | |
| 12 | **Certificate/license numbers** | |
| 13 | **Vehicle identifiers** | Biển số xe |
| 14 | **Device identifiers** | Serial number thiết bị y tế |
| 15 | **Web URLs** | URL cá nhân |
| 16 | **IP addresses** | |
| 17 | **Biometric identifiers** | Vân tay, võng mạc |
| 18 | **Full-face photos** | Ảnh chụp mặt |

---

## 4. HIPAA Security Rule — Technical Safeguards (Bắt buộc implement)

### 4.1 Access Control

| Yêu cầu | Implementation |
|---------|---------------|
| **Unique User ID** | Mỗi user phải có ID riêng — không share accounts | 
| **Emergency Access** | "Break the glass" — override với enhanced audit |
| **Automatic Logoff** | Tự logout sau X phút inactive |
| **Authentication** | Password + MFA bắt buộc cho ePHI access |
| **RBAC** | Chỉ access data cần cho công việc — least privilege |

### 4.2 Encryption

| Loại | Standard |
|------|---------|
| **At Rest** | AES-256 — encrypt database (TDE), file storage, backup, mobile |
| **In Transit** | TLS 1.2 minimum, TLS 1.3 preferred. **Disable** SSLv3, TLS 1.0, TLS 1.1 |
| **Key Management** | HSM cho production; key rotation ít nhất hàng năm |
| **Email với PHI** | Encrypted email (S/MIME) hoặc secure portal — **không gửi PHI qua plain email** |
| **Mobile** | MDM enforce encryption + remote wipe |

### 4.3 Audit Controls

HIPAA yêu cầu log **mọi access** vào ePHI:

| Event cần log | Thông tin cần capture | Retention |
|-------------|----------------------|-----------|
| User login/logout | User ID, timestamp, IP, success/fail, MFA status | 6+ năm |
| ePHI access (view) | User, patient ID, record accessed, timestamp | 6+ năm |
| ePHI modification | User, patient ID, field changed, old value, new value | 6+ năm + version history |
| Failed access | User, timestamp, attempted resource, reason | 6+ năm |
| Admin actions | Config changes, user creation/deletion | 6+ năm |
| Data export/print | User, dataset, timestamp, destination | 6+ năm |

**Audit log implementation rules:**
- **Write-once (WORM)**: Logs không được delete hay modify — append-only
- **Separate storage**: Audit logs phải ở separate system — compromise app không xóa được logs
- **Structured format**: JSON structured logs với correlation IDs
- **Centralized SIEM**: Forward logs để real-time monitoring
- **Automated alerts**: Alert khi access PHI ngoài giờ, bulk downloads, access VIP patients
- **Log integrity**: Hash-chain để detect tampering

### 4.4 Integrity Controls

Phát hiện và ngăn chặn ePHI bị modify không authorized:
- Digital signatures
- Checksums
- Input validation

### 4.5 Transmission Security

TLS cho **mọi** data transmission — không HTTP thuần.

---

## 5. CIA Triad trong Healthcare Security

| Thuộc tính | Định nghĩa | Mối đe dọa | Biện pháp |
|-----------|-----------|-----------|----------|
| **Confidentiality** | Chỉ người được phép mới đọc được | Unauthorized access, data breach | Encryption, RBAC, MFA, VPN |
| **Integrity** | Data không bị sửa đổi trái phép | Malware, insider threat, DB corruption | Digital signatures, checksums, audit logs |
| **Availability** | Hệ thống luôn hoạt động khi cần | DDoS, ransomware, hardware failure | HA architecture, backups, DR plan |

---

## 6. De-identification — Dùng data cho nghiên cứu

HIPAA cho phép dùng PHI cho nghiên cứu nếu de-identify đúng cách.

### Hai phương pháp HIPAA approve

| Phương pháp | Mô tả | Khi dùng |
|-----------|-------|---------|
| **Safe Harbor** | Remove tất cả 18 PHI identifiers | Đơn giản nhưng có thể lose valuable data |
| **Expert Determination** | Chuyên gia thống kê chứng minh re-identification probability < 0.04 | Giữ được nhiều data hơn |

### Kỹ thuật hiện đại

| Kỹ thuật | Mô tả |
|---------|-------|
| **Date Shifting** | Shift dates randomly nhưng consistent per patient (giữ age relationships) |
| **Synthetic Data** | GAN hoặc statistical models tạo data giả có cùng statistical properties |
| **Federated Learning** | Train ML tại chỗ, chỉ share model weights — không centralize PHI |

### De-identification checklist thực tế

| HIPAA Identifier | Xử lý (Safe Harbor) | Lưu ý |
|----------------|---------------------|-------|
| Names | Remove hoàn toàn | Tên xuất hiện trong free text → NLP để detect |
| Geographic < state | Remove zipcode (keep state) — nếu pop < 20,000 → generalize | 3 số đầu zipcode OK nếu dân số > 20,000 |
| Dates (except year) | Date shifting nhưng consistent per patient | Giữ age relationships |
| Ages > 89 | Aggregate thành "90 and over" | |
| Phone/Email | Remove | Check free text fields! |
| SSN | Remove | Never store trong analytics |
| MRN | Replace với pseudonymous token | Maintain mapping nếu cần re-identify |
| IP addresses | Remove hoặc generalize | Web logs có thể contain PHI |

---

## 7. NIST Cybersecurity Framework trong y tế

| Function | Hoạt động | Ví dụ |
|---------|-----------|-------|
| **Identify** | Kiểm kê assets; risk assessment | Liệt kê hệ thống có PHI; đánh giá rủi ro hàng năm |
| **Protect** | Implement safeguards | MFA, encryption, access control, training |
| **Detect** | Monitor threats | SIEM, IDS/IPS, log monitoring |
| **Respond** | Xử lý incident | IR plan, forensics, communication |
| **Recover** | Restore hoạt động | DR plan, backup restore, lessons learned |

---

## 8. Security Checklist cho Healthcare API

| Category | Checklist | HIPAA Ref |
|---------|-----------|-----------|
| Auth | OAuth2 / SMART on FHIR | §164.312(d) |
| Auth | MFA enforced cho PHI access | §164.312(d) |
| AuthZ | RBAC với least privilege | §164.312(a)(1) |
| AuthZ | Patient-level authorization | §164.502 |
| Crypto | TLS 1.2+ tất cả API calls | §164.312(e)(2)(ii) |
| Crypto | AES-256 at rest | §164.312(a)(2)(iv) |
| Audit | PHI access logged đầy đủ | §164.312(b) |
| Audit | Logs separate, tamper-evident, 6+ năm | §164.312(b) |
| Input | FHIR validation; injection prevention | Best practice |
| Rate | Rate limit per user | §164.514(e) |
| Error | Error messages không leak PHI | §164.502 |
| Data | Return chỉ data cần thiết | §164.502(b) |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết PHI vs ePHI và tại sao cần BAA
- [ ] Tôi có thể liệt kê ít nhất 10 trong 18 PHI identifiers
- [ ] Tôi biết 5 Technical Safeguards bắt buộc (Access Control, Encryption, Audit, Integrity, Transmission)
- [ ] Tôi biết AES-256 và TLS 1.2+ là minimum standards
- [ ] Tôi hiểu audit logs phải WORM và separate storage

**Tiếp theo:** `04-security-hipaa/03-cia-ransomware.md` →