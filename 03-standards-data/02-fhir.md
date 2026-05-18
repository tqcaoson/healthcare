# Chương 13b — FHIR R4: API Hiện Đại Cho Health IT

> **Mục tiêu:** Thực sự biết build FHIR API — REST operations, search params, US Core, SMART on FHIR.

---

## 1. Tại sao FHIR?

- **US Interoperability Rule 2020**: Tất cả EHR phải expose FHIR R4 API — **bắt buộc**
- RESTful API + JSON/XML → dễ implement hơn HL7 v3 rất nhiều
- Đây là **tương lai** — mọi hệ thống y tế mới phải support FHIR

---

## 2. FHIR Fundamentals

| Khái niệm | Mô tả |
|-----------|-------|
| **Resource** | Đơn vị cơ bản: Patient, Observation, MedicationRequest... Mỗi resource có JSON/XML schema |
| **RESTful API** | Standard HTTP verbs: GET, POST, PUT, DELETE, PATCH |
| **FHIR R4** | Stable version hiện tại. R5 released 2023 nhưng R4 vẫn là standard thực tế |
| **Profile** | Tùy chỉnh FHIR resource cho use case cụ thể. US Core Profile = standard Mỹ |
| **CapabilityStatement** | Mỗi FHIR server phải publish `/metadata` — liệt kê resources, operations hỗ trợ |

---

## 3. FHIR Resources phổ biến nhất

| Resource | Mô tả | Ví dụ endpoint |
|---------|-------|---------------|
| **Patient** | Demographics bệnh nhân | `GET /Patient/123` |
| **Observation** | Vital signs, lab results, clinical findings | `GET /Observation?patient=123&code=loinc|8480-6` |
| **MedicationRequest** | Đơn thuốc, lệnh thuốc | `POST /MedicationRequest` |
| **Condition** | Chẩn đoán, vấn đề sức khỏe | `GET /Condition?patient=123` |
| **DiagnosticReport** | Kết quả lab tổng hợp, radiology report | `GET /DiagnosticReport?patient=123` |
| **Encounter** | Lần khám/nhập viện | `GET /Encounter?patient=123&status=in-progress` |
| **Procedure** | Thủ thuật đã thực hiện | `GET /Procedure?patient=123` |
| **AllergyIntolerance** | Dị ứng và phản ứng bất lợi | `GET /AllergyIntolerance?patient=123` |

---

## 4. FHIR REST Operations — Cheat Sheet

| Operation | HTTP | URL Pattern | Ví dụ |
|-----------|------|-------------|-------|
| **Read** | GET | `/[type]/[id]` | `GET /Patient/123` |
| **Search** | GET | `/[type]?[params]` | `GET /Observation?patient=123&_sort=-date` |
| **Create** | POST | `/[type]` | `POST /MedicationRequest` |
| **Update** | PUT | `/[type]/[id]` | `PUT /Patient/123` |
| **Patch** | PATCH | `/[type]/[id]` | `PATCH /Patient/123` (JSON Patch) |
| **Delete** | DELETE | `/[type]/[id]` | `DELETE /Appointment/456` |
| **History** | GET | `/[type]/[id]/_history` | Tất cả versions của resource |
| **Transaction** | POST | `/` | Bundle nhiều operations trong 1 request |
| **Operation** | GET/POST | `/[type]/$[name]` | `POST /Patient/123/$everything` |

---

## 5. FHIR Search — Phần phức tạp nhất

### Search Parameter Types

| Type | Ví dụ | Cú pháp |
|------|-------|---------|
| **String** | Tìm theo tên | `GET /Patient?name=Nguyen` |
| **Token** | Tìm theo code | `GET /Condition?code=http://snomed.info/sct|22298006` |
| **Date** | Tìm theo ngày | `GET /Observation?date=ge2024-01-01&date=le2024-12-31` |
| **Reference** | Tìm linked resources | `GET /MedicationRequest?patient=Patient/123` |
| **Quantity** | Tìm theo giá trị số | `GET /Observation?value-quantity=gt140|mmHg` |

### Search Modifiers hữu ích

| Modifier | Ví dụ | Ý nghĩa |
|---------|-------|---------|
| `:exact` | `?name:exact=Nguyen Van A` | Match chính xác (case sensitive) |
| `:contains` | `?name:contains=Nguyen` | Chứa chuỗi này |
| `_include` | `?_include=MedicationRequest:patient` | Kèm theo related resources |
| `_revinclude` | `?_revinclude=Observation:patient` | Include resources trỏ về đây |
| `_count` | `?_count=20` | Pagination — số results mỗi trang |
| `_sort` | `?_sort=-date` | Sort (- = descending) |
| `_summary` | `?_summary=true` | Chỉ trả về summary fields |

---

## 6. Patient resource — JSON Example

```json
{
  "resourceType": "Patient",
  "id": "123",
  "meta": {
    "versionId": "1",
    "lastUpdated": "2024-01-01T12:00:00Z"
  },
  "identifier": [{
    "system": "http://hospital.example/mrn",
    "value": "MRN12345"
  }],
  "name": [{
    "family": "Nguyen",
    "given": ["Van", "A"],
    "use": "official"
  }],
  "gender": "male",
  "birthDate": "1980-01-01",
  "address": [{
    "line": ["123 Nguyen Hue"],
    "city": "Ho Chi Minh City",
    "country": "VN"
  }],
  "communication": [{
    "language": {
      "coding": [{"system": "urn:ietf:bcp:47", "code": "vi"}]
    }
  }]
}
```

---

## 7. US Core — Standard bắt buộc ở Mỹ

US Core là FHIR Profile **bắt buộc** cho Certified EHR theo ONC.

| Khái niệm | Ý nghĩa |
|-----------|---------|
| **Must Support** | Nếu data có, PHẢI gửi; receiver PHẢI process — không được ignore |
| **US Core Patient** | Bắt buộc: identifier, name, gender. Must-support: birthDate, address |
| **US Core Vital Signs** | LOINC codes bắt buộc cho BP, weight, height, BMI, SpO2 |
| **USCDI** | US Core Data for Interoperability — bộ data elements tối thiểu mà mọi EHR phải exchange |

---

## 8. SMART on FHIR — Build Apps cho bất kỳ EHR nào

SMART on FHIR = OAuth2 + FHIR. Build app y tế có thể chạy trong **bất kỳ EHR** nào hỗ trợ FHIR.

### SMART Launch Flow

```
1. App được launch từ trong EHR (hoặc standalone)
        ↓
2. EHR redirect đến Authorization Server
   với context (patient ID, encounter ID)
        ↓
3. User đăng nhập và cấp quyền cho app
        ↓
4. App nhận authorization code
   → exchange lấy access token
        ↓
5. App dùng access token gọi FHIR API
   → lấy dữ liệu bệnh nhân
```

### SMART Scopes

```
patient/Patient.read          → đọc Patient resource
patient/Observation.read      → đọc Observation
patient/*.read                → đọc tất cả resources của patient
user/*.read                   → đọc data của user đang login
launch/patient                → nhận patient ID khi launch từ EHR
openid profile                → lấy thông tin user (OIDC)
```

---

## 9. FHIR Bulk Data / Async API

Dùng khi cần export **toàn bộ dữ liệu** (population analytics):

```
1. Kick-off:  POST /Patient/$export?_type=Patient,Observation
              → Response: 202 Accepted + Content-Location: /export/123
              
2. Polling:   GET /export/123
              → 202 (processing) hoặc 200 (done)
              
3. Download:  Mỗi resource type = 1 NDJSON file
              { "Patient": "...", ... }
              { "Patient": "...", ... }  ← newline-delimited JSON
```

**Security**: SMART Backend Services (machine-to-machine OAuth2 client credentials)

---

## 10. Sandbox để thực hành

| Sandbox | URL | Dùng cho |
|---------|-----|---------|
| SMART on FHIR | launch.smarthealthit.org | Test SMART apps với synthetic data |
| HAPI FHIR public | hapi.fhir.org/baseR4 | Test FHIR queries; R4 |
| Synthea | synthea.mitre.org | Generate synthetic patients |

---

## ✅ Checklist sau chương này

- [ ] Tôi có thể viết FHIR search query cho patient's lab results trong date range
- [ ] Tôi hiểu `_include` và `_revinclude` dùng để làm gì
- [ ] Tôi biết SMART on FHIR flow từ launch đến gọi API
- [ ] Tôi biết US Core "Must Support" nghĩa là gì
- [ ] Tôi biết FHIR Bulk Data dùng khi nào và flow như thế nào

**Tiếp theo:** `03-standards-data/03-terminology.md` →