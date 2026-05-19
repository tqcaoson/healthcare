# Chương 16c — Technical Safeguards: Implementation Guide

> **Mục tiêu:** Biết chính xác phải implement gì ở code level để đáp ứng HIPAA Technical Safeguards.

---

## 1. Tổng quan HIPAA Security Rule

HIPAA Security Rule yêu cầu 3 loại safeguards:

| Loại | Mô tả | Ví dụ |
|------|-------|-------|
| **Administrative** | Policies, training, procedures | Security training, risk assessment, workforce management |
| **Physical** | Bảo vệ vật lý | Server room locks, workstation controls, device disposal |
| **Technical** | Controls bằng công nghệ | Encryption, access control, audit logs ← **Dev implement cái này** |

---

## 2. Access Control — §164.312(a)(1)

### 2.1 RBAC Implementation

```
Principle: Least Privilege — chỉ access data cần cho công việc

Roles mẫu trong EHR:
┌──────────────────┬────────────────────────────────────────┐
│ Role             │ Access                                  │
├──────────────────┼────────────────────────────────────────┤
│ Attending Phys.  │ Own patients + team patients            │
│ Resident         │ Assigned patients + supervision note    │
│ Nurse            │ Assigned patients; no billing codes     │
│ Lab Tech         │ Lab orders + results; no diagnoses      │
│ Billing Staff    │ Codes + claims; no clinical notes       │
│ Read-only Audit  │ All records; no write; flagged access   │
└──────────────────┴────────────────────────────────────────┘

Implementation:
- Database-level: Row-level security (PostgreSQL RLS)
- API-level: Middleware checks patient assignment
- FHIR: Consent resource to restrict access
```

### 2.2 Break-the-Glass

```
Khi bác sĩ cần access patient không trong care team (emergency):

1. User click "Access outside normal permissions"
2. System prompt: "Lý do truy cập khẩn cấp?"
3. User nhập lý do (structured + free text)
4. Access granted NGAY LẬP TỨC (không chờ approval)
5. ⚠️ Audit log đặc biệt: break-glass event + lý do + reviewer sau
6. Compliance team review tất cả break-glass events hàng tuần
7. Policy team có thể revoke access nếu abuse
```

### 2.3 Emergency Access Procedure

```
Khi hệ thống auth down:
- Không được tắt auth hoàn toàn
- Có danh sách pre-approved "break-glass" accounts trong safe
- Paper process backup
- Mọi emergency access được audit sau
```

---

## 3. Audit Controls — §164.312(b)

### 3.1 Minimum Audit Log Schema

```sql
CREATE TABLE phi_access_log (
    id              BIGSERIAL PRIMARY KEY,
    event_time      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    user_id         VARCHAR(100) NOT NULL,
    user_role       VARCHAR(50),
    session_id      UUID,
    patient_id      VARCHAR(100),           -- MRN hoặc internal ID
    resource_type   VARCHAR(50),            -- Patient, Observation, etc.
    resource_id     VARCHAR(100),
    action          VARCHAR(20) NOT NULL,   -- READ, CREATE, UPDATE, DELETE, EXPORT, PRINT
    endpoint        VARCHAR(500),           -- API endpoint
    ip_address      INET,
    user_agent      TEXT,
    success         BOOLEAN NOT NULL,
    failure_reason  VARCHAR(200),           -- Nếu fail
    break_glass     BOOLEAN DEFAULT FALSE,
    break_glass_reason TEXT,
    correlation_id  UUID                    -- Trace qua microservices
);

-- Không có DELETE permission cho bảng này
-- REVOKE DELETE ON phi_access_log FROM ALL;
```

### 3.2 Events phải log

| Event | Bắt buộc | Fields cần capture |
|-------|---------|-------------------|
| Login thành công | ✅ | user_id, ip, timestamp, MFA used |
| Login thất bại | ✅ | user_id, ip, timestamp, reason |
| PHI access (view) | ✅ | user, patient, resource, timestamp |
| PHI modification | ✅ | user, patient, field changed, old→new value |
| PHI delete | ✅ | user, patient, resource (soft delete) |
| Data export | ✅ | user, dataset scope, record count, destination |
| Print | ✅ | user, patient, document type |
| Break-glass | ✅ | user, patient, reason, timestamp |
| Admin config change | ✅ | admin, change description |
| Session timeout | 🔶 | user, session duration |

### 3.3 Log Infrastructure

```
Application → Structured JSON logs → 
    Log Aggregator (Fluentd/Logstash) → 
    SIEM (Splunk/Elastic/Microsoft Sentinel) →
    Alerts + Dashboards

Lưu trữ:
- Hot storage: 90 ngày (query nhanh)
- Warm storage: 1 năm (archive, queryable)
- Cold storage: 6+ năm (HIPAA requirement)

Bảo vệ log:
- Separate storage account (không cùng app DB)
- Append-only bucket policy (S3 Object Lock / Azure Immutable Blob)
- Alerting nếu log pipeline bị gián đoạn > 5 phút
```

---

## 4. Encryption — §164.312(a)(2)(iv) & §164.312(e)(2)(ii)

### 4.1 At Rest

```
Database:
- Transparent Data Encryption (TDE): PostgreSQL pgcrypto, SQL Server TDE
- Column-level encryption cho SSN, DOB nếu cần extra security
- Key Management: AWS KMS / Azure Key Vault / HashiCorp Vault
- Key rotation: Automated, minimum annual

File Storage:
- S3: Server-side encryption (SSE-KMS) — không dùng SSE-S3
- Azure Blob: Customer-managed keys (CMK)
- DICOM images: Encrypt trước upload hoặc PACS-level encryption

Backup:
- Encrypted backup files — test decryption quarterly
- Separate encryption keys từ primary data

Mobile:
- iOS: Data Protection API (files.completeUnlessOpen)
- Android: EncryptedFile (Jetpack Security)
- Remote wipe via MDM
```

### 4.2 In Transit

```
TLS Configuration:
- Minimum: TLS 1.2
- Recommended: TLS 1.3
- Disable: SSLv3, TLS 1.0, TLS 1.1
- HSTS: max-age=31536000; includeSubDomains; preload

Cipher suites (TLS 1.2):
✅ ECDHE-RSA-AES256-GCM-SHA384
✅ ECDHE-RSA-AES128-GCM-SHA256
❌ RC4, DES, 3DES, NULL

Certificate:
- Minimum RSA 2048 hoặc ECC 256
- Auto-renewal (Let's Encrypt hoặc internal CA)
- Certificate pinning cho mobile apps với PHI

Internal traffic:
- Service mesh với mTLS (Istio, Linkerd)
- Không exempt internal traffic khỏi encryption
```

### 4.3 Email với PHI

```
❌ KHÔNG: Gửi PHI qua plain email
❌ KHÔNG: Attach PHI file vào email thông thường

✅ ĐƯỢC:
- Encrypted email (S/MIME hoặc PGP) — nếu cả hai bên setup
- Secure patient portal message (thay email)
- Direct messaging (health-specific encrypted email)
- Password-protected secure link (expiring, tracked)

Direct Messaging:
- DirectTrust-accredited HSP (Health Service Provider)
- S/MIME encrypted email giữa Direct addresses
- Direct address format: user@direct.hospital.com
```

---

## 5. Integrity Controls — §164.312(c)(1)

```
Mục tiêu: Detect data bị modify/corrupt

Kỹ thuật:
- Digital signatures cho critical documents (discharge summaries)
- Checksums cho transmitted data (HL7, FHIR)
- Database triggers ghi version history
- Immutable audit logs (WORM)

FHIR Resource Versioning:
GET /Patient/123/_history       → tất cả versions
GET /Patient/123/_history/3     → version 3 cụ thể
meta.versionId tăng mỗi update
meta.lastUpdated timestamp mỗi version
```

---

## 6. Automatic Logoff — §164.312(a)(2)(iii)

```
Policy mẫu:
- Clinical workstations: 15 phút inactive → lock screen
- Admin workstations: 10 phút
- Shared/kiosk: 5 phút
- Mobile apps: 5 phút

Implementation:
Frontend: setTimeout + activity listeners (mousemove, keydown, touchstart)
Backend: JWT short expiry (15 phút) + refresh token rotation
Session: Redis với TTL; extend on activity

Warning:
- 2 phút trước timeout: "Phiên sẽ hết hạn trong 2 phút"
- Countdown visible
- "Tiếp tục" button extend session
```

---

## 7. Security Testing Checklist

### HIPAA Technical Controls — Verification

| Control | Test | Pass Criteria |
|---------|------|--------------|
| Auth | Login với sai password 5 lần | Account lockout |
| Auth | JWT expired | 401, không trả PHI |
| AuthZ | User A access Patient B | 403 Forbidden |
| Encryption | Capture network traffic | Chỉ thấy TLS, không thấy PHI |
| Audit | Access patient record | Log entry tồn tại với đủ fields |
| Audit | Delete audit log | Permission denied |
| Timeout | Inactive 16 phút | Session expired |
| Break-glass | Access out-of-care patient | Prompt lý do; audit log đặc biệt |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết implement RBAC và break-glass access đúng cách
- [ ] Tôi biết minimum audit log schema cần có gì (10+ events)
- [ ] Tôi biết AES-256 at rest + TLS 1.2+ in transit là minimum
- [ ] Tôi hiểu tại sao log phải append-only và separate storage
- [ ] Tôi biết auto-logoff timeout và cách implement ở cả frontend lẫn backend

**Tiếp theo:** `04-security-hipaa/03-cia-ransomware.md` →