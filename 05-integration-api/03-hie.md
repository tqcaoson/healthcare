## Health Information Exchange & TEFCA

---

### 1. HIE là gì?

HIE = chia sẻ dữ liệu lâm sàng **giữa các tổ chức y tế khác nhau**.

**Vấn đề HIE giải quyết:**
> Bệnh nhân cấp cứu ở bệnh viện B — bác sĩ không biết bệnh nhân đang dùng warfarin từ bác sĩ gia đình ở bệnh viện A → nguy cơ chảy máu nếu phẫu thuật.

### 2. Mô hình HIE

| Mô hình | Cơ chế | Ưu điểm | Nhược điểm |
|---------|--------|---------|-----------|
| **Centralized** | Data copy về central repository | Query nhanh; data sẵn sàng | Privacy risk; governance phức tạp |
| **Federated** | Query trực tiếp đến từng nguồn | Không centralize data; privacy tốt | Query chậm; phụ thuộc uptime nguồn |
| **Hybrid** | Metadata/index central + query federated | Balance | Phức tạp implement |

### 3. IHE Profiles quan trọng

| Profile | Mô tả | Dùng để |
|---------|-------|---------|
| **XDS.b** | Cross-Enterprise Document Sharing | Share documents (CDA) giữa tổ chức |
| **PIX/PDQ** | Patient Identity Cross-reference | Match patient identity qua MPI |
| **XCA** | Cross-Community Access | Federated query nhiều HIE |
| **MHD** | Mobile access to Health Documents | FHIR-based document sharing |

### 4. TEFCA — National HIE Framework (Mỹ, 2022)

**TEFCA** = Trusted Exchange Framework and Common Agreement

Mục tiêu: Tạo nationwide interoperability network — bất kỳ authorized user nào cũng query được data bệnh nhân trên cả nước.

```
Architecture:
  Organization A  ──┐
  Organization B  ──┤──→  QHIN A  ──┐
  Organization C  ──┘               │──→  National Network
                                    │
  Organization D  ──┐               │
  Organization E  ──┤──→  QHIN B  ──┘
```

**QHIN** = Qualified Health Information Network — intermediaries kết nối các tổ chức.

| Exchange Purpose (TEFCA) | Ai được dùng |
|--------------------------|-------------|
| Treatment | Clinicians trực tiếp điều trị |
| Payment | Billing, insurance |
| Operations | Quality measures, analytics |
| Individual Access | Bệnh nhân xem data của mình |
| Public Health | Reporting đến cơ quan y tế |

---