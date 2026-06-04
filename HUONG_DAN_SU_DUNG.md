# Hướng dẫn sử dụng Hệ thống HIS trên trình duyệt

> Hệ thống EHR/HIS dành cho bệnh viện/phòng khám — giao diện web Laravel 11.
> URL mặc định: `http://localhost` (hoặc domain triển khai của cơ sở)

---

## Mục lục

1. [Đăng nhập](#1-đăng-nhập)
2. [Dashboard — Tổng quan](#2-dashboard--tổng-quan)
3. [Quản lý bệnh nhân](#3-quản-lý-bệnh-nhân)
4. [Lần khám (Encounter)](#4-lần-khám-encounter)
5. [Đặt lệnh CPOE](#5-đặt-lệnh-cpoe)
6. [Kết quả xét nghiệm (Lab Results)](#6-kết-quả-xét-nghiệm-lab-results)
7. [Thuốc & Dược (Medications)](#7-thuốc--dược-medications)
8. [Phát thuốc tại giường — Medication Scan](#8-phát-thuốc-tại-giường--medication-scan)
9. [Lịch hẹn (Appointments)](#9-lịch-hẹn-appointments)
10. [Báo cáo (Reports)](#10-báo-cáo-reports)
11. [Quản trị — Audit & Users](#11-quản-trị--audit--users)
12. [Flow tổng thể từ đầu đến cuối](#12-flow-tổng-thể-từ-đầu-đến-cuối)

---

## 1. Đăng nhập

**URL:** `/login`

1. Nhập **email** và **mật khẩu**.
2. Nhấn **Đăng nhập**.
3. Hệ thống kiểm tra quyền và chuyển hướng về `/dashboard`.

> **Lưu ý bảo mật:** Phiên làm việc tự động hết hạn sau thời gian không hoạt động (cấu hình trong `SESSION_LIFETIME`). Một cửa sổ đếm ngược sẽ xuất hiện trước khi hết phiên.

**Sau đăng nhập → [Dashboard](#2-dashboard--tổng-quan)**

---

## 2. Dashboard — Tổng quan

**URL:** `/dashboard`

Hiển thị 3 chỉ số chính dưới dạng thẻ:

| Thẻ | Ý nghĩa |
|---|---|
| **Tổng bệnh nhân** | Tổng số hồ sơ bệnh nhân trong hệ thống |
| **Đang điều trị** | Số lần khám có trạng thái `in-progress` |
| **Nhập viện hôm nay** | Số lần khám mở trong ngày hiện tại |

**Từ Dashboard, điều hướng sang:**
- Sidebar → **Bệnh nhân** để xem/thêm hồ sơ
- Sidebar → **Lần khám** để xem ca đang điều trị
- Sidebar → **Lệnh** để đặt chỉ định mới

---

## 3. Quản lý bệnh nhân

### 3.1 Danh sách bệnh nhân

**URL:** `/patients`

- Bảng hiển thị: MRN, Họ tên, Ngày sinh, Giới tính, SĐT, Trạng thái.
- **Tìm kiếm:** Gõ tên hoặc MRN vào ô tìm → nhấn **Tìm kiếm**.
- Nhấn **Xoá lọc** để về danh sách đầy đủ.

**Thao tác trên từng dòng (phụ thuộc quyền):**

| Nút | Hành động |
|---|---|
| **Xem** | Mở hồ sơ chi tiết bệnh nhân |
| **Sửa** | Chỉnh sửa thông tin nhân khẩu |
| **Xoá** | Vô hiệu hoá hồ sơ (soft-disable, không xoá vật lý) |

### 3.2 Thêm bệnh nhân mới

**URL:** `/patients/create`  
Nhấn nút **+ Thêm bệnh nhân** ở góc trên phải.

Điền các trường:
- Họ (`family_name`), Tên (`given_name`)
- Ngày sinh, Giới tính
- Số điện thoại, Email
- Địa chỉ

Nhấn **Lưu** → hệ thống tạo MRN tự động và chuyển về trang hồ sơ.

### 3.3 Hồ sơ chi tiết bệnh nhân

**URL:** `/patients/{id}`

Hiển thị theo dạng lưới 3 cột:

| Cột | Nội dung |
|---|---|
| **Dị ứng** (đỏ) | Danh sách dị ứng + mức độ |
| **Bệnh lý hiện tại** | Các chẩn đoán đang active |
| **Thông tin liên lạc** | Địa chỉ, SĐT, email |

**Từ hồ sơ bệnh nhân → tiếp theo:**
- Nhấn **Chỉnh sửa** → `/patients/{id}/edit`
- Vào **Lần khám** liên quan → click tên encounter trong danh sách

---

## 4. Lần khám (Encounter)

### 4.1 Danh sách lần khám

**URL:** `/encounters`

Bảng liệt kê tất cả lần khám: bệnh nhân, loại, trạng thái, thời gian.

### 4.2 Tạo lần khám mới

**URL:** `/encounters/create`

Điền:
- **Bệnh nhân** — chọn từ danh sách
- **Loại lần khám:** `ambulatory` (ngoại trú), `inpatient` (nội trú), `emergency` (cấp cứu)...
- **Lý do khám** (reason_text)
- **Thời gian bắt đầu**

Nhấn **Lưu** → tạo encounter, trạng thái ban đầu là `planned` hoặc `in-progress`.

### 4.3 Chi tiết lần khám

**URL:** `/encounters/{id}`

Hiển thị:
- **Banner bệnh nhân:** tên, MRN, ngày sinh (link về hồ sơ bệnh nhân)
- **Thông tin khám:** loại, trạng thái, class, thời gian, lý do
- **Chẩn đoán** (ICD-10)
- **Lệnh đã đặt** trong lần khám này
- **Ghi chú lâm sàng**

**Từ chi tiết lần khám → tiếp theo:**
- Nhấn **Đặt lệnh mới** → [CPOE](#5-đặt-lệnh-cpoe)
- Nhấn **Xem hồ sơ →** → về hồ sơ bệnh nhân

---

## 5. Đặt lệnh CPOE

> CPOE = Computerized Physician Order Entry — nhập lệnh điện tử của bác sĩ.

### 5.1 Danh sách lệnh

**URL:** `/orders`

Bảng hiển thị tất cả lệnh: bệnh nhân, loại, mức ưu tiên, trạng thái, người đặt.

### 5.2 Đặt lệnh mới

**URL:** `/orders/create` hoặc `/orders/place-order`

**Bước 1 — Chọn lần khám:**
- Dropdown liệt kê các encounter đang điều trị (`in-progress`)
- Chọn đúng lần khám của bệnh nhân cần đặt lệnh

**Bước 2 — Chọn loại lệnh:**

| Loại | Mô tả |
|---|---|
| `lab` | Xét nghiệm |
| `radiology` | Hình ảnh (X-quang, CT, MRI...) |
| `medication` | Thuốc |
| `nursing` | Điều dưỡng |
| `diet` | Chế độ ăn |
| `referral` | Chuyển khoa |
| `procedure` | Thủ thuật |

**Bước 3 — Mức ưu tiên:**
- `routine` — thông thường
- `urgent` — khẩn
- `stat` — cấp cứu ngay

**Bước 4 — Điền chi tiết lệnh** (tuỳ loại): tên xét nghiệm, loại thuốc, liều dùng...

**Bước 5 — Nhấn Đặt lệnh** → hệ thống chạy CDS Alert (kiểm tra tương tác thuốc, chống chỉ định) trước khi lưu.

> **CDS Alert:** Nếu hệ thống phát hiện cảnh báo lâm sàng, một hộp thoại sẽ hiện lên yêu cầu bác sĩ xác nhận hoặc huỷ lệnh.

**Sau khi đặt lệnh:**
- Lệnh `medication` → kích hoạt quy trình [Phát thuốc](#8-phát-thuốc-tại-giường--medication-scan)
- Lệnh `lab` → chờ kỹ thuật viên nhập [Kết quả xét nghiệm](#6-kết-quả-xét-nghiệm-lab-results)

---

## 6. Kết quả xét nghiệm (Lab Results)

**URL:** `/results`

### 6.1 Xem danh sách kết quả

Bộ lọc:
- **Loại cờ:** Tất cả / ⚠ Nguy kịch (Critical) / Bất thường (Abnormal)
- **Khoảng thời gian:** 24h / 7 ngày / 30 ngày / 90 ngày

Bảng hiển thị: Bệnh nhân, Tên xét nghiệm, Giá trị, Khoảng tham chiếu, Cờ, Thời gian, Trạng thái.

> **Kết quả Critical** được tô nền đỏ — hệ thống tự động gửi thông báo đến bác sĩ phụ trách qua queue job `NotifyCriticalLabValue`.

### 6.2 Nhập kết quả mới

**URL:** `/results/create`  
Nhấn **+ Nhập kết quả**.

Điền:
- Bệnh nhân (hoặc lần khám)
- Tên xét nghiệm, Giá trị, Đơn vị
- Khoảng tham chiếu (reference range)
- Cờ: `normal` / `abnormal` / `critical`
- Trạng thái: `preliminary` / `final`

Nhấn **Lưu** → nếu cờ là `critical`, hệ thống tự gửi cảnh báo.

---

## 7. Thuốc & Dược (Medications)

### 7.1 Danh sách đơn thuốc

**URL:** `/medications`

Hiển thị tất cả đơn thuốc đã kê: bệnh nhân, thuốc, liều, tần suất, trạng thái.

### 7.2 Kê đơn thuốc mới

**URL:** `/medications/create`

> Thường được tạo tự động khi đặt lệnh `medication` qua CPOE.  
> Cũng có thể tạo trực tiếp tại đây.

Điền:
- **Lần khám** liên kết
- **Tên thuốc** (medication name)
- **Liều dùng** (dose + đơn vị)
- **Đường dùng** (route): uống, tiêm, nhỏ giọt...
- **Tần suất** (frequency): 1 lần/ngày, 2 lần/ngày...
- **Ngày bắt đầu / kết thúc**

Nhấn **Kê đơn** → đơn thuốc chuyển sang hàng đợi dược.

### 7.3 Chi tiết đơn thuốc

**URL:** `/medications/{id}`

Xem lịch sử phát thuốc (MAR — Medication Administration Record): ai phát, lúc nào, ghi chú.

---

## 8. Phát thuốc tại giường — MAR Scan

**URL giao diện:** `/pharmacy/medication-scan` → chọn bệnh nhân  
**URL trực tiếp:** `/pharmacy/medication-scan?encounter_id={id}` → vào thẳng lần khám

> **Quyền truy cập:** Điều dưỡng (`nurse`), Bác sĩ (`physician`), Quản trị (`superadmin`).  
> Dược sĩ chỉ verify order, không ghi nhận phát thuốc tại giường.

Tính năng áp dụng **5 Rights** (đúng bệnh nhân · đúng thuốc · đúng liều · đúng đường dùng · đúng giờ) thông qua quét barcode bằng camera thiết bị.

---

### 8.1 Giao diện tổng quan

Màn hình chia 2 cột:

```
┌─────────────────────────────┬──────────────────────────────┐
│   Camera / Barcode Scanner  │       Verification Panel     │
│                             │                              │
│   ┌─────────────────────┐   │  Patient Match     [✓ / ✗]  │
│   │  [khung đỏ quét mã] │   │  Medication Match  [✓ / ✗]  │
│   └─────────────────────┘   │  Dose Match        [✓ / ✗]  │
│                             │  Schedule Match    [✓ / ✗]  │
│   Waiting for scan...       │                              │
│                             │  ⚠ (cảnh báo nếu có ✗)     │
│                             │                              │
│                             │  [ Administer Medication ]   │
│                             │   (khả dụng khi tất cả ✓)   │
└─────────────────────────────┴──────────────────────────────┘
```

---

### 8.2 Quy trình từng bước

#### Bước 1 — Mở MAR Schedule

Truy cập `/pharmacy/medication-scan?encounter_id=<id>` hoặc nhấn nút **MAR Scan** từ trang chi tiết lần khám.

Hệ thống tự động tải danh sách thuốc đang active của lần khám, hiển thị:

| Cột | Ý nghĩa |
|---|---|
| Tên thuốc / Liều | drug_name + dose_value + dose_unit + route |
| Tần suất | frequency (BID, TID, q8h...) |
| Dược sĩ xác nhận | ✓ nếu đã `verified_by_pharmacist` |
| Trạng thái | **DUE** (đến giờ) · **OVERDUE** (quá giờ) · chờ |
| Lần phát gần nhất | administered_at của MAR record cuối |

Nhấn **Chọn** trên dòng thuốc cần phát → chuyển sang Bước 2.

#### Bước 2 — Quét vòng tay bệnh nhân (Right Patient)

Camera bật lên, hướng vào **vòng đeo tay** hoặc **thẻ MRN** của bệnh nhân.

- Barcode được đọc → hệ thống gọi `POST /api/v1/mar/scan/patient`
- **Nếu MRN khớp** → `Patient Match ✓` (xanh), chuyển sang quét thuốc
- **Nếu sai MRN** → cảnh báo đỏ _"Patient identity mismatch — barcode không khớp"_ — **dừng lại**, xác nhận lại bệnh nhân

> **Lý do quan trọng:** Bước này ngăn phát thuốc nhầm người. Sai ở đây → toàn bộ flow bị chặn.

#### Bước 3 — Quét hộp thuốc (Right Medication)

Sau khi `Patient Match ✓`, hướng camera vào **barcode GS1/DataMatrix** trên hộp hoặc lọ thuốc.

Hỗ trợ 3 định dạng barcode:

| Định dạng | Ví dụ | Cách nhận dạng |
|---|---|---|
| GS1-128 với AI (01) | `(01)00861004123456` | GTIN-14 → trích NDC |
| RxNorm code thuần | `861004` | Chuỗi 5–9 chữ số |
| Tên thuốc (fallback) | `Metformin` | So khớp drug_name |

- **Nếu khớp order** → `Medication Match ✓` + `Dose Match ✓` (xanh)
- Nếu order đến giờ phát → `Schedule Match ✓`
- Nếu ngoài cửa sổ ±60 phút → `Schedule Match ✗` nhưng **không bị chặn** — hệ thống ghi chú deviation trong MAR

#### Bước 4 — Xác nhận phát thuốc

Khi **cả 4 tiêu chí ✓**, nút **Administer Medication** chuyển sang màu xanh và có thể nhấn.

Nhấn nút:
1. Hệ thống gọi `POST /api/v1/mar/administer`
2. Ghi nhận `MedicationAdministration` với flag `patient_verified_by_barcode = true`, `medication_verified_by_barcode = true`
3. Audit log ghi sự kiện `medication.administered` (người dùng, bệnh nhân, thuốc, liều, giờ)
4. Event `MedicationAdministered` dispatch → listener trừ tồn kho (`DeductMedicationInventory`)
5. Màn hình hiển thị thông báo xanh: _"Đã phát thuốc [Tên thuốc] thành công lúc [giờ]"_
6. MAR Schedule tự động reload — dòng thuốc vừa phát cập nhật "Lần phát gần nhất"

---

### 8.3 Các trường hợp đặc biệt

#### Thuốc PRN (khi cần)

Nếu order có `is_prn = true`, trước khi nhấn **Administer** cần nhập thêm:
- **Chỉ định dùng PRN** (prn_indication): ví dụ _"Đau mức 7/10 sau phẫu thuật"_

Trường này bắt buộc — hệ thống từ chối nếu bỏ trống.

#### Thuốc có wasted amount (hủy phần thừa)

Nếu liều phải hủy (ví dụ chia lọ thuốc gây nghiện), điền thêm:
- **Lượng hủy** (wasted_amount)
- **Người chứng kiến** (waste_witnessed_by) — bắt buộc điền user ID người chứng kiến

#### Phát ngoài giờ (Late Administration)

Nếu `Schedule Match ✗` nhưng điều dưỡng vẫn phát:
- Hệ thống vẫn cho phép nhấn **Administer Medication**
- Ghi chú tự động thêm vào trường `notes`: `[LATE: administered outside scheduled window]`
- Báo cáo sai lệch giờ phát có thể xem tại `/reports/medication-errors`

---

### 8.4 API endpoints (dành cho tích hợp / mobile)

| Method | Endpoint | Mô tả |
|--------|----------|--------|
| `GET` | `/api/v1/mar/schedule?encounter_id=X` | Lịch phát thuốc của lần khám |
| `POST` | `/api/v1/mar/scan/patient` | Xác minh barcode bệnh nhân |
| `POST` | `/api/v1/mar/scan/medication` | Xác minh barcode thuốc |
| `POST` | `/api/v1/mar/administer` | Ghi nhận phát thuốc (5 Rights) |

Tất cả yêu cầu header `Authorization: Bearer {token}` (Sanctum) và middleware `audit.phi`.

**Ví dụ — Xác minh bệnh nhân:**
```http
POST /api/v1/mar/scan/patient
Content-Type: application/json

{
  "barcode": "MRN-2024-0042",
  "patient_id": 7
}
```
```json
{
  "verified": true,
  "patient": { "id": 7, "mrn": "MRN-2024-0042", "uuid": "..." }
}
```

**Ví dụ — Ghi nhận phát thuốc:**
```http
POST /api/v1/mar/administer
Content-Type: application/json

{
  "order_id": 15,
  "patient_id": 7,
  "encounter_id": 3,
  "patient_barcode": "MRN-2024-0042",
  "medication_barcode": "861004",
  "drug_name": "Metformin 500mg",
  "rxnorm_code": "861004",
  "dose_value": 500,
  "dose_unit": "mg",
  "route": "oral"
}
```
```json
{
  "data": {
    "uuid": "...",
    "drug_name": "Metformin 500mg",
    "administered_at": "2026-06-04T08:30:00+07:00",
    "patient_verified_by_barcode": true,
    "medication_verified_by_barcode": true,
    "status": "completed"
  }
}
```

---

## 9. Lịch hẹn (Appointments)

### 9.1 Danh sách lịch hẹn

**URL:** `/appointments`

Chế độ xem:
- **Danh sách** — bảng liệt kê theo ngày
- **Lịch (Calendar)** — `/appointments/calendar` — xem theo tháng/tuần

### 9.2 Đặt lịch hẹn mới

**URL:** `/appointments/create`

Điền:
- Bệnh nhân
- Bác sĩ / chuyên khoa
- Ngày giờ hẹn
- Lý do / ghi chú

Nhấn **Lưu** → hệ thống gửi thông báo nhắc nhở tự động (`AppointmentReminder`) trước giờ hẹn.

### 9.3 Sửa / Huỷ lịch hẹn

- Nhấn **Sửa** trên dòng lịch hẹn → chỉnh ngày giờ
- Huỷ: đổi trạng thái sang `cancelled`

---

## 10. Báo cáo (Reports)

**URL:** `/reports`

Trang tổng hợp số liệu vận hành:

- Thống kê bệnh nhân nhập/xuất viện theo kỳ
- Tỷ lệ kết quả xét nghiệm bất thường
- Chỉ số chất lượng lâm sàng (Quality Metrics)
- Xuất báo cáo (print layout tại `/reports/print-layout`)

---

## 11. Quản trị — Audit & Users

### 11.1 Nhật ký truy cập PHI (Audit Log)

**URL:** `/admin/audit`

> Chỉ dành cho Admin / HIPAA Compliance Officer.

Hiển thị mọi lần truy cập dữ liệu nhạy cảm (PHI):
- Người dùng, thời gian, hành động
- Bệnh nhân / tài nguyên bị truy cập
- IP, User-Agent

### 11.2 Quản lý người dùng

**URL:** `/admin/users`

- Xem danh sách tài khoản
- Tạo tài khoản mới, phân quyền role (`physician`, `nurse`, `pharmacist`, `admin`...)
- Vô hiệu hoá tài khoản

---

## 12. Flow tổng thể từ đầu đến cuối

Dưới đây là luồng hoàn chỉnh cho một ca điều trị nội trú điển hình:

```
[Đăng nhập] (/login)
      │
      ▼
[Dashboard] (/dashboard) — xem tổng quan ca đang điều trị
      │
      ▼
[Thêm bệnh nhân] (/patients/create)  ← nếu bệnh nhân mới
      │   hoặc tìm bệnh nhân cũ (/patients?search=...)
      ▼
[Hồ sơ bệnh nhân] (/patients/{id})
      │  xem dị ứng, bệnh lý nền trước khi khám
      ▼
[Tạo lần khám] (/encounters/create)
      │  chọn bệnh nhân + loại (inpatient/ambulatory/emergency)
      ▼
[Chi tiết lần khám] (/encounters/{id})
      │
      ├──► [Đặt lệnh CPOE] (/orders/create)
      │         │
      │         ├── lệnh `lab`  ─────────► [Nhập kết quả] (/results/create)
      │         │                                │
      │         │                                └── nếu critical → thông báo tự động
      │         │
      │         └── lệnh `medication` ──► [Kê đơn thuốc] (/medications)
      │                                         │  (dược sĩ verify order)
      │                                         ▼
      │                               [MAR Schedule]
      │                               (/pharmacy/medication-scan?encounter_id=X)
      │                               chọn thuốc → Bước 1: quét vòng tay → Patient Match ✓
      │                                                    Bước 2: quét hộp thuốc → Med/Dose/Schedule ✓
      │                                                    Bước 3: [Administer] → ghi MAR + audit + tồn kho
      │
      ├──► [Lịch hẹn tái khám] (/appointments/create)
      │
      └──► [Kết thúc lần khám] (/encounters/{id}/edit)
                status → `finished` + ghi ngày xuất viện

[Báo cáo] (/reports) — tổng kết định kỳ
[Audit Log] (/admin/audit) — giám sát truy cập PHI
```

### Sơ đồ quyền theo role

| Tính năng | Physician | Nurse | Pharmacist | Admin |
|---|:---:|:---:|:---:|:---:|
| Xem hồ sơ bệnh nhân | ✓ | ✓ | ✓ | ✓ |
| Thêm/sửa bệnh nhân | ✓ | ✓ | — | ✓ |
| Tạo lần khám | ✓ | ✓ | — | ✓ |
| Đặt lệnh CPOE | ✓ | — | — | ✓ |
| Nhập kết quả xét nghiệm | — | ✓ | — | ✓ |
| Kê đơn thuốc | ✓ | — | ✓ | ✓ |
| Xem MAR Schedule | — | ✓ | ✓ | ✓ |
| Phát thuốc (MAR Scan) | ✓ | ✓ | — | ✓ |
| Xem Audit Log | — | — | — | ✓ |
| Quản lý người dùng | — | — | — | ✓ |

---

> **Phiên bản tài liệu:** 2026-06-04  
> **Hệ thống:** Laravel 11 / PHP 8.3 — FHIR R4 compatible
