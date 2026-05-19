# Chương 9 — SMART on FHIR & CDS Hooks: Build Apps cho bất kỳ EHR nào

> **Mục tiêu:** Implement SMART launch flow và CDS Hooks service từ đầu — không phụ thuộc vendor.

---

## 1. SMART on FHIR — Tổng quan

**SMART** = Substitutable Medical Applications, Reusable Technologies

= **OAuth 2.0** + **OpenID Connect** + **FHIR R4**

Cho phép build app y tế chạy trong **bất kỳ EHR nào** hỗ trợ FHIR — Epic, Cerner, Allscripts đều dùng cùng standard.

---

## 2. SMART Launch Flows

### 2.1 EHR Launch (phổ biến nhất)

App được khởi động **từ bên trong EHR** — bác sĩ click button trong EHR, app mở ra và đã biết patient/encounter context.

```
1. User click "Launch App" trong EHR
        ↓
2. EHR gọi: GET https://myapp.com/launch
               ?iss=https://ehr.hospital.org/fhir
               &launch=abc123   ← launch token (context)
        ↓
3. App redirect đến EHR Authorization Server:
   GET /authorize
     ?response_type=code
     &client_id=myapp
     &redirect_uri=https://myapp.com/callback
     &scope=launch openid patient/*.read
     &launch=abc123
     &state=random_string
        ↓
4. EHR hỏi user xác nhận quyền (hoặc auto-approve)
        ↓
5. EHR redirect về: GET /callback?code=AUTH_CODE&state=random_string
        ↓
6. App exchange code lấy token:
   POST /token
     grant_type=authorization_code
     &code=AUTH_CODE
     &redirect_uri=...
        ↓
7. Token response:
   {
     "access_token": "...",
     "token_type": "Bearer",
     "expires_in": 3600,
     "patient": "Patient/123",     ← context từ EHR
     "encounter": "Encounter/456"
   }
        ↓
8. App gọi FHIR API:
   GET /Patient/123
   Authorization: Bearer <access_token>
```

### 2.2 Standalone Launch

App launch độc lập (không từ EHR). User tự chọn patient. Dùng cho consumer-facing apps.

```
1. User mở app trực tiếp (bookmark, app store)
        ↓
2. App cần biết FHIR server nào để connect
   → User chọn institution hoặc app hardcode
        ↓
3. Flow tương tự EHR Launch nhưng KHÔNG có launch token
   → App request scope: launch/patient để user chọn patient
```

### 2.3 Backend Services (Machine-to-Machine)

Không có user — server-to-server. Dùng cho bulk export, analytics pipelines.

```
POST /token
  grant_type=client_credentials
  &client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer
  &client_assertion=<signed JWT>    ← dùng private key, không password
```

---

## 3. SMART Scopes — Chi tiết

### Clinical Scopes

```
Format: [context]/[resourceType].[permission]
Context: patient (data của patient đang chọn) | user (data user có quyền)
Permission: read | write | * (all)

Ví dụ:
patient/Patient.read          → đọc Patient resource
patient/Observation.read      → đọc vital signs, lab results của patient
patient/MedicationRequest.read→ đọc thuốc của patient
patient/*.read                → đọc tất cả resources của patient
user/Patient.write            → tạo/edit Patient records (user có quyền)
```

### Identity Scopes (OpenID Connect)

```
openid     → nhận id_token (biết ai đang login)
profile    → nhận user profile (name, role)
fhirUser   → nhận FHIR Practitioner resource của user
```

### Launch Scopes

```
launch          → EHR launch (cần launch token)
launch/patient  → user phải chọn patient khi standalone launch
launch/encounter→ user phải chọn encounter
```

---

## 4. SMART Discovery — Well-Known Endpoints

```
GET https://ehr.hospital.org/fhir/.well-known/smart-configuration

Response:
{
  "issuer": "https://ehr.hospital.org",
  "authorization_endpoint": "https://ehr.hospital.org/oauth/authorize",
  "token_endpoint": "https://ehr.hospital.org/oauth/token",
  "capabilities": [
    "launch-ehr",
    "launch-standalone",
    "client-public",
    "client-confidential-symmetric",
    "sso-openid-connect",
    "context-ehr-patient",
    "permission-patient",
    "permission-user"
  ],
  "scopes_supported": ["openid", "profile", "launch", "patient/*.read"]
}
```

---

## 5. CDS Hooks — Nhúng CDS vào EHR qua API

### Tại sao CDS Hooks?

Trước CDS Hooks: mỗi CDS service phải tích hợp riêng cho từng EHR.  
Với CDS Hooks: **1 service chạy được trong mọi EHR** support standard.

### Architecture

```
EHR (Epic/Cerner)                    CDS Service (your server)
─────────────────                    ─────────────────────────
User mở hồ sơ bệnh nhân
    ↓
EHR phát hiện hook:
"patient-view" triggered
    ↓
POST /cds-services/my-service ──────→ Nhận request
{                                     Đọc patient data từ FHIR
  "hook": "patient-view",             Thực thi rules/ML model
  "hookInstance": "...",                    ↓
  "context": {                        Return cards
    "userId": "Practitioner/123",           ↓
    "patientId": "Patient/456"   ←────{
  },                                    "cards": [{
  "prefetch": {                           "summary": "Patient overdue for flu shot",
    "patient": { ...FHIR Patient... }     "indicator": "warning",
  }                                       "source": { "label": "CDC Guidelines" },
}                                         "suggestions": [{
                                            "label": "Order flu vaccine",
                                            "actions": [{...}]
                                          }]
                                        }]
                                      }
```

### Hook Types

| Hook | Trigger | Use Case |
|------|---------|---------|
| `patient-view` | Mở hồ sơ bệnh nhân | Risk scores, overdue screenings, care gaps |
| `order-sign` | Ký lệnh thuốc/xét nghiệm | Drug-drug interaction, dosing check, alternatives |
| `order-select` | Chọn loại thuốc | Formulary compliance, generic alternatives |
| `appointment-book` | Đặt lịch | Preventive care reminders |
| `encounter-start` | Bắt đầu khám | Clinical protocol reminders |

### CDS Hooks Service Discovery

```
GET https://mycds.example.com/cds-services

Response:
{
  "services": [{
    "hook": "patient-view",
    "id": "sepsis-risk",
    "title": "Sepsis Risk Calculator",
    "description": "Early warning for sepsis based on vitals and labs",
    "prefetch": {
      "patient": "Patient/{{context.patientId}}",
      "observations": "Observation?patient={{context.patientId}}&_sort=-date&_count=20"
    }
  }]
}
```

### Card Indicators — Priority Levels

| Indicator | Màu | Ý nghĩa | Khi dùng |
|---------|-----|---------|---------|
| `info` | Xanh | Thông tin tham khảo | Không urgent |
| `warning` | Vàng | Cần chú ý | Có risk nhưng không cần block |
| `critical` | Đỏ | Cần hành động ngay | Drug interaction nguy hiểm |

---

## 6. Prefetch — Tối ưu performance

CDS Hooks request có thể kèm prefetch data — EHR tự fetch FHIR data trước khi gọi CDS service:

```json
"prefetch": {
  "patient": "Patient/{{context.patientId}}",
  "recentVitals": "Observation?patient={{context.patientId}}&category=vital-signs&_sort=-date&_count=5",
  "medications": "MedicationRequest?patient={{context.patientId}}&status=active"
}
```

**Lợi ích:** CDS service không cần tự gọi FHIR API → faster response.  
**Khi prefetch không có:** Service phải gọi FHIR API dùng access token từ request.

---

## 7. Security — CDS Hooks

| Security yêu cầu | Mô tả |
|-----------------|-------|
| **JWT Authorization** | EHR gửi JWT trong header → CDS verify signature |
| **HTTPS bắt buộc** | Tất cả CDS Hooks qua TLS |
| **Token validation** | Verify issuer, audience, expiry |
| **No PHI trong logs** | Patient data trong request không được log plain text |

---

## 8. Môi trường test

| Tool | URL | Mô tả |
|------|-----|-------|
| **SMART App Launcher** | launch.smarthealthit.org | Test SMART apps với synthetic data |
| **CDS Hooks Sandbox** | sandbox.cds-hooks.org | Test CDS Hooks services |
| **HAPI FHIR** | hapi.fhir.org/baseR4 | Public FHIR server R4 |
| **Inferno** | inferno.healthit.gov | Test FHIR conformance |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết sự khác nhau EHR Launch vs Standalone Launch vs Backend Services
- [ ] Tôi hiểu SMART scope format `[context]/[resource].[permission]`
- [ ] Tôi biết 5 CDS Hook types và khi nào dùng
- [ ] Tôi hiểu prefetch tại sao quan trọng cho performance
- [ ] Tôi biết card indicator levels (info / warning / critical)

**Tiếp theo:** `04-security-hipaa/01-hipaa-co-ban.md` →