# Chương — Design Patterns & Anti-patterns trong Health IT

> **Mục tiêu:** Học từ lỗi của người khác — những pattern đúng và những thứ tuyệt đối không được làm.

---

## 1. Design Patterns — Những gì hoạt động tốt

### Pattern 1: Event Sourcing cho Clinical Data

**Vấn đề:** Medication history, vital sign history — cần biết **mọi thứ đã xảy ra**, không chỉ state hiện tại.

**Giải pháp:** Lưu tất cả events (immutable), derive state từ events.

```
❌ Sai (mutable state):
UPDATE medications SET status = 'discontinued' WHERE id = 123
-- Mất thông tin: ai discontinue? khi nào? lý do?

✅ Đúng (event sourcing):
INSERT INTO medication_events (medication_id, event_type, actor, timestamp, reason)
VALUES (123, 'DISCONTINUED', 'Dr.Nguyen', NOW(), 'Adverse reaction - rash')
-- Audit trail đầy đủ; HIPAA compliant; có thể replay
```

**Khi áp dụng:** Medication orders, order cancellations, patient status changes, allergy updates.

---

### Pattern 2: FHIR Resource Versioning

**Vấn đề:** Patient demographics thay đổi — cần lịch sử.

**Giải pháp:** FHIR built-in versioning — mỗi update tạo version mới.

```
GET /Patient/123/_history        → tất cả versions
GET /Patient/123/_history/3      → version 3 cụ thể

FHIR Resource có meta.versionId và meta.lastUpdated
→ Không bao giờ hard delete — dùng logical delete (Patient.active = false)
```

---

### Pattern 3: Circuit Breaker cho CDS Services

**Vấn đề:** CDS service bên ngoài bị down → không được block CPOE.

**Giải pháp:** Circuit breaker — nếu CDS service không respond trong X ms, fail open (cho phép tiếp tục, không block).

```javascript
// Pseudocode
async function callCDSService(hookRequest) {
  try {
    const response = await fetch(cdsUrl, {
      ...hookRequest,
      signal: AbortSignal.timeout(500) // 500ms timeout
    });
    circuitBreaker.recordSuccess();
    return response.json();
  } catch (err) {
    circuitBreaker.recordFailure();
    if (circuitBreaker.isOpen()) {
      // Fail OPEN: return empty cards, log warning
      logger.warn('CDS service unavailable — proceeding without CDS');
      return { cards: [] };
    }
    throw err;
  }
}
// Rule: CDS NEVER blocks patient care workflow
```

---

### Pattern 4: Outbox Pattern cho HL7 Delivery

**Vấn đề:** Order saved trong DB nhưng HL7 message chưa gửi đi — inconsistency.

**Giải pháp:** Outbox pattern — ghi message vào DB cùng transaction với order, worker gửi async.

```sql
-- Trong 1 transaction:
BEGIN;
  INSERT INTO orders (patient_id, test, status) VALUES (...);
  INSERT INTO outbox_messages (type, payload, status)
    VALUES ('HL7_ORM', '{"MSH|..."}', 'PENDING');
COMMIT;

-- Background worker:
-- SELECT pending messages → send via MLLP → mark SENT / FAILED
-- Nếu MLLP down → retry với exponential backoff
-- Failed sau N retries → DLQ → alert on-call engineer
```

---

### Pattern 5: Terminology Lookup Table (không hardcode codes)

**Vấn đề:** ICD/LOINC/SNOMED update hàng năm. Hardcode codes → break.

**Giải pháp:** Lookup table với versioned mappings.

```sql
-- Đừng làm thế này:
WHERE icd_code = 'E11.9'  -- hardcoded

-- Làm thế này:
CREATE TABLE value_sets (
  vs_name    VARCHAR,  -- 'DiabetesType2'
  code       VARCHAR,  -- 'E11.9'
  system     VARCHAR,  -- 'http://hl7.org/fhir/sid/icd-10-cm'
  valid_from DATE,
  valid_to   DATE
);

WHERE icd_code IN (
  SELECT code FROM value_sets
  WHERE vs_name = 'DiabetesType2'
    AND valid_from <= CURRENT_DATE
    AND (valid_to IS NULL OR valid_to >= CURRENT_DATE)
)
```

---

### Pattern 6: Idempotent HL7 Processing

**Vấn đề:** HL7 messages có thể gửi lại (retry). Duplicate processing = duplicate records.

**Giải pháp:** Track Message Control ID (MSH-10), idempotency check trước khi process.

```python
def process_hl7_message(message):
    msg_control_id = message.MSH.field(10)
    
    # Idempotency check
    if message_already_processed(msg_control_id):
        send_ack(message, status='AA')  # acknowledge nhưng không process lại
        return
    
    with transaction():
        process_business_logic(message)
        mark_as_processed(msg_control_id)
    
    send_ack(message, status='AA')
```

---

### Pattern 7: PHI Scrubbing trong Logs

**Vấn đề:** Application logs không được chứa PHI — HIPAA violation.

**Giải pháp:** Middleware tự động detect và mask PHI trước khi ghi log.

```python
PHI_PATTERNS = {
    'ssn': r'\b\d{3}-\d{2}-\d{4}\b',
    'mrn': r'\bMRN\s*:?\s*\d{6,10}\b',
    'dob': r'\b(DOB|date.of.birth)\s*:?\s*\d{1,2}/\d{1,2}/\d{4}\b',
    'phone': r'\b\d{3}[-.]?\d{3}[-.]?\d{4}\b',
    'email': r'\b[\w.]+@[\w.]+\.\w+\b',
    'ip': r'\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b',
}

def scrub_phi(log_message: str) -> str:
    for phi_type, pattern in PHI_PATTERNS.items():
        log_message = re.sub(pattern, f'[{phi_type.upper()}_REDACTED]', log_message)
    return log_message

# Apply as logging middleware — mọi log line đều qua scrubber
```

---

## 2. Anti-patterns — Tuyệt đối không làm

### ❌ Anti-pattern 1: Log PHI vào Application Logs

```python
# ❌ HIPAA VIOLATION
logger.info(f"Processing order for patient {patient.name}, DOB {patient.dob}, MRN {patient.mrn}")

# ✅ Đúng
logger.info(f"Processing order | patient_ref=Patient/{patient.id} | order_id={order.id}")
# Chỉ log non-PHI identifiers (FHIR resource ID ≠ PHI)
```

---

### ❌ Anti-pattern 2: Random Train/Test Split cho Clinical ML

```python
# ❌ NGUY HIỂM — data leakage
from sklearn.model_selection import train_test_split
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)
# Tại sao sai: future data trong training set → inflated metrics

# ✅ Đúng — temporal split
train = data[data.admission_date < '2022-01-01']
val   = data[(data.admission_date >= '2022-01-01') & (data.admission_date < '2023-01-01')]
test  = data[data.admission_date >= '2023-01-01']
```

---

### ❌ Anti-pattern 3: Delete PHI thay vì Deactivate

```sql
-- ❌ Không được — HIPAA yêu cầu giữ records tối thiểu 6 năm
DELETE FROM patients WHERE id = 123;

-- ✅ Logical delete
UPDATE patients SET active = false, deactivated_at = NOW(), deactivated_by = 'user_456'
WHERE id = 123;
-- Record vẫn tồn tại cho audit; không hiện trong UI thông thường
```

---

### ❌ Anti-pattern 4: Block CPOE vì CDS Service Down

```javascript
// ❌ Cực kỳ nguy hiểm — bác sĩ không ra được lệnh cứu sống bệnh nhân
const cdsResult = await cdsService.check(order); // nếu throw → CPOE bị block
await saveOrder(order);

// ✅ CDS là advisory — không bao giờ block
let cards = [];
try {
  cards = await Promise.race([
    cdsService.check(order),
    timeout(500).then(() => []) // fail open sau 500ms
  ]);
} catch (e) {
  logger.warn('CDS unavailable', e);
  // cards = [] → không block, chỉ không có gợi ý
}
await saveOrder(order);
showCards(cards); // hiển thị nếu có
```

---

### ❌ Anti-pattern 5: Không Validate Terminology Codes

```python
# ❌ Accept bất kỳ string nào
medication_request = {
    "code": {"coding": [{"system": "...", "code": user_input}]}
}

# ✅ Validate code tồn tại trong terminology server
def validate_rxnorm_code(code: str) -> bool:
    response = requests.get(f"https://rxnav.nlm.nih.gov/REST/rxcui/{code}.json")
    return response.status_code == 200

# FHIR Validator cũng check terminology binding
```

---

### ❌ Anti-pattern 6: Impute Missing Lab Values với Mean

```python
# ❌ NGUY HIỂM trong healthcare
imputer = SimpleImputer(strategy='mean')
X_imputed = imputer.fit_transform(X)
# Tại sao sai: Lab không được order VÌ bệnh nhân ổn → missing ≠ average
# Mean imputation tạo ra fake "normal" values

# ✅ Đúng: indicator features
X['creatinine_missing'] = X['creatinine'].isna().astype(int)
X['creatinine'] = X['creatinine'].fillna(-1)  # sentinel value rõ ràng
# Model học được "missing itself" là informative
```

---

### ❌ Anti-pattern 7: Share Accounts giữa Nhân Viên

```
❌ Điều dưỡng chia sẻ login để tiết kiệm thời gian
  → HIPAA violation: audit log không biết ai làm gì
  → Không track được khi có sự cố

✅ Giải pháp: Proximity cards / badge tap-in SSO
  → Một tap badge → logged in với đúng user
  → Fast enough không gây friction
  → Đầy đủ audit trail
```

---

### ❌ Anti-pattern 8: PHI trong URL / Query String

```
❌ GET /api/patient?name=Nguyen+Van+A&dob=1980-01-01&ssn=123-45-6789
  → URLs được log trong web server logs
  → Appears in browser history
  → Logged by CDNs, load balancers
  → HIPAA violation

✅ POST /api/patient/search với body (HTTPS encrypted)
  { "identifiers": [{"system": "MRN", "value": "12345"}] }
```

---

## 3. Code Review Checklist cho Healthcare APIs

Dán vào PR template của team:

```markdown
## Healthcare Security Checklist

### PHI Handling
- [ ] Không có PHI trong log messages
- [ ] Không có PHI trong URL params hoặc query strings
- [ ] Error messages không leak patient info
- [ ] API chỉ trả về fields thực sự cần thiết (FHIR _elements filter)

### Authentication & Authorization
- [ ] Endpoint có auth check không?
- [ ] Authorization check ở resource level (không chỉ endpoint level)?
- [ ] Patient-level access control đã verify đúng patient?

### Audit Trail
- [ ] PHI access được logged (user, resource, timestamp, action)?
- [ ] Logs là append-only?
- [ ] Modification có ghi lại old value không?

### Data Integrity
- [ ] Soft delete (không hard delete PHI records)?
- [ ] Timestamp có sử dụng event time không (không phải processing time)?
- [ ] Terminology codes được validate không?
- [ ] HL7 message có idempotency check không?

### Availability
- [ ] External service calls có timeout không?
- [ ] External service failure có fail open không (đặc biệt CDS)?
- [ ] Circuit breaker được implement?

### ML/Analytics (nếu applicable)
- [ ] Training/test split theo temporal order?
- [ ] Missing data không imputed với mean?
- [ ] Model có explainability không?
```

---

## 4. Incident Severity Matrix — Healthcare

| Severity | Định nghĩa | Ví dụ | Response SLA |
|---------|-----------|-------|-------------|
| **P0 — Critical** | Patient safety risk ngay lập tức | CPOE down, medication system down, critical lab alert không gửi được | 15 phút |
| **P1 — High** | Ảnh hưởng care nhưng workaround có | EHR slow (>10s load), một module down | 1 giờ |
| **P2 — Medium** | Analytics, reporting bị ảnh hưởng | Dashboard không load, report sai | 4 giờ |
| **P3 — Low** | Cosmetic, non-clinical | UI bug không ảnh hưởng function | Next sprint |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 7 design patterns và tại sao mỗi cái cần thiết trong healthcare
- [ ] Tôi biết 8 anti-patterns nguy hiểm và cách fix từng cái
- [ ] Tôi có code review checklist để dùng ngay cho team
- [ ] Tôi hiểu Healthcare Severity Matrix — P0 = patient safety = 15 phút response

**Tiếp theo:** `08-career/06-mental-models.md` →