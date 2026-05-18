# FHIR R4 API — Cheat Sheet Đầy Đủ

> **Dùng như reference sheet**: copy URL, params, examples trực tiếp vào code.  
> Base URL ví dụ: `https://hapi.fhir.org/baseR4`

---

## 1. FHIR REST Operations — Quick Reference

| Operation | Method | URL | Example |
|-----------|--------|-----|---------|
| **Read** | GET | `/[Type]/[id]` | `GET /Patient/123` |
| **VRead** (version) | GET | `/[Type]/[id]/_history/[vid]` | `GET /Patient/123/_history/2` |
| **Search** | GET | `/[Type]?[params]` | `GET /Observation?patient=123` |
| **Search (POST)** | POST | `/[Type]/_search` | Body: `patient=123&_count=20` |
| **Create** | POST | `/[Type]` | `POST /MedicationRequest` |
| **Update** | PUT | `/[Type]/[id]` | `PUT /Patient/123` |
| **Conditional Update** | PUT | `/[Type]?[params]` | `PUT /Patient?identifier=MRN|12345` |
| **Patch** | PATCH | `/[Type]/[id]` | `PATCH /Patient/123` |
| **Delete** | DELETE | `/[Type]/[id]` | `DELETE /Appointment/456` |
| **History (resource)** | GET | `/[Type]/[id]/_history` | `GET /Patient/123/_history` |
| **History (type)** | GET | `/[Type]/_history` | `GET /Patient/_history?_since=2024-01-01` |
| **History (server)** | GET | `/_history` | `GET /_history?_count=100` |
| **Transaction/Batch** | POST | `/` | Bundle with multiple operations |
| **Capabilities** | GET | `/metadata` | Server CapabilityStatement |
| **Operation** | POST/GET | `/[Type]/$[name]` | `POST /Patient/$everything` |

---

## 2. HTTP Headers Quan Trọng

```http
# Request headers
Authorization: Bearer <smart_access_token>
Content-Type: application/fhir+json
Accept: application/fhir+json
Prefer: return=representation          # Server trả về resource sau create/update
If-Match: W/"3"                        # Optimistic locking (conditional update)

# Response headers
Content-Type: application/fhir+json; charset=utf-8
ETag: W/"3"                            # Current version
Last-Modified: Tue, 19 May 2026 00:00:00 GMT
Location: https://server/Patient/123  # Sau create (201 Created)
```

---

## 3. HTTP Status Codes

| Code | Khi nào | Ý nghĩa |
|------|---------|---------|
| **200 OK** | Read, Search, Update (Prefer: minimal) | Thành công |
| **201 Created** | Create | Tạo thành công; Location header có URL mới |
| **204 No Content** | Delete | Xóa thành công |
| **400 Bad Request** | Invalid FHIR | Malformed JSON, invalid resource type |
| **401 Unauthorized** | Missing/expired token | Re-authenticate |
| **403 Forbidden** | Valid token, no permission | Scope không đủ |
| **404 Not Found** | Resource không tồn tại | |
| **409 Conflict** | Version conflict | ETag không match — optimistic lock |
| **410 Gone** | Resource đã bị delete | |
| **412 Precondition Failed** | If-Match failed | |
| **422 Unprocessable** | Valid JSON, invalid FHIR | Validation error; OperationOutcome trong body |

---

## 4. Search Parameters — Common Types

### String Search
```
GET /Patient?name=Nguyen            # contains (case-insensitive)
GET /Patient?name:exact=Nguyen      # exact match
GET /Patient?name:contains=guyễn   # contains (explicit)
```

### Token Search (codes, identifiers)
```
# Format: system|code  or  |code  or  code  or  system|
GET /Patient?identifier=http://hospital.org/mrn|12345
GET /Condition?code=http://snomed.info/sct|22298006
GET /Observation?code=http://loinc.org|2345-7
GET /MedicationRequest?status=active
```

### Date Search
```
GET /Observation?date=2024-01-15             # exact date
GET /Observation?date=ge2024-01-01           # on or after
GET /Observation?date=le2024-12-31           # on or before
GET /Encounter?date=ge2024-01-01&date=lt2025-01-01  # range
GET /Patient?birthdate=1980                  # born in 1980
```

### Quantity Search
```
GET /Observation?value-quantity=140|mm[Hg]           # exact
GET /Observation?value-quantity=gt140|mm[Hg]         # greater than
GET /Observation?value-quantity=ge120|mm[Hg]         # >=
```

### Reference Search
```
GET /Observation?patient=Patient/123
GET /Observation?patient=123          # shorthand
GET /MedicationRequest?subject=Patient/123
GET /Condition?encounter=Encounter/456
```

### Composite Search
```
GET /Observation?code-value-quantity=http://loinc.org|8480-6$gt140
# code AND value in same Observation
```

---

## 5. Search Result Control

```
# Pagination
GET /Patient?_count=20                      # 20 per page (default varies)
# Navigate pages via Bundle.link[next].url

# Sorting
GET /Observation?_sort=date                 # ascending
GET /Observation?_sort=-date                # descending (newest first)
GET /Patient?_sort=family,given             # multi-sort

# Field selection
GET /Patient?_elements=id,name,birthDate    # only these fields
GET /Patient?_summary=true                  # server-defined summary

# Total count
GET /Patient?_total=accurate                # exact count in Bundle.total
GET /Patient?_total=estimate                # fast estimate
```

---

## 6. _include và _revinclude

```
# _include: kéo theo referenced resources
GET /MedicationRequest?patient=123&_include=MedicationRequest:patient
# → trả về MedicationRequests + Patient/123 trong cùng Bundle

GET /DiagnosticReport?patient=123&_include=DiagnosticReport:result
# → trả về DiagnosticReports + tất cả Observations referenced

# _revinclude: kéo theo resources trỏ VỀ resource này
GET /Patient/123?_revinclude=Observation:patient
# → trả về Patient/123 + tất cả Observations của patient đó

# Nested include (FHIR R4)
GET /MedicationRequest?_include=MedicationRequest:medication
                       &_include:iterate=Medication:ingredient
```

---

## 7. Common Clinical Queries — Copy & Use

```bash
# Patient lookup by MRN
GET /Patient?identifier=http://hospital.org/mrn|MRN12345

# Patient demographics + all resources ($everything operation)
POST /Patient/123/$everything

# All vital signs for a patient (sorted newest first)
GET /Observation?patient=123&category=vital-signs&_sort=-date&_count=50

# Specific vital sign (systolic BP) with date range
GET /Observation?patient=123
    &code=http://loinc.org|8480-6
    &date=ge2024-01-01
    &_sort=-date

# Lab results (newest 20)
GET /Observation?patient=123&category=laboratory&_sort=-date&_count=20

# Active medications
GET /MedicationRequest?patient=123&status=active&_sort=-authoredOn

# Active problems/diagnoses
GET /Condition?patient=123&clinical-status=active

# Current encounter
GET /Encounter?patient=123&status=in-progress

# Allergies
GET /AllergyIntolerance?patient=123&clinical-status=active

# Procedures (last 12 months)
GET /Procedure?patient=123&date=ge2024-01-01&_sort=-performed-date

# Immunizations
GET /Immunization?patient=123&_sort=-date

# Care team members
GET /CareTeam?patient=123&status=active
```

---

## 8. FHIR Bundle — Transactions

```json
POST /

{
  "resourceType": "Bundle",
  "type": "transaction",
  "entry": [
    {
      "fullUrl": "urn:uuid:patient-temp-id",
      "resource": {
        "resourceType": "Patient",
        "name": [{"family": "Nguyen", "given": ["Van", "A"]}],
        "birthDate": "1980-01-01"
      },
      "request": {
        "method": "POST",
        "url": "Patient"
      }
    },
    {
      "resource": {
        "resourceType": "Observation",
        "status": "final",
        "subject": {"reference": "urn:uuid:patient-temp-id"},
        "code": {
          "coding": [{"system": "http://loinc.org", "code": "8480-6"}]
        },
        "valueQuantity": {"value": 140, "unit": "mm[Hg]"}
      },
      "request": {
        "method": "POST",
        "url": "Observation"
      }
    }
  ]
}
```

---

## 9. OperationOutcome — Xử lý lỗi

```json
HTTP/1.1 422 Unprocessable Entity

{
  "resourceType": "OperationOutcome",
  "issue": [
    {
      "severity": "error",          // fatal | error | warning | information
      "code": "required",           // required | invalid | not-found | ...
      "details": {
        "text": "Patient.birthDate is required"
      },
      "expression": ["Patient.birthDate"]  // FHIRPath to problem field
    }
  ]
}
```

```python
# Python error handling
response = requests.post(f"{base_url}/Patient", json=patient_data, headers=headers)

if not response.ok:
    outcome = response.json()
    if outcome.get("resourceType") == "OperationOutcome":
        for issue in outcome.get("issue", []):
            print(f"[{issue['severity']}] {issue.get('details', {}).get('text')}")
            if 'expression' in issue:
                print(f"  Field: {issue['expression']}")
    response.raise_for_status()
```

---

## 10. FHIR Bulk Data API — Population Export

```bash
# 1. Kick off export
POST /Patient/$export?_type=Patient,Observation,MedicationRequest
     &_since=2024-01-01
     &_outputFormat=application/fhir+ndjson

# Response:
# 202 Accepted
# Content-Location: https://server/export-jobs/abc123

# 2. Poll for completion
GET /export-jobs/abc123
# 202 → still processing (check X-Progress header)
# 200 → done, body has file URLs

# 3. Response when complete (200)
{
  "transactionTime": "2024-01-15T12:00:00Z",
  "requiresAccessToken": true,
  "output": [
    {"type": "Patient", "url": "https://server/files/patients-1.ndjson"},
    {"type": "Observation", "url": "https://server/files/obs-1.ndjson"},
    {"type": "Observation", "url": "https://server/files/obs-2.ndjson"}
  ],
  "error": []
}

# 4. Download each file (NDJSON — one resource per line)
GET https://server/files/patients-1.ndjson
Authorization: Bearer <token>
# → {"resourceType":"Patient","id":"1",...}
#   {"resourceType":"Patient","id":"2",...}
```

---

## 11. SMART on FHIR — Token Request Quick Reference

```bash
# EHR Launch — exchange code for token
curl -X POST https://ehr.hospital.org/oauth/token \
  -d "grant_type=authorization_code" \
  -d "code=AUTH_CODE_FROM_EHR" \
  -d "redirect_uri=https://myapp.com/callback" \
  -d "client_id=my-app-client-id"

# Response
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "patient/*.read launch/patient openid",
  "patient": "123",           # ← patient context từ EHR
  "encounter": "456",
  "id_token": "eyJ..."
}

# Backend Services (machine-to-machine)
curl -X POST https://ehr.hospital.org/oauth/token \
  -d "grant_type=client_credentials" \
  -d "client_assertion_type=urn:ietf:params:oauth:client-assertion-type:jwt-bearer" \
  -d "client_assertion=<signed-jwt>" \
  -d "scope=system/*.read"
```

---

## 12. FHIR R4 Resource → FHIR Path Quick Map

| Muốn lấy | FHIR Path | Ví dụ |
|----------|----------|-------|
| Patient ID | `Patient.id` | `123` |
| Patient MRN | `Patient.identifier.where(system='mrn').value` | `MRN12345` |
| Patient name | `Patient.name.where(use='official').family` | `Nguyen` |
| DOB | `Patient.birthDate` | `1980-01-01` |
| Observation value (numeric) | `Observation.valueQuantity.value` | `140` |
| Observation value (coded) | `Observation.valueCodeableConcept.coding.code` | `LA6576-8` |
| Lab LOINC code | `Observation.code.coding.where(system='http://loinc.org').code` | `2345-7` |
| Med drug name | `MedicationRequest.medicationCodeableConcept.text` | `Metformin 500mg` |
| Diagnosis code | `Condition.code.coding.where(system='icd-10').code` | `E11.9` |
| Encounter start | `Encounter.period.start` | `2024-01-15T08:00:00Z` |

---

## ✅ Cheat Sheet này bạn nên:

- [ ] Bookmark / lưu lại để tra cứu khi code
- [ ] Test các queries trên hapi.fhir.org/baseR4 (sandbox công khai)
- [ ] Dùng Postman collection FHIR để thực hành trực tiếp

**Tiếp theo:** `05-integration-api/02-hl7-integration.md` →