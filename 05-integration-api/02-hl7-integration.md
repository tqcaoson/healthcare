## Mirth Connect Deep Dive & HL7 Integration Patterns

> Hướng dẫn thực hành Mirth Connect — interface engine phổ biến nhất healthcare.

---

### 1. Mirth Connect Architecture

```
┌──────────────────────────────────────────────┐
│                  CHANNEL                     │
│                                              │
│  [Source Connector]                          │
│  TCP Listener (MLLP) :6661                  │
│         ↓                                    │
│  [Source Transformer]  ← JavaScript/JS       │
│  Parse HL7 → extract fields → validate       │
│         ↓                                    │
│  [Filter]                                    │
│  if (MSH.9 != "ADT^A01") return false        │
│         ↓                                    │
│  [Destination 1: EHR]                        │
│  TCP Sender → ehr.hospital.org:7771          │
│         ↓                                    │
│  [Destination 2: LIS]                        │
│  TCP Sender → lis.hospital.org:7772          │
└──────────────────────────────────────────────┘
```

### 2. Mirth JavaScript Transformer — Thao tác HL7

```javascript
// Đọc field từ HL7 message
var patientName = msg['PID']['PID.5']['PID.5.1'].toString(); // Last name
var mrn = msg['PID']['PID.3']['PID.3.1'].toString();
var msgType = msg['MSH']['MSH.9']['MSH.9.1'].toString(); // ADT, ORM, etc.

// Thêm/sửa field
msg['PID']['PID.3']['PID.3.4'] = 'HOSPITAL_A'; // Set assigning authority

// Tạo segment mới
var nte = <NTE/>;
nte['NTE.3'] = 'Processed by Mirth Connect';
msg.appendChild(nte);

// Map terminology: local lab code → LOINC
var localCode = msg['OBX']['OBX.3']['OBX.3.1'].toString();
var loincMap = { 'GLU': '2345-7', 'HBA1C': '4548-4', 'CREAT': '2160-0' };
msg['OBX']['OBX.3']['OBX.3.1'] = loincMap[localCode] || localCode;
msg['OBX']['OBX.3']['OBX.3.3'] = 'LN'; // coding system = LOINC
```

### 3. Integration Patterns

| Pattern | Diagram | Use case |
|---------|---------|---------|
| **Hub-and-Spoke** | All → IE → All | Môi trường phức tạp, nhiều systems |
| **Publish-Subscribe** | Source → IE → broadcast | ADT → notify LIS + RIS + Pharmacy |
| **Content-Based Routing** | IE routes by MSH.9 | ADT^A01 → path A, ORU^R01 → path B |
| **Message Enrichment** | IE adds data from DB | Thêm provider NPI lookup trước khi forward |
| **Dead Letter Queue** | Failed → DLQ → alert | Retry logic; manual review queue |

### 4. HL7 v2 Troubleshooting Checklist

```
□ ACK không về → check MLLP framing (0x0B...0x1C0x0D)
□ Parse error → kiểm tra delimiter characters trong MSH.1-2
□ Encoding → HL7 v2 dùng ASCII; UTF-8 cần encode đặc biệt
□ Field truncation → check max field length per segment
□ Missing required fields → validate MSH.3,4,5,6,7,9,10,11,12
□ Timestamp → format YYYYMMDDHHmmss (no separators)
□ Timezone → HL7 v2.5+ support offset (+0700); trước đó không
```

---

