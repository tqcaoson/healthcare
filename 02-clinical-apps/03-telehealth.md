# Chương 6 — Telehealth, mHealth & Internet of Medical Things

> **Mục tiêu:** Hiểu các loại telehealth, yêu cầu kỹ thuật bắt buộc, và kiến trúc RPM.

---

## 1. Phân loại Telehealth

| Loại | Mô tả | Công nghệ cần | Thách thức |
|------|-------|--------------|-----------|
| **Synchronous (Live)** | Video call real-time bác sĩ–bệnh nhân | WebRTC, HIPAA-compliant video | Bandwidth, latency, privacy |
| **Asynchronous (Store & Forward)** | Gửi ảnh/video/data để bác sĩ xem sau | Secure file upload, encrypted storage | Không real-time; phù hợp dermatology, radiology |
| **Remote Patient Monitoring (RPM)** | Thu thập vital signs từ thiết bị tại nhà | IoT sensors, wearables, data pipeline | Device integration, data volume, alert logic |
| **mHealth** | Ứng dụng di động hỗ trợ theo dõi sức khỏe | iOS/Android, wearable SDK | FDA regulation, data privacy |

---

## 2. Yêu cầu kỹ thuật bắt buộc khi xây dựng Telehealth

| Yêu cầu | Chi tiết |
|---------|---------|
| **HIPAA Compliance** | Video platform phải ký BAA; encryption in-transit và at-rest bắt buộc |
| **Authentication** | Multi-factor cho cả bác sĩ và bệnh nhân |
| **Audit Logging** | Ghi lại tất cả sessions — ai kết nối, khi nào, bao lâu |
| **EHR Integration** | Session notes phải tự động sync vào EHR — không gõ tay |
| **Prescribing Rules** | Telehealth prescription có quy định riêng từng bang |

---

## 3. Remote Patient Monitoring (RPM) Architecture

### RPM Technical Stack — Từ thiết bị đến bác sĩ

```
[Thiết bị đo]          [Smartphone/Hub]      [Cloud]              [Bác sĩ]
 ─────────────         ──────────────────    ─────────────────     ─────────
 Apple Watch      →    App/Gateway        →  IoT Platform      →   RPM Dashboard
 Glucometer            - Bluetooth LE         (AWS IoT,            trong EHR
 BP Cuff               - Offline storage       Azure IoT)
 Holter Monitor        - Batched upload       Time-series DB      Alert routing
 SpO2 Monitor                                 (InfluxDB)          Automated docs
                                             
                                             Processing Layer:
                                             - Anomaly detection (ML)
                                             - Threshold alerts
                                             - Trend analysis
```

**6 layers chi tiết:**

| Layer | Công nghệ | Vai trò |
|-------|-----------|---------|
| **Device** | Wearables, medical-grade devices | Collect data via Bluetooth LE/cellular |
| **Edge/Gateway** | Smartphone app / dedicated hub | Aggregate, local storage offline, batched upload |
| **Cloud Ingestion** | AWS IoT, Azure IoT Hub | Receive high-frequency data streams; time-series DB |
| **Processing** | ML models, rule engine | Real-time alerting; anomaly detection; trend analysis |
| **Clinical** | RPM dashboard in EHR | Alert routing đến care team; automated documentation |
| **Patient** | Patient-facing app | Hiển thị trends; medication reminders; 2-way comms |

---

## 4. mHealth — Phân loại theo mức độ rủi ro

| Loại | Mô tả | FDA Regulation | Ví dụ |
|------|-------|---------------|-------|
| **General Wellness** | Theo dõi thông thường; không claim điều trị | Không regulated | Step counter, sleep tracker |
| **Medical Device Software (low risk)** | Hỗ trợ chẩn đoán; CDSS bổ trợ | FDA 510(k) có thể cần | ECG analysis app |
| **SaMD (moderate-high risk)** | Tự động chẩn đoán; AI độc lập | FDA De Novo hoặc PMA | AI đọc ECG, insulin dosing |
| **Connected Medical Device** | Điều khiển thiết bị y tế vật lý | FDA Class II-III | Insulin pump control |

---

## 5. IoMT Security Challenges

| Thách thức | Ví dụ thực tế | Giải pháp |
|-----------|--------------|-----------|
| **Legacy OS** | Infusion pump chạy Windows XP | Network segmentation; compensating controls |
| **Hardcoded credentials** | Admin password mặc định trong firmware | Security review khi procurement |
| **Unencrypted transmission** | Vital sign gửi plain HTTP | TLS enforcement tại gateway |
| **Physical access** | USB port trên thiết bị tại phòng bệnh nhân | USB blocking; physical security |
| **Supply chain** | Firmware bị compromise tại nhà máy | Secure boot; code signing; SBOM |
| **No auth on device APIs** | Insulin pump accept commands qua Bluetooth | Security assessment trước deploy |

---

## 6. Regulatory Landscape cho mHealth

| Quy định | Tác động |
|---------|---------|
| **FDA Digital Health Center of Excellence** | Guidance cho AI/ML-based SaMD |
| **FTC Health Breach Notification Rule** | App thu thập health data phải notify khi breach — dù không phải Covered Entity |
| **ONC Interoperability Rule** | Health apps có quyền access patient data qua FHIR API |
| **EU MDR** | Nếu sell ở châu Âu → CE marking bắt buộc |

---

## ✅ Checklist sau chương này

- [ ] Tôi biết 4 loại telehealth và khi nào dùng loại nào
- [ ] Tôi nhớ 5 yêu cầu kỹ thuật bắt buộc khi build telehealth
- [ ] Tôi hiểu RPM architecture có 6 layers
- [ ] Tôi biết khi nào app mHealth cần FDA approval
- [ ] Tôi hiểu IoMT tạo ra attack surface gì và cách mitigate

**Tiếp theo:** `03-standards-data/01-hl7v2.md` →