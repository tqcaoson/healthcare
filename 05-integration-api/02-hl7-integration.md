# Chương — HL7 Integration: Mirth Connect & Integration Patterns

> **Mục tiêu:** Build production-grade HL7 interfaces bằng Mirth Connect — debug được message thực tế.

---

## 1. Mirth Connect Architecture

Mirth Connect (NextGen Connect) là open-source interface engine phổ biến nhất — miễn phí, mạnh, dùng rộng rãi.

```
┌─────────────────────────────────────────────────────────┐
│                     CHANNEL                             │
│                                                         │
│  ┌──────────────────┐                                   │
│  │  Source Connector │  ← nhận message vào              │
│  │  TCP (MLLP) :6661 │                                  │
│  └────────┬─────────┘                                   │
│           ↓                                             │
│  ┌──────────────────┐                                   │
│  │ Source Filter    │  ← có xử lý message này không?   │
│  │ (JavaScript)     │                                   │
│  └────────┬─────────┘                                   │
│           ↓                                             │
│  ┌──────────────────┐                                   │
│  │ Source Transformer│  ← transform message             │
│  │ (JavaScript/XML) │                                   │
│  └────────┬─────────┘                                   │
│           ↓                                             │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ Destination 1    │  │ Destination 2    │  ← gửi đi  │
│  │ TCP Sender (EHR) │  │ TCP Sender (LIS) │            │
│  └──────────────────┘  └──────────────────┘            │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Connector Types

### Source Connectors (nhận message)

| Connector | Protocol | Dùng khi |
|-----------|---------|---------|
| **TCP Listener** | MLLP/HL7 | Nhận HL7 v2 từ LIS, RIS, EHR |
| **HTTP Listener** | REST/SOAP | Nhận FHIR, CDA, hoặc custom API |
| **File Reader** | Filesystem | Batch import file (lab results, ADT feeds) |
| **Database Reader** | JDBC | Poll database table cho messages mới |
| **FHIR Listener** | FHIR REST | Nhận FHIR resources |
| **Email Reader** | IMAP/POP3 | Nhận order qua email (legacy systems) |

### Destination Connectors (gửi message)

| Connector | Dùng khi |
|-----------|---------|
| **TCP Sender** | Gửi HL7 đến receiver qua MLLP |
| **HTTP Sender** | Gửi FHIR, REST API calls |
| **Database Writer** | Insert/update trực tiếp vào DB |
| **File Writer** | Export ra file |
| **SMTP** | Gửi email alert khi có critical value |
| **JavaScript Writer** | Custom logic (gọi API phức tạp) |

---

## 3. JavaScript Transformer — Thao tác HL7

Mirth dùng E4X (XML extension trong JavaScript) để thao tác HL7.

```javascript
// ============================================================
// Đọc fields từ message
// ============================================================

// Message type (MSH-9)
var msgType = msg['MSH']['MSH.9']['MSH.9.1'].toString();  // 'ADT'
var msgEvent = msg['MSH']['MSH.9']['MSH.9.2'].toString(); // 'A01'

// Patient info từ PID
var mrn = msg['PID']['PID.3']['PID.3.1'].toString();
var lastName = msg['PID']['PID.5']['PID.5.1'].toString();
var firstName = msg['PID']['PID.5']['PID.5.2'].toString();
var dob = msg['PID']['PID.7']['PID.7.1'].toString();        // YYYYMMDD
var gender = msg['PID']['PID.8'].toString();                 // M/F/U

// ============================================================
// Sửa đổi fields
// ============================================================

// Set assigning authority
msg['PID']['PID.3']['PID.3.4'] = 'HOSPITAL_MAIN';

// Thay đổi timestamp sang format chuẩn
var rawDate = msg['MSH']['MSH.7'].toString(); // 20240115120000
msg['MSH']['MSH.7'] = rawDate.substring(0, 12); // truncate seconds

// ============================================================
// Thêm/xóa segments
// ============================================================

// Thêm NTE segment
var nte = <NTE/>;
nte['NTE.1'] = '1';
nte['NTE.3'] = 'Processed by Interface Engine v2.0';
msg.appendChild(nte);

// Xóa segment không cần
delete msg['ZZZ']; // xóa custom Z-segment

// ============================================================
// Terminology mapping (local → LOINC)
// ============================================================

var localCodeMap = {
    'GLU':    {code: '2345-7',  system: 'LN', name: 'Glucose'},
    'HBA1C':  {code: '4548-4',  system: 'LN', name: 'HbA1c'},
    'CREAT':  {code: '2160-0',  system: 'LN', name: 'Creatinine'},
    'WBC':    {code: '6690-2',  system: 'LN', name: 'WBC'},
    'SODIUM': {code: '2951-2',  system: 'LN', name: 'Sodium'},
};

// Apply to each OBX segment
for each (var obx in msg.OBX) {
    var localCode = obx['OBX.3']['OBX.3.1'].toString();
    if (localCodeMap[localCode]) {
        var mapped = localCodeMap[localCode];
        obx['OBX.3']['OBX.3.1'] = mapped.code;
        obx['OBX.3']['OBX.3.2'] = mapped.name;
        obx['OBX.3']['OBX.3.3'] = mapped.system;
    }
}

// ============================================================
// Filter logic (trong Source Filter tab)
// ============================================================

// Chỉ xử lý ADT A01 và A03
var msgType = msg['MSH']['MSH.9']['MSH.9.1'].toString();
var event = msg['MSH']['MSH.9']['MSH.9.2'].toString();

if (msgType == 'ADT' && (event == 'A01' || event == 'A03')) {
    return true;  // process
} else {
    return false; // discard
}
```

---

## 4. Integration Patterns

### Pattern 1: Hub-and-Spoke (Most Common)

```
           LIS ──────────┐
           RIS ──────────┤──→ Interface Engine ──→ EHR
           Pharmacy ──────┤                     ──→ Billing
           Registration ──┘                     ──→ Public Health

Ưu điểm:
- Mỗi system chỉ cần 1 connection đến IE
- Transform logic tập trung (dễ maintain)
- Monitoring tập trung

Nhược điểm:
- Single point of failure (phải HA)
- IE trở thành bottleneck nếu volume cao
```

### Pattern 2: Publish-Subscribe

```
ADT System ──→ IE ──→ Topic: "patient.admission"
                        ├──→ EHR (subscriber)
                        ├──→ LIS (subscriber)
                        ├──→ Pharmacy (subscriber)
                        └──→ Billing (subscriber)

Dùng cho: ADT events cần notify nhiều systems
Tool: Apache Kafka + Mirth; hoặc Azure Service Bus
```

### Pattern 3: Content-Based Routing

```javascript
// Trong Mirth Destination router
var msgType = msg['MSH']['MSH.9']['MSH.9.1'].toString();
var event = msg['MSH']['MSH.9']['MSH.9.2'].toString();

// Route ADT đến một destination
if (msgType == 'ADT') {
    destinationSet.add(0); // destination index 0 = EHR-ADT channel
}
// Route ORU đến destination khác
if (msgType == 'ORU') {
    destinationSet.add(1); // destination index 1 = EHR-Results channel
}
// Critical results thêm vào alert channel
if (msgType == 'ORU') {
    for each (var obx in msg.OBX) {
        var flag = obx['OBX.8'].toString();
        if (flag == 'HH' || flag == 'LL') {
            destinationSet.add(2); // critical alert channel
        }
    }
}
```

### Pattern 4: Dead Letter Queue (DLQ) — Bắt buộc production

```javascript
// Destination transformer — nếu gửi fail
try {
    // attempt delivery
    var response = sendToEndpoint(transformedMessage);
    if (!isAckSuccess(response)) {
        throw new Error('NACK received: ' + response);
    }
} catch(e) {
    // Log để alert on-call
    logger.error('Delivery failed: ' + e.message);
    logger.error('Message: ' + msg.toString());
    
    // Mirth tự động retry theo config
    // Sau N retries → message goes to Error state → DLQ queue
    // DLQ: engineers review, manual resend hoặc escalate
    throw e; // re-throw để Mirth track as error
}
```

---

## 5. HL7 Troubleshooting Checklist

```
□ Message không đến IE?
  → Check TCP port open (netstat -tlnp | grep 6661)
  → Check firewall rules
  → Test với telnet: telnet ehr.hospital.org 6661

□ ACK không về?
  → Check MLLP framing: 0x0B (start) và 0x1C 0x0D (end)
  → Nhiều systems quên 0x0D sau 0x1C

□ Parse error trong Mirth?
  → Check field separator (|) và component separator (^) trong MSH.1-2
  → HL7 v2 dùng | ^ ~ \ & (MSH.1 = |, MSH.2 = ^~\&)
  → Encoding issue: special chars trong tên bệnh nhân

□ Data không mapping đúng?
  → Double-check field numbering (PID.3 = field 3, 1-indexed)
  → Component: PID.5.1 = last name, PID.5.2 = first name
  → Repetition: PID.3[0] = first identifier, PID.3[1] = second

□ Encoding/charset issue?
  → HL7 v2 mặc định là 7-bit ASCII
  → Unicode/UTF-8: cần MSH.18 = UNICODE UTF-8
  → Vietnamese names cần check encoding carefully

□ Timestamp format?
  → YYYYMMDDHHMMSS (no separators)
  → Timezone: HL7 v2.5+ support offset: 20240115120000+0700
  → Versions trước không support timezone → normalize về UTC

□ Message order quan trọng?
  → HL7 messages phải process theo ORDER (ADT trước ORM)
  → Dùng sequence number tracking
  → Mirth: set channel queue để sequential processing
```

---

## 6. Performance & Monitoring

### Mirth Monitoring Dashboard — Metrics cần track

| Metric | Threshold cần alert | Ý nghĩa |
|--------|-------------------|---------|
| **Queued messages** | > 100 | Downstream system chậm hoặc down |
| **Error count** | > 0 trong 5 phút | Delivery failures |
| **Processing time** | > 1000ms average | Transform quá chậm |
| **Throughput** | < baseline - 20% | Volume drop bất thường |

### Message Volume Benchmarks (điển hình)

| Message Type | Volume điển hình | Peak |
|-------------|-----------------|------|
| ADT | 500–2000/day cho bệnh viện 300 giường | Shift changes (7am, 3pm, 11pm) |
| ORU (lab results) | 5000–20000/day | 6am–10am sau overnight labs |
| ORM (orders) | 2000–8000/day | Business hours |
| All combined | 10,000–50,000/day | Depends on size |

---

## 7. HIE Integration — Sharing với hệ thống bên ngoài

### IHE XDS.b — Document Sharing

```
Provider A                    XDS Registry/Repository               Provider B
──────────                    ────────────────────────              ──────────
Discharge summary   →  [Provide & Register]  →  Store CDA doc
                         (HTTP SOAP / MTOM)
                                                      ↓ (later)
                       [Query Registry]       ←  Search by patient
                       [Retrieve Document]    ←  Get document URL
                                              →  Download CDA doc
```

### FHIR-based HIE (Modern approach)

```
// Search for patient across HIE (FHIR $match operation)
POST /Patient/$match
{
  "resourceType": "Parameters",
  "parameter": [{
    "name": "resource",
    "resource": {
      "resourceType": "Patient",
      "name": [{"family": "Nguyen", "given": ["Van", "A"]}],
      "birthDate": "1980-01-01",
      "identifier": [{"system": "http://hospital-a.org/mrn", "value": "12345"}]
    }
  }]
}

// Query patient's documents from HIE
GET /DocumentReference?patient=Patient/global-id-123&type=18842-5  // discharge summaries
```

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 6 loại Source Connector và khi nào dùng mỗi loại
- [ ] Tôi có thể viết Mirth JavaScript transformer để đọc, sửa, map HL7 fields
- [ ] Tôi biết 4 integration patterns và khi nào dùng pattern nào
- [ ] Tôi biết troubleshoot được 6 loại HL7 errors phổ biến
- [ ] Tôi biết DLQ là gì và tại sao bắt buộc trong production

**Tiếp theo:** `05-integration-api/03-hie.md` →