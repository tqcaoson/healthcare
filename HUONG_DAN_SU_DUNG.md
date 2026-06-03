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

## 8. Phát thuốc tại giường — Medication Scan

**URL:** `/pharmacy/medication-scan` (hoặc nút từ MAR)

> Dành cho điều dưỡng/dược sĩ thực hiện tại giường bệnh.  
> Áp dụng quy tắc **5 Rights** (đúng bệnh nhân, đúng thuốc, đúng liều, đúng đường, đúng giờ).

**Quy trình:**

1. **Quét mã vạch bệnh nhân** — vòng đeo tay hoặc thẻ MRN
2. **Quét mã vạch thuốc** — barcode trên hộp/lọ thuốc
3. Hệ thống kiểm tra 4 tiêu chí:

| Tiêu chí | Kết quả |
|---|---|
| Patient Match | ✓ / ✗ |
| Medication Match | ✓ / ✗ |
| Dose Match | ✓ / ✗ |
| Schedule Match | ✓ / ✗ |

4. **Nếu tất cả ✓** → nút **Administer Medication** được kích hoạt
5. Nhấn **Administer Medication** → hệ thống ghi nhận vào MAR, lưu audit log

> **Nếu có ✗** → xuất hiện cảnh báo đỏ "Không thể cho thuốc" — điều dưỡng cần kiểm tra lại và liên hệ dược sĩ.

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
      │                                         │
      │                                         ▼
      │                               [Phát thuốc tại giường]
      │                               (/pharmacy/medication-scan)
      │                               quét barcode → xác minh 5 Rights → ghi MAR
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
| Phát thuốc (MAR Scan) | — | ✓ | ✓ | ✓ |
| Xem Audit Log | — | — | — | ✓ |
| Quản lý người dùng | — | — | — | ✓ |

---

> **Phiên bản tài liệu:** 2026-06-03  
> **Hệ thống:** Laravel 11 / PHP 8.3 — FHIR R4 compatible
